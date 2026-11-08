<?php
namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductPresentationTest extends TestCase {
 private function admin(): User {$user=User::factory()->create();$user->is_admin=true;$user->save();return $user;}
 public function test_product_can_be_created_without_area_despite_non_nullable_database_column(): void {
  Storage::fake('public');$this->actingAs($this->admin());
  $this->post('/admin/produkty',[
   'name'=>'Produkt bez powierzchni','category'=>'domki','description'=>'Opis produktu pod zdjęciami.','size'=>'8 × 3 m',
   'sort_order'=>0,'is_published'=>1,'equipment_text'=>"Klimatyzacja\nAneks kuchenny",
   'image'=>UploadedFile::fake()->createWithContent('domek.jpg',file_get_contents(public_path('images/domek.jpeg'))),
  ])->assertSessionHasNoErrors()->assertRedirect();
  $product=Product::where('slug','produkt-bez-powierzchni')->firstOrFail();$this->assertSame('',$product->area);
  $this->get('/produkty/'.$product->slug)->assertOk()->assertDontSee('<dt>Przestrzeń</dt>',false)
   ->assertSeeInOrder(['<article class="product-description">','<h2>Opis produktu</h2>','<div class="container equipment-grid">','Możliwości i wyposażenie','Klimatyzacja','Aneks kuchenny','Co obejmuje oferta?'],false);
 }
 public function test_dimensions_are_optional_on_create_and_can_be_cleared_on_update(): void {
  Storage::fake('public');$this->actingAs($this->admin());
  $data=['name'=>'Model bez wymiarów','category'=>'domki','description'=>'Opis modelu bez wymiarów.','sort_order'=>0,'is_published'=>1,'image'=>UploadedFile::fake()->createWithContent('domek.jpg',file_get_contents(public_path('images/domek.jpeg')))];
  $this->post('/admin/produkty',$data)->assertSessionHasNoErrors();
  $product=Product::where('slug','model-bez-wymiarow')->firstOrFail();$this->assertSame('',$product->size);
  $this->get('/produkty/'.$product->slug)->assertOk()->assertDontSee('<dt>Wymiary</dt>',false)->assertDontSee('<dl class="specs">',false);
  $card=view('components.product-card',['product'=>$product->catalogData()])->render();$this->assertStringNotContainsString('class="dimensions"',$card);
  unset($data['image']);$data['size']='8 × 3 m';$this->put('/admin/produkty/'.$product->id,$data)->assertSessionHasNoErrors();
  $this->get('/produkty/'.$product->slug)->assertOk()->assertSee('<dt>Wymiary</dt><dd>8 × 3 m</dd>',false);
  $data['size']='';$data['area']='24 m²';$this->put('/admin/produkty/'.$product->id,$data)->assertSessionHasNoErrors();
  $this->assertSame('',$product->fresh()->size);
  $this->get('/produkty/'.$product->slug)->assertOk()->assertDontSee('<dt>Wymiary</dt>',false)->assertSee('<dt>Przestrzeń</dt><dd>24 m²</dd>',false);
 }
 public function test_existing_area_can_be_cleared_and_description_stays_in_its_own_section(): void {
  $this->actingAs($this->admin());$product=Product::first();
  $data=['name'=>$product->name,'slug'=>$product->slug,'category'=>$product->category,'description'=>"Pierwszy akapit.\nDrugi akapit.",'size'=>$product->size,'area'=>'','sort_order'=>0,'is_published'=>1];
  $this->put('/admin/produkty/'.$product->id,$data)->assertSessionHasNoErrors();$this->assertSame('',$product->fresh()->area);
  $response=$this->get('/produkty/'.$product->slug)->assertOk()->assertDontSee('<dt>Przestrzeń</dt>',false);
  $this->assertStringContainsString('<article class="product-description"><h2>Opis produktu</h2><div class="rich-description"><p>',$response->getContent());
  $response->assertSeeInOrder(['Pierwszy akapit.','Drugi akapit.']);
  $data['area']='24 m²';$this->put('/admin/produkty/'.$product->id,$data)->assertSessionHasNoErrors();
  $this->get('/produkty/'.$product->slug)->assertOk()->assertSee('<dt>Przestrzeń</dt><dd>24 m²</dd>',false);
 }
}
