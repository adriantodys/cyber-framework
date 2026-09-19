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
| Breadcrumb | `template-parts/breadcrumb/breadcrumb.php` | `context`, `items` | sekcja „Breadcrumb” w `assets/css/main.css` + zmienne z `cyber_breadcrumb_css()` |
| Breadcrumb WooCommerce | `template-parts/breadcrumb/breadcrumb-woocommerce.php` | `context` | ta sama sekcja CSS; ścieżkę wypisuje `woocommerce_breadcrumb()` we własnych znacznikach motywu |
| Header — slot akcji: CTA | `template-parts/header/actions-cta.php` | `cta` | `.cyber-header__actions` w `assets/css/main.css`; przycisk z `cyber_button()` w rozmiarze `medium` |
| Header — slot akcji: WooCommerce | `template-parts/header/actions-woocommerce.php` | `wc` | sekcja „Header: konto i koszyk WooCommerce” w `assets/css/main.css`; ikony `user` i `cart` z `cyber_icons()` |
| Header — licznik koszyka | `template-parts/header/cart-count.php` | `count` | `.cyber-wc-count` w `assets/css/main.css` |
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
plik sekcji z osobna — zmiana struktury w dwunastu plikach naraz to gwarancja
rozjazdu.

### Pliki

| Plik | Rola |
|---|---|
| `inc/sections.php` | rejestr, walidacja wartości, budowa opakowania, renderer, assety |
| `template-parts/sections/basic.php` | layout `basic` — WYSIWYG → kontener → WYSIWYG |
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
| `basic` | Sekcja podstawowa | `basic` | `page`, `post` |
| `cards` | Karty (icon boxes) | `cards` | `page`, `post` |
| `columns` | Kolumny tekstowe (WYSIWYG) | `columns` | `page`, `post` |
| `slider` | Slider | `slider` | `page`, `post` |
| `carousel` | Karuzela kart | `carousel` | `page`, `post` |
| `faq` | FAQ (pytania i odpowiedzi) | `faq` | `page`, `post` |
| `counter` | Licznik (counter) | `counter` | `page`, `post` |
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

### Punkt rozszerzenia

Layout `basic` ma w środku hook `cyber_section_basic_body` — dołożenie
zawartości nie wymaga przepisywania pliku szablonu.


### Sekcja `cards` — Karty (icon boxes)

Ten sam szkielet co `basic`, ze środkowym kontenerem wypełnionym siatką
powtarzalnych elementów.

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
bo edytory są wspólne z sekcją podstawową.
Oba są **domyślnie wyłączone**; brak wartości liczy się jako wyłączony,
zgodnie z `default_value` pola w ACF.


### Sekcja `columns` — Kolumny tekstowe (WYSIWYG)

Opakowanie i ustawienia sekcji jak w `basic`, ale **bez WYSIWYG nad i pod
treścią** — kolumny same są polami WYSIWYG, więc dodatkowe pola treści byłyby
powtórzeniem. Nagłówek sekcji wpisuje się w pierwszej kolumnie albo w osobnej
sekcji podstawowej nad tą.

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

Sekcje z włącznikami (`cards`, `carousel`, `faq`, `counter`) nie mają własnych
filtrów. Mapa „klucz klonu edytora → klucz włącznika” jest jedna:
`cyber_section_wysiwyg_toggles()` w `inc/sections.php`, a warunek nakłada
`cyber_section_wysiwyg_condition()`. Na froncie sprawdza je
`cyber_section_shows_wysiwyg( $row, 'cyber_[sekcja]_show', $slot )`.
**Nowa sekcja z włącznikami dopisuje dwa wiersze do mapy** zamiast kopiować filtr.

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
Strona: [basic] [global → wpis #12] [cards]
                       ↓
Na froncie: [basic] [carousel z #12] [basic z #12] [cards]
```

| Funkcja | Rola |
|---|---|
| `cyber_global_section_rows( $id )` | sekcje opublikowanego wpisu, bez wierszy `global` (brak zagnieżdżania), z pamięcią na czas żądania |
| `cyber_section_rows_expanded( $post_id )` | płaska lista sekcji strony z rozwiniętymi sekcjami globalnymi — do decyzji o assetach |
| `cyber_global_section_problem( $id )` | powód, dla którego nic się nie wyświetla (szkic, kosz, brak sekcji) |
| `cyber_global_section_usage( $id )` | wpisy, które wstawiają sekcję — kolumna „Używana na” |
| `cyber_section_unique_id( $id )` | unikalna kotwica przy wielokrotnym wstawieniu |

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
