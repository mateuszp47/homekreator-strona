<?php
namespace App\Http\Requests;
use App\Support\Catalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ContactRequest extends FormRequest
{
 public function authorize(): bool { return true; }
 public function rules(): array { return [
  'name'=>['required','string','max:100'],'email'=>['required','email','max:254'],'phone'=>['nullable','string','max:30'],
  'city'=>['required','string','max:150'],'model'=>['nullable',Rule::in(app(Catalog::class)->products()->pluck('slug')->all())],
  'message'=>['required','string','min:10','max:5000'],'privacy'=>['accepted'],'website'=>['nullable','max:0'],
 ]; }
 public function messages(): array { return [
  'name.required'=>'Podaj imię i nazwisko.','email.required'=>'Podaj adres e-mail.','email.email'=>'Wpisz poprawny adres e-mail.',
  'city.required'=>'Podaj miejscowość dostawy.','message.required'=>'Napisz, jakiego modułu potrzebujesz.',
  'message.min'=>'Wiadomość powinna zawierać co najmniej 10 znaków.','privacy.accepted'=>'Potwierdź zapoznanie się z informacją o prywatności.',
  'model.in'=>'Wybierz model z oferty.','*.max'=>'Wartość jest za długa.',
 ]; }
}
