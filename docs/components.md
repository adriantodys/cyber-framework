# Komponenty i layouty — Cyber Framework

Ostatnia aktualizacja: 2026-09-10 (moduły: Header desktop/mobile, Button).

## Status

**Brak layoutów Flexible Content.** System komponentów to etap 4 w kolejności budowy
(CLAUDE.md sekcja 17); obecnie zrealizowane są etapy 1, 2 oraz część etapu 3 (header).

Katalogi `template-parts/components/` i `template-parts/sections/` istnieją, ale są puste.
Zajęty jest `template-parts/header/` — patrz tabela „Komponenty reużywalne”.

## Reguła, którą będzie realizować ten dokument

Relacja jest jednokierunkowa i jawna:

```
ACF layout → template-part → HTML → CSS
```

Każdy nowy layout Flexible Content musi trafić do tabeli poniżej **w tym samym commicie**,
w którym powstał.

## Layouty Flexible Content

| Nazwa layoutu (ACF) | Plik | Używane pola | Assety |
|---|---|---|---|
| *(brak)* | — | — | — |

## Komponenty reużywalne

| Komponent | Plik | Argumenty (`$args`) | Assety |
|---|---|---|---|
| Top Header | `template-parts/header/top-header.php` | `phone`, `email`, `social` | sekcja „Top Header” w `assets/css/main.css` + zmienne z `cyber_top_header_css()`; ikony z `cyber_icons()` |
| Button | `template-parts/components/button.php` | `text`, `url`, `size`, `target`, `rel` | sekcja „Przyciski” w `assets/css/main.css` + zmienne z `cyber_button_css()` |
| Header (desktop + mobile) | `template-parts/header/header.php` | `logo_url`, `site_name`, `menu_alignment`, `menu_indicator`, `mobile_breakpoint`, `has_menu` | sekcje „Header Desktop” i „Header Mobile” w `assets/css/main.css`, zmienne z `cyber_header_css()`, blok `@media` z `cyber_header_mobile_css()`, skrypt `assets/js/header.js` (enqueue warunkowy) |

#### Top Header

Renderowany z `header.php` w rootcie, **przed** nagłówkiem. Dane buduje
`cyber_top_header_data()` (`inc/header.php`) — widok nie zawiera żadnych warunków
biznesowych, tylko sprawdza, czy dana pozycja istnieje w `$args`.

Pasek nie renderuje się wcale, gdy nie ma czego pokazać — decyduje o tym
`cyber_top_header_has_content()` wywoływane przed `get_template_part()`.

Ikony pochodzą z rejestru `cyber_icons()` — `cyber_get_icon()` dla telefonu
i koperty, `cyber_get_social_icon()` dla profili — i są wypisywane bez escapowania —
to stały markup z kodu, bez danych użytkownika; `esc_html()` zamieniłoby znaczniki
SVG w tekst. Dostępna nazwa siedzi w `aria-label` linku, bo sama ikona ma
`aria-hidden="true"`.

#### Button

Wywoływany funkcją `cyber_button( $args )` z `inc/components.php`, nie bezpośrednio
przez `get_template_part()` — funkcja waliduje rozmiar i uzupełnia `rel="noopener"`
dla `target="_blank"`, a plik widoku zakłada, że dostaje dane już sprawdzone.
Funkcja **wypisuje** znacznik, nie zwraca go.

| `size` | Klasa CSS | Pola ACF |
|---|---|---|
| `large` | `.btn .btn-large` | `cyber_btn_large_padding_y` / `_padding_x` / `_font_size` / `_font_weight` / `_color` / `_color_hover` / `_bg_color` / `_bg_color_hover` |
| `medium` *(domyślny)* | `.btn .btn-medium` | `cyber_btn_medium_*`, ten sam zestaw ośmiu pól |
| `small` | `.btn .btn-small` | `cyber_btn_small_*`, ten sam zestaw ośmiu pól |

Argumenty: `text` (wymagany), `url` (wymagany), `size` (domyślnie `medium`),
`target`, `rel`. Pusty `text` albo `url` = komponent nie generuje nic, dzięki czemu
opcjonalny przycisk w sekcji ACF nie wymaga warunku po stronie widoku.
Nieznany `size` → `_doing_it_wrong()` i fallback na `medium`.

Komponent nie przyjmuje kolorów ani pikseli — wyłącznie nazwę rozmiaru. Wygląd
należy do Global Options, nie do miejsca wywołania.

#### Header

Header dostaje wszystkie dane przez `$args` z `header.php` w rootcie — nie woła
`cyber_get_option()` samodzielnie. Wartości liczbowe i kolory w ogóle nie przechodzą
przez PHP widoku: trafiają na front jako zmienne CSS w `wp_head`
(patrz `docs/acf-schema.md`, sekcja „Stan: generowanie CSS”).

## Zasady

1. Jeden layout ACF = jeden plik w `template-parts/sections/`.
2. Komponent nie sięga po stan globalny — dane dostaje jawnie przez `$args`
   w `get_template_part()` (CLAUDE.md sekcja 4).
3. Assety specyficzne dla komponentu kolejkowane są warunkowo, przy jego renderowaniu,
   nie globalnie (CLAUDE.md sekcja 10).
4. Escaping wykonywany bezpośrednio przy outpucie w pliku widoku.
