<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use PharData;
use Phar;
use Throwable;
class BackupService {
 public function create(): string {
  $root=config('backups.directory');File::ensureDirectoryExists($root,0700);
  $id='homekreator-'.now()->format('Ymd-His-u').'-'.Str::lower(Str::random(8));$work=$root.'/.'.$id;File::makeDirectory($work,0700);$tar=$root.'/'.$id.'.tar';$archive=null;
  try {
   $driver=DB::connection()->getDriverName();
   if($driver==='sqlite'){
    $target=$work.'/database.sqlite';DB::statement('VACUUM INTO '.DB::connection()->getPdo()->quote($target));
   } elseif($driver==='mysql'){
    $c=config('database.connections.'.config('database.default'));
    $args=['mysqldump','--host='.$c['host'],'--port='.($c['port']??3306),'--user='.$c['username'],'--single-transaction','--routines','--triggers','--result-file='.$work.'/database.sql',$c['database']];
    if(!empty($c['unix_socket']))$args[]='--socket='.$c['unix_socket'];
    $process=new Process($args,null,['MYSQL_PWD'=>$c['password']??'']);$process->setTimeout(300);$process->run();
    if(!$process->isSuccessful())throw new \RuntimeException('Nie udało się wykonać kopii MySQL. Sprawdź dostępność mysqldump i uprawnienia bazy.');
   } else throw new \RuntimeException('Kopie obsługują SQLite i MySQL.');
   $archive=new PharData($tar);$archive->addFile($work.($driver==='sqlite'?'/database.sqlite':'/database.sql'),$driver==='sqlite'?'database.sqlite':'database.sql');
   foreach(Storage::disk('public')->allFiles() as $file){if(basename($file)==='.gitignore')continue;$path=Storage::disk('public')->path($file);if(!is_link($path))$archive->addFile($path,'uploads/'.$file);}
   if(File::isDirectory(public_path('images')))foreach(File::allFiles(public_path('images')) as $file){if(!$file->isLink())$archive->addFile($file->getPathname(),'images/'.$file->getRelativePathname());}
   $archive->addFromString('manifest.json',json_encode(['version'=>1,'created_at'=>now()->toIso8601String(),'database_driver'=>$driver,'application'=>'Home Kreator'],JSON_PRETTY_PRINT));
   unset($archive);$archive=null;
   $input=fopen($tar,'rb');$output=gzopen($tar.'.gz','wb6');
   if(!$input||!$output)throw new \RuntimeException('Nie udało się skompresować kopii.');
   try {while(!feof($input)){$chunk=fread($input,65536);if($chunk===false||gzwrite($output,$chunk)!==strlen($chunk))throw new \RuntimeException('Błąd zapisu archiwum.');}}
   finally {fclose($input);gzclose($output);}
   File::delete($tar);
   $file=$tar.'.gz';chmod($file,0600);$this->prune();return basename($file);
  } catch(Throwable $e){unset($archive);File::delete([$tar,$tar.'.gz']);throw $e;}
  finally {File::deleteDirectory($work);}
 }
 public function files(): array {
  if(!File::isDirectory(config('backups.directory')))return [];
  $files=glob(config('backups.directory').'/homekreator-*.tar.gz')?:[];rsort($files);
  return array_map(fn($p)=>['name'=>basename($p),'size'=>filesize($p),'created_at'=>filemtime($p)],$files);
 }
 private function prune(): void {foreach(array_slice($this->files(),max(1,(int)config('backups.keep'))) as $file)File::delete(config('backups.directory').'/'.$file['name']);}
 public function path(string $name): string {abort_unless(preg_match('/^homekreator-\d{8}-\d{6}-\d{6}-[a-z0-9]{8}\.tar\.gz$/D',$name),404);$path=config('backups.directory').'/'.$name;abort_unless(is_file($path)&&!is_link($path),404);return $path;}
}
