# Hosting, poczta i kopie zapasowe

## Konfiguracja hostingu Laravel

- PHP 8.3+, rozszerzenia Laravel, GD z WebP, Phar, zlib; opcjonalnie EXIF dla orientacji zdjęć.
- Domena wskazuje wyłącznie katalog `public/`. Baza, `.env`, backupy i `storage/app/private` nie mogą być dostępne przez WWW.
- `APP_ENV=production`, `APP_DEBUG=false`, prawidłowe `APP_URL`; HTTPS.
- `composer install --no-dev --optimize-autoloader`, `npm ci`, `npm run build`, `php artisan migrate --force`, `php artisan storage:link`.
- Prawa zapisu dla `storage` i `bootstrap/cache`.

### Limity galerii na hostingu

W panelu hostingu lub php.ini ustaw:

```ini
upload_max_filesize=10M
post_max_size=350M
max_file_uploads=40
memory_limit=256M
max_execution_time=120
```

Jeśli hosting obsługuje `.user.ini`, można tam umieścić te ustawienia w katalogu publicznym; działanie zależy od konfiguracji PHP-FPM/CGI. Dla Nginx administrator musi też ustawić odpowiednie `client_max_body_size` (np. 350m). Proxy/CDN może mieć własny, niższy limit. Gdy nie da się go zwiększyć, wysyłaj zdjęcia mniejszymi partiami. Nie wyłączaj limitów całkowicie.

Lokalnie polecenie `php artisan site:serve` ustawia limity tylko dla uruchamianego serwera. Nie zmienia globalnego php.ini.

## Dane i zdjęcia przy przenoszeniu strony

Przenieś bazę SQLite `database/database.sqlite` oraz `storage/app/public` (a także kod i `public/images`). Na serwerze ustaw prawidłową ścieżkę SQLite w `.env` lub przeprowadź osobną migrację danych do MySQL. Zachowaj prywatny `APP_KEY`; nie generuj nowego podczas zwykłej aktualizacji istniejącej instalacji.

`db:seed` służy jedynie danym początkowym i nie zastępuje przeniesienia własnej bazy. Przed transferem zatrzymaj zapisy na starej instalacji albo użyj mechanizmu kopii poniżej.

## Powiadomienia o zapytaniach

Formularz zawsze zapisuje poprawne zapytanie w bazie. W `/admin/ustawienia` wpisz odbiorcę i włącz powiadomienia. Na serwerze skonfiguruj `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` i `MAIL_FROM_NAME`; w razie potrzeby `MAIL_SCHEME`. Hasło SMTP pozostaje w `.env`, nie w panelu ani GitHubie.

Po zmianie `.env`: `php artisan config:clear` (lub ponowne `config:cache` na produkcji). Przetestuj wysyłkę rzeczywistym zapytaniem. Status w Wiadomościach wskazuje przekazanie e-maila do transportu lub błąd; nie potwierdza dostarczenia do skrzynki odbiorczej. Na potrzeby implementacji używamy symulowanej poczty w testach, a nie wysyłki na prawdziwe adresy.

## Kopie zapasowe

Obsługiwane bazy: SQLite i MySQL. SQLite używa spójnego `VACUUM INTO`; MySQL wymaga dostępnego programu `mysqldump` i odpowiednich uprawnień. Konto MySQL i hasło są pobierane z konfiguracji Laravel; hasło nie trafia do argumentów polecenia.

```bash
php artisan site:backup
```

Kopia `.tar.gz` zawiera bazę (`database.sqlite` lub `database.sql`), `uploads/` ze zdjęciami z panelu, `images/` ze zdjęciami początkowymi i `manifest.json`. Nie zawiera `.env`, sesji, zależności ani kodu. Archiwa znajdują się w `storage/app/private/backups`, są dostępne do pobrania tylko administratorom i mają prywatne uprawnienia pliku. Przechowujemy 7 ostatnich poprawnie utworzonych kopii. Nieudany backup nie usuwa poprzednich kopii.

Duże kopie uruchamiaj w terminalu, aby uniknąć limitu czasu żądania WWW.

### Automatyczne kopie

W aplikacji zdefiniowano codzienne zadanie o 02:00 Europe/Warsaw. Na hostingu dodaj zadanie cron (podmień pełne ścieżki):

```cron
* * * * * cd /pelna/sciezka/homekreator-strona && /sciezka/do/php artisan schedule:run >> /pelna/sciezka/homekreator-strona/storage/logs/scheduler.log 2>&1
```

Bez tego zadania harmonogram nie uruchamia się sam. Na komputerze do prób można uruchomić `php artisan schedule:work`, ale działa tylko, gdy proces jest włączony. Sprawdzaj powodzenie kopii w panelu i logach.

**Pobieraj kopie na osobny nośnik lub skonfiguruj niezależną kopię hostingu. Lokalna kopia na tym samym serwerze nie chroni przed jego utratą.** Zewnętrzne miejsce przechowywania trzeba ustalić z dostawcą hostingu; aplikacja nie ma jeszcze podłączonego konta zewnętrznego magazynu.

### Przywracanie (ręczne, z kontrolą)

1. Wykonaj kopię bieżącego stanu i włącz tryb konserwacji (`php artisan down`). Zatrzymaj harmonogram na czas odtwarzania.
2. Rozpakuj zaufane archiwum poza `public/`, np. `tar -xzf nazwa-kopii.tar.gz -C /prywatny/folder/odtwarzania`. Sprawdź `manifest.json` i zawartość.
3. SQLite: przy zatrzymanych zapisach zastąp plik bazy kopią `database.sqlite`. MySQL: odtwórz `database.sql` do właściwej bazy narzędziem dostawcy lub klientem MySQL. Nie mieszaj silników baz.
4. Odtwórz `uploads/` do `storage/app/public/` oraz `images/` do `public/images/`. Pozostaw prywatną konfigurację `.env` właściwą dla serwera.
5. Ustaw prawa zapisu, wykonaj `php artisan storage:link`, `php artisan optimize:clear`, w razie potrzeby migracje zgodne z wersją kodu (`php artisan migrate --force`). Następnie `php artisan up`.
6. Sprawdź logowanie, produkty, realizacje, zdjęcia i wiadomości. Włącz harmonogram ponownie.

Archiwa zawierają dane kontaktowe i konta użytkowników. Nie umieszczaj ich w GitHubie ani publicznej przestrzeni WWW.
