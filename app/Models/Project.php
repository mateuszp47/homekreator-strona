<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Project extends Model {
 use SoftDeletes;
 protected $guarded=['id'];
 protected function casts(): array {return ['gallery'=>'array','is_published'=>'boolean','seo_noindex'=>'boolean'];}
 public function product(): BelongsTo {return $this->belongsTo(Product::class);}
 public function catalogData(): array {return [...$this->toArray(),'product'=>$this->product?->is_published && \App\Models\Category::where('key',$this->product->category)->where('is_active',true)->exists()?$this->product->slug:null];}
}
