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

## Decyzje etapu 2 (system designu)

| # | Decyzja |
|---|---|
| E2-1 | Paleta ma 8 ról: 7 ze specyfikacji + `on-primary`, który gwarantuje czytelny tekst przycisków przy dowolnym `primary`. |
| E2-2 | Domyślnie fonty systemowe (0 KB). Fraunces (OFL, ~70 KB) tylko w opcjonalnym presecie Editorial. Inter odrzucony: ~133 KB z polskimi znakami tylko dla kroju normalnego. Sprzedawca może dodać inne fonty przez wbudowaną Bibliotekę fontów WordPressa. |
| E2-3 | Breakpointy `settings.viewport` = domyślne WordPressa (480 / 782 px), zapisane jawnie, żeby zgadzały się z podglądem w edytorze. |
| E2-4 | Style komponentów WooCommerce (pola checkoutu, karty produktów, Mini-Cart) powstaną w etapach 4–5. W etapie 2 sprawdzamy tylko, że paleta trafia do przycisków WooCommerce. |
| E2-5 | `base.css` jest ładowany z danymi `path`, więc WordPress go inline'uje (brak dodatkowego żądania). |

## Decyzje etapu 3 (szablony WordPressa)

| # | Decyzja |
|---|---|
| E3-1 | Części `header` i `footer` zawierają tylko odwołanie do wzorców PHP (`starter-pack/header`, `starter-pack/footer`), bo pliki `.html` nie obsługują tłumaczeń. WooCommerce w swoich szablonach odwołuje się do tych samych slugów, więc sklep od razu dostaje nagłówek i stopkę motywu. Landmarki `<header>`/`<footer>` pochodzą z obszaru części zadeklarowanego w `theme.json` (`templateParts`). |
| E3-2 | Domyślne menu nagłówka (Sklep + do 5 kategorii produktów najwyższego poziomu) i stopki (Sklep, Moje konto, Koszyk) buduje `StarterPack\Navigation` z prawdziwych stron sklepu. Po edycji w Edytorze witryny WordPress zapisuje menu jako `wp_navigation`, a kod przestaje być używany. |
| E3-3 | Wyszukiwarka produktów jest blokiem `core/search` (`post_type=product`) wewnątrz nawigacji: na desktopie stoi w linii, na mobile trafia do menu nakładkowego. Jedna instancja, bez dodatkowego JS. |
| E3-4 | Announcement bar to wzorzec `starter-pack/announcement-bar` (grupa z wariantem sekcji „Inverted”). Wyłącza się go, usuwając blok w Edytorze witryny. Wzorzec jest też dostępny w inserterze. |
| E3-5 | WooCommerce wstawia przez Block Hooks Mini-Cart i Konto za `core/navigation` w nagłówkach. Nagłówek motywu ma je już na stałe, więc wzorzec jest na liście wykluczeń (`woocommerce_hooked_blocks_pattern_exclude_list`, klasa `WooCommerce\HookedBlocks`). Bez tego ikona konta była zdublowana. |
| E3-6 | **Błąd WC 11.2.1 (A11Y-3):** zamknięta szuflada Mini-Cart ma `aria-hidden="true"`, ale jej przycisk i link pozostają w kolejności Tab (axe: `aria-hidden-focus`). Poprawka: `visibility: hidden` z opóźnieniem 0,3 s (animacja zamykania zostaje) w `assets/css/blocks/woocommerce-mini-cart.css`, ładowanym przez `wp_enqueue_block_style` tylko z blokiem. Do zgłoszenia w WooCommerce. |
| E3-7 | **Błąd WC 11.2.1:** na stronach koszyka i kasy Mini-Cart renderuje wersję nieinteraktywną bez `restUrl`, a współdzielony store koszyka wysyła `GET undefinedwc/store/v1/cart` (404). Motyw nie renderuje Mini-Cart na tych dwóch stronach (`WooCommerce\MiniCart`, filtr `render_block_woocommerce/mini-cart`). Tam i tak dublowałby treść strony. |
| E3-8 | Środowisko wp-env: `WP_DEVELOPMENT_MODE=theme` (bez cache wzorców i `theme.json` podczas pracy) oraz `AUTOMATIC_UPDATER_DISABLED` (automatyczne aktualizacje pisały do `debug.log` i mogłyby zmienić wersję WordPressa w trakcie testów). Uwaga: na produkcji WordPress cache'uje listę wzorców per wersja motywu, więc nowe pliki w `patterns/` wymagają podbicia `Version` w `style.css`. |
| E3-9 | Szablony: `index` (blog), `single`, `page`, `archive`, `search`, `404`. Listy wpisów korzystają ze wspólnego wzorca `hidden-posts-grid` (siatka `grid` z min. szerokością kolumny 18rem, więc liczba kolumn dopasowuje się sama). `front-page.html` powstanie w etapie 7 razem z hero. |

## Decyzje etapu 4 (szablony WooCommerce)

