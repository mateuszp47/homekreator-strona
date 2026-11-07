<?php
namespace App\Support;
class Seo {
 public static function rules(): array {return [
  'meta_title'=>['nullable','string','max:200'],
  'meta_description'=>['nullable','string','max:500'],
  'canonical_url'=>['nullable','url:http,https','max:2048'],
  'seo_noindex'=>['sometimes','boolean'],
 ];}
 public static function metadata(array $data,string $title,string $description,string $image): array {return [
  'title'=>($data['meta_title']??null) ?: $title,
  'description'=>($data['meta_description']??null) ?: \Illuminate\Support\Str::limit(strip_tags($description),160,''),
  'canonical'=>($data['canonical_url']??null) ?: url()->current(),
  'noindex'=>(bool)($data['seo_noindex']??false),
  'image'=>$image,
 ];}
 public static function defaults(): array {return ['meta_title'=>null,'meta_description'=>null,'canonical_url'=>null,'seo_noindex'=>false];}
}
