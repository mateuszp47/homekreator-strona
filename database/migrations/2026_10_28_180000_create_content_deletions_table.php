<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {Schema::create('content_deletions',function(Blueprint $t){$t->id();$t->string('kind');$t->string('source_key');$t->timestamps();$t->unique(['kind','source_key']);});}
 public function down(): void {Schema::dropIfExists('content_deletions');}
};
