<?php
namespace App\Http\Controllers;
use App\Http\Requests\ContactRequest;
use App\Mail\ProductInquiry;
use App\Support\Catalog;
use Illuminate\Support\Facades\Mail;
use Throwable;
class ContactController extends Controller
{
 public function __invoke(ContactRequest $request,Catalog $catalog) {
  if(!$catalog->contactAvailable()) return back()->withInput()->withErrors(['delivery'=>'Wysyłka wiadomości jest obecnie niedostępna. Zadzwoń pod 730 204 633.']);
  try { Mail::to(config('homekreator.contact_recipient'))->send(new ProductInquiry($request->safe()->except(['privacy','website']))); }
  catch(Throwable $exception) { report($exception);return back()->withInput()->withErrors(['delivery'=>'Nie udało się wysłać wiadomości. Spróbuj ponownie lub zadzwoń pod 730 204 633.']); }
  return to_route('contact')->with('success','Wiadomość została wysłana. Dziękujemy za kontakt z Home Kreator.');
 }
}
