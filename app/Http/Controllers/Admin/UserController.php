<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserController extends Controller {
 public function index(Request $request){
  $data=$request->validate(['q'=>['nullable','string','max:100'],'status'=>['nullable','in:active,blocked']]);
  $query=User::query();$search=trim($data['q']??'');$status=$data['status']??'';
  if($search!=='')$query->where(fn($q)=>$q->where('name','like','%'.$search.'%')->orWhere('email','like','%'.$search.'%'));
  if($status!=='')$query->where('is_blocked',$status==='blocked');
  return view('admin.users.index',['users'=>$query->orderBy('name')->orderBy('id')->paginate(20)->withQueryString(),'search'=>$search,'status'=>$status]);
 }
 public function create(){return view('admin.users.form',['user'=>new User(['name'=>'','email'=>''])]);}
 public function edit(User $user){return view('admin.users.form',compact('user'));}
 public function store(Request $request){return $this->save($request,new User);}
 public function update(Request $request,User $user){return $this->save($request,$user);}
 public function status(Request $request,User $user){
  $data=$request->validate(['is_blocked'=>['required','boolean']]);
  $this->persist($request,$user,['is_blocked'=>(bool)$data['is_blocked']]);
  return to_route('admin.users.index')->with('success',$user->is_blocked?'Zablokowano konto i unieważniono jego sesje.':'Odblokowano konto. Użytkownik może ponownie się zalogować.');
 }
 private function save(Request $request,User $user){
  if(is_string($request->input('email')))$request->merge(['email'=>Str::lower(trim($request->input('email')))]);
  $data=$request->validate([
   'name'=>['required','string','max:100'],
   'email'=>['required','email','max:254',function($attribute,$value,$fail)use($user){
    if(User::whereRaw('LOWER(email) = ?',[$value])->when($user->exists,fn($q)=>$q->where('id','!=',$user->id))->exists())$fail('Ten adres e-mail jest już używany.');
   }],
   'password'=>[$user->exists?'nullable':'required','string','min:12','max:72','confirmed',function($attribute,$value,$fail){if(strlen($value)>72)$fail('Hasło może mieć maksymalnie 72 bajty.');}],
   'is_admin'=>['required','boolean'],'is_blocked'=>['required','boolean'],
  ]);
  if(empty($data['password']))unset($data['password']);
  $data['is_admin']=(bool)$data['is_admin'];$data['is_blocked']=(bool)$data['is_blocked'];
  $this->persist($request,$user,$data);
  return to_route('admin.users.edit',$user)->with('success','Zapisano konto użytkownika.');
 }
 private function persist(Request $request,User $user,array $data): void {
  DB::transaction(function()use($request,$user,$data){
   // Serialize changes to active administrators so parallel edits cannot remove the last one.
   $admins=User::where('is_admin',true)->where('is_blocked',false)->orderBy('id')->lockForUpdate()->get();
   abort_unless($admins->contains('id',$request->user()->id),403);
   if($user->exists)$user->setRawAttributes(User::whereKey($user->id)->lockForUpdate()->firstOrFail()->getAttributes(),true);
   $admin=$data['is_admin']??$user->is_admin;$blocked=$data['is_blocked']??$user->is_blocked;
   if($user->exists && $user->id===$request->user()->id && (!$admin||$blocked)){
    throw ValidationException::withMessages([$blocked?'is_blocked':'is_admin'=>'Nie możesz zablokować własnego konta ani odebrać sobie dostępu administratora.']);
   }
   if($user->exists && $user->is_admin && !$user->is_blocked && (!$admin||$blocked) && $admins->count()<=1){
    throw ValidationException::withMessages([$blocked?'is_blocked':'is_admin'=>'Musi pozostać co najmniej jeden aktywny administrator.']);
   }
   $revoke=$user->exists && (isset($data['password']) || $admin!==$user->is_admin || $blocked!==$user->is_blocked);
   if(isset($data['name']))$user->name=$data['name'];
   if(isset($data['email']) && $user->email!==$data['email']){$user->email=$data['email'];$user->email_verified_at=null;}
   if(isset($data['password']))$user->password=$data['password'];
   $user->is_admin=$admin;$user->is_blocked=$blocked;
   if($revoke){$user->auth_version++;$user->remember_token=Str::random(60);}
   $user->save();
   if($revoke && $user->id===$request->user()->id)$request->session()->put('admin_auth_version.'.$user->id,$user->auth_version);
  });
 }
}
