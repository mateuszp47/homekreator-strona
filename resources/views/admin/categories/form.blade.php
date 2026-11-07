@extends('admin.layout')
@section('title',$category->exists?'Edycja kategorii':'Nowa kategoria')
@section('content')
<div class="admin-title"><div><a class="text-link" href="{{ route('admin.categories.index') }}">← Kategorie</a><h1>{{ $category->exists?'Edytuj kategorię':'Dodaj kategorię' }}</h1></div></div>
<form method="post" enctype="multipart/form-data" action="{{ $category->exists?route('admin.categories.update',$category):route('admin.categories.store') }}">@csrf @if($category->exists) @method('put') @endif
<div class="admin-form-grid"><div><div class="admin-box"><h2>Dane kategorii</h2>
<label>Nazwa *<input name="name" value="{{ old('name',$category->name) }}" maxlength="200" required></label>
<label>Adres URL (slug)<input name="slug" value="{{ old('slug',$category->slug) }}" maxlength="200" pattern="[a-z0-9]+(-[a-z0-9]+)*"><small>Pusty adres utworzy się automatycznie z nazwy. Przy edycji pustego pola zachowamy obecny adres. Ręczna zmiana adresu zmienia link do kategorii.</small></label>
<label>Opis<textarea name="description" rows="4" maxlength="3000">{{ old('description',$category->description) }}</textarea></label>
<h2>Zdjęcie kategorii</h2>@if($category->image)<img class="admin-cover" src="{{ \App\Support\Catalog::imageUrl($category->image) }}" alt="Zdjęcie kategorii">@endif
<label>Wybierz zdjęcie<input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-image-preview="category-preview"></label><div id="category-preview" class="upload-previews" aria-live="polite"></div><p class="price-note">JPG, PNG lub WebP, maks. 10 MB. Bez zdjęcia nowa kategoria otrzyma domyślne zdjęcie domku.</p></div></div>
<aside><div class="admin-box publish-box"><h2>Widoczność i kolejność</h2><label>Kolejność<input type="number" name="sort_order" value="{{ old('sort_order',$category->sort_order??0) }}" min="0" max="99999" required><small>Niższa liczba wyświetla się wcześniej.</small></label>
<input type="hidden" name="is_active" value="0"><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$category->is_active))><span>Kategoria włączona</span></label><p class="price-note">Wyłączenie ukryje kategorię i przypisane produkty oraz realizacje na stronie, także pod ich bezpośrednimi adresami. Ponowne włączenie przywróci opublikowane wpisy.</p><button type="submit" class="btn btn-primary">Zapisz kategorię</button></div></aside></div></form>
@endsection
