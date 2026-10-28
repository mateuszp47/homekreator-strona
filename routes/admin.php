<?php
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Middleware\EnsureAdmin;
use App\Models\Product;
use App\Models\Inquiry;
use App\Http\Controllers\Admin\InquiryController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
Route::middleware('guest')->group(function(){
 Route::view('/admin/login','admin.login')->name('login');
 Route::post('/admin/login',[AuthController::class,'login'])->middleware('throttle:20,1')->name('admin.login');
});
Route::prefix('admin')->name('admin.')->middleware(['auth',EnsureAdmin::class])->group(function(){
 Route::get('/',fn()=>view('admin.dashboard',['unreadInquiries'=>Inquiry::whereNull('read_at')->count(),'products'=>Product::count(),'projects'=>Project::count(),'drafts'=>Product::where('is_published',false)->count()+Project::where('is_published',false)->count()]))->name('dashboard');
 Route::get('/kopie',[\App\Http\Controllers\Admin\BackupController::class,'index'])->name('backups.index');
 Route::post('/kopie',[\App\Http\Controllers\Admin\BackupController::class,'store'])->middleware('throttle:3,10')->name('backups.store');
 Route::get('/kopie/{file}',[\App\Http\Controllers\Admin\BackupController::class,'download'])->name('backups.download');
 Route::get('/ustawienia',[\App\Http\Controllers\Admin\SettingsController::class,'edit'])->name('settings');
 Route::put('/ustawienia',[\App\Http\Controllers\Admin\SettingsController::class,'update'])->name('settings.update');
 Route::get('/wiadomosci',[InquiryController::class,'index'])->name('inquiries.index');
 Route::get('/wiadomosci/{inquiry}',[InquiryController::class,'show'])->whereNumber('inquiry')->name('inquiries.show');
 Route::patch('/wiadomosci/{inquiry}/status',[InquiryController::class,'read'])->whereNumber('inquiry')->name('inquiries.read');
 Route::post('/logout',[AuthController::class,'logout'])->name('logout');
 Route::prefix('/{kind}')->where(['kind'=>'produkty|realizacje'])->name('content.')->group(function(){
  Route::get('/',[ContentController::class,'index'])->name('index');
  Route::get('/dodaj',[ContentController::class,'create'])->name('create');
  Route::post('/',[ContentController::class,'store'])->name('store');
  Route::get('/{id}/edytuj',[ContentController::class,'edit'])->whereNumber('id')->name('edit');
  Route::put('/{id}',[ContentController::class,'update'])->whereNumber('id')->name('update');
  Route::delete('/{id}',[ContentController::class,'destroy'])->whereNumber('id')->name('destroy');
  Route::get('/{id}/usun-trwale',[ContentController::class,'confirmDelete'])->whereNumber('id')->name('confirm-delete');
  Route::delete('/{id}/usun-trwale',[ContentController::class,'forceDelete'])->whereNumber('id')->name('force-delete');
  Route::post('/{id}/przywroc',[ContentController::class,'restore'])->whereNumber('id')->name('restore');
 });
});
