# Praca deweloperska

## Wymagania

- Node.js ≥ 22.22.2, npm
- PHP ≥ 8.3 i Composer 2 (lint i analiza statyczna lokalnie)
- Docker (Docker Desktop) dla `wp-env`

## Pierwsze uruchomienie

```sh
npm install
composer install
npm run env:start      # WordPress + WooCommerce na http://localhost:8890 (admin / password)
npx playwright install chromium
```

`env:start` po starcie automatycznie uruchamia `bin/seed.php`. Skrypt jest idempotentny: aktywuje motyw, importuje przykładowe produkty WooCommerce, regeneruje tabele lookup oraz ustawia PLN, VAT 23%, wysyłkę (kurier 15 zł, darmowa od 200 zł), kupon `TEST10`, przelew/COD i jeden wariant bez stanu (Hoodie: Blue + Logo „Yes”). Ponowne wywołanie: `npm run env:seed`.

Środowisko `wp-env` ma własne kontenery (`wp-env-woocommerce-starter-pack-*`) i nie dotyka innych projektów Dockera. Zatrzymanie: `npm run env:stop`. Reset bazy (tylko tego środowiska): `npx wp-env reset development`, potem `npm run env:start`.

## Kontrola jakości

| Komenda | Co robi |
|---|---|
| `npm run lint:php` | PHPCS, WordPress Coding Standards 3 (`phpcs.xml.dist`) |
| `npm run analyse:php` | PHPStan poziom 6 ze stubami WordPress i WooCommerce |
| `npm run lint:js` | ESLint (`@wordpress/scripts`) |
| `npm run test:e2e` | Playwright: mobile (Pixel 7) + desktop. Suite kończy się błędem, jeśli `wp-content/debug.log` nie jest pusty. |

CI (`.github/workflows/ci.yml`) uruchamia te same kroki na każdym PR i pushu do `main`.

## Konwencje

- PHP: namespace `StarterPack\`, klasy w `inc/` w układzie PSR-4 (`inc/Foo/Bar.php`), ładowane przez `inc/autoload.php`. Composer służy tylko do narzędzi deweloperskich, a motyw nie dołącza `vendor/`.
- Text domain: `starter-pack`. Teksty widoczne dla użytkownika w szablonach umieszczamy we wzorcach PHP (`patterns/`), bo pliki `templates/*.html` nie obsługują tłumaczeń.
