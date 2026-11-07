<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {Schema::table('users',function(Blueprint $table){
  $table->boolean('is_blocked')->default(false)->index();
  $table->unsignedInteger('auth_version')->default(0);
 });}
 public function down(): void {Schema::table('users',fn(Blueprint $table)=>$table->dropColumn(['is_blocked','auth_version']));}
};
