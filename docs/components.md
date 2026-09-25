# Komponenty i layouty — Cyber Framework

Ostatnia aktualizacja: 2026-09-13 (moduły: Top Header, Header desktop/mobile, Footer,
Copyright, Breadcrumb, Button, Social icons; warianty wrapperów, przyklejony header,
cztery warianty układu headera ze slotem akcji oraz warstwa sklepowa WooCommerce —
koszyk i strona zamówienia).

## Status

**Brak layoutów Flexible Content.** System komponentów to etap 4 w kolejności budowy
(CLAUDE.md sekcja 17); obecnie zrealizowane są etapy 1, 2 i 3.

Zajęte są `template-parts/header/`, `template-parts/footer/` oraz
`template-parts/components/` — patrz tabela „Komponenty reużywalne”. Pusty pozostaje
wyłącznie `template-parts/sections/`, który zapełni etap 4.

## Warianty wrapperów

Każdy wrapper modułu — Header, Top Header, Footer, Copyright, Breadcrumb,
lista produktów (`.cyber-shop`) i strona produktu (`.cyber-product`) — niesie
klasę bazową **oraz** modyfikator wariantu, budowane przez `cyber_variant_class()`
z `inc/components.php`:

```php
$cyber_class = cyber_variant_class( 'cyber-footer', $variant ); // 'cyber-footer cyber-footer--default'
```

Argument `variant` jest opcjonalny — brak w `$args` oznacza `default`.

Dwa moduły mają już realne warianty:

| Moduł | Źródło wartości | Wartości |
|---|---|---|
| Header | pole ACF `cyber_header_variant` | `default`, `centered`, `cta`, `woocommerce` |
| Lista produktów | ciasteczko `cyber_shop_view` | `grid`, `list` |

Reszta stoi na `default` — tam klasa jest punktem zaczepienia wymaganym przez
CLAUDE.md sekcja 20, nie martwym kodem.

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
| Breadcrumb | `template-parts/breadcrumb/breadcrumb.php` | `context`, `items`, `in_page_header` | sekcja „Breadcrumb” w `assets/css/main.css` + zmienne z `cyber_breadcrumb_css()` |
| Breadcrumb WooCommerce | `template-parts/breadcrumb/breadcrumb-woocommerce.php` | `context`, `in_page_header` | ta sama sekcja CSS; ścieżkę wypisuje `woocommerce_breadcrumb()` we własnych znacznikach motywu |
| Header — slot akcji: CTA | `template-parts/header/actions-cta.php` | `cta` | `.cyber-header__actions` w `assets/css/main.css`; przycisk z `cyber_button()` w rozmiarze `medium` |
| Header — slot akcji: WooCommerce | `template-parts/header/actions-woocommerce.php` | `wc` | sekcja „Header: konto i koszyk WooCommerce” w `assets/css/main.css`; ikony `user` i `cart` z `cyber_icons()` |
| Header — licznik koszyka | `template-parts/header/cart-count.php` | `count` | `.cyber-wc-count` w `assets/css/main.css` |
| Przycisk do góry | `template-parts/go-to-top/go-to-top.php` | `class`, `show_after`, `label`, `icon` | `assets/css/go-to-top.css` + zmienne z `cyber_go_to_top_css()`, skrypt `assets/js/go-to-top.js` — oba tylko przy włączonym przycisku; ikona `arrow-up` z `cyber_icons()` |
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

#### Breadcrumb

Dwa widoki dzielą **identyczny markup**, więc obsługuje je jeden blok CSS:

```html
<div class="cyber-breadcrumb cyber-breadcrumb--default">   <!-- albo --wc -->
  <div class="cyber-container">
    <nav class="cyber-breadcrumb__nav" aria-label="Okruszki">
      <span class="cyber-breadcrumb__item"><a href="…">Strona glowna</a></span>
      <span class="cyber-breadcrumb__item" aria-current="page">Bieżąca</span>
    </nav>
  </div>
</div>
```

Różnica jest jedna: w wariancie `default` pętlę po okruszkach wykonuje widok,
w wariancie `wc` wypisuje ją `woocommerce_breadcrumb()` — ale w **tych samych**
znacznikach, podstawionych przez `wrap_before` / `before` / `after`.

Ostatni okruszek to strona bieżąca. W naszym markupie niesie `aria-current="page"`;
WooCommerce takiego atrybutu nie dodaje, dlatego kolor bieżącej strony bierze się
z selektora `:last-child`, prawdziwego w obu przypadkach.

Widok WooCommerce sprawdza `function_exists( 'woocommerce_breadcrumb' )` mimo że
wariant jest wybrany w panelu — wtyczkę można wyłączyć w każdej chwili, a brak
funkcji ma oznaczać brak paska, nigdy błąd.

Klasę wrappera buduje `cyber_variant_class( 'cyber-breadcrumb', $context )`,
ten sam mechanizm co w Header, Footer i Copyright.

#### Header — warianty układu i slot akcji

Cztery warianty (`cyber_header_variant`) nakładają klasę na wrapper i nic poza tym:

| Wariant | Klasa | Slot akcji |
|---|---|---|
| `default` | `.cyber-header--default` | brak |
| `centered` | `.cyber-header--centered` | brak |
| `cta` | `.cyber-header--cta` | `actions-cta.php` |
| `woocommerce` | `.cyber-header--woocommerce` | `actions-woocommerce.php` |

**Slot akcji** (`.cyber-header__actions`) to jedyne miejsce, w którym warianty
różnią się markupem. Każdy wnosi **własny template-part**, zamiast rozrastać
`header.php` o kolejne gałęzie — nowy wariant to nowy plik obok, a nie kolejny `if`.

Slot jest renderowany **poza** warunkiem `has_menu`: przycisk CTA i ikony sklepu
mają sens także wtedy, gdy do lokalizacji `primary` nie przypisano jeszcze menu.

Dwa warianty nie renderują slotu wcale — nie generują też pustego `<div>`.
Dotyczy to również wariantu `cta` z niewypełnionymi polami.

#### Header

Header dostaje wszystkie dane przez `$args` z `header.php` w rootcie — nie woła
`cyber_get_option()` samodzielnie. Dotyczy to również `sticky`: widok dostaje
gotowy `bool` i jedynie dokłada klasę `.cyber-header--sticky`, a całe zachowanie
przyklejania leży w CSS (`position: sticky`), bez JavaScriptu. Wartości liczbowe i kolory w ogóle nie przechodzą
przez PHP widoku: trafiają na front jako zmienne CSS w `wp_head`
(patrz `docs/acf-schema.md`, sekcja „Stan: generowanie CSS”).

## Sekcje — Flexible Content

Treść strony budowana z klocków. Jedno pole `cyber_sections`, każdy klocek to
layout ACF z odpowiednikiem w rejestrze i jednym plikiem w
`template-parts/sections/`.

### Szkielet wspólny dla wszystkich sekcji

```
<section class="cyber-section cyber-section--[layout]" style="--cyber-section-*">
  <div class="cyber-section__overlay">        ← tylko gdy ustawiono nakładkę
  <div class="cyber-section__inner">          ← tu działa szerokość
    <div class="cyber-section__top">          ← WYSIWYG góra
    <div class="cyber-section__body">         ← treść layoutu
    <div class="cyber-section__bottom">       ← WYSIWYG dół
```

Opakowanie wypisują `cyber_section_open()` i `cyber_section_close()`, nie każdy
plik sekcji z osobna — zmiana struktury w jedenastu plikach naraz to gwarancja
rozjazdu.

### Wspólne cegiełki funkcji `*_attributes()`

Każda sekcja ma własne `cyber_<layout>_attributes( $row )`, które buduje tablicę
zmiennych CSS i oddaje ją jako `style`. Te fragmenty są **wspólne dla
wszystkich** i nie wolno ich przepisywać w nowym module:

