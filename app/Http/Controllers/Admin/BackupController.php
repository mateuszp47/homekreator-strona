<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Support\Facades\Artisan;
class BackupController extends Controller {
 public function index(BackupService $backups){return view('admin.backups',['files'=>$backups->files()]);}
 public function store(){
  if(Artisan::call('site:backup')!==0)return back()->withErrors(['backup'=>'Nie udało się wykonać kopii. Sprawdź log aplikacji lub uruchom php artisan site:backup w terminalu.']);
  return back()->with('success','Utworzono kopię bazy i zdjęć.');
 }
 public function download(string $file,BackupService $backups){return response()->download($backups->path($file))->header('Cache-Control','private, no-store');}
}
