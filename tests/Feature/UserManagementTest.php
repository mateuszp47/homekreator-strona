<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase {
 private function admin(): User {$u=User::factory()->create();$u->is_admin=true;$u->save();return $u;}
 private function data(array $overrides=[]): array {return [...['name'=>'Nowy administrator','email'=>'new-admin@example.com','password'=>'new-secure-password','password_confirmation'=>'new-secure-password','is_admin'=>1,'is_blocked'=>0],...$overrides];}
 public function test_user_management_requires_an_active_administrator(): void {
  $target=$this->admin();
  foreach(['/admin/uzytkownicy','/admin/uzytkownicy/dodaj','/admin/uzytkownicy/'.$target->id.'/edytuj'] as $url)$this->get($url)->assertRedirect(route('login'));
  $this->post('/admin/uzytkownicy',$this->data())->assertRedirect(route('login'));
  $this->actingAs(User::factory()->create());
  $this->get('/admin/uzytkownicy')->assertForbidden();
  $this->post('/admin/uzytkownicy',$this->data())->assertForbidden();
  $this->put('/admin/uzytkownicy/'.$target->id,$this->data())->assertForbidden();
  $this->patch('/admin/uzytkownicy/'.$target->id.'/status',['is_blocked'=>1])->assertForbidden();
 }
 public function test_admin_can_create_accounts_with_hashed_passwords_and_render_forms(): void {
  $this->actingAs($this->admin());
  $this->get('/admin/uzytkownicy')->assertOk();$this->get('/admin/uzytkownicy/dodaj')->assertOk();
  $this->post('/admin/uzytkownicy',$this->data(['email'=>'NEW-ADMIN@EXAMPLE.COM']))->assertSessionHasNoErrors()->assertRedirect();
  $user=User::where('email','new-admin@example.com')->firstOrFail();
  $this->assertTrue($user->is_admin);$this->assertFalse($user->is_blocked);$this->assertTrue(Hash::check('new-secure-password',$user->password));
  $this->get('/admin/uzytkownicy/'.$user->id.'/edytuj')->assertOk()->assertDontSee($user->password,false);
  $this->post('/admin/uzytkownicy',$this->data(['email'=>'regular@example.com','is_admin'=>0,'is_blocked'=>1]))->assertSessionHasNoErrors();
  $regular=User::where('email','regular@example.com')->firstOrFail();$this->assertFalse($regular->is_admin);$this->assertTrue($regular->is_blocked);
 }
 public function test_editing_details_preserves_password_and_clears_email_verification(): void {
  $this->actingAs($this->admin());$target=$this->admin();$hash=$target->password;
  $this->put('/admin/uzytkownicy/'.$target->id,$this->data(['name'=>'Zmienione dane','email'=>'changed@example.com','password'=>'','password_confirmation'=>'']))->assertSessionHasNoErrors();
  $target->refresh();$this->assertSame('Zmienione dane',$target->name);$this->assertSame('changed@example.com',$target->email);$this->assertSame($hash,$target->password);$this->assertNull($target->email_verified_at);
 }
 public function test_admin_can_reset_password_and_change_access(): void {
  $this->actingAs($this->admin());$target=$this->admin();$version=$target->auth_version;
  $this->put('/admin/uzytkownicy/'.$target->id,$this->data(['email'=>$target->email,'is_admin'=>0]))->assertSessionHasNoErrors();
  $target->refresh();$this->assertTrue(Hash::check('new-secure-password',$target->password));$this->assertFalse($target->is_admin);$this->assertSame($version+1,$target->auth_version);
 }
 public function test_self_blocking_and_self_demotion_are_rejected_even_with_another_admin(): void {
  $admin=$this->admin();$this->admin();$this->actingAs($admin);
  $this->patch('/admin/uzytkownicy/'.$admin->id.'/status',['is_blocked'=>1])->assertSessionHasErrors('is_blocked');
  $this->put('/admin/uzytkownicy/'.$admin->id,$this->data(['email'=>$admin->email,'is_admin'=>0]))->assertSessionHasErrors('is_admin');
  $this->assertTrue($admin->fresh()->is_admin);$this->assertFalse($admin->fresh()->is_blocked);
 }
 public function test_last_admin_remains_active_and_can_edit_own_details(): void {
  $admin=$this->admin();$this->actingAs($admin);
  $this->put('/admin/uzytkownicy/'.$admin->id,$this->data(['email'=>$admin->email,'is_blocked'=>1]))->assertSessionHasErrors('is_blocked');
  $this->put('/admin/uzytkownicy/'.$admin->id,$this->data(['email'=>$admin->email,'is_admin'=>0]))->assertSessionHasErrors('is_admin');
  $this->put('/admin/uzytkownicy/'.$admin->id,$this->data(['name'=>'Właściciel','email'=>$admin->email]))->assertSessionHasNoErrors();
  $this->get('/admin')->assertOk();$this->assertTrue($admin->fresh()->is_admin);$this->assertFalse($admin->fresh()->is_blocked);
 }
 public function test_blocked_user_cannot_login_and_can_login_after_unblocking(): void {
  $actor=$this->admin();$target=$this->admin();$target->password='target-secure-password';$target->remember_token='old-remember-token';$target->save();$version=$target->auth_version;
  $this->actingAs($actor)->patch('/admin/uzytkownicy/'.$target->id.'/status',['is_blocked'=>1])->assertRedirect();
  $this->assertTrue($target->fresh()->is_blocked);$this->assertSame($version+1,$target->fresh()->auth_version);$this->assertNotSame('old-remember-token',$target->fresh()->remember_token);
  $this->post('/admin/logout');
  $this->post('/admin/login',['email'=>$target->email,'password'=>'target-secure-password'])->assertSessionHasErrors('email');$this->assertGuest();
  $this->actingAs($actor)->patch('/admin/uzytkownicy/'.$target->id.'/status',['is_blocked'=>0])->assertRedirect();
  $this->post('/admin/logout');
  $this->post('/admin/login',['email'=>$target->email,'password'=>'target-secure-password'])->assertSessionHasNoErrors()->assertRedirect(route('admin.dashboard'));$this->assertAuthenticatedAs($target);
 }
 public function test_blocking_revokes_existing_panel_session_and_remember_token(): void {
  $target=$this->admin();$target->remember_token='old-token';$target->save();$this->actingAs($target)->get('/admin')->assertOk();
  $target->is_blocked=true;$target->auth_version++;$target->remember_token='new-token';$target->save();
  $this->get('/admin/uzytkownicy')->assertRedirect(route('login'));$this->assertGuest();
 }
 public function test_block_unblock_cycle_and_password_resets_revoke_older_sessions(): void {
  foreach(['cycle','password','demotion'] as $change){
   $target=$this->admin();$this->actingAs($target)->get('/admin')->assertOk();
   $target->auth_version+=$change==='cycle'?2:1;
   if($change==='password')$target->password='replaced-password-long';
   if($change==='demotion')$target->is_admin=false;
   $target->save();
   $this->get('/admin')->assertRedirect(route('login'));$this->assertGuest();
  }
 }
 public function test_search_filters_and_pagination_work(): void {
  $this->actingAs($this->admin());$target=$this->admin();$target->name='Szukany użytkownik';$target->is_blocked=true;$target->save();
  $this->get('/admin/uzytkownicy?q=Szukany&status=blocked')->assertOk()->assertSee($target->email);
  $this->get('/admin/uzytkownicy?q=Szukany&status=active')->assertOk()->assertDontSee($target->email);
  User::factory()->count(25)->create();$this->get('/admin/uzytkownicy?page=2')->assertOk()->assertSee('Strona 2 z 2');
 }
 public function test_duplicates_short_mismatched_and_overlong_passwords_are_rejected(): void {
  $actor=$this->admin();$this->actingAs($actor);
  $this->post('/admin/uzytkownicy',$this->data(['email'=>strtoupper($actor->email)]))->assertSessionHasErrors('email');
  $this->post('/admin/uzytkownicy',$this->data(['password'=>'short','password_confirmation'=>'short']))->assertSessionHasErrors('password');
  $this->post('/admin/uzytkownicy',$this->data(['password_confirmation'=>'different-password']))->assertSessionHasErrors('password');
  $this->post('/admin/uzytkownicy',$this->data(['password'=>str_repeat('ą',40),'password_confirmation'=>str_repeat('ą',40)]))->assertSessionHasErrors('password');
  $this->patch('/admin/uzytkownicy/'.$actor->id.'/status',[])->assertSessionHasErrors('is_blocked');
 }
}