| Funkcja | Plik | Rola |
|---|---|---|
| `cyber_css_declarations()` | `inc/helpers.php` | Skleja pary `nazwa => wartość` w `--a:1px;--b:red;`. Wynik idzie do atrybutu `style`. **Bez wyjątków** — patrz CLAUDE.md sekcja 6 |
| `cyber_css_root()` | `inc/helpers.php` | To samo, opakowane w `:root{…}` — dla modułów globalnych wypisywanych w `wp_head` |
| `cyber_row_colors()` | `inc/helpers.php` | Wybiera z wiersza tylko te kolory, które redaktor **faktycznie ustawił**. Puste pole koloru znaczy „zostaw wartość z arkusza", więc pusta wartość nie ma prawa trafić do `style` jako pusta deklaracja |
| `cyber_section_choice()` | `inc/sections.php` | Wartość z białej listy albo domyślna |
| `cyber_section_spacing()` | `inc/sections.php` | Wartość ze skali odstępów (CLAUDE.md sekcja 6) |
| `cyber_sanitize_color()` | `inc/helpers.php` | Kolor z kanałem alfa — `sanitize_hex_color()` odrzuciłoby `rgba()` |

`cyber_row_colors()` powstało z pięciu identycznych kopii tej samej pętli
(galeria, kontakt, licznik, FAQ, tabela). Wynik dokleja się przez
`array_merge()`, nie `+=` — operator `+` **nie** nadpisuje kluczy już
obecnych w tablicy, a pierwotna pętla nadpisywała.

### Pliki

| Plik | Rola |
|---|---|
| `inc/sections.php` | rejestr, walidacja wartości, budowa opakowania, renderer, assety |
| `inc/animations.php` | animacje wejścia — rejestr animacji, atrybut `data-cyber-animate`, warunkowe assety |
| `assets/js/animations.js` | animacje wejścia — kiedy uruchomić (IntersectionObserver) |
| `assets/css/animations.css` | animacje wejścia — jak wygląda każda animacja |
| `inc/sections-cards.php` | layout `cards` — logika siatki i elementów |
| `template-parts/sections/cards.php` | layout `cards` — widok |
| `acf-json/group_section_cards.json` | źródło klonowania: pola sekcji Karty |
| `inc/sections-columns.php` | layout `columns` — proporcje i siatka |
| `template-parts/sections/columns.php` | layout `columns` — widok |
| `acf-json/group_section_columns.json` | źródło klonowania: pola sekcji Kolumny |
| `inc/sections-slider.php` | layout `slider` — logika i assety |
| `template-parts/sections/slider.php` | layout `slider` — widok |
| `assets/js/slider.js` | layout `slider` — inicjalizacja Swipera |
| `acf-json/group_section_slider.json` | źródło klonowania: pola sekcji Slider |
| `inc/sections-carousel.php` | layout `carousel` — konfiguracja przewijania |
| `template-parts/sections/carousel.php` | layout `carousel` — widok |
| `template-parts/components/card.php` | wspólny markup karty dla `cards` i `carousel` |
| `acf-json/group_section_carousel.json` | źródło klonowania: ustawienia karuzeli |
| `inc/sections-faq.php` | layout `faq` — normalizacja pytań, kolumny, klasy i zmienne |
| `template-parts/sections/faq.php` | layout `faq` — widok (`<details>`/`<summary>`) |
| `acf-json/group_section_faq.json` | źródło klonowania: pola sekcji FAQ |
| `inc/sections-counter.php` | layout `counter` — normalizacja liczb, formatowanie, klasy i zmienne, warunkowy skrypt |
| `template-parts/sections/counter.php` | layout `counter` — widok |
| `assets/js/counter.js` | layout `counter` — odliczanie (IntersectionObserver + requestAnimationFrame) |
| `acf-json/group_section_counter.json` | źródło klonowania: pola sekcji Licznik |
| `inc/sections-contact.php` | layout `contact` — dane z Global Options, konfiguracja, klasy i zmienne |
| `inc/contact-form-7.php` | zależność miękka Contact Form 7 — wykrycie, formularz, komunikaty |
| `template-parts/sections/contact.php` | layout `contact` — widok |
| `acf-json/group_section_contact.json` | źródło klonowania: pola sekcji Kontakt |
| `inc/posts.php` | wpis → element karty (`cyber_post_card_item()`), data, zajawka, dozwolone typy treści — wspólne dla sekcji Wpisy, bloga i widgetu |
| `inc/sections-posts.php` | layout `posts` — zapytanie, elementy, lista typów w panelu |
| `template-parts/sections/posts.php` | layout `posts` — widok (siatka albo komponent karuzeli) |
| `acf-json/group_section_posts.json` | źródło klonowania: źródło i ustawienia elementu |
| `inc/sections-table.php` | layout `table` — dane, klasy, zmienne |
| `template-parts/sections/table.php` | layout `table` — widok |
| `acf-json/group_section_table.json` | źródło klonowania: pola tabeli |
| `assets/js/admin-sections.js` | panel: liczba pól komórek w wierszu idzie za liczbą kolumn (ładowany tylko tam, gdzie ACF rysuje formularz) |
| `template-parts/components/carousel.php` | **wspólny** rząd kart ze Swiperem albo taśmą — sekcje `carousel` i `posts` |
| `inc/gallery.php` | CPT `cyber_gallery`, kategorie, dane i klasy sekcji, warunkowe assety |
| `template-parts/sections/gallery.php` | layout `gallery` — widok sekcji |
| `template-parts/components/gallery.php` | **wspólna** siatka zdjęć z filtrami — sekcja i strona galerii |
| `templates/single-gallery.php` | strona pojedynczej galerii |
| `assets/js/gallery.js` | filtry i lightbox (natywny `<dialog>`, bez biblioteki) |
| `assets/css/gallery.css` | siatka, pasek filtrów, lightbox, strona galerii |
| `acf-json/group_section_gallery.json` | źródło klonowania: pola sekcji Galeria |
| `acf-json/group_gallery.json` | zdjęcia pojedynczej galerii (CPT) |
| `inc/sections-global.php` | layout `global` — CPT `cyber_global_section`, odczyt, kolumna „Używana na” |
| `template-parts/sections/global.php` | layout `global` — **tylko** podpowiedź dla redaktora, gdy nie ma czego pokazać |
| `assets/css/sections.css` | style opakowania — ładowany **tylko** gdy wpis ma sekcje |
| `acf-json/group_sections.json` | pole Flexible Content |
| `acf-json/group_section_content.json` | źródło klonowania: WYSIWYG góra/dół |
| `acf-json/group_section_settings.json` | źródło klonowania: ustawienia wyglądu |

### Rejestr

`cyber_section_types()` w `inc/sections.php` — jedno źródło prawdy:

| Klucz | Etykieta | Szablon | Konteksty |
|---|---|---|---|
| `cards` | Karty (icon boxes) | `cards` | `page`, `post` |
| `columns` | Kolumny tekstowe (WYSIWYG) | `columns` | `page`, `post` |
| `slider` | Slider | `slider` | `page`, `post` |
| `carousel` | Karuzela kart | `carousel` | `page`, `post` |
| `faq` | FAQ (pytania i odpowiedzi) | `faq` | `page`, `post` |
| `counter` | Licznik (counter) | `counter` | `page`, `post` |
| `contact` | Kontakt (dane + formularz) | `contact` | `page`, `post` |
| `posts` | Wpisy / CPT (karty lub slider) | `posts` | `page`, `post` |
| `gallery` | Galeria (zdjęcia albo galerie z CPT) | `gallery` | `page`, `post` |
| `table` | Tabela | `table` | `page`, `post` |
| `global` | Sekcja globalna | `global` | `page`, `post` — **nigdy** `cyber_global_section` |

Kolumna **Konteksty** jest już wypełniona, ale jeszcze nieużywana — filtrowanie
dostępności layoutów per typ treści to osobny krok.

