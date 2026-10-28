@inject('catalog', 'App\Support\Catalog')
@extends('layouts.app')
@section('title','Realizacje')
@section('content')
<x-page-heading title="Nasze realizacje" subtitle="Poznaj przykłady modułów i wykończeń z galerii Home Kreator. Zobacz więcej zdjęć w wybranej realizacji."/>
<section class="section"><div class="container"><nav class="filter-tabs" aria-label="Kategorie realizacji"><a @class(['active'=>!$category]) href="{{ route('projects') }}">Wszystkie</a>@foreach($catalog->categories() as $key=>$item)<a @class(['active'=>$category===$key]) href="{{ route('projects',['kategoria'=>$key]) }}">{{ $item['name'] }}</a>@endforeach</nav><div class="project-grid">@foreach($projects as $project)<x-project-card :project="$project"/>@endforeach</div></div></section><x-cta/>
@endsection
