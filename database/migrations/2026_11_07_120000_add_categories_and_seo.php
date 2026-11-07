<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void {
  Schema::create('categories',function(Blueprint $table){
   $table->string('key',100)->primary();$table->string('slug',200)->unique();$table->string('name',200);
   $table->text('description')->nullable();$table->string('image')->default('domek.jpeg');
   $table->boolean('is_active')->default(true)->index();$table->unsignedInteger('sort_order')->default(0);$table->timestamps();
  });
  foreach(config('homekreator.categories') as $key=>$category){
   DB::table('categories')->insert([...$category,'key'=>$key,'sort_order'=>array_search($key,array_keys(config('homekreator.categories'))),'created_at'=>now(),'updated_at'=>now()]);
  }
  // Preserve any category keys already used by content outside the original configuration.
  $keys=DB::table('products')->pluck('category')->merge(DB::table('projects')->pluck('category'))->unique();
  foreach($keys as $key){
   if(!DB::table('categories')->where('key',$key)->exists()){
    $base=\Illuminate\Support\Str::slug($key) ?: 'kategoria';$slug=substr($base,0,190);$suffix=2;
    while(DB::table('categories')->where('slug',$slug)->exists()){$slug=substr($base,0,190).'-'.$suffix++;}
    DB::table('categories')->insert(['key'=>$key,'slug'=>$slug,'name'=>$key,'image'=>'domek.jpeg','created_at'=>now(),'updated_at'=>now()]);
   }
  }
  foreach(['products','projects'] as $name){
   Schema::table($name,function(Blueprint $table){
    $table->string('meta_title',200)->nullable();$table->text('meta_description')->nullable();
    $table->string('canonical_url',2048)->nullable();$table->boolean('seo_noindex')->default(false);
   });
  }
 }
 public function down(): void {
  foreach(['products','projects'] as $name)Schema::table($name,fn(Blueprint $table)=>$table->dropColumn(['meta_title','meta_description','canonical_url','seo_noindex']));
  Schema::dropIfExists('categories');
 }
};