**Dołożenie sekcji:** wpis w rejestrze + plik w `template-parts/sections/` +
layout w `group_sections.json` (klon ustawień w środku) + blok CSS. Wspólnych
pól się **nie kopiuje** — wchodzą polem Clone.

### Klasy

| Klasa | Skąd |
|---|---|
| `.cyber-section` | `cyber_section_open()`, wspólna dla wszystkich layoutów |
| `.cyber-section--[layout]` | klucz layoutu, punkt zaczepienia dla stylów jednego typu sekcji |
| `.cyber-section--has-overlay` | dokładana tylko wtedy, gdy kolor nakładki jest ustawiony |
| `.cyber-section__inner` | kontener treści; szerokość ze zmiennej, marginesy boczne wspólne z resztą strony |
| `.cyber-section__overlay` | warstwa nad zdjęciem, `aria-hidden` |

Dodatkowe klasy z pola ACF dokładane są do `.cyber-section`, każda przez
`sanitize_html_class()`.

### Punkt wpięcia: filtr `cyber_section_attributes`

`cyber_section_attributes()` oddaje wynik przez filtr
`cyber_section_attributes( $attributes, $type, $row )`. Tablica ma klucze
`class`, `style`, `id` i `data` — ten ostatni to atrybuty `data-*`
(nazwa bez prefiksu `data-` → wartość), wypisywane przez `cyber_section_open()`
z `sanitize_key()` na nazwie i `esc_attr()` na wartości.

Filtr jest dla modułów, które dokładają coś do **każdej** sekcji. Taki moduł
nie dopisuje się do `inc/sections.php`, więc jego usunięcie nie wymaga zmian
w tym pliku. Pierwszy użytkownik: animacje wejścia.

### Animacje wejścia sekcji

Każda sekcja ma w **Ustawieniach sekcji** pole **Animacja wejścia**
(`cyber_section_animation`). Parametry wspólne — czas, opóźnienie, płynność,
start, dystans, powtarzanie — są w **Global Options → Animacje**.

| Klucz | Etykieta | Stan początkowy (przed wejściem na ekran) |
|---|---|---|
| `none` | Brak | — (domyślne; sekcja bez atrybutu) |
| `fade` | Zanikanie | `opacity: 0` |
| `from-bottom` | Wjazd z dołu | przesunięcie w dół o `--cyber-anim-distance` |
| `from-top` | Wjazd z góry | przesunięcie w górę |
| `from-left` | Wjazd z lewej | przesunięcie w lewo |
| `from-right` | Wjazd z prawej | przesunięcie w prawo |
| `zoom-in` | Powiększenie | `scale(0.92)` |
| `zoom-out` | Pomniejszenie | `scale(1.08)` |
| `blur` | Rozmycie | `filter: blur(8px)` |

Każda animacja poza `none` startuje też od `opacity: 0`.

**Przepływ:**

```
pole cyber_section_animation ──► filtr cyber_section_attributes (inc/animations.php)
                                   └─► <section … data-cyber-animate="from-bottom">
Global Options → Animacje ──► --cyber-anim-* w <style> (cyber_animation_css(), inc/enqueue.php)
                          └─► window.cyberAnimations = {offset, once} (skrypt startowy w <head>)
animations.js: sekcja wjeżdża na ekran ──► klasa .is-animated ──► animations.css: przejście do stanu końcowego
```

| Selektor / znacznik | Skąd | Rola |
|---|---|---|
| `[data-cyber-animate="…"]` | `inc/animations.php` | Jaka animacja. Atrybut, nie klasa: animacja to osobna oś obok wariantu sekcji (CLAUDE.md sekcja 20, „Stan to nie wariant”), a biblioteki animacji konfiguruje się właśnie atrybutami |
| `html.cyber-anim-ready` | skrypt startowy w `<head>` | Bez tej klasy CSS **niczego nie ukrywa** |
| `.is-animated` | `animations.js` | Sekcja w stanie końcowym |

| Funkcja / stała | Plik | Rola |
|---|---|---|
| `cyber_animation_types()` | `inc/animations.php` | Rejestr animacji: klucz → etykieta. Jedyne źródło listy |
| `CYBER_ANIMATION_NONE` | `inc/animations.php` | Klucz „bez animacji” (`none`) |
| `cyber_animation_field()` | `inc/animations.php` | `acf/load_field` — opcje pola w panelu z rejestru |
| `cyber_section_animation()` | `inc/animations.php` | Animacja wiersza po walidacji (biała lista rejestru) |
| `cyber_animation_section_attributes()` | `inc/animations.php` | Filtr `cyber_section_attributes` — dokłada `data-cyber-animate` |
| `cyber_animations_needed()` | `inc/animations.php` | Czy wpis ma choć jedną animowaną, włączoną sekcję (z sekcjami globalnymi) |
| `cyber_animation_init_script()` | `inc/animations.php` | Skrypt startowy w `<head>`: konfiguracja i flaga `cyber-anim-ready` |
| `cyber_animation_assets()` | `inc/animations.php` | Warunkowe kolejkowanie arkusza, skryptu startowego i silnika |
| `cyber_animation_css_map()` / `cyber_animation_css()` | `inc/enqueue.php` | Zmienne `--cyber-anim-*` (warstwa danych — zostaje przy wymianie silnika) |

**Odporność — sekcja nigdy nie zostaje niewidoczna:**

- Bez JavaScriptu nie ma klasy `cyber-anim-ready`, więc sekcje są widoczne od razu.
- Przy systemowym ograniczeniu ruchu (`prefers-reduced-motion`) albo w przeglądarce
  bez `IntersectionObserver` skrypt startowy nie ustawia klasy — sekcje są widoczne
  bez animacji. CSS ma dodatkowo blok `prefers-reduced-motion`.
- Jeśli `animations.js` nie wystartuje w ciągu 3 s (błąd sieci, bloker), skrypt
  startowy zdejmuje klasę i pokazuje treść. Spóźniony silnik widzi brak klasy
  i nic nie robi, zamiast chować widoczną już treść.
- Klasa jest ustawiana w `<head>`, przed pierwszym malowaniem — ustawiana
  w stopce powodowałaby mrugnięcie sekcji na górze strony.
- Stan końcowy nie ma `transform` ani `filter`, więc po animacji sekcja nie
  tworzy nowego bloku zawierającego (nie psuje `position: fixed` i `sticky`
  w środku). Na czas animacji `body` ma `overflow-x: clip`, żeby sekcja
  wjeżdżająca z prawej nie dokładała poziomego paska; `clip`, nie `hidden`,
  bo `hidden` wyłączyłoby przyklejony header.

**Assety** ładują się tylko na wpisie, na którym choć jedna **włączona** sekcja
— także wewnątrz sekcji globalnej — ma animację inną niż `none`, i tylko przy
włączonym `cyber_anim_enable` (CLAUDE.md sekcja 10).

**Nowa animacja:** klucz i etykieta w `cyber_animation_types()` + reguła
w `assets/css/animations.css` + kopia opcji w `acf-json/group_section_settings.json`
(pole ma ją dla panelu bez modułu; na co dzień listę podmienia `acf/load_field`)
+ wiersz w tabeli wyżej i w `docs/acf-schema.md`.

#### Usunięcie albo wymiana silnika animacji

Moduł ma **dwie warstwy**, rozdzielone celowo:

| Warstwa | Co | Przy wymianie silnika |
|---|---|---|
| **Silnik** | `inc/animations.php`, `assets/js/animations.js`, `assets/css/animations.css`, linia `'inc/animations.php'` w `functions.php` | usuwasz |
| **Dane** | pole `cyber_section_animation`, zakładka „Animacje”, wpisy `anim_*` w `cyber_option_schema()` i `default-acf.php`, `cyber_animation_css()` w `inc/enqueue.php`, filtr `cyber_section_attributes` | zostają |

