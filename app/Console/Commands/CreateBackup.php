<?php
namespace App\Console\Commands;
use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;
class CreateBackup extends Command {
 protected $signature='site:backup';protected $description='Wykonaj prywatną kopię bazy i zdjęć Home Kreator';
 public function handle(BackupService $backups): int {
  $lock=Cache::lock('site-backup',1800);if(!$lock->get()){$this->error('Inna kopia jest już wykonywana.');return self::FAILURE;}
  try{$name=$backups->create();$this->info('Utworzono kopię: '.$name);return self::SUCCESS;}
  catch(Throwable $e){report($e);$this->error('Nie udało się utworzyć kopii. Sprawdź log aplikacji.');return self::FAILURE;}
  finally{$lock->release();}
 }
}
