<?php
namespace Database\Seeders;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class DatabaseSeeder extends Seeder {
 public function run(): void {
  foreach(json_decode(file_get_contents(base_path('data/products.json')),true,flags:JSON_THROW_ON_ERROR) as $i=>$product){
   if(DB::table('content_deletions')->where('kind','produkty')->where('source_key',$product['slug'])->exists())continue;
   $product['full_price']=$product['fullPrice'];unset($product['fullPrice']);
   Product::withTrashed()->firstOrCreate(['source_key'=>$product['slug']],[...$product,'is_published'=>true,'sort_order'=>$i]);
  }
  foreach(json_decode(file_get_contents(base_path('data/projects.json')),true,flags:JSON_THROW_ON_ERROR) as $i=>$project){
   if(DB::table('content_deletions')->where('kind','realizacje')->where('source_key',$project['slug'])->exists())continue;
   $project['product_id']=Product::where('slug',$project['product'])->value('id');unset($project['product']);
   Project::withTrashed()->firstOrCreate(['source_key'=>$project['slug']],[...$project,'is_published'=>true,'sort_order'=>$i]);
  }
 }
}