**Samo usunięcie animacji:** skasuj trzy pliki silnika i linię w
`functions.php`. Nic więcej nie trzeba — sekcje renderują się bez atrybutu
`data-cyber-animate`, zmienne `--cyber-anim-*` nikomu nie przeszkadzają,
a wybory redaktorów zostają w bazie na wypadek powrotu.

**Wymiana na bibliotekę** (np. AOS, Motion):

1. Usuń silnik jak wyżej.
2. Nowy moduł (np. `inc/animations-aos.php`) wpina się w filtr
   `cyber_section_attributes` i ustawia atrybuty biblioteki, tłumacząc klucze
   z bazy na jej nazwy — np. `from-bottom` → `data-aos="fade-up"`. **Kluczy
   w bazie nie zmieniaj** (CLAUDE.md sekcja 7).
3. Czas, opóźnienie i płynność czyta z `cyber_get_option( 'anim_duration' )`
   itd. albo ze zmiennych `--cyber-anim-*`.
4. Biblioteka wymaga zgody i wpisu w rejestrze bibliotek (CLAUDE.md sekcja 2).
5. Zaktualizuj ten rozdział i `docs/acf-schema.md`.

**Pełne usunięcie funkcji łącznie z danymi** to operacja na ACF: usunięcie pola
z `group_section_settings.json` i klonu slidera oraz zakładki z Global Options
wymaga też wpisów w schemacie, `default-acf.php` i dokumentacji. Pola nie
kasują treści sekcji — znikają tylko zapisane wybory animacji.

### Usunięty layout `basic`

Layout `basic` („Sekcja podstawowa”, WYSIWYG → pusty kontener → WYSIWYG)
usunięto 2026-09-24 razem z plikiem i hookiem `cyber_section_basic_body`.
Klucza `basic` **nie wolno** użyć ponownie dla innego layoutu.


### Sekcja `cards` — Karty (icon boxes)

Wspólny szkielet sekcji (WYSIWYG → kontener → WYSIWYG), ze środkowym
kontenerem wypełnionym siatką powtarzalnych elementów.

| Plik | Rola |
|---|---|
| `inc/sections-cards.php` | walidacja ustawień siatki, normalizacja elementów repeatera |
| `template-parts/sections/cards.php` | widok |
| `acf-json/group_section_cards.json` | pola: repeater + ustawienia |

Podział odpowiedzialności: opakowanie sekcji, oba pola WYSIWYG i ustawienia tła
obsługuje `inc/sections.php`. Moduł kart zajmuje się **wyłącznie** siatką —
dzięki temu zmiana w opakowaniu nie wymaga dotykania żadnej sekcji.

| Klasa | Skąd |
|---|---|
| `.cyber-cards` | kontener siatki; liczba kolumn i odstępy ze zmiennych |
| `.cyber-cards--hover` | dokładana tylko przy ustawionym tle po najechaniu — bez niej karta nie niesie `transition`, którego nigdy nie użyje |
| `.cyber-cards--shadow` / `--border` | włączniki; barwy z zakładki Kolory, wartości na sztywno w arkuszu |
| `.cyber-cards--image-bg` | tryb „zdjęcie jako tło": brak `<img>`, adres idzie zmienną `--cyber-card-image` na karcie |
| `.cyber-cards--{media,title,text}-{left,center,right}` | wyrównanie jako modyfikator klasy |
| `.cyber-card` | pojedynczy element; kolumna flex, żeby stopka dosuwała się do dołu |
| `.cyber-card__footer` | `margin-top: auto` — przyciski wszystkich kart w wierszu stoją w jednej linii |

Przycisk renderuje wspólny komponent `cyber_button()`, więc karty nie mają
własnego markupu przycisku ani własnych pól jego wyglądu.

Treść nad i pod siatką ma **osobne włączniki** (`cyber_cards_show_top`,
`cyber_cards_show_bottom`), stojące w panelu bezpośrednio nad swoim edytorem.
Na froncie sprawdza je `cyber_cards_shows_wysiwyg()`, w panelu edytor chowa
wspólny filtr `cyber_section_wysiwyg_condition()` — warunek nie może siedzieć w JSON-ie,
bo edytory są wspólne dla wszystkich sekcji.
Oba są **domyślnie wyłączone**; brak wartości liczy się jako wyłączony,
zgodnie z `default_value` pola w ACF.


### Sekcja `columns` — Kolumny tekstowe (WYSIWYG)

Opakowanie i ustawienia sekcji wspólne z innymi layoutami, ale **bez WYSIWYG
nad i pod treścią** — kolumny same są polami WYSIWYG, więc dodatkowe pola
treści byłyby powtórzeniem. Nagłówek sekcji wpisuje się w pierwszej kolumnie
albo w polu WYSIWYG innej sekcji nad tą.

| Plik | Rola |
|---|---|
| `inc/sections-columns.php` | rejestr proporcji, walidacja liczby kolumn, zmienne siatki |
| `template-parts/sections/columns.php` | widok |
| `acf-json/group_section_columns.json` | pola: układ, 4 × WYSIWYG, ustawienia |

| Klasa | Skąd |
|---|---|
| `.cyber-columns` | kontener; ścieżki siatki ze zmiennej `--cyber-columns-template` |
| `.cyber-columns--{1..4}` | liczba kolumn, punkt zaczepienia dla stylów |
| `.cyber-columns--hover` / `--border` | jak w kartach |
| `.cyber-column` | pojedyncza kolumna |

**Proporcje są w `fr`, nie w procentach.** 40% + 60% plus odstęp przekracza
100% i wiersz wyjeżdża poza kontener; `fr` dzieli miejsce, które zostaje po
odjęciu odstępu, więc `2fr 3fr` daje dokładne 40/60 przy każdym `gap`.


### Sekcja `slider` — Slider

Karuzela na **Swiperze 14.2.0** (rejestr bibliotek: CLAUDE.md sekcja 2).

| Plik | Rola |
|---|---|
| `inc/sections-slider.php` | walidacja, konfiguracja karuzeli, zdjęcie jako `<picture>`, warunkowe assety |
| `template-parts/sections/slider.php` | widok; konfiguracja w atrybutach `data-*` |
| `assets/js/slider.js` | inicjalizacja Swipera — **moduł ES** |
| `assets/vendor/swiper-14.2.0/` | rdzeń + 5 modułów, bajt w bajt z npm |
| `acf-json/group_section_slider.json` | pola: ustawienia + repeater slajdów |

| Klasa | Skąd |
|---|---|
| `.cyber-slider` (+ `.swiper`) | kontener karuzeli; wysokość, dopasowanie i pozycja zdjęcia ze zmiennych |
| `.cyber-slider--section` / `--full` | tryb szerokości zdjęcia |
| `.cyber-slider--x-*` / `--y-*` | położenie treści — klasa, nie zmienna (sekcja 20) |
| `.cyber-slider--has-overlay` | tylko przy ustawionym kolorze nakładki |
| `.cyber-slider--has-pagination` | większy dolny odstęp treści, żeby przycisk nie wchodził na kropki |
| `.cyber-slide__media` / `__image` | `<picture>` z osobnym `<source>` dla telefonu |
| `.cyber-slide__body` / `__content` | warstwa treści nad zdjęciem |

**Zdjęcie jako `<picture>`, nie tło CSS.** Telefon pobiera wyłącznie swój kadr,
zdjęcie ma tekst alternatywny i `srcset`. Pierwszy slajd ładuje się z
`fetchpriority="high"`, kolejne z `loading="lazy"`.

Przycisk renderuje wspólny `cyber_button()` — wygląd z zakładki Przyciski.


### Sekcja `faq` — FAQ

Pytania rozwijane po kliknięciu, na natywnym `<details>` — bez biblioteki
i bez JavaScriptu.

