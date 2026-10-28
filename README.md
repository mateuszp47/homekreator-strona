# Home Kreator — Laravel

Strona Home Kreator zbudowana od nowa w **Laravel 13 + Blade + Vite**. Wygląd nawiązuje do olaya.pl i walbud24.pl: biały nagłówek, ciemnozielone przyciski, jasnoszare sekcje, rozwijana oferta i katalog produktów.

## Wymagania

- PHP 8.3+ z rozszerzeniami Laravel oraz **GD z obsługą WebP, Phar i zlib**. Na typowym PHP z Homebrew są dostępne.
- Composer 2.
- Node.js 22.12+ i npm (Vite 8).

## Pierwsze uruchomienie na Macu

Jeśli nie masz narzędzi: `brew install php composer node`.

```bash
git clone https://github.com/mateuszp47/homekreator-strona.git
cd homekreator-strona
composer install
test -f .env || cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan site:serve
```

Otwórz **http://127.0.0.1:8000**. Aplikacja używa lokalnej bazy SQLite. Polecenie migrate --seed przenosi początkową ofertę i realizacje do bazy. Sesje i cache działają na plikach.

Jeśli masz już poprzednią wersję repozytorium, wejdź do jego folderu i wykonaj `git pull origin main`, a następnie powyższe kroki od `composer install`. Przed aktualizacją zachowaj własne niezapisane zmiany.

Po pierwszej konfiguracji możesz uruchamiać PHP oraz Vite razem:

```bash
composer run dev
```

Albo w dwóch terminalach: `php artisan site:serve` oraz `npm run dev`. Zatrzymanie serwerów: Control+C.

## Funkcje

- Strona główna i cztery kategorie oferty.
- Katalog z wyszukiwaniem i sortowaniem obsługiwanym przez Laravel.
- Sześć kart produktów z cenami netto, wyposażeniem, miniaturami i powiększaniem zdjęć.
- Cztery realizacje ze szczegółami i galeriami.
- O nas, transport i montaż, kontakt, FAQ i polityka prywatności.
- Formularz kontaktowy zapisuje zgłoszenia w bazie: walidacja Laravel, CSRF, limit 5 żądań/minutę i honeypot.
- Skrzynka „Wiadomości” w panelu: dane kontaktowe, model, treść i status przeczytania. Opcjonalne powiadomienia SMTP; awaria poczty nie gubi zapytania.
- Mapa witryny `/sitemap.xml`, metadane i przekierowania 301 ze starych URL `.html`.
- Responsywna nawigacja i rozwijane kategorie.

## Edycja treści

- Panel `/admin` — produkty, realizacje, opisy, ceny, publikacja i zdjęcia.
- `data/products.json` i `data/projects.json` — dane początkowe importowane przy seedowaniu. Późniejsze edycje są przechowywane w bazie.
- `config/homekreator.php` — kategorie, FAQ i dane kontaktowe.
- `app/Support/Catalog.php` — publiczny odczyt opublikowanych wpisów z bazy.
- `resources/views/` — widoki Blade.
- `resources/css/app.css` — wygląd.
- `resources/js/app.js` — menu i galerie.
- `public/images/` — zdjęcia pobrane z homekreator.com.
- `docs/content-sources.md` — źródła i informacje wymagające potwierdzenia.

## Poczta

Formularz działa bez SMTP: zapisuje każde poprawne zapytanie w bazie, a administrator odczytuje je w `/admin/wiadomosci`. Firmowego adresu e-mail nie udało się potwierdzić. Aby dodatkowo otrzymywać powiadomienia e-mail, ustaw w lokalnym `.env` prawdziwe dane:

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

W razie potrzeby ustaw też `MAIL_SCHEME` zgodnie z wymaganiami dostawcy. Nie zapisuj `.env` w repozytorium. Po zmianie konfiguracji wykonaj `php artisan config:clear`. Tryb `MAIL_MAILER=log` nie wysyła powiadomień. Formularz nadal przyjmuje zapytania do panelu. Komunikat potwierdza przyjęcie zapytania, nie doręczenie e-maila.

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


## Panel administracyjny

