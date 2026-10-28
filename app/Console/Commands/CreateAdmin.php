<?php
namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class CreateAdmin extends Command {
 protected $signature='admin:create';
 protected $description='Utwórz administratora panelu Home Kreator';
 public function handle(): int {
  $name=$this->ask('Imię i nazwisko');$email=strtolower(trim($this->ask('E-mail administratora')??''));
  $password=$this->secret('Hasło (minimum 12 znaków)');$confirmation=$this->secret('Powtórz hasło');
  $validator=Validator::make(['name'=>$name,'email'=>$email,'password'=>$password,'password_confirmation'=>$confirmation],['name'=>'required|string|max:100','email'=>'required|email|max:254|unique:users,email','password'=>'required|string|min:12|confirmed']);
  if($validator->fails()){foreach($validator->errors()->all() as $error)$this->error($error);return self::FAILURE;}
  $user=new User(['name'=>$name,'email'=>$email,'password'=>Hash::make($password)]);$user->is_admin=true;$user->save();
  $this->info('Administrator utworzony. Otwórz /admin i zaloguj się podanym adresem.');return self::SUCCESS;
 }
}
