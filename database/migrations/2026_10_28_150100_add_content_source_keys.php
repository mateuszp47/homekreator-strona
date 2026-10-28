<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
  foreach(['products','projects'] as $table){
   Schema::table($table,fn(Blueprint $blueprint)=>$blueprint->string('source_key')->nullable()->unique());
   foreach(json_decode(file_get_contents(base_path('data/'.$table.'.json')),true,flags:JSON_THROW_ON_ERROR) as $entry){DB::table($table)->where('slug',$entry['slug'])->update(['source_key'=>$entry['slug']]);}
  }
 }
 public function down(): void {foreach(['products','projects'] as $table)Schema::table($table,fn(Blueprint $blueprint)=>$blueprint->dropColumn('source_key'));}
};
