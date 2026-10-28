<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller {
 public function login(Request $request) {
  $data=$request->validate(['email'=>['required','email'],'password'=>['required','string']]);
  $key='admin-login:'.hash('sha256',Str::lower($data['email']).'|'.$request->ip());
  if(RateLimiter::tooManyAttempts($key,5)) throw ValidationException::withMessages(['email'=>'Zbyt wiele prób. Spróbuj ponownie za '.RateLimiter::availableIn($key).' sekund.']);
  if(!Auth::attempt([...$data,'is_admin'=>true])) {RateLimiter::hit($key,60);throw ValidationException::withMessages(['email'=>'Nieprawidłowy e-mail lub hasło.']);}
  RateLimiter::clear($key);$request->session()->regenerate();return redirect()->intended(route('admin.dashboard'));
 }
 public function logout(Request $request) {Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return to_route('login');}
}
