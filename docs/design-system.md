# System designu

Cały wygląd motywu wynika z `theme.json` i jest edytowalny bez kodu w **Wygląd → Edytor → Style**. Własny CSS (`assets/css/base.css`, ~0,8 KB gzip, inline'owany) obsługuje tylko to, czego `theme.json` nie wyraża: pola formularzy, globalny focus i `prefers-reduced-motion`.

## Paleta: 8 ról

| Slug | Rola |
|---|---|
| `background` | Tło strony |
| `surface` | Tło kart, sekcji, kodu |
| `text` | Tekst podstawowy |
| `muted` | Tekst drugorzędny, podpisy, obramowania pól formularzy |
| `primary` | Przyciski, linki, akcenty interaktywne |
| `on-primary` | Tekst na `primary` i `secondary` (przyciski) |
| `secondary` | Hover przycisków |
| `accent` | Wyróżnienia nietekstowe (np. promocje) |

### Kontrakt kontrastu (WCAG 2.2 AA)

Każda paleta, także własna paleta sklepu, musi spełniać:

| Pierwszy plan / tło | Minimum |
|---|---|
| `text` / `background`, `text` / `surface` | 4.5 : 1 |
| `muted` / `background`, `muted` / `surface` | 4.5 : 1 |
| `primary` / `background` (linki) | 4.5 : 1 |
| `on-primary` / `primary`, `on-primary` / `secondary` | 4.5 : 1 |
| `accent` / `background` | 3 : 1 |

Dla palet dostarczonych z motywem sprawdzają to testy E2E (`tests/e2e/design-system.spec.mjs`). Palety ustawionej ręcznie przez sprzedawcę motyw nie zablokuje, bo WordPress nie ma takiego mechanizmu. Edytor pokazuje ostrzeżenia o kontraście dla części bloków.

## Tokeny

| Grupa | Wartości | Zmienna CSS |
|---|---|---|
| Fonty | `system-sans`, `system-mono` (0 KB, bez pobierania) | `--wp--preset--font-family--*` |
| Rozmiary fontów | `small`, `medium`, `large`, `x-large`, `xx-large`, `xxx-large` (płynne) | `--wp--preset--font-size--*` |
| Odstępy | `20`–`80` (od `50` wzwyż płynne, `clamp()`) | `--wp--preset--spacing--*` |
| Promienie | `none`, `small`, `medium`, `large`, `full` | `--wp--preset--border-radius--*` |
| Promienie semantyczne | `control`, `button`, `card` → wskazują na presety | `--wp--custom--radius--*` |
| Focus | `width`, `offset` | `--wp--custom--focus--*` |
| Szerokości | treść 720 px, „wide” 1280 px, boczny padding `spacing-50` | `--wp--style--global--*-size` |
| Breakpointy (WP 7.1) | `mobile` ≤ 480 px, `tablet` ≤ 782 px | `settings.viewport` |

Breakpointy pozwalają ustawiać style bloków osobno dla mobile i tablet oraz ukrywać bloki per urządzenie, natywnie w edytorze.

## Warianty stylów (`styles/`)

| Plik | Typ | Opis |
|---|---|---|
| `colors/ocean.json` | paleta | Niebieski primary, pomarańczowy accent |
| `colors/midnight.json` | paleta | Ciemny motyw |
| `typography/editorial.json` | typografia | Nagłówki w **Fraunces** (font lokalny, OFL, ~70 KB w 2 subsetach: latin + latin-ext, ładowane tylko przy użyciu) |
| `editorial.json` | pełny wariant | Ciepła paleta + Fraunces + ostre narożniki (promienie `none`) |
| `sections/surface.json` | styl sekcji | Grupa/kolumny na tle `surface` |
| `sections/inverted.json` | styl sekcji | Odwrócone kolory (`text` jako tło) |

### Dodanie nowej palety

1. Skopiuj `styles/colors/ocean.json`, zmień `title`, `slug` i 8 kolorów.
2. Dodaj wariant do `VARIATIONS` w `tests/e2e/design-system.spec.mjs`.
3. `npm run test:e2e`: test sprawdzi kontrakt kontrastu, axe i propagację do przycisków WooCommerce.

## Zasady dla kodu

- W CSS motywu nie wolno wpisywać kolorów na sztywno. `npm run lint:css` odrzuca hex, nazwy kolorów i `rgb()`/`hsl()`/itp. Dozwolone są tylko `var(--wp--preset--color--*)` i `color-mix()` na tych zmiennych.
- Nowe style bloków dodajemy najpierw w `theme.json` (`styles.blocks`), a do CSS sięgamy dopiero wtedy, gdy `theme.json` nie wystarcza.
