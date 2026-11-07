<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;
class CategoryController extends Controller {
 public function index(){return view('admin.categories.index',['categories'=>Category::orderBy('sort_order')->orderBy('name')->get()]);}
 public function create(){return view('admin.categories.form',['category'=>new Category(['is_active'=>true,'sort_order'=>0,'image'=>'domek.jpeg'])]);}
 public function edit(Category $category){return view('admin.categories.form',compact('category'));}
 public function store(Request $request){return $this->save($request,new Category);}
 public function update(Request $request,Category $category){return $this->save($request,$category);}
 public function toggle(Category $category){$category->is_active=!$category->is_active;$category->save();return to_route('admin.categories.index')->with('success',$category->is_active?'Włączono kategorię.':'Wyłączono kategorię i ukryto jej wpisy na stronie.');}
 private function save(Request $request,Category $category){
  if(!$request->filled('slug') && is_string($request->input('name'))){
   $base=rtrim(substr(Str::slug($request->input('name')) ?: 'kategoria',0,190),'-');$slug=$category->exists?$category->slug:$base;$suffix=2;
   while(Category::where('slug',$slug)->when($category->exists,fn($q)=>$q->where('key','!=',$category->key))->exists())$slug=$base.'-'.$suffix++;
   $request->merge(['slug'=>$slug]);
  }
  $data=$request->validate([
   'name'=>['required','string','max:200'],'slug'=>['required','string','max:200','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',Rule::unique('categories','slug')->ignore($category->key,'key')],
   'description'=>['nullable','string','max:3000'],'sort_order'=>['required','integer','min:0','max:99999'],'is_active'=>['required','boolean'],
   'image'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:'.config('images.max_upload_kb'),'dimensions:max_width=6000,max_height=6000'],
  ]);
  $upload=null;
  try {
   unset($data['image']);
   if($request->hasFile('image')){$upload=app(ImageProcessor::class)->store($request->file('image'));if(!$upload)throw new \RuntimeException('Nie udało się zapisać zdjęcia.');$data['image']=$upload;}
   if(!$category->exists){$category->key=(string)Str::uuid();$data['image']??='domek.jpeg';}
   DB::transaction(function()use($category,$data){$category->fill($data)->save();Product::withTrashed()->where('category',$category->key)->update(['label'=>$category->name]);});
  } catch(Throwable $exception){if($upload)Storage::disk('public')->delete($upload);throw $exception;}
  return to_route('admin.categories.edit',$category)->with('success','Zapisano kategorię.');
 }
}
