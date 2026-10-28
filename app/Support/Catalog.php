<?php
namespace App\Support;
use Illuminate\Support\Collection;
class Catalog
{
 public function products(): Collection { return collect(json_decode(file_get_contents(base_path('data/products.json')), true, flags: JSON_THROW_ON_ERROR)); }
 public function product(string $slug): array { return $this->products()->firstWhere('slug',$slug) ?? abort(404); }
 public function categories(): Collection { return collect(config('homekreator.categories')); }
 public function category(string $slug): array {
  foreach($this->categories() as $key=>$category) if($category['slug']===$slug) return ['key'=>$key,...$category];
  abort(404);
 }
 public function projects(): Collection { return collect([
  ['slug'=>'domek-z-tarasem','name'=>'Domek modułowy z tarasem','image'=>'domek.jpeg','gallery'=>['domek.jpeg','wnetrze.jpeg','wnetrze-2.jpeg','gabinet.jpeg'],'category'=>'domki','product'=>'domek-modulowy-6x3','description'=>'Przeszklony moduł z zadaszonym tarasem, aneksem kuchennym i łazienką. Zobacz przykładowe wykończenie oraz połączenie wnętrza z przestrzenią wypoczynkową.'],
  ['slug'=>'pawilon-sanitarny-wc','name'=>'Pawilon sanitarny z dwiema toaletami','image'=>'sanitarny.jpeg','gallery'=>['sanitarny.jpeg'],'category'=>'sanitarne','product'=>'pawilon-sanitarny','description'=>'Przykład kompaktowego zaplecza sanitarnego. Moduł z dwoma WC i pisuarem, przygotowany do podłączenia instalacji na miejscu.'],
  ['slug'=>'sauna-w-ogrodzie','name'=>'Nowoczesna sauna ogrodowa','image'=>'sauna.webp','gallery'=>['sauna.webp'],'category'=>'sauny','product'=>'sauna-ogrodowa','description'=>'Sauna z dużym frontowym przeszkleniem. Przykładowa realizacja pokazuje połączenie drewnianego wnętrza z nowoczesną formą.'],
  ['slug'=>'przestrzen-do-pracy','name'=>'Moduł jako miejsce do pracy','image'=>'biuro.jpeg','gallery'=>['biuro.jpeg','wnetrze-2.jpeg'],'category'=>'biznes','product'=>'biuro-modulowe','description'=>'Jasne wnętrze modułu do wykorzystania jako biuro. Wykończenie i układ można dostosować do planowanego sposobu pracy.'],
 ]); }
 public function contactAvailable(): bool { return config('homekreator.contact_enabled') && filter_var(config('homekreator.contact_recipient'),FILTER_VALIDATE_EMAIL) && in_array(config('mail.default'),['smtp','sendmail','ses','postmark','resend','mailgun'],true); }
}