| Plik | Rola |
|---|---|
| `inc/sections-faq.php` | normalizacja pytań, podział na kolumny, konfiguracja, klasy i zmienne, ikona z lewej, włącznik treści nad/pod (`cyber_faq_shows_wysiwyg()`) |
| `template-parts/sections/faq.php` | widok |
| `acf-json/group_section_faq.json` | pola: repeater pytań i ustawienia |

| Klasa | Skąd |
|---|---|
| `.cyber-faq` | kontener pytań; zmienne `--cyber-faq-*` w atrybucie `style` |
| `.cyber-faq--cols-1` / `--cols-2` | liczba kolumn; dwie to dwie niezależne listy `.cyber-faq__col` |
| `.cyber-faq--divider` | linia między pytaniami |
| `.cyber-faq--has-icon` / `--has-toggle` | włączona ikona z lewej / plus-minus z prawej |
| `.cyber-faq--open-bg` / `--hover` | ustawione tło otwartego pytania / kolor po najechaniu |
| `.cyber-faq__item` | `<details>`; z `name` w trybie jednej otwartej odpowiedzi |
| `.cyber-faq__summary` | `<summary>` — ikona, pytanie, plus/minus |
| `.cyber-faq__icon` | ikona z lewej: `<img>` z panelu albo `.cyber-icon--question` |
| `.cyber-faq__toggle` | plus/minus rysowany w CSS; kolor `--cyber-color-icons`, do nadpisania tą klasą |
| `.cyber-faq__answer` | odpowiedź; niesie też `.cyber-wysiwyg` |

### Włączniki treści nad i pod sekcją — wspólne

Sekcje z włącznikami (`cards`, `carousel`, `faq`, `counter`, `contact`, `posts`, `table`, `gallery`) nie mają własnych
filtrów. Mapa „klucz klonu edytora → klucz włącznika” jest jedna:
`cyber_section_wysiwyg_toggles()` w `inc/sections.php`, a warunek nakłada
`cyber_section_wysiwyg_condition()`. Na froncie sprawdza je
`cyber_section_shows_wysiwyg( $row, 'cyber_[sekcja]_show', $slot )`.
**Nowa sekcja z włącznikami dopisuje dwa wiersze do mapy** zamiast kopiować filtr.

### Sekcja `gallery` — Galeria

| Klasa | Skąd |
|---|---|
| `.cyber-gallery` | kontener; zmienne `--cyber-gallery-*` w `style` |
| `--ratio-1-1` / `-4-3` / `-3-2` / `-16-9` | proporcje kadru; brak klasy = zdjęcia naturalne |
| `--zoom` / `--lightbox` | powiększenie po najechaniu / kliknięcie otwiera lightbox |
| `.cyber-gallery__filters` / `__filter` | pasek kategorii; aktywna ma `.is-active` i `aria-pressed` |
| `.cyber-gallery__grid` / `__item` / `__link` / `__image` / `__caption` | siatka i zdjęcie (link do pliku w pełnym rozmiarze) |
| `.cyber-lightbox` | `<dialog>` budowany przez skrypt przy pierwszym otwarciu |
| `.cyber-gallery-page` | strona pojedynczej galerii (CPT) |

### Sekcja `posts` — Wpisy

Karty wpisów albo innego publicznego typu treści. **Bez własnego wyglądu**:
siatka to klasy `.cyber-cards`, slider to `template-parts/components/carousel.php`,
element to `template-parts/components/card.php`.

| Klasa | Skąd |
|---|---|
| `.cyber-section--posts` | opakowanie; otwierane bez kontenera, jak karuzela (tryb „full”) |
| `.cyber-cards` / `.cyber-carousel` | siatka albo slider — te same klasy co w sekcjach Karty i Karuzela |

**Rozmiar zdjęcia: `medium`** (od 2026-09-25, stała `CYBER_POSTS_IMAGE_SIZE`
w `inc/sections-posts.php`). Wbudowany rozmiar WordPressa: maks. 300 × 300 px,
**proporcjonalny, bez przycinania** — to nie jest miniatura `thumbnail`
(kwadrat 150 × 150). Dotyczy także trybu „zdjęcie jako tło”. `srcset`
z `wp_get_attachment_image()` zostaje, więc na ekranie o wysokiej gęstości
pikseli przeglądarka może wziąć większy wariant tego samego pliku.

Zmiana jest **testem** mniejszego rozmiaru i dotyczy wyłącznie sekcji Wpisy —
blog i widget „Ostatnie wpisy” zostają przy `large`. Powrót: wartość stałej
na `large`. Wymiary `medium` ustawia się w **Ustawienia → Media**; zmiana
dotyczy nowych uploadów (starsze wymagają ponownego wygenerowania miniatur).

### Komponent karty — klucze opcjonalne

`template-parts/components/card.php` przyjmuje dodatkowo (karty wpisów):

| Klucz | Efekt |
|---|---|
| `meta` | `<p class="cyber-card__meta">` nad tytułem (data · kategoria) |
| `title_url` | tytuł `.cyber-card__title-link` i zdjęcie `.cyber-card__media-link` prowadzą do wpisu (zdjęcie poza tabulacją) |
| `title_tag` | `h2` / `h3` / `h4`; domyślnie `h3` |
| `image_size` | `medium` / `medium_large` / `large`; domyślnie `large`. Wartość spoza listy (także `thumbnail`) wraca do `large` |

Proporcje zdjęcia: klasy `.cyber-cards--ratio-16-9` / `-3-2` / `-4-3` / `-1-1`
na kontenerze (pole `cyber_cards_image_ratio`).

### Sekcja `table` — Tabela

| Klasa | Skąd |
|---|---|
| `.cyber-table` | kontener: tło, zaokrąglenie, przewijanie poziome; `role="region"` |
| `.cyber-table--left` / `--center` / `--right` | wyrównanie |
| `.cyber-table--stripes` | pasy co drugi wiersz |
| `.cyber-table__table` | `<table>`, `table-layout: fixed` |
| `.cyber-table__label` | `<th scope="row">` — etykieta wiersza |
| `.cyber-table__cell` + `--left` / `--center` / `--right` | komórka; modyfikator z wyrównania kolumny (repeater „Kolumny”) |

### Czcionki

| Plik | Rola |
|---|---|
| `inc/fonts.php` | rejestr rodzin (`cyber_font_families()`), opcje w panelu, warunkowy arkusz |
| `assets/css/fonts.css` | deklaracje `@font-face` — ładowany tylko przy czcionce z motywu |
| `assets/fonts/` | pliki `woff2` (po jednym zmiennym na podzbiór) i `README.md` z licencjami |

### Page header

Pasek z tytułem strony nad treścią, wypisywany z `header.php` nad okruszkami.

| Plik | Rola |
|---|---|
| `inc/page-header.php` | widoczność (globalna + wyjątek strony), dane, zmienne CSS, assety |
| `template-parts/page-header/page-header.php` | widok |
| `assets/css/page-header.css` | wygląd — ładowany tylko tam, gdzie page header się pokazuje |
| `acf-json/group_page_header.json` | wyjątki pojedynczej strony |

| Klasa | Skąd |
|---|---|
| `.cyber-page-header` | opakowanie; zmienne `--cyber-ph-*` w `style` (wysokość, tło, nakładka) |
| `--full` / `--container` | szerokość: cały ekran albo szerokość strony |
| `--left` / `--center` / `--right` | wyrównanie treści |
| `--auto` | wysokość `0` — pasek tak wysoki jak treść |
| `.cyber-page-header__video` | wideo tła (bez dźwięku, w pętli; ukrywane przy ograniczeniu animacji) |
| `.cyber-page-header__overlay` | nakładka nad tłem |
| `.cyber-page-header__title` / `__excerpt` | `<h1>` strony i zajawka |
| `.cyber-breadcrumb--in-page-header` | okruszki wewnątrz page headera (stan obok wariantu `--default` / `--wc`) |

