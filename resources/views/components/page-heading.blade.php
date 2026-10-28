@props(['title','subtitle'=>null])
<section class="page-heading"><div class="container"><nav class="breadcrumbs" aria-label="Okruszki"><a href="{{ route('home') }}">Strona główna</a><span>/</span><span>{{ $title }}</span></nav><h1>{{ $title }}</h1>@if($subtitle)<p>{{ $subtitle }}</p>@endif</div></section>
