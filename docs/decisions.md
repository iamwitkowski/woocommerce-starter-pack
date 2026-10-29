# Decyzje architektoniczne i wyniki spike'a (etap 0)

Data: 2026-10-29. Środowisko: wp-env 12.0.0 (Docker), WordPress 7.1.4, WooCommerce 11.2.1, PHP 8.3.36, motyw Twenty Twenty-Five + szablony testowe w bazie (nie w repo), dane: `sample_products.xml` z WooCommerce.

Oznaczenia: **[F]** zweryfikowane w kodzie lub w działającym sklepie, **[R]** rekomendacja, **[?]** otwarte.

## Zatwierdzone decyzje (2026-10-29)

| # | Decyzja |
|---|---|
| D1 | Wyłącznie motyw blokowy. Bez klasycznych szablonów PHP i page builderów. |
| D2 | Wyłącznie Checkout Block i Cart Block. |
| D3 | Variant switcher na listingu w ograniczonym zakresie (1 atrybut, kilka opcji, dane z Store API po interakcji). Do oceny po implementacji. |
| D4 | Jedyny własny blok to hero. Grid, tekst + obraz, galeria i announcement bar to wzorce na blokach core, w jednym gridzie i w pełni ostylowane. |
| D5 | Model dystrybucji: na razie poza zakresem. |
| D6 | PHP min. 8.3, testy na 8.4 i 8.5. WordPress min. 7.0 (testy 7.1). WooCommerce min. 10.6.1 (testy najnowsze 11.x). |
| D7 | Front: HTML renderowany po stronie serwera, zwykły CSS, Interactivity API tylko dla elementów interaktywnych. |
| D8 | Koszyk jest pełnoprawnie wspierany: szablon `page-cart.html`, style, testy E2E (ilość, usuwanie, kupon, wysyłka, pusty koszyk). Pominięcie koszyka w ścieżce (Mini-Cart → Checkout) to **opcja**, domyślnie wyłączona. |
| D9 | Opcja „Szybka ścieżka do kasy” (nie „Express checkout”, bo ta nazwa koliduje z sekcją szybkich płatności w Checkout Block): checkbox w WooCommerce → Ustawienia → Zaawansowane, dodany przez filtry `woocommerce_get_sections_advanced` / `woocommerce_get_settings_advanced` [F: `class-wc-settings-page.php`]. Po włączeniu motyw ukrywa blok `woocommerce/mini-cart-cart-button-block` w szufladzie (filtr `render_block`), a przycisk „Do kasy” jest jedynym CTA. Strona koszyka działa dalej. Test E2E dla obu stanów. |

## Decyzje etapu 1 (założenia odwracalne)

| # | Decyzja |
|---|---|
| E1-1 | Robocza nazwa: **Starter Pack**, slug i text domain `starter-pack`, namespace `StarterPack`, prefiks `starter_pack_`. Zmiana nazwy oznacza search/replace w całym repo. Najtaniej zrobić ją przed etapem 2. |
| E1-2 | Klasy w `inc/` w układzie PSR-4 z własnym autoloaderem (bez `vendor/` w motywie). Reguła `WordPress.Files.FileName` jest wyłączona dla `inc/`. |
| E1-3 | `bin/seed.php` jest pominięty w PHPStan, bo `php-stubs/wp-cli-stubs` nie obsługuje jeszcze `wordpress-stubs` 7.x (konflikt zależności w Composerze). PHPCS go sprawdza. |
| E1-4 | Brak pola licencji w `style.css` i `composer.json` (model dystrybucji odłożony, D5). |
| E1-5 | Klasy `Assets`, `Blocks` i `WooCommerce\*` powstaną w etapach, które ich potrzebują. W etapie 1 nie ma pustych klas. |

## Wyniki spike'a

### Cart drawer → natywny Mini-Cart [F]