**Breadcrumb w page headerze** (pole `cyber_pageheader_breadcrumb`). Kolejność
wewnątrz page headera: tytuł → zajawka (jeśli włączona) → okruszki. Osobny pasek
pod page headerem wtedy się nie pojawia — `header.php` pyta
`cyber_page_header_has_breadcrumb()`.

| Funkcja | Plik | Rola |
|---|---|---|
| `cyber_breadcrumb_render( $data, $page_header )` | `inc/breadcrumb.php` | Jedno miejsce wypisania okruszków: wybiera widok (zwykły albo WooCommerce); `$page_header = true` — bez kontenera `.cyber-container` (szerokość trzyma page header) i z klasą `--in-page-header` |
| `cyber_page_header_has_breadcrumb()` | `inc/page-header.php` | Czy okruszki stoją w page headerze — wtedy `header.php` pomija osobny pasek |

Okruszki w page headerze: bez górnego i dolnego odstępu paska i bez dolnej
kreski; kolor zajawki, bieżąca strona w kolorze tytułu, rozmiar czcionki
z zakładki Breadcrumb. Wyrównanie idzie za wyrównaniem treści page headera
(`justify-content`, bo lista okruszków jest flexem).

### Przycisk do góry

Kwadrat ze strzałką przy dolnej krawędzi ekranu. Pojawia się po przewinięciu
strony o zadaną liczbę pikseli i przewija ją z powrotem na górę. Ustawienia:
Global Options → Przycisk do góry. **Domyślnie wyłączony.**

| Plik | Rola |
|---|---|
| `inc/go-to-top.php` | dane dla widoku, wypisanie na hooku `wp_footer` (priorytet 5), warunkowe assety |
| `template-parts/go-to-top/go-to-top.php` | widok — `<button>` z ikoną `arrow-up` |
| `assets/css/go-to-top.css` | wygląd, położenie, pokazywanie i ukrywanie |
| `assets/js/go-to-top.js` | próg przewinięcia, powrót na górę, fokus po użyciu klawiatury |

| Funkcja | Plik | Rola |
|---|---|---|
| `cyber_go_to_top_data()` | `inc/go-to-top.php` | Klasy, próg, etykieta i ikona albo `null` przy wyłączonym przycisku |
| `cyber_go_to_top()` | `inc/go-to-top.php` | `wp_footer` — wypisuje widok |
| `cyber_go_to_top_assets()` | `inc/go-to-top.php` | Arkusz i skrypt tylko przy włączonym przycisku |
| `cyber_go_to_top_css_map()` / `cyber_go_to_top_css()` | `inc/enqueue.php` | Zmienne `--cyber-totop-*` w `<style>` w `wp_head` |

| Klasa / atrybut | Skąd | Rola |
|---|---|---|
| `.cyber-totop` `.cyber-totop--default` | `cyber_variant_class()` | Wrapper z klasą wariantu (CLAUDE.md sekcja 20) |
| `.cyber-totop--left` / `--center` / `--right` | pole Położenie | Modyfikator położenia |
| `.cyber-totop--hide-mobile` | pole „Pokazuj na telefonie” wyłączone | **Stan**, nie wariant — ukrycie poniżej 767px |
| `.is-visible` | `go-to-top.js` | Przycisk pokazany (przewinięto dalej niż próg) |
| `data-cyber-totop` | `cyber_go_to_top_data()` | Próg przewinięcia w pikselach |

**Decyzje:**

- **`<button>`, nie `<a href="#">`** — to akcja na stronie, nie nawigacja.
  Dostępna nazwa: `aria-label` („Przewin do gory strony”); strzałka ma
  `aria-hidden`.
- **Ukryty przez `visibility`, nie samo `opacity`** — niewidoczny przycisk
  wypada z kolejki Tab i nie przechwytuje kliknięć.
- **Bez JavaScriptu przycisk się nie pojawia.** Pokazuje go dopiero skrypt po
  przewinięciu, więc na stronie nie zostaje martwy element.
- **Fokus po użyciu klawiatury** przechodzi na skip-link na górze strony.
  Inaczej zostałby na przycisku, który za chwilę znika. Przy kliknięciu myszą
  fokus się nie przenosi, żeby skip-link nie mignął na ekranie.
- **Przewijanie płynne**, a przy systemowym ograniczeniu ruchu — natychmiastowe.
  Pokazywanie i ukrywanie bez przejść przy `prefers-reduced-motion`.
- **Zdarzenie `scroll`** jest pasywne i przeliczane najwyżej raz na klatkę
  (`requestAnimationFrame`).
- **`z-index: 20`** — nad treścią, pod headerem (30) i jego panelem mobilnym.
- **Wypisywany na `wp_footer`, nie w `footer.php`** — działa w każdym widoku
  (strony, blog, sklep), a cały moduł mieszka w swoich plikach.

### Blog — widoki i pasek boczny

| Plik | Rola |
|---|---|
| `inc/blog.php` | obszar widgetów, hierarchia szablonów, ustawienia z Global Options → Blog, CSS, assety |
| `inc/class-cyber-recent-posts-widget.php` | widget „Cyber: Ostatnie wpisy” — karty ze zdjęciem, datą i przyciskiem |
| `templates/blog.php` | lista: strona wpisów, kategorie, tagi, archiwa dat i autorów |
| `templates/single-post.php` | pojedynczy wpis (tylko typ `post`) |
| `template-parts/blog/sidebar.php` | pasek boczny — `dynamic_sidebar( 'cyber-blog' )` |
| `assets/css/blog.css` | układ, wpis, pasek boczny, paginacja — tylko na widokach bloga |

| Klasa | Skąd |
|---|---|
| `.cyber-blog` + `--sidebar` / `--full` | układ treść + pasek boczny (albo pełna szerokość, gdy pasek pusty lub wyłączony) |
| `.cyber-blog__main` / `__sidebar` | kolumny |
| `.cyber-blog__list` | siatka kart listy (klasy `.cyber-cards`) |
| `.cyber-post__image` / `__title` / `__meta` / `__content` | pojedynczy wpis |
| `.cyber-blog-widget` / `__title` | opakowanie widgetu w pasku bocznym |
| `.cyber-pagination` | paginacja listy (`the_posts_pagination()`) |

### Sekcja `contact` — Kontakt

| Plik | Rola |
|---|---|
| `inc/sections-contact.php` | `cyber_contact_section_data()` — dane z Global Options według włączników; konfiguracja, klasy, zmienne |
| `inc/contact-form-7.php` | `cyber_cf7_form_html()`, `cyber_cf7_missing_hint()`, ostrzeżenie w panelu |
| `template-parts/sections/contact.php` | widok |
| `template-parts/components/social-icons.php` | wspólny komponent ikon, tu z nazwami platform (`show_labels`) |

| Klasa | Skąd |
|---|---|
| `.cyber-contact` | siatka dwóch kolumn; `--cyber-contact-cols` z proporcji |
| `.cyber-contact--btn-large` / `-medium` / `-small`, `--btn-full` | wygląd przycisku wysyłki formularza |
| `.cyber-contact__col--info` / `--form` | lewa / prawa kolumna |
| `.cyber-contact__data` | `<dl>` danych; `.cyber-contact__item--email` … `--regon` |
| `.cyber-contact__social` | nagłówek i lista social media |
| `.cyber-social-icons--labels` | wariant komponentu ikon z nazwami |
| `.cyber-contact__form` | opakowanie markupu Contact Form 7 |
| `.cyber-form-row` | dwa pola formularza w jednym rzędzie — klasa do użycia w szablonie CF7 |

### Sekcja `counter` — Licznik

| Plik | Rola |
|---|---|
| `inc/sections-counter.php` | normalizacja liczb, `cyber_counter_format()`, konfiguracja, klasy i zmienne, enqueue skryptu |
| `template-parts/sections/counter.php` | widok |
| `assets/js/counter.js` | odliczanie; ładowany tylko przy animowanym liczniku |
| `acf-json/group_section_counter.json` | pola |

