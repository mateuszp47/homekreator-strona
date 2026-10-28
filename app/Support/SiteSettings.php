<?php
namespace App\Support;
use App\Models\SiteSetting;
class SiteSettings {
 public function defaults(): array {return [
  'company'=>config('homekreator.company'),'phone'=>config('homekreator.phone'),'secondary_phone'=>config('homekreator.secondary_phone'),
  'street'=>'ul. Słomiana 18','postal_code'=>'26-500','city'=>'Szydłowiec',
  'instagram'=>config('homekreator.socials.0.url'),'tiktok'=>config('homekreator.socials.1.url'),'faq'=>config('homekreator.faq'),
  'contact_enabled'=>(bool)config('homekreator.contact_enabled'),'contact_recipient'=>config('homekreator.contact_recipient'),
 ];}
 public function values(): array {return array_replace($this->defaults(),SiteSetting::find('site')?->value??[]);}
 public function apply(): void {
  $s=$this->values();foreach(['company','phone','secondary_phone','street','postal_code','city','faq','contact_enabled','contact_recipient'] as $key)config(['homekreator.'.$key=>$s[$key]]);
  config(['homekreator.phone_href'=>preg_replace('/[^+0-9]/','',$s['phone']),'homekreator.secondary_phone_href'=>preg_replace('/[^+0-9]/','',$s['secondary_phone']??''),
   'homekreator.address'=>$s['street'].', '.$s['postal_code'].' '.$s['city'],
   'homekreator.socials'=>array_values(array_filter([
    $s['instagram']?['name'=>'Instagram','icon'=>'instagram','url'=>$s['instagram']]:null,
    $s['tiktok']?['name'=>'TikTok','icon'=>'tiktok','url'=>$s['tiktok']]:null,
   ])),
  ]);
 }
}
