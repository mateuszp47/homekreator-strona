@inject('catalog', 'App\Support\Catalog')
<!doctype html>
<html lang="pl">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
 <title>@yield('title', 'Domki modułowe, pawilony i sauny') | Home Kreator</title>
 <meta name="description" content="@yield('description', 'Home Kreator — domki modułowe, pawilony sanitarne i sauny. Poznaj ofertę i zapytaj o wycenę.')">
 <meta name="theme-color" content="#245b4b"><link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
 <link rel="canonical" href="{{ url()->current() }}">
 <meta property="og:title" content="@yield('title', 'Domki modułowe, pawilony i sauny') | Home Kreator">
 <meta property="og:type" content="website"><meta property="og:image" content="{{ asset('images/domek.jpeg') }}">
 @if(request()->hasAny(['q','sort']))<meta name="robots" content="noindex,follow">@endif
 @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#main">Przejdź do treści</a>
<header class="site-header"><div class="container header-inner">
 <a class="logo" href="{{ route('home') }}" aria-label="Home Kreator — strona główna"><svg viewBox="0 0 44 44" aria-hidden="true"><path d="M4 19 22 5l18 14v21H27V27H17v13H4Z"/><path d="M12 13V6h7"/></svg><span>home<strong>kreator</strong><small>DOMKI · PAWILONY · SAUNY</small></span></a>
 <button class="menu-toggle" aria-expanded="false" aria-controls="navigation" aria-label="Otwórz menu"><span></span><span></span><span></span></button>
 <nav id="navigation" aria-label="Nawigacja główna">
  <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Strona główna</a>
  <div class="offer-nav"><div class="offer-nav-title"><a href="{{ route('offer') }}" @if(request()->routeIs('offer','product')) aria-current="page" @endif>Oferta</a><button aria-expanded="false" aria-controls="offer-dropdown" aria-label="Rozwiń kategorie oferty"><svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></button></div>
   <div class="offer-dropdown" id="offer-dropdown"><a href="{{ route('offer') }}">Wszystkie produkty</a>@foreach($catalog->categories() as $category)<a href="{{ route('offer', $category['slug']) }}">{{ $category['name'] }}</a>@endforeach</div>
  </div>
  <a href="{{ route('projects') }}" @if(request()->routeIs('projects','project')) aria-current="page" @endif>Realizacje</a>
  <a href="{{ route('delivery') }}" @if(request()->routeIs('delivery')) aria-current="page" @endif>Transport i montaż</a>
  <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>O nas</a>
  <a href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>Kontakt</a>
 </nav>
 <a class="btn btn-primary header-call" href="tel:{{ config('homekreator.phone_href') }}"><x-icon name="phone"/>{{ config('homekreator.phone') }}</a>
</div></header>
<main id="main">@yield('content')</main>
<footer class="site-footer"><div class="container footer-grid">
 <div><a class="logo footer-logo" href="{{ route('home') }}">home<strong>kreator</strong></a><p>Gotowe moduły.<br>Przestrzeń dla Twoich pomysłów.</p><a class="footer-phone" href="tel:{{ config('homekreator.phone_href') }}">{{ config('homekreator.phone') }}</a><div class="footer-socials" aria-label="Media społecznościowe">@foreach(config('homekreator.socials') as $social)<a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Home Kreator — {{ $social['name'] }} (otwiera nową kartę)"><x-icon :name="$social['icon']"/><span>{{ $social['name'] }}</span><span aria-hidden="true">↗</span></a>@endforeach</div></div>
 <div><h2>Nasza oferta</h2>@foreach($catalog->categories() as $category)<a href="{{ route('offer',$category['slug']) }}">{{ $category['name'] }}</a>@endforeach</div>
 <div><h2>Warto wiedzieć</h2><a href="{{ route('projects') }}">Realizacje</a><a href="{{ route('delivery') }}">Transport i montaż</a><a href="{{ route('about') }}">O nas</a><a href="{{ route('contact') }}">Kontakt</a><a href="{{ route('privacy') }}">Polityka prywatności</a></div>
 <div><h2>Dane firmy</h2><p>{{ config('homekreator.company') }}<br>{{ config('homekreator.street') }}<br>{{ config('homekreator.postal_code') }} {{ config('homekreator.city') }}</p><a href="tel:{{ config('homekreator.secondary_phone_href') }}">{{ config('homekreator.secondary_phone') }}</a><a href="https://www.google.com/maps/search/?api=1&amp;query={{ rawurlencode(config('homekreator.address')) }}" target="_blank" rel="noopener">Wyznacz trasę ↗</a></div>
</div><div class="container footer-bottom"><span>© {{ date('Y') }} Home Kreator. Wszelkie prawa zastrzeżone.</span><span>Domki modułowe · Pawilony sanitarne · Sauny</span></div></footer>
<a class="mobile-call" href="tel:{{ config('homekreator.phone_href') }}"><x-icon name="phone"/> Zadzwoń: {{ config('homekreator.phone') }}</a>
<dialog id="lightbox" aria-label="Galeria zdjęć"><button data-close-lightbox aria-label="Zamknij zdjęcie">×</button><img alt=""><p></p></dialog>
</body></html>