| # | Decyzja |
|---|---|
| E4-1 | Testy E2E działają na **osobnym środowisku** `.wp-env.test.json` (port 8891, własne kontenery i baza), uruchamianym przez `npm run env:test:start`. Środowisko deweloperskie 8890 służy do ręcznego klikania i zmian w edytorze, a testy go nie dotykają. Mechanizm: `--config` z wp-env 12 (opcja `testsEnvironment` jest przestarzała). |
| E4-2 | Listing (`archive-product`, `product-search-results`; kategorie, tagi i atrybuty dziedziczą `archive-product`): natywne **Product Filters** w panelu bocznym, poniżej breakpointu tablet (782 px z `theme.json`) jako nakładka WooCommerce. Sortowanie i licznik wyników z bloków WC. 12 produktów na stronę (`loop_shop_per_page`, priorytet 5, więc wtyczki mogą to nadpisać). |
| E4-3 | Karta produktu to wzorzec `hidden-product-card`, wspólny dla listingu i powiązanych produktów. Przycisk w karcie jest obrysowy (drugorzędny), a główny „Add to cart” na stronie produktu jest wypełniony. |
| E4-4 | Strona produktu używa bloków na Interactivity API zamiast klasycznych jQuery: `product-gallery` zamiast `product-image-gallery` (flexslider/photoswipe), Add to Cart + Options zamiast `add-to-cart-form`, `product-details` jako `core/accordion` (Opis, Informacje dodatkowe, Opinie z blokiem `product-reviews`). |
| E4-5 | Szkielet `single-product.html` leży bezpośrednio w szablonie, a nie w jednym wzorcu. Warstwa kompatybilności WooCommerce wybiera „pierwszy blok” na podstawie struktury szablonu. Przy całej treści we wzorcu doklejała region komunikatów do karty w powiązanych produktach. We wzorcach zostały tylko części z tekstami do tłumaczenia (akordeon, powiązane produkty). |
| E4-6 | JSON-LD: szablon produktu zawiera `woocommerce/breadcrumbs` i `woocommerce/product-summary`. Test sprawdza dokładnie jeden `Product` i jeden `BreadcrumbList`. |
| E4-7 | Kasa ma osobną część `checkout-header` (nazwa sklepu i „Back to cart”, bez menu i Mini-Cart) i nie ma stopki. Koszyk ma pełny nagłówek i stopkę. |
| E4-8 | „Moje konto” to klasyczny shortcode WooCommerce (nie ma bloku). Jego style (`assets/css/woocommerce-account.css`) ładują się tylko na `is_account_page()`. Panel menu ma tę samą szerokość co filtry sklepu (15rem). |
| E4-9 | Style WooCommerce są podzielone per blok (`Assets::BLOCK_STYLES`, `wp_enqueue_block_style`) i inline'owane tylko tam, gdzie blok się renderuje. Pola, checkboxy i radio w koszyku i kasie biorą kolory z palety, bo WC ma na sztywno białe tła (sprawdzone testem na palecie Midnight). |
| E4-10 | **Błędy dostępności WC 11.2.1 załatane w motywie** (`WooCommerce\Accessibility` i CSS): (a) `aria-label` bez roli na gwiazdkach filtra ocen → `role="img"`; (b) `role="list"` bez elementów listy w selektorze wariantów → rola usunięta; (c) za małe cele checkboxów filtrów i przycisku „pokaż hasło” (WCAG 2.5.8) → większe odstępy i rozmiar; (d) nieaktywny „Add to cart” przyciemniany przez `opacity` (kontrast 3,3:1) → kolory `muted`/`surface` z kontraktu palety; (e) limit 1000 px na tytułach koszyka i konta z `woocommerce-blocktheme.css` → szeroka siatka motywu. Do zgłoszenia w WooCommerce. |
| E4-11 | Seed dodaje 3 opinie z ocenami. `wp_insert_comment()` przelicza liczniki przed zapisem meta `rating`, dlatego średnia jest przeliczana jawnie (`WC_Comments::clear_transients`). |

## Decyzje etapu 5 (szuflada koszyka)

| # | Decyzja |
|---|---|
| E5-1 | Natywny Mini-Cart z `addToCartBehaviour: "open_drawer"` w nagłówku. Kolory szuflady dziedziczą z palety (sprawdzone na Midnight). Motyw dodaje tylko typografię, zaokrąglenia i separatory (`assets/css/blocks/woocommerce-mini-cart.css`). |
| E5-2 | **A11Y-1 (nazwa dialogu):** WooCommerce drukuje szufladę w `wp_footer` przez `echo`, bez filtra. `WooCommerce\MiniCart::wrap_overlay()` podmienia tylko ten callback na wersję z buforem i dodaje `aria-label="Shopping cart"` przez `WP_HTML_Tag_Processor`. Jeśli WooCommerce zmieni nazwę metody, poprawka się po prostu nie zastosuje, bez błędu. |
| E5-3 | **A11Y-2 (powrót focusu):** w WC 11.2.1 otwarcie z ikony i zamknięcie Escape/przyciskiem przywraca focus poprawnie. Nadal błędny jest tylko przypadek automatycznego otwarcia po „Add to cart”: focus trafiał na `<body>`. `assets/js/mini-cart-focus.js` (~1 KB, bez zależności, `defer`, ładowany tylko z blokiem Mini-Cart) przywraca focus do ostatniego elementu sprzed otwarcia albo do ikony koszyka. |
| E5-4 | **Szybka ścieżka do kasy (D9):** sekcja „Theme” w WooCommerce → Ustawienia → Zaawansowane (`woocommerce_get_sections_advanced` / `woocommerce_get_settings_advanced`), opcja `starter_pack_quick_checkout`, domyślnie `no`. Po włączeniu blok `woocommerce/mini-cart-cart-button-block` nie jest renderowany. Test E2E obejmuje oba stany, działającą stronę koszyka i zapis ustawienia w panelu. |
| E5-5 | Na stronach koszyka i kasy Mini-Cart nie jest renderowany (E3-7), więc skrypt focusu też się tam nie ładuje. |

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
