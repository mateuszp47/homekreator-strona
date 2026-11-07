@extends('admin.layout')
@section('title',$user->exists?'Edycja użytkownika':'Nowy użytkownik')
@section('content')
@php($own=$user->exists&&$user->id===auth()->id())
<div class="admin-title"><div><a class="text-link" href="{{ route('admin.users.index') }}">← Użytkownicy</a><h1>{{ $user->exists?'Edytuj użytkownika':'Dodaj użytkownika' }}</h1></div></div>
<form method="post" action="{{ $user->exists?route('admin.users.update',$user):route('admin.users.store') }}">@csrf @if($user->exists) @method('put') @endif
<div class="admin-form-grid"><div><div class="admin-box"><h2>Dane konta</h2>
<label>Imię i nazwisko *<input name="name" value="{{ old('name',$user->name) }}" maxlength="100" autocomplete="name" required></label>
<label>Adres e-mail *<input type="email" name="email" value="{{ old('email',$user->email) }}" maxlength="254" autocomplete="username" required><small>Adres służy do logowania.</small></label></div>
<div class="admin-box"><h2>{{ $user->exists?'Zmiana hasła':'Hasło do konta' }}</h2>
@if($user->exists)<p>Pozostaw oba pola puste, aby zachować obecne hasło. Zmiana hasła unieważni pozostałe sesje tego użytkownika.</p>@endif
<label>{{ $user->exists?'Nowe hasło':'Hasło *' }}<input type="password" name="password" minlength="12" maxlength="72" autocomplete="new-password" @required(!$user->exists)><small>Minimum 12 znaków, maksymalnie 72 bajty.</small></label>
<label>Powtórz hasło<input type="password" name="password_confirmation" minlength="12" maxlength="72" autocomplete="new-password" @required(!$user->exists)></label></div></div>
<aside><div class="admin-box publish-box"><h2>Dostęp i status</h2>
<input type="hidden" name="is_admin" value="{{ $own?1:0 }}"><label class="checkbox-label"><input type="checkbox" name="is_admin" value="1" @checked(old('is_admin',$user->exists?$user->is_admin:true)) @disabled($own)><span>Dostęp administratora</span></label><p class="price-note">Administrator może zarządzać całą stroną, ustawieniami i kontami użytkowników. Konto bez tego uprawnienia nie ma dostępu do panelu.</p>
<input type="hidden" name="is_blocked" value="0"><label class="checkbox-label"><input type="checkbox" name="is_blocked" value="1" @checked(old('is_blocked',$user->is_blocked)) @disabled($own)><span>Konto zablokowane</span></label><p class="price-note">Blokada uniemożliwia logowanie oraz dalsze korzystanie z panelu w już otwartych sesjach. Dane konta pozostają zapisane.</p>
@if($own)<p class="price-note">Nie możesz zablokować własnego konta ani odebrać sobie dostępu administratora.</p>@endif
<button class="btn btn-primary" type="submit">{{ $user->exists?'Zapisz zmiany':'Utwórz konto' }}</button></div></aside></div></form>
@endsection
