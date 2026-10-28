<?php
namespace App\Http\Controllers;
use App\Support\Catalog;
use Illuminate\Http\Request;
class WebsiteController extends Controller
{
 public function __construct(private Catalog $catalog) {}
 public function home() { return view('pages.home',['products'=>$this->catalog->featuredProducts(),'projects'=>$this->catalog->projects()->take(3)]); }
 public function offer(Request $request,?string $category=null) {
  $validated=$request->validate(['q'=>['nullable','string','max:100'],'sort'=>['nullable','in:default,price-asc,price-desc']]);
  $selected=$category ? $this->catalog->category($category):null;
  $products=$this->catalog->products();
  if($selected) $products=$products->where('category',$selected['key']);
  $query=trim($validated['q']??'');
  if($query!=='') $products=$products->filter(fn($p)=>mb_stripos($p['name'].' '.$p['description'].' '.$p['label'],$query)!==false);
  $sort=$validated['sort']??'default';
  if($sort==='price-asc') $products=$products->sortBy(fn($p)=>$p['price']??PHP_INT_MAX);
  elseif($sort==='price-desc') $products=$products->sortBy(fn($p)=>$p['price']===null?PHP_INT_MAX:-$p['price']);
  return view('pages.offer',compact('products','selected','query','sort'));
 }
 public function product(string $slug) {
  $product=$this->catalog->product($slug);$related=$this->catalog->products()->where('slug','!=',$slug)->take(3);
  return view('pages.product',compact('product','related'));
 }
 public function projects(Request $request) {
  $category=$request->query('kategoria');
  abort_if($category!==null && (!is_string($category)||!$this->catalog->categories()->has($category)),404);
  $projects=$this->catalog->projects();if($category) $projects=$projects->where('category',$category);
  return view('pages.projects',compact('projects','category'));
 }
 public function project(string $slug) { $project=$this->catalog->projects()->firstWhere('slug',$slug)??abort(404);return view('pages.project',compact('project')); }
 public function contact(Request $request) {
  $model=$request->query('model');$products=$this->catalog->products();
  if(!is_string($model)||!$products->contains('slug',$model)) $model='';
  return view('pages.contact',['products'=>$products,'model'=>$model,'contactAvailable'=>$this->catalog->contactAvailable()]);
 }
}