- Atrybut bloku `addToCartBehaviour: "open_drawer"` (domyślnie `"none"`) otwiera szufladę po dodaniu produktu. Działa ze strony produktu i z listingu (Product Collection).
- Synchronizacja: Mini-Cart nasłuchuje zdarzeń `wc-blocks_added_to_cart` / `wc-blocks_removed_from_cart` i ma most dla zdarzeń jQuery (`callbacks.setupJQueryEventBridge`). Dodanie klasyczne przez `?add-to-cart=` daje poprawny licznik po przeładowaniu. Stan po każdej operacji jest zgodny z `GET /wc/store/v1/cart`.
- Zmiana ilości aktualizuje sumę (42,00 → 84,00 zł) zgodnie ze Store API. Usunięcie pozycji pokazuje stan pusty („Your shopping cart is empty” + „Return to shop”).
- Dostępność, co działa: `role="dialog"`, `aria-modal="true"`, focus przenoszony do przycisku zamknięcia, focus trap (25× Tab, 0 wyjść), Escape zamyka, otwarcie Enterem z ikony działa.
- **Luka A11Y-1:** dialog nie ma dostępnej nazwy (brak `aria-label` / `aria-labelledby`), co narusza WCAG 4.1.2.
- **Luka A11Y-2:** po zamknięciu Escape focus wraca do ikony koszyka tylko wtedy, gdy szufladę otworzono klawiaturą z ikony. Po otwarciu kliknięciem albo automatycznie po dodaniu produktu focus zostaje na elemencie wewnątrz zamkniętej szuflady.
- [R] Używamy natywnego Mini-Cart. Obie luki łatamy minimalnie w motywie: nazwa dialogu przez filtr `render_block`, a powrót focusu przez mały moduł Interactivity API lub vanilla JS. Zgłaszamy je też do WooCommerce. Własnej szuflady nie budujemy.

### Variant switcher na stronie produktu → Add to Cart + Options [F]

- W 11.2.1 blok jest nadal oznaczony jako **Beta**. Domyślny szablon `single-product` w WooCommerce wciąż używa klasycznego `woocommerce/add-to-cart-form` (jQuery, `add-to-cart-variation.js`, underscore).
- Blok renderuje części szablonu `{typ}-product-add-to-cart-with-options.html` (simple, variable, grouped, external). **Motyw może je nadpisać** plikami w `parts/`. To jest nasz punkt stylowania i kontroli układu.
- Typy produktów spoza tych czterech, np. z wtyczek, dostają klasyczne `do_action( 'woocommerce_{type}_add_to_cart' )`, czyli działający fallback.
- Wybór wariantu: chipy z `role="radiogroup"`/`role="radio"` i `aria-checked`. Kombinacje nieistniejące są `disabled`. Kombinacja istniejąca, ale bez stanu (Blue+Yes), da się wybrać: pokazuje się „Out of stock”, a przycisk dodania jest ukryty.
- Próba dodania bez wyboru: komunikat „Please select product attributes before adding to cart.”, do koszyka nic nie trafia.
- Cena (`product-price`) aktualizuje się do ceny wybranego wariantu.
- Brak błędów JS i PHP w scenariuszu.

### Waga JS (gzip, SCRIPT_DEBUG=false, Twenty Twenty-Five) [F]

| Widok | JS gzip | Uwagi |
|---|---|---|
| Strona treści / home | 70 KB (11 plików) | jQuery + migrate + blockUI + js-cookie + `woocommerce.js` + `add-to-cart.js` ładowane przez WC na **każdej** stronie; emoji 5 KB; sourcebuster + order attribution (ustawienie WC) |
| Listing (Product Collection) | 77 KB | |
| Produkt wariantowy, klasyczny formularz | 127 KB | flexslider, photoswipe, zoom, underscore, add-to-cart-variation |
| Produkt wariantowy, Add to Cart + Options + Mini-Cart | **94 KB** | jQuery dalej obecne (globalne skrypty WC) |
| **Cart (blok)** | **705 KB** (63 pliki) | React, `wp-components` (265 KB gz), moment, date… |
| **Checkout (blok)** | **689 KB** (60 plików) | jw. |

