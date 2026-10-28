<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model {
 use SoftDeletes;
 protected $guarded=['id'];
 protected function casts(): array {return ['gallery'=>'array','equipment'=>'array','is_published'=>'boolean','price'=>'integer','full_price'=>'integer'];}
 public function catalogData(): array {return [...$this->toArray(),'fullPrice'=>$this->full_price];}
}
