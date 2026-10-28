<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('users',fn(Blueprint $table)=>$table->boolean('is_admin')->default(false));
  Schema::create('products',function(Blueprint $table){
   $table->id();$table->string('slug')->unique();$table->string('name');$table->string('category');$table->string('label');
   $table->text('description');$table->string('size');$table->string('area');$table->unsignedInteger('price')->nullable();$table->unsignedInteger('full_price')->nullable();
   $table->string('image');$table->json('gallery');$table->json('equipment');$table->boolean('is_published')->default(false)->index();$table->unsignedInteger('sort_order')->default(0);$table->timestamps();$table->softDeletes();
  });
  Schema::create('projects',function(Blueprint $table){
   $table->id();$table->string('slug')->unique();$table->string('name');$table->string('category');$table->text('description');
   $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();$table->string('image');$table->json('gallery');
   $table->boolean('is_published')->default(false)->index();$table->unsignedInteger('sort_order')->default(0);$table->timestamps();$table->softDeletes();
  });
 }
 public function down(): void {Schema::dropIfExists('projects');Schema::dropIfExists('products');Schema::table('users',fn(Blueprint $table)=>$table->dropColumn('is_admin'));}
};
