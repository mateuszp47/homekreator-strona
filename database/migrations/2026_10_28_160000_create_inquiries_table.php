<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('inquiries',function(Blueprint $table){
   $table->id();$table->string('name',100);$table->string('email',254);$table->string('phone',30)->nullable();$table->string('city',150);
   $table->string('model')->nullable();$table->string('model_name')->nullable();$table->text('message');
   $table->string('mail_status')->default('not_configured');$table->timestamp('read_at')->nullable()->index();$table->timestamps();
  });
 }
 public function down(): void {Schema::dropIfExists('inquiries');}
};
