<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
class ServeSite extends Command {
 protected $signature='site:serve {--host=127.0.0.1} {--port=8000}';protected $description='Lokalny serwer z limitami do galerii zdjęć';
 public function handle(): int {
  $port=filter_var($this->option('port'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1024,'max_range'=>65535]]);$host=$this->option('host');
  if(!$port||!in_array($host,['127.0.0.1','localhost','0.0.0.0'],true)){$this->error('Niepoprawny host lub port.');return self::FAILURE;}
  $this->info('Home Kreator: http://'.$host.':'.$port.' — zatrzymaj Control+C.');
  $process=new Process([PHP_BINARY,'-d','post_max_size=350M','-d','upload_max_filesize=10M','-d','max_file_uploads=40','-d','memory_limit=256M','-S',$host.':'.$port,base_path('vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php')],public_path());
  $process->setTimeout(null);return $process->run(fn($type,$buffer)=>$this->output->write($buffer));
 }
}
