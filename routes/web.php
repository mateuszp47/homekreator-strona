<?php
use App\Http\Controllers\ContactController;
use App\Http\Controllers\WebsiteController;
use App\Support\Catalog;
use Illuminate\Support\Facades\Route;
Route::get('/',[WebsiteController::class,'home'])->name('home');
Route::get('/oferta/{category?}',[WebsiteController::class,'offer'])->name('offer');
Route::get('/produkty/{slug}',[WebsiteController::class,'product'])->name('product');
Route::get('/realizacje',[WebsiteController::class,'projects'])->name('projects');
Route::get('/realizacje/{slug}',[WebsiteController::class,'project'])->name('project');
Route::view('/o-nas','pages.about')->name('about');
Route::view('/transport-i-montaz','pages.delivery')->name('delivery');
Route::get('/kontakt',[WebsiteController::class,'contact'])->name('contact');
Route::post('/kontakt',ContactController::class)->middleware('throttle:5,1')->name('contact.send');
Route::view('/polityka-prywatnosci','pages.privacy')->name('privacy');
Route::get('/sitemap.xml',function(Catalog $catalog){
 $urls=collect(['home','offer','projects','about','delivery','contact','privacy'])->map(fn($name)=>route($name));
 $urls=$urls->merge($catalog->categories()->map(fn($c)=>route('offer',$c['slug'])))->merge($catalog->products()->map(fn($p)=>route('product',$p['slug'])))->merge($catalog->projects()->map(fn($p)=>route('project',$p['slug'])));
 return response()->view('sitemap',compact('urls'))->header('Content-Type','application/xml');
})->name('sitemap');
Route::get('/{legacy}.html',function(string $legacy,Catalog $catalog){
 $pages=['index'=>'home','oferta'=>'offer','realizacje'=>'projects','o-nas'=>'about','kontakt'=>'contact','prywatnosc'=>'privacy'];
 if(isset($pages[$legacy])) return redirect()->route($pages[$legacy],status:301);
 if($catalog->products()->contains('slug',$legacy)) return redirect()->route('product',$legacy,301);
 abort(404);
});
