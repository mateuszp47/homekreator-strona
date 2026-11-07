<?php
namespace App\Support;
use Illuminate\Support\Collection;
class Catalog
{
 public function products(): Collection { return \App\Models\Product::whereIn('category',$this->categories()->keys())->where('is_published',true)->orderBy('sort_order')->orderBy('id')->get()->map(fn($p)=>$p->catalogData()); }
 public function featuredProducts(): Collection { $products=$this->products();$featured=$products->where('is_featured',true);return ($featured->isNotEmpty()?$featured:$products)->take(3); }
 public function product(string $slug): array { return $this->products()->firstWhere('slug',$slug) ?? abort(404); }
 public function allCategories(): Collection { return \App\Models\Category::orderBy('sort_order')->orderBy('name')->get()->keyBy('key')->map(fn($category)=>$category->toArray()); }
 public function categories(): Collection { return $this->allCategories()->where('is_active',true); }
 public function category(string $slug): array {
  foreach($this->categories() as $key=>$category) if($category['slug']===$slug) return ['key'=>$key,...$category];
  abort(404);
 }
 public function projects(): Collection { return \App\Models\Project::with('product')->whereIn('category',$this->categories()->keys())->where('is_published',true)->orderBy('sort_order')->orderBy('id')->get()->map(fn($p)=>$p->catalogData()); }
 public static function imageUrl(string $path): string { return str_starts_with($path,'catalog/') ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : asset('images/'.$path); }
 public function contactAvailable(): bool { return config('homekreator.contact_enabled') && filter_var(config('homekreator.contact_recipient'),FILTER_VALIDATE_EMAIL) && in_array(config('mail.default'),['smtp','sendmail','ses','postmark','resend','mailgun'],true); }
}
