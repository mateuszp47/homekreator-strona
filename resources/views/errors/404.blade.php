@extends('layouts.app')
@section('title','Nie znaleziono strony')
@section('content')<section class="section"><div class="container empty-state"><span class="eyebrow">BŁĄD 404</span><h1>Nie znaleziono tej strony.</h1><p>Sprawdź naszą ofertę lub wróć na stronę główną.</p><a class="btn btn-primary" href="{{ route('home') }}">Strona główna <x-icon/></a></div></section>@endsection
