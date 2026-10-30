# Starter Pack — WooCommerce block theme

Szybki, blokowy motyw startowy dla WooCommerce.

- Specyfikacja: `.claude/IDEA.md`
- Decyzje i wyniki weryfikacji: [`docs/decisions.md`](docs/decisions.md)
- Praca deweloperska: [`docs/development.md`](docs/development.md)
- System designu: [`docs/design-system.md`](docs/design-system.md)

Wymagania: PHP 8.3+, WordPress 7.0+, WooCommerce 10.6.1+.

## Konfiguracja sklepu

| Ustawienie | Gdzie | Domyślnie |
|---|---|---|
| **Szybka ścieżka do kasy**: szuflada koszyka pokazuje tylko „Go to checkout”, bez „View cart”. Strona koszyka nadal działa. | WooCommerce → Ustawienia → Zaawansowane → Theme | wyłączone |
| Szuflada koszyka otwiera się po dodaniu produktu | Edytor witryny → Header → blok Mini-Cart → ustawienia bloku (zachowanie po dodaniu do koszyka; atrybut `addToCartBehaviour`) | włączone |
| Pasek ogłoszeń: tekst, link, wyłączenie | Edytor witryny → Header → grupa „Announcement bar” (usuń blok, aby wyłączyć) | włączony |
| Kolory, fonty, warianty stylu | Edytor witryny → Style | paleta domyślna, fonty systemowe |
