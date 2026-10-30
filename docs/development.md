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

`env:start` po starcie automatycznie uruchamia `bin/seed.php`. Skrypt jest idempotentny: aktywuje motyw, importuje przykładowe produkty WooCommerce, regeneruje tabele lookup oraz ustawia nazwę witryny „Starter Pack”, PLN, VAT 23%, wysyłkę (kurier 15 zł, darmowa od 200 zł), kupon `TEST10`, przelew/COD i jeden wariant bez stanu (Hoodie: Blue + Logo „Yes”) oraz 3 wpisy blogowe ze zdjęciami w kategorii „Guides”. Ponowne wywołanie: `npm run env:seed`.

### Środowisko testowe

Testy E2E nie korzystają z witryny deweloperskiej. Mają osobne środowisko z własną bazą (`.wp-env.test.json`, http://localhost:8891), więc zmiany wprowadzone ręcznie w edytorze na 8890 nie wpływają na testy, a testy nie nadpisują tych zmian.

```sh
npm run env:test:start   # start i seed środowiska testowego
npm run test:e2e         # Playwright → http://localhost:8891
npm run env:test:stop
```

Oba środowiska mają własne kontenery (`wp-env-woocommerce-starter-pack-*` i `wp-env-woocommerce-starter-pack-test-*`) i nie dotykają innych projektów Dockera. Reset bazy deweloperskiej: `npx wp-env reset development`, potem `npm run env:start`. Reset testowej: `npx wp-env reset development --config=.wp-env.test.json`, potem `npm run env:test:start`.

## Kontrola jakości

| Komenda | Co robi |
|---|---|
| `npm run lint:php` | PHPCS, WordPress Coding Standards 3 (`phpcs.xml.dist`) |
| `npm run analyse:php` | PHPStan poziom 6 ze stubami WordPress i WooCommerce |
| `npm run lint:js` | ESLint (`@wordpress/scripts`): testy i `assets/js` |
| `npm run lint:css` | Stylelint; odrzuca kolory wpisane na sztywno |
| `npm run test:e2e` | Playwright na środowisku testowym (8891): mobile (Pixel 7) + desktop. Suite kończy się błędem, jeśli `wp-content/debug.log` nie jest pusty. |

CI (`.github/workflows/ci.yml`) uruchamia te same kroki na każdym PR i pushu do `main`.

## Konwencje

- PHP: namespace `StarterPack\`, klasy w `inc/` w układzie PSR-4 (`inc/Foo/Bar.php`), ładowane przez `inc/autoload.php`. Composer służy tylko do narzędzi deweloperskich, a motyw nie dołącza `vendor/`.
- Style dla konkretnego bloku (np. poprawki bloków WooCommerce) trafiają do `assets/css/blocks/{namespace}-{blok}.css` i są rejestrowane w `Assets::BLOCK_STYLES`. WordPress inline'uje je tylko na stronach, na których blok się renderuje.
- Text domain: `starter-pack`. Teksty widoczne dla użytkownika w szablonach umieszczamy we wzorcach PHP (`patterns/`), bo pliki `templates/*.html` nie obsługują tłumaczeń.
