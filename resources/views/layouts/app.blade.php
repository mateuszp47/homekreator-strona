@inject('catalog', 'App\Support\Catalog')
<!doctype html>
<html lang="pl">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
 <title>@yield('title', 'Domki modułowe, pawilony i sauny') | Home Kreator</title>
 <meta name="description" content="@yield('description', 'Home Kreator — domki modułowe, pawilony sanitarne i sauny. Wybierz model, poznaj wyposażenie i zapytaj o wycenę. Telefon 730 204 633.')">
 <meta name="theme-color" content="#e30613"><link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
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
  <div class="offer-nav"><div class="offer-nav-title"><a href="{{ route('offer') }}" @if(request()->routeIs('offer','product')) aria-current="page" @endif>Oferta</a><button aria-expanded="false" aria-controls="offer-dropdown" aria-label="Rozwiń kategorie oferty">⌄</button></div>
   <div class="offer-dropdown" id="offer-dropdown"><a href="{{ route('offer') }}">Wszystkie produkty</a>@foreach($catalog->categories() as $category)<a href="{{ route('offer', $category['slug']) }}">{{ $category['name'] }}</a>@endforeach</div>
  </div>
  <a href="{{ route('projects') }}" @if(request()->routeIs('projects','project')) aria-current="page" @endif>Realizacje</a>
  <a href="{{ route('delivery') }}" @if(request()->routeIs('delivery')) aria-current="page" @endif>Transport i montaż</a>
  <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>O nas</a>
  <a href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>Kontakt</a>
 </nav>
 <a class="btn btn-red header-call" href="tel:{{ config('homekreator.phone_href') }}"><x-icon name="phone"/>730 204 633</a>
</div></header>
<main id="main">@yield('content')</main>
<footer class="site-footer"><div class="container footer-grid">
 <div><a class="logo footer-logo" href="{{ route('home') }}">home<strong>kreator</strong></a><p>Gotowe moduły.<br>Przestrzeń dla Twoich pomysłów.</p><a class="footer-phone" href="tel:+48730204633">730 204 633</a></div>
 <div><h2>Nasza oferta</h2>@foreach($catalog->categories() as $category)<a href="{{ route('offer',$category['slug']) }}">{{ $category['name'] }}</a>@endforeach</div>
 <div><h2>Warto wiedzieć</h2><a href="{{ route('projects') }}">Realizacje</a><a href="{{ route('delivery') }}">Transport i montaż</a><a href="{{ route('about') }}">O nas</a><a href="{{ route('contact') }}">Kontakt</a><a href="{{ route('privacy') }}">Polityka prywatności</a></div>
 <div><h2>Dane firmy</h2><p>ZIG ZAG BUD<br>ul. Słomiana 18<br>26-500 Szydłowiec</p><a href="tel:+48577300588">+48 577 300 588</a><a href="https://www.google.com/maps/search/?api=1&query=S%C5%82omiana+18+Szyd%C5%82owiec" target="_blank" rel="noopener">Wyznacz trasę ↗</a></div>
</div><div class="container footer-bottom"><span>© {{ date('Y') }} Home Kreator. Wszelkie prawa zastrzeżone.</span><span>Domki modułowe · Pawilony sanitarne · Sauny</span></div></footer>
<a class="mobile-call" href="tel:+48730204633"><x-icon name="phone"/> Zadzwoń: 730 204 633</a>
<dialog id="lightbox" aria-label="Galeria zdjęć"><button data-close-lightbox aria-label="Zamknij zdjęcie">×</button><img alt=""><p></p></dialog>
</body></html>
