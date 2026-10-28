<?php
return [
 'phone' => '+48 730 204 633', 'phone_href' => '+48730204633',
 'secondary_phone' => '+48 577 300 588', 'company' => 'ZIG ZAG BUD',
 'address' => 'ul. Słomiana 18, 26-500 Szydłowiec',
 'contact_enabled' => env('CONTACT_FORM_ENABLED', false), 'contact_recipient' => env('CONTACT_RECIPIENT'),
 'socials' => [
  ['name'=>'Instagram','icon'=>'instagram','url'=>'https://www.instagram.com/zigzagpolska/'],
  ['name'=>'TikTok','icon'=>'tiktok','url'=>'https://www.tiktok.com/@zigzag9278'],
 ],
 'categories' => [
  'domki' => ['slug'=>'domki-modulowe','name'=>'Domki modułowe','image'=>'domek.jpeg','description'=>'Twoje miejsce na działce. Moduły do wypoczynku z możliwością wyposażenia wnętrza.'],
  'sanitarne' => ['slug'=>'pawilony-sanitarne','name'=>'Pawilony sanitarne','image'=>'sanitarny.jpeg','description'=>'Funkcjonalne zaplecze sanitarne z instalacjami przygotowanymi do podłączenia.'],
  'sauny' => ['slug'=>'sauny-ogrodowe','name'=>'Sauny ogrodowe','image'=>'sauna.webp','description'=>'Własna strefa relaksu w ogrodzie. Wymiary i wyposażenie dobierane indywidualnie.'],
  'biznes' => ['slug'=>'pawilony-dla-biznesu','name'=>'Pawilony dla biznesu','image'=>'gastronomia.jpg','description'=>'Przestrzeń na biuro, gabinet, usługi lub punkt gastronomiczny.'],
 ],
 'faq' => [
  ['question'=>'Czy mogę dopasować moduł do swoich potrzeb?','answer'=>'Tak. Ustalamy wymiary, układ pomieszczeń, przeszklenia i wyposażenie. Dobór zależy od tego, czy moduł ma służyć do wypoczynku, pracy czy prowadzenia działalności.'],
  ['question'=>'Czy domki są ocieplone?','answer'=>'Oferujemy ocieplone konstrukcje z możliwością wyposażenia w ogrzewanie i klimatyzację. Parametry izolacji i zakres instalacji potwierdzamy w ofercie dla wybranego modelu.'],
  ['question'=>'Co zawiera cena podstawowa?','answer'=>'Cena podstawowa dotyczy wskazanego wariantu modułu. Aneks kuchenny, łazienka czy klimatyzacja zależą od konfiguracji. Szczegółowy zakres i cenę transportu podajemy przy wycenie.'],
  ['question'=>'Ile trwa realizacja zamówienia?','answer'=>'Termin zależy od modelu, wyposażenia i aktualnego planu produkcji. Potwierdzamy go podczas uzgadniania zamówienia. Zadzwoń, aby poznać termin dla swojego projektu.'],
  ['question'=>'Czy zapewniacie transport i montaż?','answer'=>'Tak. Wspólnie ustalamy miejsce dostawy, warunki dojazdu i ustawienia modułu, a następnie uzgadniamy transport oraz montaż.'],
  ['question'=>'Jak uzyskać indywidualną wycenę?','answer'=>'Zadzwoń pod 730 204 633. Przygotuj przeznaczenie modułu, przybliżone wymiary, oczekiwane wyposażenie i miejscowość dostawy. Pomożemy dobrać odpowiednie rozwiązanie.'],
 ],
];
