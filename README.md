# Home Kreator

Pierwsza wersja nowej strony homekreator.com: responsywny katalog domków modułowych, pawilonów i saun. Kod na gałęzi `main`.

## Uruchomienie

Wymagany Python 3.10+; bez zależności zewnętrznych.

```sh
python3 scripts/build.py
python3 scripts/serve.py --port 3000
```

Otwórz http://localhost:3000. Opcjonalnie: `npm run build`, `npm run dev`, `npm run check`.

## Edycja

- `data/products.json` — produkty, opisy, ceny netto, zdjęcia i wyposażenie.
- `scripts/build.py` — szablony i treści podstron.
- `assets/style.css` — wygląd i responsywność.
- `assets/app.js` — filtry, wyszukiwanie, menu, galeria i przygotowanie zapytania.
- `assets/images/` — lokalne zdjęcia ze starej strony.
- `docs/content-sources.md` — źródła i niespójności wymagające potwierdzenia.

## Publikacja

`python3 scripts/build.py` tworzy katalog `dist/`, który można przesłać na hosting statyczny. Wszystkie podstrony mają rozszerzenie `.html`; ścieżki względne działają także w podkatalogu. Katalog `dist/` jest generowany i nie jest śledzony przez Git.

Wersja przeglądowa Sites ma konfigurację w `.openai/hosting.json`. Publikacja przeglądu nie zastępuje strony pod domeną homekreator.com.

## Weryfikacja

```sh
python3 scripts/build.py
python3 scripts/check.py
node --check assets/app.js
```

## Zakres

13 stron: główna, oferta, sześć kart produktów, realizacje, o nas, kontakt, prywatność, 404. Filtry kategorii, wyszukiwanie produktów, powiększanie zdjęć, miniatury, mobilne menu, FAQ i przekazywanie wybranego modelu do kontaktu.

Formularz **przygotowuje tekst zapytania lokalnie**, bez wysyłania danych. Nie ma skonfigurowanego serwera pocztowego ani panelu CMS. Dane, których nie udało się potwierdzić, nie zostały wymyślone. Funkcje katalogowe nawiązują do stron olaya.pl i walbud24.pl; nie jest to kopia ich prywatnego skryptu.
