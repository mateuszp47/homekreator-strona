<?php
namespace Tests\Feature;
use App\Mail\ProductInquiry;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
class ContactTest extends TestCase
{
 private function inquiry(): array { return ['name'=>'Jan Testowy','email'=>'jan@example.com','phone'=>'123456789','city'=>'Radom','model'=>'pawilon-sanitarny','message'=>'Proszę o wycenę pawilonu wraz z transportem.','privacy'=>'1','website'=>'']; }
 private function enableMail(): void { config(['homekreator.contact_enabled'=>true,'homekreator.contact_recipient'=>'firma@example.com','mail.default'=>'smtp']); }
 public function test_invalid_contact_is_rejected_before_sending(): void {
  Mail::fake();$this->enableMail();$this->post('/kontakt',['email'=>'brak'])->assertSessionHasErrors(['name','email','city','message','privacy']);Mail::assertNothingSent();
 }
 public function test_disabled_mail_cannot_report_success(): void {
  Mail::fake();config(['homekreator.contact_enabled'=>false]);$this->from('/kontakt')->post('/kontakt',$this->inquiry())->assertRedirect('/kontakt')->assertSessionHasErrors('delivery')->assertSessionMissing('success');Mail::assertNothingSent();
 }
 public function test_configured_mail_sends_to_company_and_sets_reply_to(): void {
  Mail::fake();$this->enableMail();$this->post('/kontakt',$this->inquiry())->assertRedirect(route('contact'))->assertSessionHas('success');
  Mail::assertSent(ProductInquiry::class,fn($mail)=>$mail->hasTo('firma@example.com')&&$mail->envelope()->replyTo[0]->address==='jan@example.com'&&$mail->inquiry['model']==='pawilon-sanitarny');
 }
 public function test_mail_transport_failure_preserves_input_and_reports_error(): void {
  $this->enableMail();Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Transport failed'));
  $this->from('/kontakt')->post('/kontakt',$this->inquiry())->assertRedirect('/kontakt')->assertSessionHasErrors('delivery')->assertSessionMissing('success')->assertSessionHasInput('city','Radom');
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
}
