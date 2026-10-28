@extends('layouts.app')
@section('title',$project['name'])
@section('content')
<x-page-heading :title="$project['name']" :subtitle="$project['description']"/>
<section class="section"><div class="container"><div class="photo-grid">@foreach($project['gallery'] as $photo)<button data-lightbox="{{ asset('images/'.$photo) }}" data-caption="{{ $project['name'] }} — zdjęcie {{ $loop->iteration }}" aria-label="Powiększ zdjęcie {{ $loop->iteration }}"><img src="{{ asset('images/'.$photo) }}" alt="{{ $project['name'] }} — widok {{ $loop->iteration }}" width="1200" height="800" loading="lazy"><span>Powiększ ↗</span></button>@endforeach</div><p class="price-note">Przykładowa konfiguracja z galerii Home Kreator. Zakres nowego zamówienia ustalamy indywidualnie.</p><div class="button-row center-action"><a class="btn btn-red" href="{{ route('product',$project['product']) }}">Poznaj podobny moduł <x-icon/></a><a class="btn btn-outline" href="{{ route('projects') }}">Wszystkie realizacje</a></div></div></section><x-cta/>
@endsection
