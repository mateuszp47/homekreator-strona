<?php
namespace App\Http\Controllers;
use App\Http\Requests\ContactRequest;
use App\Mail\ProductInquiry;
use App\Models\Inquiry;
use App\Support\Catalog;
use Illuminate\Support\Facades\Mail;
use Throwable;
class ContactController extends Controller
{
 public function __invoke(ContactRequest $request,Catalog $catalog) {
  $data=$request->safe()->except(['privacy','website']);
  $model=$catalog->products()->firstWhere('slug',$data['model']??null);
  try {
   $inquiry=Inquiry::create([...$data,'model_name'=>$model['name']??null]);
  } catch(Throwable $exception) {
   report($exception);
   return back()->withInput()->withErrors(['delivery'=>'Nie udało się zapisać zapytania. Spróbuj ponownie lub zadzwoń pod 730 204 633.']);
  }
  // The database inbox is authoritative; a mail failure must not lose the inquiry.
  if($catalog->contactAvailable()) {
   try {
    Mail::to(config('homekreator.contact_recipient'))->send(new ProductInquiry($data));
    $inquiry->update(['mail_status'=>'sent']);
   } catch(Throwable $exception) {
    report($exception);
    try {$inquiry->update(['mail_status'=>'failed']);} catch(Throwable $statusException) {report($statusException);}
   }
  }
  return to_route('contact')->with('success','Dziękujemy! Twoje zapytanie zostało przyjęte. Skontaktujemy się z Tobą na podany e-mail lub telefon.');
 }
}
