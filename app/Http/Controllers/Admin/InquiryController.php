<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
class InquiryController extends Controller {
 public function index(Request $request) {
  $unread=$request->boolean('nieprzeczytane');
  $query=Inquiry::query();if($unread)$query->whereNull('read_at');
  return view('admin.inquiries.index',['inquiries'=>$query->latest('id')->paginate(20)->withQueryString(),'unread'=>$unread,'unreadCount'=>Inquiry::whereNull('read_at')->count()]);
 }
 public function show(Inquiry $inquiry) {return view('admin.inquiries.show',compact('inquiry'));}
 public function read(Request $request,Inquiry $inquiry) {
  $request->validate(['read'=>['required','boolean']]);
  $inquiry->update(['read_at'=>$request->boolean('read')?now():null]);
  return back()->with('success',$request->boolean('read')?'Oznaczono jako przeczytaną.':'Oznaczono jako nieprzeczytaną.');
 }
}
