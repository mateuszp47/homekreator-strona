<?php
namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoAndCategoriesTest extends TestCase {
 private function admin(): User {return User::factory()->create(['is_admin'=>true]);}
 private function contentData($item): array {
  return array_intersect_key($item->toArray(),array_flip(['name','slug','category','description','size','area','sort_order','is_published']));
 }
 public function test_new_admin_pages_are_protected_and_render(): void {
  foreach(['/admin/seo','/admin/kategorie','/admin/kategorie/dodaj'] as $url)$this->get($url)->assertRedirect(route('login'));
  $this->actingAs(User::factory()->create());
  foreach(['/admin/seo','/admin/kategorie'] as $url)$this->get($url)->assertForbidden();
  $this->post('/admin/kategorie',[])->assertForbidden();
  $this->put('/admin/seo',[])->assertForbidden();
  $this->patch('/admin/kategorie/domki/status')->assertForbidden();
  $this->actingAs($this->admin());
  foreach(['/admin/seo','/admin/kategorie','/admin/kategorie/dodaj','/admin/kategorie/domki/edytuj'] as $url)$this->get($url)->assertOk();
 }
 public function test_initial_categories_preserve_existing_keys_and_urls(): void {
  foreach(config('homekreator.categories') as $key=>$category){
   $this->assertDatabaseHas('categories',['key'=>$key,'slug'=>$category['slug'],'name'=>$category['name'],'is_active'=>true]);
   $this->get('/oferta/'.$category['slug'])->assertOk();
  }
 }
 public function test_home_seo_is_saved_rendered_and_independent_of_other_settings(): void {
  SiteSetting::create(['key'=>'site','value'=>['company'=>'Firma bez zmian']]);
  $this->actingAs($this->admin())->put('/admin/seo',[
   'meta_title'=>'Domki & sauny | Home Kreator','meta_description'=>'Opis strony głównej do wyszukiwarek.',
   'canonical_url'=>'https://homekreator.com/','seo_noindex'=>1,
  ])->assertSessionHasNoErrors()->assertRedirect(route('admin.seo.edit'));
  $this->get('/')->assertOk()->assertSee('<title>Domki &amp; sauny | Home Kreator</title>',false)
   ->assertSee('<meta name="description" content="Opis strony głównej do wyszukiwarek.">',false)
   ->assertSee('<link rel="canonical" href="https://homekreator.com/">',false)
   ->assertSee('<meta property="og:description" content="Opis strony głównej do wyszukiwarek.">',false)
   ->assertSee('<meta name="robots" content="noindex,follow">',false);
  $this->get('/sitemap.xml')->assertDontSee('<loc>'.route('home').'</loc>',false);
  $this->assertSame('Firma bez zmian',SiteSetting::find('site')->value['company']);
  $this->put('/admin/seo',['meta_title'=>'','meta_description'=>'','canonical_url'=>'','seo_noindex'=>0])->assertSessionHasNoErrors();
  $this->get('/')->assertSee('<title>Domki modułowe i pawilony dopasowane do Ciebie | Home Kreator</title>',false)->assertDontSee('noindex,follow');
  $this->get('/sitemap.xml')->assertSee('<loc>'.route('home').'</loc>',false);
 }
 public function test_content_seo_is_saved_rendered_and_excluded_from_sitemap_only_when_noindex(): void {
  $this->actingAs($this->admin());
  foreach(['produkty'=>Product::first(),'realizacje'=>Project::first()] as $kind=>$item){
   $data=[...$this->contentData($item),'is_published'=>1,'meta_title'=>'Własny tytuł '.$kind,'meta_description'=>'Specjalny opis '.$kind,'canonical_url'=>'https://homekreator.com/'.$kind.'/'.$item->slug,'seo_noindex'=>1];
   $this->put('/admin/'.$kind.'/'.$item->id,$data)->assertSessionHasNoErrors();
   $this->get('/'.$kind.'/'.$item->slug)->assertOk()->assertSee('<title>Własny tytuł '.$kind.'</title>',false)
    ->assertSee('<meta name="description" content="Specjalny opis '.$kind.'">',false)
    ->assertSee('<link rel="canonical" href="'.$data['canonical_url'].'">',false)
    ->assertSee('<meta name="robots" content="noindex,follow">',false);
   $this->get('/sitemap.xml')->assertDontSee('<loc>'.url('/'.$kind.'/'.$item->slug).'</loc>',false);
   $data['seo_noindex']=0;$data['meta_title']='';$data['meta_description']='';$data['canonical_url']='';
   $this->put('/admin/'.$kind.'/'.$item->id,$data)->assertSessionHasNoErrors();
   $this->get('/'.$kind.'/'.$item->slug)->assertOk()->assertSee('<title>'.e($item->name).' | Home Kreator</title>',false)->assertDontSee('noindex,follow');
   $this->get('/sitemap.xml')->assertSee('<loc>'.url('/'.$kind.'/'.$item->slug).'</loc>',false);
  }
 }
 public function test_invalid_seo_is_rejected_and_titles_are_escaped(): void {
  $this->actingAs($this->admin());
  $this->put('/admin/seo',['canonical_url'=>'javascript:alert(1)','meta_description'=>str_repeat('a',501)])->assertSessionHasErrors(['canonical_url','meta_description']);
  $this->put('/admin/seo',['meta_title'=>'<script>alert("x")</script>','seo_noindex'=>0])->assertSessionHasNoErrors();
  $this->get('/')->assertOk()->assertSee('&lt;script&gt;',false)->assertDontSee('<title><script>',false);
 }
 public function test_categories_can_be_added_with_automatic_slugs_images_and_used_for_content(): void {
  Storage::fake('public');$this->actingAs($this->admin());
  $data=['name'=>'Nowe łazienki','description'=>'Nowa kategoria','sort_order'=>7,'is_active'=>1,'image'=>UploadedFile::fake()->image('kategoria.jpg',600,400)];
  $this->post('/admin/kategorie',$data)->assertSessionHasNoErrors()->assertRedirect();
  $category=Category::where('slug','nowe-lazienki')->firstOrFail();Storage::disk('public')->assertExists($category->image);
  unset($data['image']);$this->post('/admin/kategorie',$data)->assertSessionHasNoErrors();
  $this->assertDatabaseHas('categories',['slug'=>'nowe-lazienki-2']);
  $this->get('/')->assertOk()->assertSee('Nowe łazienki');
  foreach(['produkty'=>Product::first(),'realizacje'=>Project::first()] as $kind=>$item){
   $this->put('/admin/'.$kind.'/'.$item->id,[...$this->contentData($item),'category'=>$category->key])->assertSessionHasNoErrors();
   $this->assertSame($category->key,$item->fresh()->category);
   $this->get('/admin/'.$kind.'/'.$item->id.'/edytuj')->assertOk()->assertSee('Nowe łazienki');
  }
 }
 public function test_category_edits_keep_assignments_and_update_public_labels(): void {
  $this->actingAs($this->admin());$product=Product::where('category','domki')->firstOrFail();
  $this->put('/admin/kategorie/domki',['name'=>'Domy na działkę','slug'=>'domki-modulowe','description'=>'Nowy opis kategorii.','sort_order'=>8,'is_active'=>1])->assertSessionHasNoErrors();
  $this->assertSame('domki',$product->fresh()->category);$this->assertSame('Domy na działkę',$product->fresh()->label);
  $this->get('/oferta/domki-modulowe')->assertOk()->assertSee('Domy na działkę')->assertSee('Nowy opis kategorii.');
  $this->get('/produkty/'.$product->slug)->assertOk()->assertSee('Domy na działkę');
  $this->seed();$this->assertSame('Domy na działkę',Category::find('domki')->name);
 }
 public function test_disabling_and_reenabling_category_hides_and_restores_content_everywhere(): void {
  $this->actingAs($this->admin());$product=Product::where('category','domki')->firstOrFail();$project=Project::where('category','domki')->firstOrFail();
  $this->patch('/admin/kategorie/domki/status')->assertRedirect();
  $this->get('/oferta/domki-modulowe')->assertNotFound();
  $this->get('/produkty/'.$product->slug)->assertNotFound();$this->get('/realizacje/'.$project->slug)->assertNotFound();
  $this->get('/realizacje?kategoria=domki')->assertNotFound();
  foreach(['/','/oferta','/realizacje','/sitemap.xml'] as $url)$this->get($url)->assertOk()->assertDontSee(route('product',$product->slug))->assertDontSee(route('project',$project->slug))->assertDontSee(route('offer','domki-modulowe'));
  $this->get('/admin/produkty/'.$product->id.'/edytuj')->assertOk()->assertSee('Domki modułowe (wyłączona)');
  $this->assertTrue($product->fresh()->is_published);$this->assertTrue($project->fresh()->is_published);
  $this->patch('/admin/kategorie/domki/status')->assertRedirect();
  $this->get('/produkty/'.$product->slug)->assertOk();$this->get('/realizacje/'.$project->slug)->assertOk();
 }
 public function test_duplicate_category_slugs_invalid_images_and_unknown_categories_are_rejected(): void {
  $this->actingAs($this->admin());
  $this->post('/admin/kategorie',['name'=>'Duplikat','slug'=>'domki-modulowe','sort_order'=>0,'is_active'=>1])->assertSessionHasErrors('slug');
  $this->put('/admin/kategorie/domki',['name'=>'Nazwa','slug'=>'../zly','sort_order'=>0,'is_active'=>1,'image'=>UploadedFile::fake()->createWithContent('bad.svg','<svg/>')])->assertSessionHasErrors(['slug','image']);
  $product=Product::first();$this->put('/admin/produkty/'.$product->id,[...$this->contentData($product),'category'=>'nieistniejaca'])->assertSessionHasErrors('category');
 }
}