| Klasa | Skąd |
|---|---|
| `.cyber-counter` | siatka; zmienne `--cyber-counter-*` w `style`; `data-counter-*` przy animacji |
| `.cyber-counter--left` / `--center` / `--right` | wyrównanie |
| `.cyber-counter--divider` | linia pionowa (`::before` elementu, przycinana przez `overflow: hidden` kontenera) |
| `.cyber-counter__item` | jeden licznik |
| `.cyber-counter__icon` | opcjonalna ikona nad liczbą |
| `.cyber-counter__value` | wiersz liczby: `__prefix`, `__number`, `__suffix` (`aria-hidden`) + `.screen-reader-text` |
| `.cyber-counter__title` | tytuł pod liczbą (`<h3>`), rozmiar z `--cyber-counter-title-size` |
| `.cyber-counter__label` | opis pod liczbą |

### Sekcja `global` — Sekcja globalna

Wstawia sekcje z wpisu typu **Sekcje globalne**. Nie ma własnego markupu:
`cyber_render_sections()` podmienia wiersz na sekcje wybranego wpisu
i renderuje je ich własnymi szablonami, przez `cyber_render_section_row()`.

```
Strona: [faq] [global → wpis #12] [cards]
                     ↓
Na froncie: [faq] [carousel z #12] [table z #12] [cards]
```

| Funkcja | Rola |
|---|---|
| `cyber_global_section_rows( $id )` | sekcje opublikowanego wpisu, bez wierszy `global` (brak zagnieżdżania), z pamięcią na czas żądania |
| `cyber_section_rows_expanded( $post_id )` | płaska lista sekcji strony z rozwiniętymi sekcjami globalnymi — do decyzji o assetach |
| `cyber_global_section_problem( $id )` | powód, dla którego nic się nie wyświetla (szkic, kosz, brak sekcji) |
| `cyber_global_section_usage( $id )` | wpisy, które wstawiają sekcję — kolumna „Używana na” |
| `cyber_section_unique_id( $id )` | unikalna kotwica przy wielokrotnym wstawieniu |
| `cyber_section_row_enabled( $row )` | czy wiersz jest włączony wyłącznikiem (`inc/sections.php`) — **jedno** źródło dla renderowania i dla decyzji o assetach |

> **Wyłącznik sekcji musi działać na obu ścieżkach naraz.** Wiersz sekcji
> przechodzi przez dwie niezależne ścieżki: renderowanie
> (`cyber_render_sections()`) i decyzję o assetach
> (`cyber_section_rows_expanded()`). Warunek „czy włączony" był kiedyś wpisany
> w czterech miejscach i ścieżki się rozjechały — renderowanie pomijało
> wyłączony wiersz, a decyzja o assetach nie. Odstawiony slider nie pokazywał
> się na stronie, ale nadal kolejkował Swipera (~30 kB gzip). Objaw nie był
> widoczny w treści, tylko w zakładce Sieć.
>
> Dlatego predykat jest **jeden**: `cyber_section_row_enabled()`. Brak klucza
> `cyber_section_enabled` znaczy „włączony" — wiersze zapisane przed dodaniem
> wyłącznika go nie mają i nie wolno ich wyciąć.
>
> Filtrowanie wierszy wewnętrznych siedzi w `cyber_section_rows_expanded()`,
> a **nie** w `cyber_global_section_rows()`. Ta ostatnia ma trzeciego
> konsumenta — `cyber_global_section_problem()` — który z pustej listy
> wnioskuje „sekcja globalna nie ma jeszcze żadnej sekcji". Filtrowanie
> u źródła kazałoby mu pokazać ten komunikat sekcji, która sekcje ma, tylko
> wyłączone.

| Klasa | Skąd |
|---|---|
| `.cyber-section-missing` | podpowiedź dla redaktora; wygląd wspólny z `.cyber-wc-missing` |

### Sekcja `carousel` — Karuzela kart

Karty z sekcji `cards` w przewijanym rzędzie, na tym samym Swiperze co Slider.

| Plik | Rola |
|---|---|
| `inc/sections-carousel.php` | konfiguracja przewijania, klasy kontenera, warunek edytorów w panelu |
| `template-parts/sections/carousel.php` | widok |
| `template-parts/components/card.php` | **wspólny** z sekcją `cards` markup karty |
| `assets/js/slider.js` | inicjalizacja — ten sam skrypt co Slider (`initCarousel`) |
| `acf-json/group_section_carousel.json` | pola: wyłącznie ustawienia przewijania |

| Klasa | Skąd |
|---|---|
| `.cyber-carousel` (+ `.swiper`) | kontener; nosi **również** klasy siatki kart `.cyber-cards--*`, które dają kartom wygląd — sam układ siatki arkusz wyłącza |
| `.cyber-carousel--section` / `--full` | tryb szerokości |
| `.cyber-carousel-wrap` | opakowanie karuzeli i paska sterowania |
| `.cyber-carousel__slide` | slajd; `height:auto`, żeby karty w rzędzie miały równą wysokość |
| `.cyber-carousel__controls` | pasek pod rzędem: strzałka, kropki, strzałka |
| `.cyber-card__overtitle` | nadtytuł karty; razem z globalną `.cyber-overtitle` (wygląd z Global Options) |
| `.cyber-slide__video` | wideo w tle slajdu, nad `<picture>`; gra tylko na aktywnym slajdzie |
| `.cyber-wysiwyg` | każde opakowanie treści edytora (sekcje, kolumny, slajd, treść strony); odstępy nagłówków z `--cyber-wysiwyg-heading-mt` / `-mb` w `main.css` |
| `.cyber-carousel--continuous` | tryb ciągły — taśma z animacją CSS, Swiper się nie uruchamia |
| `.cyber-carousel__band` | taśma: zestaw kart wypisany dwa razy, animowany o `-50%` |
| `.cyber-carousel__slide--copy` | kopia karty w taśmie: `aria-hidden`, linki z `tabindex="-1"` |

**Jedna definicja karty w dwóch sekcjach.** Pola karty są klonowane
z `group_section_cards`, normalizację robi `cyber_cards_items()`, wygląd
`cyber_cards_attributes()`, a markup komponent `components/card.php`.

## Warstwa WooCommerce

Koszyk, zamówienie, lista produktów i strona produktu **nie mają
template-partów** i nie pojawiają się w tabeli wyżej — to nie są komponenty
motywu, tylko widoki WooCommerce, którym motyw nadaje wygląd z zewnątrz.
Ta sekcja istnieje po to, żeby mapa komponentów o nich wiedziała.

Pełny opis mechanizmów, pułapek i odstępstw: `docs/architecture.md`, sekcje
„Strona koszyka WooCommerce", „Strona zamówienia (checkout)" i „Strona
pojedynczego produktu".

### Pliki

| Plik | Rola |
|---|---|
| `inc/woocommerce.php` | warstwa ochronna miękkiej zależności: wykrywanie, komunikaty, dane konta i koszyka, warunkowe zdejmowanie arkuszy wtyczki |
| `inc/woocommerce-cart.php` | wygląd koszyka: hooki, etykiety, warunkowe assety |
| `inc/woocommerce-checkout.php` | wygląd zamówienia: kolejność pól, kupon, licznik ilości, endpoint AJAX |
| `inc/woocommerce-shop.php` | lista produktów: układ, widgety, pasek narzędzi, doładowywanie |
| `assets/css/woocommerce-cart.css` | style koszyka — ładowany **tylko** na `is_cart()` |
| `assets/css/woocommerce-checkout.css` | style zamówienia — ładowany **tylko** na `is_checkout()` |
| `assets/js/cart.js` | licznik ilości w koszyku, automatyczne przeliczanie |
| `assets/js/checkout.js` | licznik ilości i kupon na stronie zamówienia |
| `assets/css/woocommerce-shop.css` | style listy produktów — ładowany **tylko** na sklepie i archiwach produktów |
| `assets/js/shop.js` | przełącznik widoku siatka/lista i doładowywanie produktów |
| `inc/woocommerce-product.php` | strona produktu: układ dwukolumnowy, własna galeria, rejestr elementów, zakładki |
| `assets/css/woocommerce-product.css` | style strony produktu — ładowany **tylko** na `is_product()` |
| `assets/js/product.js` | przeciąganie galerii i licznik ilości na stronie produktu |

