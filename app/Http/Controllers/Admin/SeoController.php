<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\Seo;
use Illuminate\Http\Request;
class SeoController extends Controller {
 public function edit(){return view('admin.seo',['seo'=>array_replace(Seo::defaults(),SiteSetting::find('home_seo')?->value??[])]);}
 public function update(Request $request){
  $data=$request->validate(Seo::rules());$data['seo_noindex']=$request->boolean('seo_noindex');
  SiteSetting::updateOrCreate(['key'=>'home_seo'],['value'=>$data]);
  return to_route('admin.seo.edit')->with('success','Zapisano SEO strony głównej.');
 }
}