- Runtime Interactivity API: 15,7 KB gzip, współdzielony przez wszystkie bloki iAPI.
- `wp-components` trafia na front przez łańcuch `wc-blocks-checkout`/`wc-blocks-components` → `wp-notices` → `wp-components`. Motyw nie może tego bezpiecznie usunąć.
- **[?] Ryzyko wydajnościowe D2:** Cart i Checkout Block to ~0,7 MB gzip JS. Do decyzji: ewentualne pominięcie strony Cart w ścieżce zakupu (Mini-Cart → Checkout).

### Dane strukturalne [F]

- JSON-LD `Product` i `BreadcrumbList` generuje WooCommerce (`WC_Structured_Data`), ale **tylko gdy szablon zawiera odpowiednie bloki**:
  - `Product` przez hook `woocommerce_single_product_summary`, który warstwa kompatybilności wywołuje przy blokach `core/post-excerpt` lub `woocommerce/product-summary`,
  - `BreadcrumbList` przez blok `woocommerce/breadcrumbs`.
- Okrojony szablon testowy bez tych bloków: **brak JSON-LD**. Domyślny szablon WC: `BreadcrumbList` + `Product`.
- [R] Szablon `single-product.html` motywu musi zawierać `woocommerce/breadcrumbs` i `woocommerce/product-summary` (lub `core/post-excerpt`). Test E2E sprawdza obecność JSON-LD i to, że jest pojedynczy.

### Responsywność w WordPress 7.1 [F]

- `settings.viewport` w `theme.json` (klucze `mobile`, `tablet`, jednostki px/em/rem, walidowane od 7.1.0) generuje media queries `@mobile`, `@tablet` (i `@desktop` dla widoczności).
- Style bloków per viewport: `style['@mobile']`, `style['@tablet']`, łącznie z layoutem dzieci (`block-supports/layout.php`, `states.php`).
- Widoczność bloku per viewport: `metadata.blockVisibility.viewport` (od 7.0, `block-supports/block-visibility.php`).
- [R] Responsywną wysokość i odstępy hero oraz ukrywanie sekcji na mobile robimy natywnie. Własny blok hero zostaje tylko dla obrazu mobilnego (`<picture>`, bez pobierania dwóch obrazów LCP) i dla wideo z posterem i obsługą reduced motion.

### Dane testowe [F]

- Import `sample_products.xml` (WordPress Importer) nie wypełnia `wc_product_meta_lookup` dla wariantów. `WC_Product_Variable::sync()` oznacza wtedy produkty wariantowe jako „Out of stock”.
- Naprawa: `wp wc tool run regenerate_product_lookup_tables --user=admin` + `regenerate_product_attributes_lookup_table` + `wp action-scheduler run`, potem `sync`.
- [R] Seed fixture'ów E2E w etapie 1 jako skrypt idempotentny: import, regeneracja tabel, ustawienia sklepu (PLN, VAT 23%, kurier 15 zł, darmowa dostawa od 200 zł, kupon `TEST10`, przelew i COD, wariant bez stanu).

### Log PHP [F]

- Po przejściu home, shop, produkt, cart, checkout, my-account, wyszukiwarka i 404 `debug.log` jest pusty.
- Notice'y „already registered” widoczne wcześniej pochodziły z mojego skryptu WP-CLI (ponowne `do_action('init')`), a nie z WooCommerce.

## Konsekwencje dla planu

1. Etap 5 (cart drawer) zmniejsza się do konfiguracji i stylowania natywnego Mini-Cart + 2 poprawek dostępności.
2. Etap 6 (strona produktu) to Add to Cart + Options + nadpisane części szablonu `parts/*-product-add-to-cart-with-options.html`. Ryzyko bety pozostaje, fallback to blok `woocommerce/add-to-cart-form`.
3. Etap 8: rozważyć warunkowe usuwanie globalnych skryptów jQuery WooCommerce na stronach bez klasycznych komponentów (~45 KB gz). Wymaga testu zgodności z wtyczkami, więc jako opcja z filtrem, nie domyślnie. Order attribution zostaje ustawieniem sklepu, nie decyzją motywu.
4. Szablon produktu ma obowiązkowe bloki dla JSON-LD (patrz wyżej).