### Obszary widgetów

Jedyne obszary widgetów w całym motywie. Rejestruje je `inc/woocommerce-shop.php`.

| Obszar | Gdzie widoczny |
|---|---|
| `cyber-shop-sidebar` | kolumna boczna — sklep **i** kategorie |
| `cyber-shop-top` | pasek nad listą — tylko sklep |
| `cyber-category-top` | pasek nad listą — tylko archiwa taksonomii |

Markup widgetu: `.cyber-widget` z tytułem `.cyber-widget__title` (`<h2>`).

Żaden z tych assetów nie ma prawa załadować się poza swoją stroną
(CLAUDE.md sekcja 10) — sprawdzone: `/koszyk/` ładuje wyłącznie arkusz koszyka,
`/zamowienie/` wyłącznie arkusz zamówienia, `/sklep/` żadnego.

### Nadpisania szablonów

Dwa, oba zatwierdzone jawnie przed implementacją (CLAUDE.md sekcja 2).
Rejestr z wersjami prowadzi `CLAUDE.md`; uzasadnienia i zakres zmian —
`docs/architecture.md`.

| Plik | Wersja | Dlaczego nie hookiem ani CSS-em |
|---|---|---|
| `woocommerce/checkout/review-order.php` | `@version 11.0.0` | W `<thead>` nie ma punktu zaczepienia, więc nagłówka trzeciej kolumny nie da się dołożyć |
| `woocommerce/cart/cart-shipping.php` | `@version 8.8.0` | `colspan` jest atrybutem i **nie ma odpowiednika wśród właściwości CSS** |

### Własne elementy w tych widokach

Nie są to komponenty reużywalne — istnieją wyłącznie wewnątrz widoków sklepu.

| Klasa | Gdzie | Skąd markup |
|---|---|---|
| `.cyber-qty` | koszyk i zamówienie | koszyk: `assets/js/cart.js`; zamówienie: filtr `woocommerce_checkout_cart_item_quantity` |
| `.cyber-coupon` | zamówienie | `cyber_wc_checkout_coupon_box()` — **celowo bez `<form>`**, bo leży wewnątrz formularza zamówienia |
| `.cyber-wc-heading` | koszyk | hook `woocommerce_before_cart_table` |
| `.cyber-wc-checkout-button` | koszyk | hook `woocommerce_proceed_to_checkout`, klasy `btn btn-large` |
| `.cyber-wc-place-order` | zamówienie | filtr `woocommerce_order_button_html`, klasy `btn btn-large` |
| `.cyber-shop__view` | lista produktów | `cyber_shop_toolbar_controls()`; aktywny stan niesie `aria-pressed`, bez osobnej klasy |
| `.cyber-shop__per-page` | lista produktów | formularz GET z polami ukrytymi, żeby nie kasować sortowania |
| `.cyber-shop__more-button` | lista produktów | `cyber_shop_load_more()`, klasy `btn btn-large`; kontekst archiwum w `data-*` |
| `.cyber-product__excerpt` | lista produktów | hook `woocommerce_after_shop_loop_item_title`; w siatce ukryty CSS-em |
| `.cyber-product__media` | strona produktu | `cyber_product_gallery()` — zdjęcie główne plus pionowy pasek miniatur |
| `.cyber-product__thumb` | strona produktu | miniatura jako `<button>`; aktywny stan niesie `aria-pressed` i klasa `is-active` |
| `.cyber-product__sku` | strona produktu | `cyber_product_sku()` — SKU wyjęte z bloku `product_meta`, żeby miało własną pozycję |
| `.cyber-product__stock` | strona produktu | `cyber_product_stock()`; wersję wbudowaną gasi filtr `woocommerce_get_stock_html` |
| `.cyber-qty__button` | strona produktu | hooki `woocommerce_before/after_quantity_input_field` — bez nadpisania `quantity-input.php` |

Oba przyciski używają rozmiarów z zakładki **Przyciski** i nie mają własnych pól
wyglądu — tak samo jak przycisk CTA w headerze.

### Strona produktu — rejestr elementów

Prawa kolumna strony produktu i sekcja pod nią są **budowane od zera**: moduł
zdejmuje wszystkie domyślne callbacki hooków
`woocommerce_single_product_summary` i `woocommerce_after_single_product_summary`,
a potem dopina z powrotem tylko te włączone w panelu, na pozycjach z panelu.

Jedno źródło prawdy to `cyber_product_elements()` w `inc/helpers.php`. Z niego
powstają: pola ACF (wyłącznik + pozycja), wpisy w `cyber_option_schema()`
i podpięcie hooków.

| Element | Hook | Callback |
|---|---|---|
| `title` | `woocommerce_single_product_summary` | `woocommerce_template_single_title` |
| `sku` | `woocommerce_single_product_summary` | `cyber_product_sku()` |
| `rating` | `woocommerce_single_product_summary` | `woocommerce_template_single_rating` |
| `excerpt` | `woocommerce_single_product_summary` | `woocommerce_template_single_excerpt` |
| `price` | `woocommerce_single_product_summary` | `woocommerce_template_single_price` |
| `stock` | `woocommerce_single_product_summary` | `cyber_product_stock()` |
| `cart` | `woocommerce_single_product_summary` | `woocommerce_template_single_add_to_cart` |
| `meta` | `woocommerce_single_product_summary` | `cyber_product_meta()` |
| `tabs` | `woocommerce_after_single_product_summary` | `woocommerce_output_product_data_tabs` |
| `upsells` | `woocommerce_after_single_product_summary` | `woocommerce_upsell_display` |
| `related` | `woocommerce_after_single_product_summary` | `woocommerce_output_related_products` |

Elementy `quantity` i `sale` nie mają wiersza w tej tabeli — nie renderują się
własnym hookiem, więc mają w panelu sam wyłącznik.

**Dołożenie kolejnego elementu:** wpis w `cyber_product_elements()`, wpis
w `cyber_product_element_callbacks()`, dwa pola w `acf-json/`. Nic więcej.

**`WC_Structured_Data::generate_product_data()` (priorytet 60) zostaje na
miejscu** — dane strukturalne nie są elementem wyglądu i moduł ich nie rusza.

### Strona produktu — punkty rozszerzenia

| Punkt | Co nim zrobisz |
|---|---|
| filtr `cyber_product_tabs` | dołożenie zakładki (np. z pola ACF) albo przywrócenie wbudowanej; drugi argument to **pełny, oryginalny zestaw WooCommerce**, więc powrót „Informacji dodatkowych” to jedna linia |
| `cyber_woocommerce_css_map()` | kolejna barwa sklepu — jedna linia mapy + jeden wpis w schemacie, zero zmian w CSS |
| `cyber_product_elements()` | kolejny element z wyłącznikiem i pozycją |

```php
add_filter( 'cyber_product_tabs', function ( $tabs, $all ) {
	$tabs['additional_information'] = $all['additional_information'];

	return $tabs;
}, 10, 2 );
```

## Zasady

1. Jeden layout ACF = jeden plik w `template-parts/sections/`.
2. Komponent nie sięga po stan globalny — dane dostaje jawnie przez `$args`
   w `get_template_part()` (CLAUDE.md sekcja 4).
3. Assety specyficzne dla komponentu kolejkowane są warunkowo, przy jego renderowaniu,
   nie globalnie (CLAUDE.md sekcja 10).
4. Escaping wykonywany bezpośrednio przy outpucie w pliku widoku.
