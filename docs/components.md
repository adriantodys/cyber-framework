# Komponenty i layouty — Cyber Framework

Ostatnia aktualizacja: 2026-09-12 (moduły: Top Header, Header desktop/mobile, Footer,
Copyright, Button, Social icons; warianty wrapperów i przyklejony header).

## Status

**Brak layoutów Flexible Content.** System komponentów to etap 4 w kolejności budowy
(CLAUDE.md sekcja 17); obecnie zrealizowane są etapy 1, 2 i 3.

Zajęte są `template-parts/header/`, `template-parts/footer/` oraz
`template-parts/components/` — patrz tabela „Komponenty reużywalne”. Pusty pozostaje
wyłącznie `template-parts/sections/`, który zapełni etap 4.

## Warianty wrapperów

Każdy komponent-wrapper (Header, Top Header, Footer, Copyright) niesie klasę
bazową **oraz** modyfikator wariantu, budowane przez `cyber_variant_class()`
z `inc/components.php`:

```php
$cyber_class = cyber_variant_class( 'cyber-footer', $variant ); // 'cyber-footer cyber-footer--default'
```

Argument `variant` jest opcjonalny — brak w `$args` oznacza `default`. Na razie
żaden moduł nie ma pola ACF na wariant i `default` jest jedyną wartością; klasa
istnieje jako punkt zaczepienia wymagany przez CLAUDE.md sekcja 20.

**Stany nie są wariantami.** Przyklejony header dokłada `.cyber-header--sticky`
**obok** modyfikatora wariantu, nie zamiast niego — to druga, prostopadła oś:

```html
<header class="cyber-header cyber-header--default cyber-header--sticky">
```

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
| Copyright | `template-parts/footer/copyright.php` | `text`, `links`, `variant` | sekcja „Copyright” w `assets/css/main.css` + zmienne z `cyber_copyright_css()` |
| Footer | `template-parts/footer/footer.php` | `logo_url`, `site_name`, `content`, `copyright`, `variant` | sekcja „Footer” w `assets/css/main.css` |
| Social icons | `template-parts/components/social-icons.php` | `items`, `class` | `.cyber-social-icons` w `assets/css/main.css`; ikony z `cyber_icons()` |
| Top Header | `template-parts/header/top-header.php` | `phone`, `email`, `social`, `variant` | sekcja „Top Header” w `assets/css/main.css` + zmienne z `cyber_top_header_css()`; ikony z `cyber_icons()` |
| Button | `template-parts/components/button.php` | `text`, `url`, `size`, `target`, `rel` | sekcja „Przyciski” w `assets/css/main.css` + zmienne z `cyber_button_css()` |
| Header (desktop + mobile) | `template-parts/header/header.php` | `logo_url`, `site_name`, `menu_alignment`, `menu_indicator`, `mobile_breakpoint`, `has_menu`, `variant`, `sticky` | sekcje „Header Desktop” i „Header Mobile” w `assets/css/main.css`, zmienne z `cyber_header_css()`, blok `@media` z `cyber_header_mobile_css()`, skrypt `assets/js/header.js` (enqueue warunkowy) |

#### Social icons

Wywoływany funkcją `cyber_social_icons( $args )` z `inc/components.php`, nie
bezpośrednio przez `get_template_part()` — funkcja buduje listę
(`cyber_social_links()`) i pomija render, gdy nie ma żadnego wypełnionego adresu.

| Kontekst | Wywołanie | Warunek pokazania ikony |
|---|---|---|
| Top Header | `array( 'respect_toggles' => true, 'class' => 'cyber-topheader__social' )` | przełącznik **oraz** wypełniony adres |
| Footer | `array( 'class' => 'cyber-footer__social' )` | tylko wypełniony adres |

Klasa bazowa `.cyber-social-icons` niesie układ (flex, `nowrap`, `gap`), klasa
kontekstu służy do ewentualnych nadpisań w danym miejscu.

#### Footer

Renderowany z `footer.php` w rootcie. Kolumny 2 i 3 są celowo puste — patrz
CLAUDE.md sekcja 22.

**Pasek Copyright jest częścią tego widoku**, a nie osobnym wywołaniem obok
stopki: przychodzi jako `$args['copyright']` (tablica albo `null`) i renderuje się
jako ostatnie dziecko `<footer>`, żeby należeć do landmarka `contentinfo`. Widok
sprawdza wyłącznie, czy dane przyszły — decyzja „czy jest co pokazać” zapadła
w `footer.php`.

Odstęp pionowy stopki siedzi na wrapperze `.cyber-footer__main`, nie na
`<footer>` — inaczej padding dolny wypadłby pod paskiem Copyright. Treść kolumny 1 pochodzi z pola WYSIWYG i jest wypisywana
przez `wp_kses_post()` (escapowanie przy outpucie, CLAUDE.md sekcja 8).

Kontener treści niesie klasę `.cyber-footer-text`, więc rozmiar i kolor pochodzą
z sekcji „Stylizacja Footer". Klasy `.cyber-footer-title` i `.cyber-footer-link`
są zdefiniowane w CSS, ale nie mają jeszcze zastosowania w markupie — czekają
na zawartość kolumn 2 i 3.

#### Copyright

Renderowany **wewnątrz** `<footer>`, jako jego ostatnie dziecko — wywołuje go
widok stopki, nie `footer.php` w rootcie. Dane buduje `cyber_copyright_data()`
(`inc/footer.php`), a o tym, czy pasek w ogóle się pojawi, decyduje
`cyber_copyright_has_content()` w `footer.php` — ta sama zasada „pusty pasek się
nie renderuje” co w Top Header.

Markup jest strukturalnie tożsamy z Top Header: kontener / inner / dwie strony flex
(CLAUDE.md sekcja 16a). Kolejny moduł o tym kształcie ma reużyć ten sam wzorzec,
a nie tworzyć nowy.

| Element | Skąd pochodzi |
|---|---|
| Tekst | `cyber_copyright_text`; znacznik `{year}` podmieniany na bieżący rok (`wp_date( 'Y' )`) |
| Linki prawne | `cyber_copyright_privacy_link`, `cyber_copyright_cookies_link` — pola **Page Link**, więc zwracają sam URL |
| Etykiety linków | **stałe w widoku**, nie w ACF — Page Link nie przechowuje tytułu (CLAUDE.md sekcja 5a) |
| Styl | zmienne z `cyber_copyright_css()`; padding paska i separator między linkami są stałe w `main.css` |

Widok nie zawiera warunków biznesowych — sprawdza wyłącznie, czy tekst jest niepusty
i czy lista linków coś zawiera. Link bez znanego klucza etykiety jest pomijany.

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
`cyber_get_option()` samodzielnie. Dotyczy to również `sticky`: widok dostaje
gotowy `bool` i jedynie dokłada klasę `.cyber-header--sticky`, a całe zachowanie
przyklejania leży w CSS (`position: sticky`), bez JavaScriptu. Wartości liczbowe i kolory w ogóle nie przechodzą
przez PHP widoku: trafiają na front jako zmienne CSS w `wp_head`
(patrz `docs/acf-schema.md`, sekcja „Stan: generowanie CSS”).

## Zasady

1. Jeden layout ACF = jeden plik w `template-parts/sections/`.
2. Komponent nie sięga po stan globalny — dane dostaje jawnie przez `$args`
   w `get_template_part()` (CLAUDE.md sekcja 4).
3. Assety specyficzne dla komponentu kolejkowane są warunkowo, przy jego renderowaniu,
   nie globalnie (CLAUDE.md sekcja 10).
4. Escaping wykonywany bezpośrednio przy outpucie w pliku widoku.