Po aktualizacji istniejącego projektu uruchom:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan admin:create
```

Jeśli SQLite zgłosi brak pliku, utwórz go poleceniem `touch database/database.sqlite`. Nie usuwaj istniejącej bazy. Przy innym silniku ustaw jego parametry w `.env`.

`admin:create` zapyta o nazwę, e-mail i hasło (min. 12 znaków, wpisywane niewidocznie). **Nie ma konta z domyślnym hasłem ani publicznej rejestracji.** Zaloguj się pod **http://127.0.0.1:8000/admin**.

Panel obsługuje:

- dodawanie i edytowanie produktów oraz realizacji;
- nazwy, adresy URL, opisy i wybór kategorii;
- wymiary, ceny netto i wyposażenie produktów;
- powiązanie realizacji z produktem;
- zdjęcie główne, galerię i usuwanie zdjęć z galerii;
- szkice, publikację i kolejność wyświetlania;
- archiwizowanie i przywracanie wpisów jako szkiców;
- odczytywanie zapytań z formularza i oznaczanie wiadomości jako przeczytane/nieprzeczytane.

Zdjęcia: JPG/PNG/WebP, maks. **10 MB, 24 megapiksele i 6000 × 6000 px** na plik, do **30 nowych zdjęć galerii naraz**. Nowe zdjęcia są przetwarzane przez PHP GD: korekcja orientacji JPEG (jeśli EXIF jest dostępne), skalowanie do maks. 2048 px i zapis jako WebP. Dotychczasowe zdjęcia pozostają bez zmian. Podgląd oraz przeciąganie lub przyciski zmiany kolejności są w formularzu panelu. Ustaw limity PHP `upload_max_filesize` i `post_max_size` odpowiednio do wielkości całego formularza. Wgrane pliki trafiają do `storage/app/public/catalog`, a `storage:link` udostępnia je przez `public/storage`. Pliki usunięte z galerii pozostają w magazynie, aby nie uszkodzić innych wpisów i umożliwić odzyskiwanie treści.

Ponowne `db:seed` nie nadpisuje edycji ani archiwalnych wpisów. Twórz kopie zapasowe **bazy danych oraz storage/app/public** — te treści nie są zapisywane w Git. Na produkcji używaj HTTPS oraz `APP_DEBUG=false`.

## Aktualizacja formularza kontaktowego

Po pobraniu zmian wykonaj `php artisan migrate` oraz `npm run build`. Formularz znajduje się pod `/kontakt`, a skrzynka pod `/admin/wiadomosci`. Nie trzeba konfigurować SMTP, aby przyjmować zgłoszenia. Odpowiedź przez przycisk „Odpowiedz przez pocztę” otwiera własny program pocztowy administratora. Wiadomości są prywatne i nie trafiają do repozytorium.


## Nowe narzędzia panelu

- `/admin/ustawienia`: firma, telefony, adres, Instagram, TikTok, FAQ i odbiorca powiadomień.
- Produkty: wyróżnienie na głównej, opis ceny podstawowej, dodatków i transportu. Pokazywane są maks. 3 opublikowane wyróżnione produkty; przy braku wyróżnień pozostaje dotychczasowy wybór.
- Realizacje: miejscowość, wymiary, zastosowanie i wyposażenie.
- Galerie: podgląd nowych plików, kolejność zdjęć, WebP. Zdjęcie główne jest niezależne od kolejności galerii.
- `/admin/kopie`: utworzenie i pobranie prywatnej kopii danych. `php artisan site:backup` działa też z terminala.

Lokalnie uruchamiaj **`php artisan site:serve`** — ustawia limity PHP dla większych galerii (350 MB POST, 10 MB na plik, 40 plików, 256 MB pamięci). `composer run dev` również korzysta z tego polecenia. Standardowe `php artisan serve` pozostaje dostępne, ale korzysta z limitów Twojego php.ini.

Po tej aktualizacji wykonaj w folderze projektu `composer install`, `php artisan migrate` i `npm run build`. Twoje istniejące produkty i zdjęcia nie są nadpisywane. Jeśli Composer zgłosi brak GD, włącz to rozszerzenie w swojej instalacji PHP.

Wdrożenie, limity hostingu, harmonogram kopii i przywracanie: **[docs/hosting-i-backup.md](docs/hosting-i-backup.md)**.

## Trwałe usuwanie wpisów

W zakładce Archiwum produktów i realizacji jest przycisk „Usuń trwale”. Prowadzi do osobnej strony potwierdzenia. Usunięcie wymaga zaznaczenia pola potwierdzenia i jest blokowane po stronie serwera dla aktywnych oraz przywróconych wpisów. Powiązane realizacje pozostają po usunięciu produktu, ale tracą jego powiązanie. Pliki zdjęć pozostają ze względu na współdzielenie między wpisami. Import danych początkowych nie odtwarza wpisów trwale usuniętych z panelu. Wcześniejsze kopie zapasowe mogą nadal zawierać usunięte dane.

Po pobraniu tej aktualizacji wykonaj `php artisan migrate` oraz `npm run build`.
