@props(['name' => 'arrow'])
<svg {{ $attributes->merge(['class'=>'icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('phone')<path d="M7 3H4a1 1 0 0 0-1 1c0 9.4 7.6 17 17 17a1 1 0 0 0 1-1v-3l-5-2-2 2a14 14 0 0 1-7-7l2-2Z"/>@break
@case('truck')<path d="M2 5h13v12H2zM15 9h4l3 4v4h-7"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>@break
@case('home')<path d="m3 11 9-8 9 8v10H3zM9 21v-8h6v8"/>@break
@case('check')<path d="m5 12 4 4L20 5"/>@break
@case('settings')<path d="M4 6h16M4 12h16M4 18h16"/><circle cx="8" cy="6" r="2" fill="white"/><circle cx="16" cy="12" r="2" fill="white"/><circle cx="10" cy="18" r="2" fill="white"/>@break
@default<path d="M4 12h16m-6-6 6 6-6 6"/>
@endswitch
</svg>
