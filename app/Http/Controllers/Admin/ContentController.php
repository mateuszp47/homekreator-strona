<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;
class ContentController extends Controller {
 private function model(string $kind): string {return match($kind){'produkty'=>Product::class,'realizacje'=>Project::class,default=>abort(404)};}
 public function index(Request $request,string $kind) {
  $class=$this->model($kind);$archived=$request->boolean('archiwum');
  $query=$archived?$class::onlyTrashed():$class::query();
  $items=$query->orderBy('sort_order')->orderByDesc('id')->paginate(20)->withQueryString();
  return view('admin.index',compact('items','kind','archived'));
 }
 public function create(string $kind) { $class=$this->model($kind);return $this->form($kind,new $class); }
 public function edit(string $kind,int $id) {return $this->form($kind,$this->model($kind)::findOrFail($id));}
 private function form(string $kind,$item) {return view('admin.form',['kind'=>$kind,'item'=>$item,'products'=>Product::orderBy('name')->get(),'categories'=>config('homekreator.categories')]);}
 public function store(Request $request,string $kind) { $class=$this->model($kind);return $this->save($request,$kind,new $class); }
 public function update(Request $request,string $kind,int $id) {return $this->save($request,$kind,$this->model($kind)::findOrFail($id));}
 private function save(Request $request,string $kind,$item) {
  $product=$kind==='produkty';$table=$product?'products':'projects';
  $rules=[
   'name'=>['required','string','max:200'],'slug'=>['required','string','max:200','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',Rule::unique($table,'slug')->ignore($item->id)],
   'category'=>['required',Rule::in(array_keys(config('homekreator.categories')))],'description'=>['required','string','max:15000'],
   'is_published'=>['nullable','boolean'],'sort_order'=>['required','integer','min:0','max:99999'],
   'image'=>[$item->exists?'nullable':'required','image','mimes:jpg,jpeg,png,webp','max:8192','dimensions:max_width=6000,max_height=6000'],
   'gallery'=>['nullable','array','max:15'],'gallery.*'=>['image','mimes:jpg,jpeg,png,webp','max:8192','dimensions:max_width=6000,max_height=6000'],
   'remove_gallery'=>['nullable','array'],'remove_gallery.*'=>['string',Rule::in($item->gallery??[])],
  ];
  if($product) $rules += ['size'=>['required','string','max:100'],'area'=>['required','string','max:150'],'price'=>['nullable','integer','min:0','max:100000000'],'full_price'=>['nullable','integer','min:0','max:100000000'],'equipment_text'=>['nullable','string','max:10000']];
  else $rules['product_id']=['nullable',Rule::exists('products','id')->whereNull('deleted_at')];
  $data=$request->validate($rules, ['required'=>'Uzupełnij pole :attribute.','unique'=>'Ten adres jest już zajęty.','image'=>'Wybierz poprawny plik graficzny.','mimes'=>'Dozwolone formaty: JPG, PNG i WebP.','max'=>'Przekroczono dozwolony rozmiar lub długość pola :attribute.','regex'=>'Adres może zawierać małe litery, cyfry i pojedyncze myślniki.']);
  $uploads=[];
  try {
   $image=$item->image;
   if($request->hasFile('image')){$image=$request->file('image')->store('catalog','public');if(!$image)throw new \RuntimeException('Nie udało się zapisać zdjęcia.');$uploads[]=$image;}
   $gallery=array_values(array_diff($item->gallery??[],$data['remove_gallery']??[]));
   foreach($request->file('gallery',[]) as $file){$path=$file->store('catalog','public');if(!$path)throw new \RuntimeException('Nie udało się zapisać zdjęcia.');$uploads[]=$path;$gallery[]=$path;}
   $attributes=collect($data)->except(['image','gallery','remove_gallery','equipment_text'])->all();
   $attributes['image']=$image;$attributes['gallery']=array_values(array_unique([$image,...$gallery]));$attributes['is_published']=$request->boolean('is_published');
   if($product){$attributes['label']=config('homekreator.categories.'.$data['category'].'.name');$attributes['equipment']=array_values(array_filter(array_map('trim',preg_split('/\r\n|\r|\n/',$data['equipment_text']??''))));}
   DB::transaction(fn()=>$item->fill($attributes)->save());
  } catch(Throwable $exception){Storage::disk('public')->delete($uploads);throw $exception;}
  return to_route('admin.content.edit',[$kind,$item->id])->with('success','Zapisano zmiany. '.($item->is_published?'Wpis jest opublikowany.':'Wpis jest szkicem.'));
 }
 public function destroy(string $kind,int $id) {$this->model($kind)::findOrFail($id)->delete();return to_route('admin.content.index',$kind)->with('success','Przeniesiono do archiwum. Wpis możesz przywrócić.');}
 public function restore(string $kind,int $id) {$item=$this->model($kind)::onlyTrashed()->findOrFail($id);$item->is_published=false;$item->restore();return to_route('admin.content.edit',[$kind,$id])->with('success','Przywrócono jako szkic. Sprawdź treść i zaznacz publikację.');}
}
