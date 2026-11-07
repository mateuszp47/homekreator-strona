<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class EnsureAdmin {
 public function handle(Request $request,Closure $next) {
  $user=$request->user()?->fresh();
  abort_unless($user,403);
  $key='admin_auth_version.'.$user->id;
  if($user->is_blocked || (int)$request->session()->get($key,$user->auth_version)!==$user->auth_version){
   Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();
   return redirect()->route('login')->withErrors(['email'=>'Dostęp do konta został zmieniony. Zaloguj się ponownie lub skontaktuj się z administratorem.']);
  }
  abort_unless($user->is_admin,403);
  $request->session()->put($key,$user->auth_version);
  Auth::guard()->setUser($user);
  return $next($request);
 }
}
