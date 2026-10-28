<?php
namespace Tests\Feature;
use App\Mail\ProductInquiry;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
class ContactTest extends TestCase
{
 private function inquiry(): array { return ['name'=>'Jan Testowy','email'=>'jan@example.com','phone'=>'123456789','city'=>'Radom','model'=>'pawilon-sanitarny','message'=>'Proszę o wycenę pawilonu wraz z transportem.','privacy'=>'1','website'=>'']; }
 private function enableMail(): void { config(['homekreator.contact_enabled'=>true,'homekreator.contact_recipient'=>'firma@example.com','mail.default'=>'smtp']); }
 public function test_invalid_contact_is_rejected_before_sending(): void {
  Mail::fake();$this->enableMail();$this->post('/kontakt',['email'=>'brak'])->assertSessionHasErrors(['name','email','city','message','privacy']);Mail::assertNothingSent();$this->assertDatabaseCount('inquiries',0);
 }
 public function test_form_saves_inquiry_without_smtp(): void {
  Mail::fake();config(['homekreator.contact_enabled'=>false]);$this->from('/kontakt')->post('/kontakt',$this->inquiry())->assertRedirect('/kontakt')->assertSessionHas('success');Mail::assertNothingSent();
  $this->assertDatabaseHas('inquiries',['email'=>'jan@example.com','model_name'=>'Pawilon sanitarny 2 × WC + pisuar','mail_status'=>'not_configured']);
 }
 public function test_configured_mail_sends_to_company_and_sets_reply_to(): void {
  Mail::fake();$this->enableMail();$this->post('/kontakt',$this->inquiry())->assertRedirect(route('contact'))->assertSessionHas('success');
  $this->assertDatabaseHas('inquiries',['email'=>'jan@example.com','mail_status'=>'sent']);
  Mail::assertSent(ProductInquiry::class,fn($mail)=>$mail->hasTo('firma@example.com')&&$mail->envelope()->replyTo[0]->address==='jan@example.com'&&$mail->inquiry['model']==='pawilon-sanitarny');
 }
 public function test_mail_transport_failure_does_not_lose_the_saved_inquiry(): void {
  $this->enableMail();Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Transport failed'));
  $this->from('/kontakt')->post('/kontakt',$this->inquiry())->assertRedirect('/kontakt')->assertSessionHas('success');
  $this->assertDatabaseHas('inquiries',['email'=>'jan@example.com','mail_status'=>'failed']);
 }
 public function test_honeypot_is_rejected_and_email_html_is_escaped(): void {
  Mail::fake();$this->enableMail();$data=$this->inquiry();$data['website']='spam';$this->post('/kontakt',$data)->assertSessionHasErrors('website');Mail::assertNothingSent();
  $data=$this->inquiry();$data['message']='<script>alert("test")</script>';$html=(new ProductInquiry($data))->render();$this->assertStringNotContainsString('<script>',$html);$this->assertStringContainsString('&lt;script&gt;',$html);
 }
 public function test_contact_endpoint_is_rate_limited(): void {
  config(['homekreator.contact_enabled'=>false]);
  for($i=0;$i<5;$i++) $this->post('/kontakt',$this->inquiry())->assertRedirect();
  $this->post('/kontakt',$this->inquiry())->assertStatus(429);
 }

 public function test_database_failure_cannot_report_success(): void {
  Inquiry::creating(fn()=>throw new \RuntimeException('Database unavailable'));
  try {$this->from('/kontakt')->post('/kontakt',$this->inquiry())->assertSessionHasErrors('delivery')->assertSessionMissing('success')->assertSessionHasInput('city','Radom');}
  finally {Inquiry::flushEventListeners();}
  $this->assertDatabaseCount('inquiries',0);
 }
 public function test_inbox_is_only_accessible_to_admin_and_escapes_messages(): void {
  $this->get('/admin/wiadomosci')->assertRedirect(route('login'));
  $user=User::factory()->create();$this->actingAs($user)->get('/admin/wiadomosci')->assertForbidden();
  $inquiry=Inquiry::create([...collect($this->inquiry())->except(['privacy','website'])->all(),'message'=>'<script>alert(1)</script>']);
  $this->patch('/admin/wiadomosci/'.$inquiry->id.'/status',['read'=>1])->assertForbidden();
  $user->is_admin=true;$user->save();$this->actingAs($user);
  $this->get('/admin/wiadomosci')->assertOk()->assertSee('jan@example.com');
  $this->get('/admin/wiadomosci/'.$inquiry->id)->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;',false)->assertDontSee('<script>alert(1)</script>',false);
  $this->assertNull($inquiry->fresh()->read_at);
  $this->from('/admin/wiadomosci/'.$inquiry->id)->patch('/admin/wiadomosci/'.$inquiry->id.'/status',['read'=>1])->assertRedirect();
  $this->assertNotNull($inquiry->fresh()->read_at);
  $this->get('/admin/wiadomosci?nieprzeczytane=1')->assertOk()->assertDontSee('jan@example.com');
  $this->patch('/admin/wiadomosci/'.$inquiry->id.'/status',['read'=>0])->assertRedirect();$this->assertNull($inquiry->fresh()->read_at);
 }
 public function test_contact_form_is_enabled_without_mail_configuration(): void {
  config(['homekreator.contact_enabled'=>false]);
  $this->get('/kontakt')->assertOk()->assertSee('data-contact-submit',false)->assertDontSee('Wysyłka formularza nie jest obecnie dostępna');
 }
}
