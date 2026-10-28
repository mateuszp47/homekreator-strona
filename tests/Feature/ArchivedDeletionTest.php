<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\Product;
use App\Models\Project;
use Tests\TestCase;
class ArchivedDeletionTest extends TestCase {
 private function admin(): User {$user=User::factory()->create();$user->is_admin=true;$user->save();return $user;}
 public function test_active_products_and_projects_cannot_be_permanently_deleted(): void {
  $this->actingAs($this->admin());
  foreach(['produkty'=>Product::first(),'realizacje'=>Project::first()] as $kind=>$item){
   $this->get('/admin/'.$kind.'/'.$item->id.'/usun-trwale')->assertNotFound();
   $this->delete('/admin/'.$kind.'/'.$item->id.'/usun-trwale',['confirmation'=>1])->assertNotFound();
   $this->assertNotNull($item->fresh());
  }
 }
 public function test_archived_product_requires_confirmation_and_preserves_linked_projects(): void {
  $this->actingAs($this->admin());$product=Product::first();$project=Project::where('product_id',$product->id)->firstOrFail();$product->delete();$url='/admin/produkty/'.$product->id.'/usun-trwale';
  $this->get($url)->assertOk()->assertSee($product->name)->assertSee('Usuń trwale');
  $this->delete($url)->assertSessionHasErrors('confirmation');$this->assertNotNull($product->fresh());
  $this->delete($url,['confirmation'=>1])->assertRedirect(route('admin.content.index',['produkty','archiwum'=>1]));
  $this->assertDatabaseMissing('products',['id'=>$product->id]);$this->assertNull($project->fresh()->product_id);
  $this->seed();$this->assertDatabaseMissing('products',['source_key'=>$product->source_key]);
 }
 public function test_archived_project_can_be_deleted_but_not_reseeded(): void {
  $this->actingAs($this->admin());$project=Project::first();$project->delete();
  $this->delete('/admin/realizacje/'.$project->id.'/usun-trwale',['confirmation'=>1])->assertRedirect();
  $this->assertDatabaseMissing('projects',['id'=>$project->id]);$this->seed();$this->assertDatabaseMissing('projects',['source_key'=>$project->source_key]);
 }
 public function test_permanent_delete_is_admin_only_and_button_only_appears_in_archive(): void {
  $product=Product::first();$product->delete();$url='/admin/produkty/'.$product->id.'/usun-trwale';
  $this->delete($url,['confirmation'=>1])->assertRedirect(route('login'));
  $this->actingAs(User::factory()->create())->delete($url,['confirmation'=>1])->assertForbidden();
  $this->actingAs($this->admin())->get('/admin/produkty')->assertOk()->assertDontSee('Usuń trwale');
  $this->get('/admin/produkty?archiwum=1')->assertOk()->assertSee('Usuń trwale');
 }
 public function test_restored_record_cannot_be_deleted_from_an_old_confirmation_page(): void {
  $this->actingAs($this->admin());$product=Product::first();$product->delete();$url='/admin/produkty/'.$product->id.'/usun-trwale';$this->get($url)->assertOk();$product->restore();
  $this->delete($url,['confirmation'=>1])->assertNotFound();$this->assertNotNull($product->fresh());
 }
}
