<?php
namespace Tests\Feature;
use App\Support\Catalog;
use Tests\TestCase;
class WebsiteTest extends TestCase
{
 public function test_all_public_pages_and_catalog_entries_render(): void {
  foreach(['/','/oferta','/realizacje','/o-nas','/transport-i-montaz','/kontakt','/polityka-prywatnosci'] as $url) $this->get($url)->assertOk()->assertSee('Home Kreator');
  $catalog=app(Catalog::class);
  foreach($catalog->categories() as $category) $this->get('/oferta/'.$category['slug'])->assertOk()->assertSee($category['name']);
  foreach($catalog->products() as $product) $this->get('/produkty/'.$product['slug'])->assertOk()->assertSee($product['name']);
  foreach($catalog->projects() as $project) $this->get('/realizacje/'.$project['slug'])->assertOk()->assertSee($project['name']);
 }
 public function test_category_and_search_filter_products_on_server(): void {
  $this->get('/oferta/pawilony-sanitarne')->assertOk()->assertSee('Liczba produktów: 1')->assertSee('Pawilon sanitarny 2 × WC + pisuar')->assertDontSee('href="'.route('product','domek-modulowy-6x3').'"',false);
  $this->get('/oferta?q=7')->assertOk()->assertSee('Domek modułowy 7 × 3 m')->assertSee('Liczba produktów: 1');
  $this->get('/oferta?q=nieistniejacy')->assertOk()->assertSee('Brak pasujących produktów');
 }
 public function test_unpriced_products_sort_after_priced_products(): void {
  $this->get('/oferta?sort=price-asc')->assertOk()->assertSeeInOrder(['Pawilon sanitarny 2 × WC + pisuar','Domek modułowy 6 × 3 m','Sauna ogrodowa']);
 }
 public function test_invalid_urls_return_real_404(): void {
  foreach(['/produkty/brak','/oferta/brak','/realizacje/brak','/realizacje?kategoria=brak'] as $url) $this->get($url)->assertNotFound();
 }
 public function test_query_arrays_are_rejected_without_server_error(): void {
  $this->getJson('/oferta?q[]=x')->assertUnprocessable();
  $this->get('/kontakt?model[]=x')->assertOk();
 }
 public function test_selected_product_is_carried_to_contact(): void {
  $this->get('/kontakt?model=pawilon-sanitarny')->assertOk()->assertSee('value="pawilon-sanitarny" selected',false);
 }
 public function test_legacy_product_urls_redirect_and_sitemap_is_xml(): void {
  $this->get('/domek-modulowy-6x3.html')->assertRedirect(route('product','domek-modulowy-6x3'))->assertStatus(301);
  $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type','application/xml')->assertSee(route('product','pawilon-sanitarny'));
 }
}
