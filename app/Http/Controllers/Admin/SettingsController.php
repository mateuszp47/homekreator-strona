<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\SiteSettings;
use App\Support\Catalog;
use Illuminate\Http\Request;
class SettingsController extends Controller {
 public function edit(SiteSettings $settings,Catalog $catalog){return view('admin.settings',['settings'=>$settings->values(),'mailReady'=>$catalog->contactAvailable()]);}
 public function update(Request $request){
  $data=$request->validate([
   'company'=>'required|string|max:200','phone'=>['required','regex:/^\+?[0-9 ()-]{7,30}$/'],'secondary_phone'=>['nullable','regex:/^\+?[0-9 ()-]{7,30}$/'],
   'street'=>'required|string|max:200','postal_code'=>'required|string|max:20','city'=>'required|string|max:100',
   'instagram'=>['nullable','url:https','regex:~^https://(?:www\.)?instagram\.com/[^\s]+$~'],'tiktok'=>['nullable','url:https','regex:~^https://(?:www\.)?tiktok\.com/[^\s]+$~'],
   'contact_enabled'=>'required|boolean','contact_recipient'=>'nullable|email|max:254',
   'faq'=>'required|array|min:1|max:30','faq.*.question'=>'required|string|max:300','faq.*.answer'=>'required|string|max:3000',
  ]);
  if($request->boolean('contact_enabled')&&empty($data['contact_recipient']))return back()->withInput()->withErrors(['contact_recipient'=>'Podaj e-mail odbierający powiadomienia.']);
  SiteSetting::updateOrCreate(['key'=>'site'],['value'=>$data]);return to_route('admin.settings')->with('success','Zapisano ustawienia strony.');
 }
}
