@extends('layouts.app')
@section('title','O nas')
@section('content')
<x-page-heading title="Poznaj Home Kreator" subtitle="Modułowe rozwiązania do wypoczynku, pracy i prowadzenia biznesu."/>
<section class="section"><div class="container why-grid"><img class="why-photo" src="{{ asset('images/domek.jpeg') }}" alt="Domek Home Kreator z tarasem" width="1200" height="800"><div><span class="eyebrow">HOME KREATOR · SZYDŁOWIEC</span><h2>Tworzymy przestrzeń dla Twoich pomysłów.</h2><p>Za marką Home Kreator stoi ZIG ZAG BUD z Szydłowca. Oferujemy domki modułowe, pawilony sanitarne, sauny oraz moduły do zastosowań biurowych i usługowych.</p><p>Pomagamy dopasować wymiary, układ i wyposażenie do planowanego przeznaczenia. Możesz wybrać wariant podstawowy lub omówić pełne wyposażenie wnętrza.</p><ul class="check-list"><li><x-icon name="check"/>Konfiguracja dostosowana do potrzeb</li><li><x-icon name="check"/>Ocieplone konstrukcje i funkcjonalne wnętrza</li><li><x-icon name="check"/>Uzgodniony transport oraz montaż</li></ul><a class="btn btn-primary" href="{{ route('offer') }}">Poznaj naszą ofertę <x-icon/></a></div></div></section><x-cta/>
@endsection
