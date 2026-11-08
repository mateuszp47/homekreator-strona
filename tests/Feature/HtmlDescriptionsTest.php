<?php
namespace Tests\Feature;

use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use App\Support\FormattedDescription;
use Tests\TestCase;

class HtmlDescriptionsTest extends TestCase {
 private function admin(): User {$user=User::factory()->create();$user->is_admin=true;$user->save();return $user;}
 private function data($item,string $description): array {return [...array_intersect_key($item->toArray(),array_flip(['name','slug','category','size','area','sort_order','is_published'])),'description'=>$description];}
 public function test_editor_is_available_for_products_and_projects(): void {
  $this->actingAs($this->admin());
  foreach(['/admin/produkty/dodaj','/admin/realizacje/dodaj'] as $url)$this->get($url)->assertOk()->assertSee('data-html-editor',false)->assertSee('data-editor-source',false)->assertSee('Nagłówek H2');
 }
 public function test_formatted_descriptions_are_saved_and_displayed_safely_for_both_content_types(): void {
  $this->actingAs($this->admin());
  $html='<h2>Ławki &amp; piec</h2><p>Opis <strong>sauny</strong>.</p><ul><li>Olcha</li></ul><a href="https://homekreator.com/oferta" target="_blank" onclick="alert(1)">Oferta</a><script>alert(1)</script><img src="x" onerror="alert(1)"><a href="jav&#x61;script:alert(1)">Zły link</a>';
  foreach(['produkty'=>Product::first(),'realizacje'=>Project::first()] as $kind=>$item){
   $this->put('/admin/'.$kind.'/'.$item->id,$this->data($item,$html))->assertSessionHasNoErrors();
   $saved=$item->fresh()->description;
   $this->assertStringContainsString('<h2>Ławki &amp; piec</h2>',$saved);$this->assertStringContainsString('<strong>sauny</strong>',$saved);
   $this->assertStringContainsString('rel="noopener noreferrer"',$saved);
   foreach(['<script','<img','onclick','onerror','javascript:'] as $unsafe)$this->assertStringNotContainsString($unsafe,$saved);
   $this->get('/'.$kind.'/'.$item->slug)->assertOk()->assertSee('<strong>sauny</strong>',false)->assertSee('<ul><li>Olcha</li></ul>',false)->assertDontSee('&lt;strong&gt;',false)
    ->assertSee('<meta name="description" content="Ławki &amp; piec Opis sauny. Olcha Oferta Zły link">',false);
  }
 }
 public function test_active_content_and_unsafe_url_encodings_are_removed(): void {
  $payloads=[
   '<svg><script>alert(1)</script></svg><p>Treść</p>',
   '<iframe srcdoc="<script>alert(1)</script>"></iframe><p>Treść</p>',
   '<p style="background:url(javascript:alert(1))" onmouseover="alert(1)">Treść</p>',
   '<a href="java&#10;script:alert(1)">Treść</a>',
   '<math><mtext><img src=x onerror=alert(1)></mtext></math><p>Treść</p>',
  ];
  foreach($payloads as $payload){
   $clean=FormattedDescription::sanitize($payload);
   foreach(['script','onerror','onmouseover','style=','<svg','<math','<iframe','<img'] as $unsafe)$this->assertStringNotContainsString($unsafe,$clean);
   $this->assertStringContainsString('Treść',$clean);
  }
 }
 public function test_empty_formatting_is_rejected_and_existing_plain_text_keeps_line_breaks(): void {
  $this->actingAs($this->admin());$product=Product::first();
  foreach(['<p><br></p>','<script>alert(1)</script>','<p>&nbsp;</p>'] as $empty)$this->put('/admin/produkty/'.$product->id,$this->data($product,$empty))->assertSessionHasErrors('description');
  $product->description="Pierwsza linia.\nDruga linia < 10 & > 2.";$product->save();
  $this->get('/produkty/'.$product->slug)->assertOk()->assertSee('Pierwsza linia.<br>',false)->assertSee('Druga linia &lt; 10 &amp; &gt; 2.',false);
 }
 public function test_search_uses_description_text_instead_of_html_markup(): void {
  $product=Product::first();$product->description='<p>Wyjątkowa <strong>sauna</strong> ogrodowa</p>';$product->save();
  $this->get('/oferta?q='.urlencode('Wyjątkowa sauna'))->assertOk()->assertSee($product->name)->assertSee('Liczba produktów: 1');
 }
}
