@extends('admin.layout')
@section('title','SEO strony głównej')
@section('content')
<div class="admin-title"><div><h1>SEO strony głównej</h1><p>Ustawienia dla {{ route('home') }}.</p></div></div>
<form method="post" action="{{ route('admin.seo.update') }}">@csrf @method('put')
@include('admin.partials.seo',['seo'=>$seo])
<button class="btn btn-primary" type="submit">Zapisz SEO strony głównej</button></form>
@endsection
