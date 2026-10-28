<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('products',function(Blueprint $t){$t->boolean('is_featured')->default(false)->index();$t->text('base_includes')->nullable();$t->text('extra_options')->nullable();$t->text('transport_note')->nullable();});
  Schema::table('projects',function(Blueprint $t){$t->string('location')->nullable();$t->string('dimensions')->nullable();$t->string('purpose')->nullable();$t->text('equipment_details')->nullable();});
  Schema::create('site_settings',function(Blueprint $t){$t->string('key')->primary();$t->json('value');$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('site_settings');Schema::table('products',fn(Blueprint $t)=>$t->dropColumn(['is_featured','base_includes','extra_options','transport_note']));Schema::table('projects',fn(Blueprint $t)=>$t->dropColumn(['location','dimensions','purpose','equipment_details']));}
};
