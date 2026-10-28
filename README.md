# Home Kreator — Laravel

Strona Home Kreator zbudowana od nowa w **Laravel 13 + Blade + Vite**. Wygląd nawiązuje do olaya.pl i walbud24.pl: biały nagłówek, czerwone przyciski, jasnoszare sekcje, rozwijana oferta i katalog produktów.

## Wymagania

- PHP 8.3+ z rozszerzeniami wymaganymi przez Laravel (m.in. mbstring, DOM/XML, PDO, fileinfo, tokenizer, ctype, OpenSSL).
- Composer 2.
- Node.js 22.12+ i npm (Vite 8).

## Pierwsze uruchomienie na Macu

Jeśli nie masz narzędzi: `brew install php composer node`.

```bash
git clone https://github.com/mateuszp47/homekreator-strona.git
cd homekreator-strona
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan serve
```

Otwórz **http://127.0.0.1:8000**. Nie potrzeba Pythona ani konfiguracji bazy danych dla obecnej oferty — sesje i cache działają na plikach.

Jeśli masz już poprzednią wersję repozytorium, wejdź do jego folderu i wykonaj `git pull origin main`, a następnie powyższe kroki od `composer install`. Przed aktualizacją zachowaj własne niezapisane zmiany.

Po pierwszej konfiguracji możesz uruchamiać PHP oraz Vite razem:

```bash
composer run dev
```

Albo w dwóch terminalach: `php artisan serve` oraz `npm run dev`. Zatrzymanie serwerów: Control+C.

## Funkcje

- Strona główna i cztery kategorie oferty.
- Katalog z wyszukiwaniem i sortowaniem obsługiwanym przez Laravel.
- Sześć kart produktów z cenami netto, wyposażeniem, miniaturami i powiększaniem zdjęć.
- Cztery realizacje ze szczegółami i galeriami.
- O nas, transport i montaż, kontakt, FAQ i polityka prywatności.
- Formularz: walidacja Laravel, CSRF, limit 5 żądań/minutę i honeypot.
- SMTP z obsługą błędów; sukces wyświetla się po zaakceptowaniu wysyłki przez transport.
- Mapa witryny `/sitemap.xml`, metadane i przekierowania 301 ze starych URL `.html`.
- Responsywna nawigacja i rozwijane kategorie.

## Edycja treści

- `data/products.json` — produkty, opisy, wymiary, ceny i wyposażenie.
- `config/homekreator.php` — kategorie, FAQ i dane kontaktowe.
- `app/Support/Catalog.php` — odczyt oferty oraz dane realizacji.
- `resources/views/` — widoki Blade.
- `resources/css/app.css` — wygląd.
- `resources/js/app.js` — menu i galerie.
- `public/images/` — zdjęcia pobrane z homekreator.com.
- `docs/content-sources.md` — źródła i informacje wymagające potwierdzenia.

## Poczta

Firmowego adresu e-mail nie udało się potwierdzić. Dlatego formularz pokazuje kontakt telefoniczny i ma wyłączony przycisk wysyłki. Aby uruchomić wysyłkę, ustaw w lokalnym `.env` prawdziwe dane:

```dotenv
CONTACT_FORM_ENABLED=true
CONTACT_RECIPIENT=potwierdzony-adres-firmy
MAIL_MAILER=smtp
MAIL_HOST=serwer-poczty
MAIL_PORT=587
MAIL_USERNAME=login
MAIL_PASSWORD=haslo
MAIL_FROM_ADDRESS=adres-nadawcy
MAIL_FROM_NAME="Home Kreator"
```

W razie potrzeby ustaw też `MAIL_SCHEME` zgodnie z wymaganiami dostawcy. Nie zapisuj `.env` w repozytorium. Po zmianie konfiguracji wykonaj `php artisan config:clear`. Tryb `MAIL_MAILER=log` nie uruchamia formularza i nie udaje wysyłki.

## Testy i build

```bash
php artisan test
npm run build
php artisan view:cache
```

Testy obejmują strony, filtrowanie, sortowanie, 404, przekierowania, sitemapę, walidację i obsługę poczty (z symulowanym transportem, bez wysyłania prawdziwych wiadomości).

## Hosting

To aplikacja PHP. Katalogiem publicznym serwera musi być **`public/`**, nie katalog główny repozytorium. Wdróż źródła, wykonaj `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`, skonfiguruj `.env`, klucz aplikacji i prawa zapisu do `storage/` oraz `bootstrap/cache/`. Ustaw `APP_ENV=production`, `APP_DEBUG=false` i prawidłowy `APP_URL`.

Poprzedni podgląd ChatGPT Sites przedstawia starą wersję statyczną. Nie obsługuje wykonania PHP i nie jest wdrożeniem tego projektu Laravel. Domena homekreator.com nie została zmieniona.
