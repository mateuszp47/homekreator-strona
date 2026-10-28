<?php
namespace Tests\Feature;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use App\Models\SiteSetting;
use App\Support\SiteSettings;
use App\Services\ImageProcessor;
use App\Services\BackupService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class ImprovementsTest extends TestCase {
 private function admin(): User {$u=User::factory()->create();$u->is_admin=true;$u->save();return $u;}
 public function test_uploaded_images_are_resized_to_webp(): void {
  Storage::fake('public');$file=UploadedFile::fake()->image('large.jpg',3000,1500);$path=app(ImageProcessor::class)->store($file);
  $this->assertStringEndsWith('.webp',$path);$info=getimagesize(Storage::disk('public')->path($path));$this->assertSame(2048,$info[0]);$this->assertSame(1024,$info[1]);$this->assertSame('image/webp',$info['mime']);
 }
 public function test_gallery_order_and_product_details_are_saved_without_changing_cover(): void {
  $this->actingAs($this->admin());$p=Product::first();$gallery=$p->gallery;$order=array_reverse($gallery);
  $this->put('/admin/produkty/'.$p->id,['name'=>$p->name,'slug'=>$p->slug,'category'=>$p->category,'description'=>$p->description,'size'=>$p->size,'area'=>$p->area,'sort_order'=>0,'is_published'=>1,'is_featured'=>1,'gallery_order'=>$order,'base_includes'=>'Stalowa konstrukcja','extra_options'=>'Łazienka za dopłatą','transport_note'=>'Transport wyceniany osobno'])->assertSessionHasNoErrors();
  $p->refresh();$this->assertSame($order,$p->gallery);$this->assertSame($gallery[0],$p->image);$this->assertTrue($p->is_featured);$this->get('/produkty/'.$p->slug)->assertOk()->assertSee('Stalowa konstrukcja')->assertSee('Transport wyceniany osobno');
 }
 public function test_featured_products_are_used_on_home_but_drafts_are_hidden(): void {
  $products=Product::all();$products[4]->update(['is_featured'=>true]);$products[5]->update(['is_featured'=>true,'is_published'=>false]);
  $selected=app(\App\Support\Catalog::class)->featuredProducts();$this->assertCount(1,$selected);$this->assertSame($products[4]->slug,$selected->first()['slug']);
 }
 public function test_project_facts_are_editable_and_displayed(): void {
  $this->actingAs($this->admin());$p=Project::first();$this->put('/admin/realizacje/'.$p->id,['name'=>$p->name,'slug'=>$p->slug,'category'=>$p->category,'description'=>$p->description,'sort_order'=>0,'is_published'=>1,'location'=>'Radom','dimensions'=>'8 × 3 m','purpose'=>'Biuro','equipment_details'=>'Klimatyzacja i aneks'])->assertSessionHasNoErrors();
  $this->get('/realizacje/'.$p->slug)->assertOk()->assertSee('Radom')->assertSee('8 × 3 m')->assertSee('Klimatyzacja i aneks');
 }
 public function test_site_settings_are_admin_only_and_update_public_contact_and_faq(): void {
  $this->get('/admin/ustawienia')->assertRedirect(route('login'));$this->actingAs(User::factory()->create())->put('/admin/ustawienia',[])->assertForbidden();
  $this->actingAs($this->admin());$data=app(SiteSettings::class)->defaults();$data['company']='Firma testowa';$data['phone']='+48 111 222 333';$data['street']='Testowa 12';$data['contact_enabled']=0;$data['faq']=[['question'=>'Pytanie testowe?','answer'=>'Odpowiedź testowa.']];
  $this->put('/admin/ustawienia',$data)->assertSessionHasNoErrors();$this->get('/')->assertOk()->assertSee('+48 111 222 333')->assertSee('Pytanie testowe?');$this->get('/kontakt')->assertOk()->assertSee('Firma testowa')->assertSee('Testowa 12');
  $data['instagram']='https://evil.example/';$this->put('/admin/ustawienia',$data)->assertSessionHasErrors('instagram');
 }
 public function test_backup_and_settings_pages_render_for_admin_and_reject_regular_users(): void {
  $this->actingAs(User::factory()->create())->get('/admin/kopie')->assertForbidden();$this->actingAs($this->admin())->get('/admin/kopie')->assertOk();$this->get('/admin/ustawienia')->assertOk();
  $this->get('/admin/kopie/anything')->assertNotFound();
 }
 public function test_sqlite_backup_contains_a_restorable_database_and_uploaded_photo(): void {
  // Use an independent SQLite connection: VACUUM INTO cannot run inside RefreshDatabase's test transaction.
  $root=storage_path('framework/testing/backup-'.bin2hex(random_bytes(5)));File::ensureDirectoryExists($root);
  $oldDefault=config('database.default');Storage::fake('public');Storage::disk('public')->put('catalog/test.webp','sample-photo');
  config(['backups.directory'=>$root.'/archives','backups.keep'=>2,'database.connections.backup_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
  DB::setDefaultConnection('backup_test');DB::statement('CREATE TABLE backup_sample (name TEXT)');DB::table('backup_sample')->insert(['name'=>'Zachowane dane']);
  try {
   $service=app(BackupService::class);$name=$service->create();$path=$service->path($name);$archive=new \PharData($path);File::ensureDirectoryExists($root.'/restore/uploads/catalog');file_put_contents($root.'/restore/database.sqlite',$archive['database.sqlite']->getContent());file_put_contents($root.'/restore/uploads/catalog/test.webp',$archive['uploads/catalog/test.webp']->getContent());file_put_contents($root.'/restore/manifest.json',$archive['manifest.json']->getContent());unset($archive);
   $restored=new \PDO('sqlite:'.$root.'/restore/database.sqlite');$this->assertSame('Zachowane dane',$restored->query('SELECT name FROM backup_sample')->fetchColumn());unset($restored);
   $this->assertSame('sample-photo',file_get_contents($root.'/restore/uploads/catalog/test.webp'));$this->assertFileExists($root.'/restore/manifest.json');$this->assertFileDoesNotExist($root.'/restore/.env');
   $service->create();$service->create();$this->assertCount(2,$service->files());
  } finally {DB::setDefaultConnection($oldDefault);DB::purge('backup_test');File::deleteDirectory($root);}
 }
}
