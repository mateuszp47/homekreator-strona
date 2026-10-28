<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Project extends Model {
 use SoftDeletes;
 protected $guarded=['id'];
 protected function casts(): array {return ['gallery'=>'array','is_published'=>'boolean'];}
 public function product(): BelongsTo {return $this->belongsTo(Product::class);}
 public function catalogData(): array {return [...$this->toArray(),'product'=>$this->product?->is_published?$this->product->slug:null];}
}
