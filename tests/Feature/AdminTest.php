<?php
namespace Tests\Feature;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
class AdminTest extends TestCase {
 private function admin(): User {$user=User::factory()->create();$user->is_admin=true;$user->save();return $user;}
 private function image(): UploadedFile {return UploadedFile::fake()->createWithContent('modul.jpg',file_get_contents(public_path('images/domek.jpeg')));}
 private function productData(): array {return ['name'=>'Nowy moduł testowy','slug'=>'nowy-modul-testowy','category'=>'domki','description'=>'Opis nowego modułu dodanego przez panel.','size'=>'8 × 3 m','area'=>'24 m²','price'=>45000,'full_price'=>69000,'equipment_text'=>"Łazienka\nKuchnia",'sort_order'=>0,'is_published'=>0,'image'=>$this->image()];}
 public function test_guests_and_non_admins_cannot_manage_content(): void {
  $this->get('/admin')->assertRedirect(route('login'));
  $this->post('/admin/produkty',[])->assertRedirect(route('login'));
  $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
  $this->post('/admin/produkty',$this->productData())->assertForbidden();
 }
 public function test_admin_can_login_and_logout_but_regular_user_cannot_login(): void {
  $admin=$this->admin();$admin->password=Hash::make('test-password-long');$admin->save();
  $this->post('/admin/login',['email'=>$admin->email,'password'=>'test-password-long'])->assertRedirect(route('admin.dashboard'));
  $this->assertAuthenticatedAs($admin);$this->post('/admin/logout')->assertRedirect(route('login'));$this->assertGuest();
  $user=User::factory()->create(['password'=>Hash::make('test-password-long')]);
  $this->post('/admin/login',['email'=>$user->email,'password'=>'test-password-long'])->assertSessionHasErrors('email');$this->assertGuest();
 }
 public function test_login_is_rate_limited(): void {
  for($i=0;$i<5;$i++)$this->post('/admin/login',['email'=>'bad@example.com','password'=>'wrong'])->assertSessionHasErrors('email');
  $this->post('/admin/login',['email'=>'bad@example.com','password'=>'wrong'])->assertSessionHasErrors('email');
  $this->assertStringContainsString('Zbyt wiele prób.',session('errors')->first('email'));
 }
 public function test_panel_pages_render(): void {
  $this->actingAs($this->admin());
  foreach(['/admin','/admin/produkty','/admin/realizacje','/admin/produkty/dodaj','/admin/realizacje/dodaj','/admin/produkty/'.Product::first()->id.'/edytuj','/admin/realizacje/'.Project::first()->id.'/edytuj'] as $url)$this->get($url)->assertOk();
 }
 public function test_product_create_edit_publish_archive_restore_and_upload(): void {
  Storage::fake('public');$this->actingAs($this->admin());$data=$this->productData();
  $this->post('/admin/produkty',$data)->assertSessionHasNoErrors()->assertRedirect();
  $product=Product::where('slug',$data['slug'])->firstOrFail();Storage::disk('public')->assertExists($product->image);
  $this->assertSame(['Łazienka','Kuchnia'],$product->equipment);$this->get('/produkty/'.$product->slug)->assertNotFound();
  unset($data['image']);$data['is_published']=1;$data['name']='Nowa nazwa produktu';
  $this->put('/admin/produkty/'.$product->id,$data)->assertSessionHasNoErrors();$this->get('/produkty/'.$product->slug)->assertOk()->assertSee('Nowa nazwa produktu')->assertSee('/storage/catalog/');
  $this->delete('/admin/produkty/'.$product->id)->assertRedirect();$this->get('/produkty/'.$product->slug)->assertNotFound();
  $this->post('/admin/produkty/'.$product->id.'/przywroc')->assertRedirect();$this->assertFalse($product->fresh()->is_published);$this->assertNull($product->fresh()->deleted_at);
 }
 public function test_projects_publish_and_handle_missing_related_product(): void {
  Storage::fake('public');$this->actingAs($this->admin());$product=Product::first();
  $this->post('/admin/realizacje',['name'=>'Nowa realizacja','slug'=>'nowa-realizacja','category'=>'domki','description'=>'Opis realizacji z panelu.','product_id'=>$product->id,'sort_order'=>0,'is_published'=>1,'image'=>$this->image(),'gallery'=>[$this->image()]])->assertSessionHasNoErrors();
  $project=Project::where('slug','nowa-realizacja')->firstOrFail();$this->get('/realizacje/'.$project->slug)->assertOk()->assertSee('Poznaj podobny moduł');
  $product->delete();$this->get('/realizacje/'.$project->slug)->assertOk()->assertDontSee('Poznaj podobny moduł');
 }
 public function test_invalid_upload_slug_and_arbitrary_gallery_paths_are_rejected(): void {
  Storage::fake('public');$this->actingAs($this->admin());$data=$this->productData();$data['image']=UploadedFile::fake()->createWithContent('bad.svg','<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');$data['slug']='../../evil';
  $this->post('/admin/produkty',$data)->assertSessionHasErrors(['image','slug']);
  $data=$this->productData();unset($data['image']);$data['remove_gallery']=['../../.env'];
  $this->put('/admin/produkty/'.Product::first()->id,$data)->assertSessionHasErrors('remove_gallery.0');
 }
 public function test_reseeding_preserves_edits_and_archived_items(): void {
  $product=Product::first();$product->update(['name'=>'Własny opis','slug'=>'zmieniony-adres']);$product->delete();$this->seed();
  $this->assertSame(6,Product::withTrashed()->count());$this->assertSame('Własny opis',$product->fresh()->name);$this->assertNotNull($product->fresh()->deleted_at);
 }
 public function test_admin_creation_has_no_default_password_or_public_registration(): void {
  $this->get('/register')->assertNotFound();
  $this->artisan('admin:create')->expectsQuestion('Imię i nazwisko','Właściciel')->expectsQuestion('E-mail administratora','owner@example.com')->expectsQuestion('Hasło (minimum 12 znaków)','unique-long-password')->expectsQuestion('Powtórz hasło','unique-long-password')->assertSuccessful();
  $user=User::where('email','owner@example.com')->firstOrFail();$this->assertTrue($user->is_admin);$this->assertTrue(Hash::check('unique-long-password',$user->password));
 }
}
