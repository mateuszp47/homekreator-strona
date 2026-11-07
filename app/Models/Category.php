<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Category extends Model {
 protected $primaryKey='key';public $incrementing=false;protected $keyType='string';protected $guarded=['key'];
 protected function casts(): array {return ['is_active'=>'boolean','sort_order'=>'integer'];}
}
