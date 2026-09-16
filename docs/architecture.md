# Architektura — Cyber Framework

Ostatnia aktualizacja: 2026-09-15 (Global Options ma czternaście zakładek i 166 pól; poza etapami 1–3 z CLAUDE.md sekcja 17 istnieje pełna warstwa WooCommerce: okruszki, koszyk, zamówienie, lista produktów i strona produktu).

## Przepływ danych

```
ACF (Options Page / pola)
        │
        ▼
inc/helpers.php  ── cyber_get_option( 'klucz' )   ← walidacja + wartości domyślne + cache
        │
        ▼
templates/ , template-parts/   ← wyłącznie wyświetlanie, escaping przy outpucie
        │
        ▼
HTML  →  assets/css/
```

Kierunek jest jednokierunkowy. Widok nigdy nie sięga po `get_field()` / `get_option()`
dla danych globalnych i nigdy nie zawiera logiki biznesowej.

## Bootstrap

`functions.php` nie zawiera logiki — definiuje stałe i ładuje moduły z `inc/`
w ustalonej kolejności:

| # | Plik | Odpowiedzialność |
|---|---|---|
| 1 | `inc/helpers.php` | Schemat opcji, walidacja, `cyber_get_option()`, kanoniczne breakpointy `cyber_breakpoints()`. Musi być pierwszy — reszta może z niego korzystać. |
| 2 | `inc/acf.php` | Ścieżki Local JSON (save/load), ostrzeżenie o braku ACF PRO. Musi być przed ładowaniem pól przez ACF. |
| 3 | `inc/options.php` | `acf_add_options_page()` na hooku `acf/init`. |
| 4 | `inc/setup.php` | `add_theme_support()`, menu, rozmiary obrazków. |
| 5 | `inc/enqueue.php` | Rejestracja assetów, wersjonowanie przez `filemtime()`, inline CSS Custom Properties wszystkich modułów w `wp_head`. |
| 6 | `inc/editor.php` | Wyłączenie edytora blokowego (Gutenberg) dla wszystkich typów treści. |
| 7 | `inc/header.php` | Argumenty `wp_nav_menu()` i klasy podmenu dla modułów Header Desktop i Mobile, dane paska Top Header. Stała `CYBER_HEADER_MENU_LOCATION`. |
| 8 | `inc/footer.php` | Dane paska Copyright (`cyber_copyright_data()`). Odpowiednik `inc/header.php` po stronie stopki. |
| 9 | `inc/components.php` | Funkcje komponentów reużywalnych (`cyber_button()`): normalizacja i walidacja argumentów. |
| 10 | `inc/contact.php` | Walidacja pól kontaktowych przy zapisie w panelu. **Bez warstwy frontendowej** — patrz CLAUDE.md sekcja 22. |
| 11 | `inc/woocommerce.php` | Warstwa ochronna miękkiej zależności od WooCommerce: wykrywanie, komunikaty, dane konta i koszyka. |
| 12 | `inc/breadcrumb.php` | Ścieżka okruszków: rozstrzyga kontekst (sklep czy nie) i buduje ścieżkę poza sklepem. Ładowany **po** `inc/woocommerce.php`, bo z niego korzysta. |
| 13 | `inc/woocommerce-cart.php` | Wygląd strony koszyka: hooki, etykiety i warunkowe assety. Bez nadpisań szablonów. |
| 14 | `inc/woocommerce-checkout.php` | Wygląd strony zamówienia: kolejność pól, przeniesienie kuponu, etykiety, przycisk. |
| 15 | `inc/woocommerce-shop.php` | Lista produktów: układ dwukolumnowy, obszary widgetów, pasek narzędzi, doładowywanie. |
| 16 | `inc/woocommerce-product.php` | Strona pojedynczego produktu: układ dwukolumnowy, własna galeria, rejestr elementów z pozycjami, zakładki. Ładowany **po** `inc/woocommerce-shop.php`, bo zdejmuje jego opakowanie układu. |
| 17 | `inc/sections.php` | Sekcje Flexible Content: rejestr typów, walidacja wartości per instancja, budowa opakowania, renderer. |
| 18 | `inc/sections-cards.php` | Sekcja „Karty”: walidacja ustawień siatki i normalizacja elementów repeatera. Ładowany **po** `inc/sections.php`, bo korzysta z jego walidatorów. |

## Stałe

| Stała | Znaczenie |
|---|---|
| `CYBER_VERSION` | Wersja motywu (metadana; assety wersjonuje `filemtime()`). |
| `CYBER_DIR` | Ścieżka bezwzględna do katalogu motywu. |
| `CYBER_URI` | URL katalogu motywu. |
| `CYBER_OPTIONS_SLUG` | Slug Options Page (`cyber-settings`). Musi zgadzać się z `location` w `acf-json/group_global_options.json`. |

## Degradacja bez ACF PRO

ACF PRO jest twardą zależnością, ale motyw nie umiera bez niego:

- `inc/acf.php` pokazuje `notice-error` administratorowi (`activate_plugins`),
- `inc/options.php` nie robi nic, jeśli `acf_add_options_page()` nie istnieje,
- `cyber_get_option()` zwraca wartości domyślne ze schematu.

Efekt: strona się renderuje z wartościami domyślnymi, tracąc jedynie konfigurowalność.

## Global Options → CSS

Wartości wpływające na wygląd frontu nie trafiają do widoków jako inline style,
tylko jako **CSS Custom Properties** (CLAUDE.md sekcja 6, „Konwencja: ACF Options → CSS”):

```
ACF Options Page
      │
      ▼
cyber_get_option()            ← walidacja typu i zakresu (inc/helpers.php)
      │
      ▼
cyber_container_css()         ← moduł „Główne ustawienia strony”
cyber_font_css()              ← moduł „Ustawienia czcionki”
cyber_header_css()            ← moduł „Header Desktop”
cyber_header_mobile_css()     ← moduł „Header Mobile”
cyber_button_css()            ← moduł „Przyciski”
cyber_colors_css()            ← moduł „Kolory”
cyber_top_header_css()        ← moduł „Top Header”
cyber_footer_css()            ← moduł „Footer”
cyber_copyright_css()         ← moduł „Copyright”               (inc/enqueue.php)
      │
      ▼
cyber_print_inline_css()      ← jeden wspólny <style id="cyber-global-vars">
      │                         na wp_head, priorytet 20
      ▼
assets/css/main.css           ← jedyny konsument zmiennych
                                (.cyber-container, typografia, .cyber-header__inner,
                                 .cyber-menu, .cyber-submenu)
```

Wyjątek od tego przepływu: **wyrównanie**. Nie jest zmienną CSS, tylko modyfikatorem
klasy w markupie (CLAUDE.md sekcja 20), bo steruje układem, a nie pojedynczą wartością.

Priorytet 20 jest celowy: `wp_head` wypisuje arkusze stylów wcześniej
(`wp_enqueue_scripts` na 1, `wp_print_styles` na 8), więc wartości z panelu
nadpisują statyczne wartości domyślne z `main.css` przy tej samej specyficzności `:root`.

Konsekwencja praktyczna: żeby dodać kolejną opcję sterującą wyglądem, dopisuje się
ją do `cyber_option_schema()` oraz do funkcji budującej CSS dla danego obszaru,
a tę funkcję dokleja się w `cyber_print_inline_css()`. Nowy moduł **nie rejestruje
własnego hooka** — cały motyw wypisuje jeden blok `<style>`.

### Wspólna pętla: `cyber_css_vars_from_map()`

Moduły opisane mapą (Header, Footer, Copyright) nie mają własnej pętli po polach.
Wszystkie trzy sprowadzają się do jednej linii:

```php
function cyber_header_css() {
	return cyber_css_vars_from_map( cyber_header_css_map() );
}
```

Wcześniej każdy z nich niósł identyczną kopię tego samego `foreach` — łącznie
trzy takie same bloki, które przy kolejnym module rozmnożyłyby się na cztery.
Mapa zostaje osobna dla każdego modułu, bo nazwy zmiennych nie przekładają się
mechanicznie z nazw pól (`footer_bg_color` → `--cyber-footer-bg`).

Moduły, które **nie** korzystają z tego mechanizmu, robią to z powodu:
`cyber_font_css()` liczy skalowanie na breakpointach, `cyber_container_css()`
wybiera szerokość zależnie od typu, `cyber_header_mobile_css()` generuje cały
blok `@media`, a `cyber_colors_css()` tworzy nazwy zmiennych mechanicznie i mapy
w ogóle nie potrzebuje.

### Warianty komponentów

Każdy wrapper modułu niesie klasę bazową **oraz** modyfikator wariantu, budowane
przez `cyber_variant_class()` (`inc/components.php`):

```
cyber-header     cyber-header--default
cyber-topheader  cyber-topheader--default
cyber-footer     cyber-footer--default
cyber-copyright  cyber-copyright--default
```

Na razie istnieje wyłącznie wariant `default` i żadna reguła CSS go nie celuje.
Nie jest to martwy kod, tylko punkt zaczepienia wymagany przez CLAUDE.md
sekcja 20: dołożenie drugiego wariantu ma być nową klasą obok istniejącej,
a nie przepisywaniem selektora bazowego. Dodane teraz kosztuje jedną klasę;
dodane później oznacza rewizję każdego selektora zakładającego gołe
`.cyber-header`.

Wariant przechodzi przez `sanitize_html_class()`, bo trafia wprost do atrybutu
`class`; pusta lub niepoprawna wartość wraca do `default` zamiast wygenerować
klasę-śmiecia.

**Stany są prostopadłe do wariantów.** Przyklejony header to nie kolejna wartość
modyfikatora, tylko druga, niezależna klasa obok niego:

```html
<header class="cyber-header cyber-header--default cyber-header--sticky">
```

Dzięki temu przyszły wariant układu (logo na środku, menu rozbite na dwie strony)
łączy się z przyklejaniem bez kombinatoryki `--centered-sticky`.

### Breakpointy

`cyber_breakpoints()` w `inc/helpers.php` jest jedynym źródłem progów
(CLAUDE.md sekcja 18) i używają go oba moduły. Dodanie progu w jednym miejscu
zmienia zachowanie całego motywu naraz — o to chodzi.

**Znana rozbieżność nazewnicza:** pola marginesów powstały przed konwencją z sekcji 18
i mają końcówki `_mobile_l` / `_mobile_s` zamiast kanonicznych `_mobile` / `_mobile_small`
(pola czcionek są już zgodne). Mapowanie nazw siedzi w `cyber_container_css()`.
Zmiana nazw pól ACF oznacza utratę zapisanych wartości w `wp_options`, więc wymaga
świadomej migracji — nie jest robiona przy okazji.

### Skalowanie wartości responsywnych

Moduł czcionek realizuje wariant B (CLAUDE.md sekcja 19): pola trzymają wartości
wyłącznie dla desktopu, a `cyber_font_css()` mnoży je w PHP przez skalę procentową
danego breakpointu i wypisuje gotowe liczby w px. W CSS nie ma `calc()` — przeglądarka
dostaje policzone wartości, a panel nie puchnie od pól per element × breakpoint.

## Moduły bez frontu: Kontakt i Social Media

Dwa moduły **celowo nie mają warstwy widoku**: żadnych zmiennych CSS, markupu ani
wpisu w `cyber_print_inline_css()` (CLAUDE.md sekcja 22).

**Social Media** nie ma nawet pliku w `inc/` — 6 pól typu URL w całości obsługuje
istniejący typ schematu `url` (`esc_url_raw()`), ten sam, którego używa logo nagłówka.
Nie było czego dokładać.

**Kontakt** ma `inc/contact.php` z wyłączną walidacją przy zapisie w panelu.

Walidacja działa w dwóch warstwach, ale wzorce żyją raz — w `cyber_contact_patterns()`
(`inc/helpers.php`). Warstwa panelu (`acf/validate_value/name=…`) blokuje zapis
i tłumaczy redaktorowi, co jest nie tak; warstwa odczytu (typ `text` z kluczem
`pattern`) odrzuca wartość, która mimo wszystko trafiłaby do bazy. Gdyby te dwie
listy się rozjechały, redaktor zapisywałby wartość, której motyw nie przyjmuje,
i nie zobaczyłby żadnego komunikatu.

Wszystkie pola są tekstowe, nie liczbowe — typ Number zjadłby zera wiodące w REGON
i znak `+` w numerze telefonu.

## Kolory

Moduł realizuje dwa wzorce naraz (CLAUDE.md sekcja 5):

| Wzorzec | Gdzie ląduje w CSS | Kiedy działa |
|---|---|---|
| Semantyczny | dopisany do **istniejących** reguł `h1`–`h6`, `p/span/ul/li`, `a`, `.cyber-overtitle*` | od razu po zapisaniu pola |
| Narzędziowy | samodzielne klasy `.cyber-hover-color`, `.cyber-border-1`, `.cyber-border-2`, `.cyber-shadow`, `.cyber-shadow-hover` | dopiero po ręcznym dodaniu klasy do elementu |

Kolor linków jest selektorem bazowym `a` o specyficzności `(0,0,1)` i **nie ma
żadnych wykluczeń**. Linki menu (`.cyber-menu a`) i przyciski (`.btn-*`) wygrywają
kaskadą same z siebie. Konstrukcja `a:not(…)` byłaby krucha — każdy nowy komponent
z własnym kolorem linku wymagałby dopisania kolejnego wyjątku.

Pola cieni mają włączoną przezroczystość, więc zwracają `rgba()`. Obsługuje je
osobny typ walidacji `color_alpha`, który przepuszcza HEX albo `rgb()`/`rgba()`
o ścisłym wzorcu — `sanitize_hex_color()` odrzuciłby `rgba()`, a cień bez kanału
alfa jest wizualnie bezużyteczny.

## Copyright

Pasek pod stopką, **strukturalnie tożsamy z Top Header** — ten sam układ kontener /
inner / dwie strony i ta sama zasada „pusty pasek się nie renderuje"
(CLAUDE.md sekcja 16a). Logika mieszka w `inc/footer.php`, odpowiedniku
`inc/header.php` po stronie stopki.

Dwie rzeczy specyficzne dla tego modułu:

- **Pola typu Page Link.** Redaktor wybiera istniejącą stronę, więc adres podąża
  za nią przy zmianie slugu. Page Link nie przechowuje tytułu, dlatego etykiety
  („Polityka prywatności", „Polityka cookies") są stałe w widoku, a nie w ACF
  (CLAUDE.md sekcja 5a). Walidację pokrywa istniejący typ schematu `url`.
- **Znacznik `{year}`** w tekście copyright podmieniany na bieżący rok
  (`wp_date( 'Y' )`). Pole pozostaje statycznym tekstem — kto nie użyje znacznika,
  dostaje dokładnie to, co wpisał.

## Footer

Cztery kolumny w CSS grid, ale tylko pierwsza ma pola ACF. Kolumny 2 i 3 renderują
się jako puste `<div>` — bez tekstu zastępczego na froncie — a kolumna 4 czerpie
w całości z `cyber_social_*`.

Różnica wobec Top Header sprowadza się do jednego argumentu:

```
cyber_social_links( true )   → pasek: przełącznik ORAZ wypełniony adres
cyber_social_links( false )  → stopka: tylko wypełniony adres
```

Obie ścieżki prowadzą przez ten sam komponent `cyber_social_icons()`
(CLAUDE.md sekcja 22b), więc pętla po platformach istnieje w kodzie raz.
`cyber_top_header_data()` też z niej korzysta — klucz `social` służy tam wyłącznie
do decyzji, czy pasek ma się w ogóle renderować.

**Pasek Copyright renderuje się wewnątrz `<footer>`**, jako jego ostatnie dziecko.
Wcześniej był rodzeństwem stopki i przez to leżał **poza landmarkiem
`contentinfo`** — czytnik ekranu nie zaliczał go do stopki. Dane paska idą teraz
do widoku stopki jako `$args['copyright']` (albo `null`), a decyzja „czy jest co
pokazać” zostaje w `footer.php` w rootcie, tak samo jak przy Top Headerze.

Konsekwencja układowa: odstęp pionowy przeniósł się z `.cyber-footer` na wrapper
`.cyber-footer__main`. Gdyby został na `<footer>`, padding dolny wypadłby
**poniżej** paska Copyright i zostawił pod nim pas tła stopki.

Stopka ma własne tło (`cyber_footer_bg_color`) oraz trzy klasy narzędziowe
ograniczone do niej konwencją: `.cyber-footer-title`, `.cyber-footer-text`
i `.cyber-footer-link` (CLAUDE.md sekcja 5). Nazwy klas są stałe w kodzie —
ACF ustawia wyłącznie rozmiar i kolor.

Dwie z tych klas nie mają jeszcze zastosowania w markupie: stopka nie ma tytułów
kolumn ani linków tekstowych. Są zdefiniowane i gotowe pod zawartość kolumn 2 i 3,
a nie dopięte na siłę do pustych znaczników.

## Top Header

Pierwszy moduł, który **konsumuje dane innego modułu** zamiast definiować własne.
Telefon, email i sześć adresów profili czyta z zakładek „Kontakt" i „Social Media"
przez `cyber_get_option()`; własne ma tylko trzy pola stylu i osiem przełączników.

```
cyber_top_header_data()          ← koniunkcja: przełącznik ORAZ pole niepuste
      │
      ▼
cyber_top_header_has_content()   ← pusty pasek w ogóle się nie renderuje
      │
      ▼
template-parts/header/top-header.php   ← widok bez warunków biznesowych
      │
      ▼
cyber_get_icon() / _social_icon() ← własne inline SVG z cyber_icons(), currentColor
```

Dwie decyzje warte zapamiętania:

- **Koniunkcja w PHP, nie w conditional logic ACF.** Przełącznik i pole źródłowe
  to dwa niezależne stany. ACF potrafiłby ukryć pole w panelu, ale widoczność
  elementu na pasku jest decyzją widoku — te same pola będzie konsumować stopka,
  z własnym zestawem przełączników.
- **Ikony dziedziczą kolor tekstu** przez `fill="currentColor"`. Osobne pole
  koloru ikon pozwoliłoby rozjechać je z tekstem na tym samym pasku.

## Header Desktop

Pierwszy moduł z warstwą markupu, więc ustala wzorzec dla kolejnych:

```
header.php (root)                  ← zbiera dane przez cyber_get_option()
      │                              i przekazuje je jawnie jako $args
      ▼
template-parts/header/header.php   ← czysty widok, zero dostępu do stanu globalnego
      │
      ▼
wp_nav_menu( cyber_header_menu_args( $alignment ) )
      │
      ▼
filtr nav_menu_submenu_css_class   ← dokłada .cyber-submenu i modyfikator wyrównania
```

Dwie decyzje warte zapamiętania:

- **Bez własnego Walkera.** Podmenu potrzebowało tylko dwóch dodatkowych klas na `<ul>`,
  a to załatwia filtr rdzenia. Własna klasa `Walker_Nav_Menu` byłaby kilkudziesięcioma
  liniami kodu do utrzymania przy każdej zmianie w rdzeniu WordPressa.
- **Bez `fallback_cb`.** Gdy do lokalizacji `primary` nie przypisano żadnego menu,
  header nie renderuje nawigacji w ogóle — zamiast wyrzucać na front listę wszystkich
  stron witryny, co jest domyślnym zachowaniem WordPressa.

Rozwijanie podmenu działa na `:hover` **oraz** `:focus-within`, żeby było osiągalne
z klawiatury (CLAUDE.md sekcja 11). Na desktopie nie ma tu JavaScriptu.

**Przyklejony header** (`cyber_header_sticky`) to `position: sticky` nałożone klasą
`.cyber-header--sticky`, bez grama JavaScriptu i bez nasłuchu na scroll. Trzy rzeczy
warte zapamiętania:

- `top: var(--wp-admin--admin-bar--height, 0px)` — wysokość paska administratora
  podaje sam WordPress w `admin-bar.css` (32px, na wąskich ekranach 46px). Motyw
  nie wykrywa zalogowanego użytkownika i **nie dokłada własnego breakpointu**
  (CLAUDE.md sekcja 18 nietknięta). Dla wylogowanych arkusz się nie ładuje
  i obowiązuje fallback `0px`.
- `z-index: 30` podnosi cały header ponad treść. Podmenu (`10`) i panel mobilny
  (`20`) leżą wewnątrz niego i układają się w jego kontekście, więc nie wymagały
  podbicia.
- Panel mobilny jest pozycjonowany `absolute` względem `.cyber-header`, a `sticky`
  nadal tworzy kontekst pozycjonowania — panel jedzie razem z przyklejonym
  headerem, co jest zachowaniem pożądanym.

Razem ze sticky powstało pole `cyber_header_bg_color` (`--cyber-header-bg`). Header
wcześniej nie miał tła i przy przewijaniu treść byłaby widoczna pod przyklejonym
paskiem. Domyślne `#ffffff` nie zmienia niczego wizualnie — strona i tak stoi na
białym tle przeglądarki.

## Header Mobile

**Jeden markup obsługuje oba widoki.** Element `<nav>` z menu jest jednocześnie
panelem mobilnym — niesie `id` (cel `aria-controls` hamburgera), klasę wariantu
pozycji i próg dla JS w `data-breakpoint`. Drugie wywołanie `wp_nav_menu()`
dałoby ten sam zestaw linków po raz drugi, a razem z nim **zduplikowane
`id="menu-item-…"`** na każdej pozycji — czyli niepoprawny HTML i mylące cele
dla technologii asystujących.

| Element | Realizacja |
|---|---|
| Przełączenie widoków | `cyber_header_mobile_css()` — cały blok `@media` generowany w PHP, bo próg pochodzi z ACF |
| Otwieranie panelu | klasa `.is-open` dodawana przez `assets/js/header.js` — klik/tap, nie `:hover` |
| Wariant pozycji | `.cyber-mobile-menu--dropdown`; selektor bazowy celowo bez pozycjonowania (CLAUDE.md sekcja 20) |
| Skrypt | czysty JS, enqueue warunkowy (tylko gdy `primary` ma menu), wersja z `filemtime()` |

Skrypt nie zna żadnej wartości z panelu: próg i etykiety `aria-label` przychodzą
z markupu przez atrybuty `data-*`. Dzięki temu liczba żyje w jednym miejscu (ACF),
a teksty pozostają tłumaczalne w PHP.

Dostępność: hamburger to `<button>` z `aria-expanded` i `aria-controls`, panel
zamyka się na Escape (z powrotem fokusu na przycisk) i kliknięciem poza obszarem,
a zamknięty panel ma `display: none`, więc jego linki nie łapią fokusu.
Podmenu na mobile działa jako **akordeon**: pozycja z `menu-item-has-children` jest
domyślnie zwinięta, a kliknięcie rozwija wyłącznie jej własne podmenu. Skrypt blokuje
wtedy domyślną nawigację (`preventDefault`) i przełącza `.is-open` oraz `aria-expanded`
na tym konkretnym `<li>`. Powyżej progu atrybut `aria-expanded` jest **usuwany**, bo
na desktopie podmenu otwiera `:hover` / `:focus-within` i link przestaje być elementem
rozwijającym. Wskaźnik strzałki z modułu Header Desktop obraca się o 180° w stanie
rozwiniętym, zamiast dublować się osobną ikoną.

Blok `@media` neutralizuje przy tym desktopowe reguły `:hover` / `:focus-within` —
poniżej progu podmenu otwiera wyłącznie kliknięcie, bo na ekranie dotykowym hover
potrafi się „przykleić" po tapnięciu.

**Konsekwencja do zapamiętania:** na mobile link pozycji z podmenu **nie nawiguje** —
pierwszy klik rozwija, drugi zwija. Jeśli strona-rodzic ma być osiągalna z telefonu,
trzeba ją zdublować jako pierwszą pozycję wewnątrz jej własnego podmenu (standardowy
zabieg redakcyjny, robiony w `Wygląd → Menu`, bez zmian w kodzie).

## Warianty układu headera

Cztery warianty (`cyber_header_variant`) różnią się **wyłącznie** klasą na wrapperze
i obecnością slotu akcji. Żaden nie przepisuje komponentu:

```
default      logo | menu                       — bez slotu
centered     logo nad menu (kolumna)           — bez slotu
cta          logo | menu | przycisk CTA        — actions-cta.php
woocommerce  logo | menu | konto + koszyk      — actions-woocommerce.php
```

**Slot akcji** (`.cyber-header__actions`) to jedyny punkt rozbieżności markupu.
Każdy wariant wnosi własny template-part, więc dołożenie piątego wariantu oznacza
nowy plik obok istniejących plus reguły CSS — bez dotykania `header.php`.

Trzy decyzje warte zapamiętania:

- **Slot leży poza warunkiem `has_menu`.** Przycisk CTA i ikony sklepu mają sens
  także na witrynie, która nie ma jeszcze przypisanego menu.
- **`margin-right: auto` przeniosło się na `.cyber-header__brand`.** Wcześniej
  odpychaniem sterował `margin-left: auto` na hamburgerze, ale odkąd istnieje slot,
  hamburger nie jest ostatnim elementem i musiałby o to miejsce konkurować.
- **Na mobile kolejność zmienia `order`, nie DOM.** Slot przechodzi wizualnie przed
  hamburgera (`order: 2` / `order: 3` w bloku `cyber_header_mobile_css()`), ale
  w DOM hamburger nadal stoi przed `<nav>` — dzięki temu po otwarciu panelu Tab
  prowadzi z przycisku prosto do pozycji menu, a nie do ikon sklepu.

Wariant `centered` **nie** nadpisuje `cyber_header_menu_alignment` — zmienia tylko oś
układu. Pole, które w jednym wariancie przestaje działać, byłoby cichą magią; przy
tym wariancie wyrównanie ustawia się osobno na „Do środka”.

## WooCommerce — zależność miękka

WooCommerce jest zależnością **miękką**, inaczej niż ACF PRO. Motyw działa bez niego
w całości, a funkcje, które go wymagają, wyłączają się same. `inc/woocommerce.php`
jest **jedynym** miejscem, które o tym decyduje — kolejne moduły sklepowe pytają
stąd, zamiast wołać `class_exists( 'WooCommerce' )` u siebie. Rozsypanie tego warunku
po plikach kończy się tym, że po wyłączeniu wtyczki część miejsc milknie cicho,
a część głośno.

| Funkcja | Rola |
|---|---|
| `cyber_is_woocommerce_active()` | jedyne źródło prawdy o obecności wtyczki |
| `cyber_woocommerce_required_by()` | lista funkcji motywu, które **w bieżącej konfiguracji** wymagają WooCommerce |
| `cyber_woocommerce_missing_message()` | treść komunikatu (czysty tekst, escapuje miejsce wypisania); pusty string, gdy wtyczka jest aktywna **albo** żadna funkcja jej nie potrzebuje |
| `cyber_woocommerce_missing_notice()` | `notice-warning` w panelu |
| `cyber_woocommerce_missing_hint()` | podpowiedź na froncie, wyłącznie dla administratora |
| `cyber_header_woocommerce_data()` | adresy konta i koszyka plus licznik, albo `null` |
| `cyber_wc_cart_count_fragment()` | licznik koszyka aktualizowany AJAX-em WooCommerce |

**Komunikat ma trzy poziomy, świadomie:**

1. **Gość witryny nie widzi nic.** Header renderuje się normalnie, po prostu bez ikon.
   Brak wtyczki nie jest jego problemem i nie ma prawa wyciec na front.
2. **Zalogowany administrator** widzi w miejscu ikon krótką podpowiedź, żeby wiedzieć,
   *dlaczego* jest tam pusto, zamiast szukać błędu.
3. **W panelu** czeka pełne ostrzeżenie — ale tylko dla kogoś z `activate_plugins`
   i tylko wtedy, gdy jakaś funkcja faktycznie jest włączona. Sam brak WooCommerce
   na witrynie bez sklepu nie jest błędem i nie ma o czym informować.

**Punkt rozszerzenia na przyszłość:** filtr `cyber_woocommerce_required_by`. Kolejny
moduł sklepowy dopisuje się do niego jedną linią i dostaje komplet ostrzeżeń
(panel + front) bez własnego kodu:

```php
add_filter( 'cyber_woocommerce_required_by', function ( $features ) {
	if ( cyber_get_option( 'footer_show_cart' ) ) {
		$features[] = __( 'koszyk w stopce', 'cyber-framework' );
	}
	return $features;
} );
```

To pierwszy własny hook w tym motywie — do tej pory nie było ani jednego
`apply_filters()`. Powstał, bo rozbudowa o WooCommerce jest zaplanowana, a nie
„na wszelki wypadek” (CLAUDE.md sekcja 2).

**Licznik koszyka** korzysta z natywnego mechanizmu WooCommerce
(`woocommerce_add_to_cart_fragments`), więc aktualizuje się po dodaniu produktu bez
przeładowania strony i bez linijki własnego JavaScriptu. Markup mieszka w osobnym
template-part, bo trafia w dwa miejsca: do headera przy renderowaniu i do
WooCommerce jako fragment. Pusty koszyk dostaje klasę `--empty` zamiast znikać
z DOM — bez elementu WooCommerce nie miałby czego podmienić.

`cyber_wc_cart_count()` sprawdza koszyk **podwójnie**: `WC()` istnieje także wtedy,
gdy koszyk nie został jeszcze zainicjowany (REST, cron, część zadań w panelu),
a odwołanie do niezainicjowanego koszyka jest błędem krytycznym.

### Arkusze WooCommerce ładowane warunkowo

WooCommerce kolejkuje trzy arkusze — `woocommerce-general`, `woocommerce-layout`
i `woocommerce-smallscreen`, razem około **50 kB** — **bezwarunkowo, na każdej
podstronie witryny**, także tam, gdzie nie ma ani jednego elementu sklepu.
Motyw zdejmuje je poza widokami sklepu (`cyber_dequeue_woocommerce_styles()`).

**Dlaczego `wp_dequeue_style()`, a nie filtr `woocommerce_enqueue_styles`.**
Tamten filtr działa na etapie **rejestracji**, czyli zanim WordPress wie, jaką
stronę wyświetla — da się nim wyłączyć arkusze na stałe albo wcale. Decyzja
zależna od widoku musi zapaść później, stąd `wp_enqueue_scripts` na priorytecie
99, po kolejkowaniu wtyczki.

**`woocommerce-inline` zostaje.** Ten uchwyt nie ma źródła; jest wyłącznie
zaczepem dla stylów dopisywanych inline (m.in. pasek informacyjny sklepu, który
może pojawić się na dowolnej stronie). Nie kosztuje żadnego zapytania, więc nie
ma czego oszczędzać, a zdjęcie go zepsułoby pasek.

Arkusze zostają, gdy zachodzi którykolwiek warunek:

| Warunek | Obejmuje |
|---|---|
| `is_woocommerce()` | sklep, archiwa taksonomii produktów, strona produktu |
| `is_cart()` / `is_checkout()` / `is_account_page()` | strony na shortcode'ach — `is_woocommerce()` ich **nie** obejmuje |
| `is_wc_endpoint_url()` | końcówki konta (zamówienia, adresy, wylogowanie) |
| shortcode sklepu w treści wpisu | redaktor może wstawić `[products]` gdziekolwiek i nie ma obowiązku o tym uprzedzać |
| filtr `cyber_woocommerce_needs_styles` | furtka na przypadki niewidoczne w treści |

**Własny nagłówek z licznikiem koszyka nie jest powodem do ładowania tych
arkuszy** — jego markup, ikony i style są w całości motywu
(`template-parts/header/`, `assets/css/main.css`). Sprawdzone: strona główna
i pojedynczy wpis ładują wyłącznie `main.css`.

Przypadek, którego ta heurystyka **nie** złapie: widget WooCommerce w obszarze
widgetów strony spoza sklepu albo widok zbudowany własnym szablonem PHP. Od tego
jest filtr:

```php
add_filter( 'cyber_woocommerce_needs_styles', function ( $needed ) {
	return $needed || is_page( 'promocje' );
} );
```

### Główny kolor sklepu

`cyber_wc_color_main` (Global Options → WooCommerce) jest wspólną barwą
**wszystkich** widoków sklepu, nie tylko strony produktu. Trafia do CSS jako
`--cyber-wc-color-main` przez `cyber_woocommerce_css_map()`.

| Widok | Co bierze barwę |
|---|---|
| Lista produktów | cena, przycisk „Dodaj do koszyka”, przycisk „Pokaż więcej”, aktywny przełącznik widoku, bieżąca strona paginacji |
| Strona produktu | cena, przycisk zakupu, licznik ilości, aktywna zakładka, plakietka promocji, ramka wybranej miniatury |
| Koszyk | przycisk „Przejdź do zamówienia”, licznik ilości |
| Zamówienie | przycisk „Kupuję i płacę”, przycisk kuponu, licznik ilości |

**Przyciski sklepowe świadomie nie biorą barw z zakładki Przyciski.** Tamta
opisuje przyciski całej witryny; wezwanie do zakupu ma iść za kolorem sklepu,
inaczej zmiana barwy sklepu omijałaby najważniejszy element na stronie. Rozmiar,
padding i grubość czcionki nadal pochodzą z `btn-large`, więc zmiana rozmiaru
w panelu działa tak jak wszędzie.

**Czego barwa celowo nie dotyka:** kwot w tabelach koszyka i zamówienia. Te
zostają czarne, zgodnie z zatwierdzonym projektem tych dwóch widoków. Zmiana to
jedna linia w każdym z dwóch arkuszy, jeśli kiedyś ma być inaczej.

Kolejna barwa sklepu (np. osobny kolor ceny promocyjnej) to **jedna linia
w `cyber_woocommerce_css_map()` plus jeden wpis w `cyber_option_schema()`** —
bez dotykania plików CSS.

## Breadcrumb

Wąska belka pod headerem, nad treścią. **Dwa niezależne moduły dzielące jeden
markup** — nie jeden moduł z przełącznikiem.

```
cyber_breadcrumb_context()    ← 'wc' albo 'default'
      │
      ▼
cyber_breadcrumb_enabled()    ← osobny włącznik dla każdego kontekstu
      │
      ▼
cyber_breadcrumb_data()       ← null = nie ma czego pokazać
      │
      ├── 'default' ─→ cyber_breadcrumb_items() ─→ breadcrumb.php
      └── 'wc'      ─────────────────────────────→ breadcrumb-woocommerce.php
                                                     └─ woocommerce_breadcrumb()
```

**Dlaczego dwie zakładki.** Osobne włączniki pozwalają mieć okruszki wyłącznie
w sklepie albo wyłącznie poza nim. Jedna para pól wymuszałaby wszystko albo nic,
a sklep z głębokimi kategoriami produktów i strona firmowa o trzech podstronach
mają różne potrzeby. Wspólne zostały wyłącznie markup i jedna mapa zmiennych CSS
(`cyber_breadcrumb_css_map()`) — z punktu widzenia stylów to ten sam komponent
w dwóch wariantach, `.cyber-breadcrumb--default` i `.cyber-breadcrumb--wc`.

**Ścieżkę sklepową buduje WooCommerce**, bo tylko ono zna hierarchię kategorii
produktów. Motyw podstawia wyłącznie własne znaczniki przez argumenty
`wrap_before` / `before` / `after`, więc oba paski mają identyczny markup i jeden
blok CSS. `delimiter` jest pusty celowo — separator rysuje pseudoelement, tak samo
jak w pasku Copyright; znak wpisany w treść byłby czytany przez czytniki ekranu.

**Warunek „strona sklepu” jest szerszy niż `is_woocommerce()`** — ta funkcja nie
obejmuje koszyka, zamówienia ani konta klienta, a dla odwiedzającego to również
sklep. Koniunkcja zaczyna się od `cyber_is_woocommerce_active()`, bo wywołanie
`is_woocommerce()` przy nieaktywnej wtyczce byłoby błędem krytycznym.

**Pułapka przy testowaniu:** WooCommerce gatuje `is_cart()` i `is_checkout()`
warunkiem `did_action( 'wp' )` (`CartCheckoutUtils::is_page_type()`). W skrypcie
CLI ten hook nigdy nie pada, więc obie funkcje zwracają `false` niezależnie od
zapytania. Weryfikacja wymaga prawdziwego żądania HTTP — najprościej po klasach
`<body>`, bo WooCommerce dokłada tam `woocommerce-cart`, `woocommerce-account`
i `woocommerce-shop` dokładnie wtedy, gdy odpowiedni warunek jest prawdziwy.

**Pasek renderuje się wewnątrz `<main>`**, zaraz po jego otwarciu. To nawigacja
kontekstowa tej konkretnej strony, a nie nawigacja globalna witryny — ta mieszka
w headerze.

**Dwa przypadki, w których pasek nie powstaje mimo włączonego przełącznika:**
strona główna (ścieżka z jednego okruszka nie niesie informacji) oraz zapytanie,
którego żadna gałąź `cyber_breadcrumb_items()` nie rozpoznaje — wtedy zostałaby
sama „Strona glowna” linkująca donikąd poza samą sobą. Ta sama zasada „pusty
pasek się nie renderuje” co w Top Header i Copyright.

Zakres ścieżki poza sklepem jest świadomie podstawowy: strony z pełną hierarchią
rodziców, wpisy, archiwa, wyszukiwanie i 404. Ścieżka wpisu przez kategorię
(Strona główna > Kategoria > Wpis) to jedna dodatkowa gałąź w
`cyber_breadcrumb_items()`, do zrobienia wtedy, gdy blog faktycznie powstanie.

## Strona koszyka WooCommerce

Koszyk używa **klasycznego shortcode'u** `[woocommerce_cart]`, nie bloku
(uzasadnienie: CLAUDE.md sekcja 2). Cały wygląd powstaje z hooków i CSS —
**zero nadpisań szablonów**, więc aktualizacja WooCommerce niczego tu nie psuje
i nie pojawia się ostrzeżenie „template is out of date" w WooCommerce → Status.

### Co skąd się bierze

| Element projektu | Mechanizm |
|---|---|
| Układ dwukolumnowy | CSS grid na `div.woocommerce` — `form.woocommerce-cart-form` i `div.cart-collaterals` są jego **rodzeństwem**, więc wystarczył grid na rodzicu |
| Nagłówek „Produkty w koszyku” | `woocommerce_before_cart_table` → `cyber_wc_cart_products_heading()` |
| Nagłówek „Podsumowanie koszyka” | filtr `gettext`: `Cart totals` |
| Nagłówki tabeli (Produkt / Cena / Ilość / **Razem**) | trzy pierwsze są już w tłumaczeniu WooCommerce; `Subtotal` → „Razem” filtrem |
| Jeden wiersz „Wartość zamówienia” | filtr `gettext`: `Total`; pozostałe wiersze ukryte CSS-em |
| Przycisk `btn-large` | `remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 )` + własny callback |
| Brak produktów powiązanych | `remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' )` |
| Licznik ilości z − i + | `assets/js/cart.js` — progresywne ulepszenie |

### Trzy decyzje warte zapamiętania

**Filtr `gettext` jest zawężony do `is_cart()`.** Ciągi „Total" i „Subtotal"
występują w WooCommerce w dziesiątkach miejsc; globalna podmiana rozjechałaby
zamówienia, maile i panel. Porównujemy ciąg **źródłowy**, nie przetłumaczony,
więc filtr działa niezależnie od wgranego tłumaczenia.

**`remove_action()` musi odpalić po starcie wtyczki.** Domyślne callbacki
rejestrują się w `includes/wc-template-hooks.php` przy ładowaniu WooCommerce,
dlatego przestawienie hooków wisi na akcji `wp`, a nie wykonuje się przy
ładowaniu pliku motywu.

**WooCommerce narzuca własny układ na floatach — trzeba go neutralizować.**
`woocommerce-layout.css` ustawia `.cart-collaterals .cart_totals { float: right;
width: 48% }`. To wartość sprzed ery grida, policzona pod dwie kolumny na
floatach; w naszej siatce zwężała podsumowanie do połowy prawej kolumny.
Neutralizujemy ją regułą o **identycznym selektorze** — ta sama specyficzność,
wygrywamy kolejnością wczytania, bo arkusz koszyka zależy od `cyber-main`
i ładuje się po arkuszach wtyczki.

To nie jest przypadek jednostkowy: `woocommerce-layout.css` zawiera cały zestaw
procentowych szerokości i floatów (`.related` 30.75%, `.cross-sells` 48%,
kolumny formularza zamówienia). **Przy każdym kolejnym widoku sklepu trzeba
sprawdzić, co ten arkusz narzuca**, zanim uzna się układ za gotowy — samo
napisanie grida nie wystarczy.

**Ukryty przycisk „Zaktualizuj koszyk" nadal działa.** `display: none` nie usuwa
pól z formularza ani nie blokuje kliknięcia z poziomu JS — `assets/js/cart.js`
klika go po zmianie ilości, a nonce `woocommerce-cart-nonce` (leżący w tej samej,
ukrytej komórce `td.actions`) nadal jest wysyłany. Bez tego skryptu zmiana
ilości nie robiłaby nic.

### Świadome odstępstwa od projektu graficznego

| Rzecz | Decyzja |
|---|---|
| Tytuł strony | Zostaje `<h1>`, dostaje tylko **rozmiar** `h2`. Zmiana znacznika złamałaby zasadę jednego `<h1>` na stronę (CLAUDE.md sekcja 11) |
| Nagłówki kolumn | `<h2>` w rozmiarze `h3` — `<h3>` po `<h1>` przeskakiwałby poziom |
| Kolumna usuwania pozycji | Ukryta, bo projekt jej nie pokazuje. **Konsekwencja: pozycję usuwa się ustawiając ilość na 0.** Przywrócenie to jedna linia CSS |
| Pole kuponu | Ukryte — projekt go nie przewiduje |

### Czego tu nie ma

`add_theme_support( 'woocommerce' )` **nie jest zadeklarowane** i koszyk go nie
potrzebuje: strona koszyka to zwykła strona z shortcode'em, renderowana przez
`the_content()`, a nie przez szablony WooCommerce. Deklaracja stanie się
potrzebna dopiero przy stronach sklepu i produktu — i wtedy będzie osobną
decyzją, bo zmienia sposób renderowania wszystkich widoków sklepowych.

## Strona zamówienia (checkout)

Jak koszyk: klasyczny shortcode `[woocommerce_checkout]`, układ z hooków i CSS.
W odróżnieniu od koszyka **jest tu jedno nadpisanie szablonu** — opisane niżej.

### Układ

Trzy elementy są rodzeństwem wewnątrz `form.checkout`, więc siatka na formularzu
wystarcza, żeby ustawić je 50/50:

```
form.checkout                  grid: 1fr 1fr
  ├ #customer_details          kolumna 1, oba wiersze
  │   ├ .col-1  → dane rozliczeniowe
  │   └ .col-2  → uwagi do zamówienia
  ├ #order_review_heading      kolumna 2, wiersz 1
  └ #order_review              kolumna 2, wiersz 2
        ├ tabela podsumowania
        ├ kupon      ← przeniesiony hookiem
        └ #payment   ← metody płatności + przycisk
```

### Co skąd się bierze

| Element projektu | Mechanizm |
|---|---|
| Kolejność pól (kraj przed ulicą, kod przed miastem) | filtr `woocommerce_checkout_fields`, klucz `priority` |
| Etykiety „Ulica”, „Miasto”, „Numer telefonu” | ten sam filtr, klucz `label` |
| Kupon między tabelą a płatnościami | `remove_action` z `woocommerce_before_checkout_form` + **własny blok** na `woocommerce_checkout_order_review` z priorytetem 15 (tabela to 10, płatności 20) — patrz niżej |
| „Kwota zamówienia”, „Łącznie” | filtr `gettext`, zawężony do `is_checkout()` |
| **„Dostawa”** | filtr `woocommerce_shipping_package_name` — **nie** `gettext` |
| Przycisk `btn-large` „Kupuję i płacę” | filtr `woocommerce_order_button_html` |
| Licznik ilości − / + | filtr `woocommerce_checkout_cart_item_quantity` + endpoint AJAX + `assets/js/checkout.js` |

### Pułapka: w tej pozycji kupon nie może być formularzem

Docelowe miejsce kuponu — między tabelą a płatnościami — leży **wewnątrz**
`<form class="checkout">`. Szablon WooCommerce opakowuje kupon we własny
`<form>`, a HTML zabrania zagnieżdżania formularzy: przeglądarka usuwa
wewnętrzny znacznik i zostawia jego dzieci.

Serwer wysyła wtedy poprawny markup, a w DOM nie ma po nim śladu. Skutki są trzy:

1. `style="display:none"` znika razem z wrapperem → pola widoczne od razu.
2. Skrypt wtyczki przełącza `.checkout_coupon`, którego już nie ma → odnośnik
   nic nie robi.
3. **Przycisk kuponu (`type="submit"`) należy wtedy do formularza zamówienia** —
   kliknięcie próbuje złożyć zamówienie zamiast dodać kupon.

Punkt 3 jest groźny i nie widać go „na oko": strona wygląda tylko na
niedokończoną wizualnie.

**Rozwiązanie:** własny blok (`cyber_wc_checkout_coupon_box()`), celowo **bez**
`<form>`, z oboma przyciskami jako `type="button"`. Samo dodanie kuponu wykonuje
`assets/js/checkout.js`, wołając natywny endpoint `wc-ajax=apply_coupon` wraz
z nonce `apply_coupon_nonce` z `wc_checkout_params`. Nie dublujemy logiki
wtyczki — tylko jej interfejs. Odpowiedzią jest gotowy komunikat WooCommerce,
który trafia do `.cyber-coupon__message`.

Pole kodu **nie ma atrybutu `name`**: leży wewnątrz formularza zamówienia, więc
nazwane trafiałoby do danych składanego zamówienia bez potrzeby.

Pola są ukrywane atrybutem `hidden`, nie klasą — działa bez CSS, a JS przełącza
jedną właściwość zamiast zarządzać klasami. Przełącznik jest `<button>`, nie
`<a href="#">`: to sterowanie widokiem, nie odnośnik.

> **Reguła na resztę sklepu:** zanim przeniesiesz cokolwiek od WooCommerce
> w nowe miejsce, sprawdź, czy przenoszony fragment nie jest formularzem i czy
> cel nie leży wewnątrz innego. Ta sama pułapka czeka przy logowaniu w kasie
> i przy formularzach adresowych.

### Pułapka: `_x()` nie przechodzi przez `gettext`

Etykieta wiersza dostawy opierała się mapie etykiet i zostawała jako „Przesyłka”.
Powód: WooCommerce buduje ją przez `_x( 'Shipping', 'shipping packages', … )`,
a funkcje z kontekstem idą hookiem **`gettext_with_context`**, nie `gettext`.
Zamiast dokładać drugi filtr tłumaczeń użyliśmy dedykowanego
`woocommerce_shipping_package_name`.

**Wniosek na przyszłość:** jeśli etykieta nie reaguje na filtr `gettext`,
sprawdź w źródle, czy nie pochodzi z `_x()` — i poszukaj dedykowanego filtra,
zanim dołożysz `gettext_with_context`.

### Nadpisania szablonów w projekcie

Dwa, oba zatwierdzone jawnie przed implementacją (CLAUDE.md sekcja 2).

#### `woocommerce/cart/cart-shipping.php` — `@version 8.8.0`

**Powód:** tabela podsumowania ma trzy kolumny, a wiersz dostawy miał dwie
komórki i żadnego `colspan`. Lista metod kończyła się przez to w połowie
szerokości tabeli.

**Dlaczego nie CSS-em.** `colspan` jest atrybutem i **nie ma odpowiednika wśród
właściwości CSS**. Pierwsze podejście — `display: block; width: 100%` na
komórkach — nie działało i warto wiedzieć dlaczego: przeglądarka owija blokowe
dziecko wiersza tabeli w **anonimową komórkę**, więc element nadal siedzi
w jednej kolumnie, a `width: 100%` odnosi się do tej komórki, nie do tabeli.

**Zakres zmian:** wiersz rozbity na **dwa**, każdy z jedną komórką
`colspan="3"` — etykieta nad listą metod, oba na pełną szerokość.

> Dwie komórki po `colspan="3"` w **jednym** wierszu nie zadziałałyby: `th`
> zająłby kolumny 1–3, a `td` zaczął się od kolumny 4, więc tabela urosłaby do
> sześciu kolumn. Stąd podział na dwa wiersze, a nie samo dodanie atrybutu.

Wszystkie 6 hooków i filtrów oryginału zachowanych. Liczba `3` jest związana
z liczbą kolumn ustaloną w `review-order.php` — zmiana tam wymaga poprawienia
jej tutaj.

#### `woocommerce/checkout/review-order.php` — `@version 11.0.0`

`woocommerce/checkout/review-order.php`, skopiowany z WooCommerce 11.1.0,
**`@version 11.0.0`**.

**Powód:** projekt wymaga tabeli o trzech kolumnach (Produkt / Ilość / Kwota).
Szablon rdzenia ma dwie, a ilość wypisuje jako `<strong>` **wewnątrz** komórki
produktu. Nagłówka trzeciej kolumny nie da się dołożyć żadnym hookiem — w
`<thead>` nie ma punktu zaczepienia. Decyzja podjęta jawnie przed implementacją
(CLAUDE.md sekcja 2).

**Zakres zmian wobec oryginału — świadomie minimalny:**

1. `<thead>`: dodana komórka `<th class="product-quantity">`.
2. `<tbody>`: ilość przeniesiona do własnej komórki. Filtr
   `woocommerce_checkout_cart_item_quantity` **zachowany**, żeby wtyczki trzecie
   nadal mogły go używać.
3. `<tfoot>`: `colspan="2"` na komórkach etykiet, żeby wiersze podsumowania
   zgadzały się z nową liczbą kolumn także bez naszego CSS.

Wszystkie **12 hooków i filtrów** oryginału jest zachowanych — sprawdzone
porównaniem z plikiem rdzenia.

> **Przy aktualizacji WooCommerce** sprawdź, czy rdzeń podbił `@version` tego
> pliku. Jeśli tak — porównaj zmiany i przenieś je do kopii w motywie.
> WooCommerce → Status pokaże ostrzeżenie „template is out of date”, dopóki tego
> nie zrobisz. Nagłówek `@version` w kopii **musi zostać nietknięty**, inaczej ta
> kontrola przestanie działać.

Wiersz dostawy pochodzi z `cart/cart-shipping.php`, którego **nie**
nadpisujemy — dlatego jego układ (etykieta nad listą metod) załatwia CSS,
a nie szablon.

### Edytowalna ilość w podsumowaniu

Licznik − / + przy każdej pozycji. To **nie jest samo stylowanie** — zmiana
ilości musi przeliczyć sumy, więc moduł ma własny endpoint AJAX.

```
klik − / +
   │
   ▼
POST admin-ajax.php  action=cyber_checkout_qty  (nonce + klucz pozycji + ilość)
   │
   ▼
cyber_wc_checkout_update_quantity()   ← walidacja: pozycja, limit zakupu, stan magazynu
   │  WC()->cart->set_quantity( $key, $qty, true )
   ▼
jQuery( document.body ).trigger( 'update_checkout' )
   │
   ▼
WooCommerce przerenderowuje cały #order_review — z nową ilością i sumami
```

**Trzy rzeczy warte zapamiętania:**

- **Delegacja zdarzeń jest wymogiem, nie stylem.** Po `update_checkout` tabela
  wraz z przyciskami jest w DOM nowym elementem, więc nasłuch podpięty
  bezpośrednio do przycisku przestałby istnieć po pierwszej zmianie.
- **jQuery jest tu konieczne** mimo zasady z CLAUDE.md sekcja 2. WooCommerce
  nasłuchuje `update_checkout` przez jQuery, a zdarzenie natywne tam nie dotrze.
  Nie dokłada to nowej zależności — wtyczka i tak ładuje jQuery na tej stronie.
  Poza tym jednym wywołaniem skrypt jest czystym JS.
- **Dolna granica to 1, nie 0.** Zejście do zera usunęłoby pozycję w trakcie
  składania zamówienia, a przy ostatniej opróżniłoby koszyk i wyrzuciło klienta
  ze strony. Usuwanie pozycji należy do koszyka. Granica jest pilnowana
  **w dwóch miejscach** — w skrypcie i w endpoincie — bo pierwsze można ominąć.

Zabezpieczenia endpointu opisuje `docs/security.md`; to pierwszy i na razie
jedyny endpoint AJAX w motywie.

### Świadome odstępstwa od projektu graficznego

| Rzecz | Decyzja |
|---|---|
| Nagłówki kolumn | WooCommerce wypisuje je jako `<h3>` na sztywno w `form-billing.php` i `form-checkout.php`. Po `<h1>` to **przeskoczenie poziomu** (CLAUDE.md sekcja 11). Naprawa wymagałaby dwóch kolejnych nadpisań, więc zostawione — do decyzji |
| Sekcja „Wysłać na inny adres?” | Ukryta CSS-em; zamówienie idzie na adres rozliczeniowy. To **decyzja biznesowa**, nie kosmetyczna |
| Pole „Nazwa firmy” | **Nie pokazuje się**, bo `woocommerce_checkout_company_field` = `hidden` w ustawieniach sklepu. To ustawienie WooCommerce, nie motywu |
| Tło pól formularza | Przezroczyste z ramką z `--cyber-color-border-1`. Projekt ma jasnoszare wypełnienie — wymagałoby nowego pola albo koloru wpisanego na sztywno |
| „Rodzaj dokumentu sprzedaży” | Pole ze strony referencyjnej, **nie zamówione** — nie dodane |

## Lista produktów: sklep i kategorie

Dotyczy strony sklepu **oraz wszystkich archiwów taksonomii produktów** —
kategorii, tagów i atrybutów. Jeden moduł, jeden wygląd; nie ma osobnego kodu
dla kategorii.

`add_theme_support( 'woocommerce' )` jest wreszcie zadeklarowane
(`inc/setup.php`) — bez tego WooCommerce traktuje motyw jako niewspierany
i owija widoki sklepu własnymi znacznikami. **Nie wymagało to żadnego
nadpisania:** układ wchodzi przez hooki, a `archive-product.php` zostaje
nietknięty.

### Układ

```
woocommerce_before_main_content  →  cyber_shop_wrapper_open()
   <div class="cyber-container">
     <div class="cyber-shop cyber-shop--grid">     ← klasa widoku
       <aside class="cyber-shop__sidebar">         ← widgety
       <div class="cyber-shop__main">
          tytuł  →  opis  →  pasek widgetów  →  pasek narzędzi  →  produkty
woocommerce_after_main_content   →  cyber_shop_wrapper_close()
```

Kontener jest w hooku, a nie w szablonie, bo `archive-product.php` renderuje się
bezpośrednio w `<main>` z `header.php` — nie przechodzi przez `index.php`, więc
nie dostaje `.cyber-container` po drodze.

Klasę widoku buduje `cyber_variant_class()` — ten sam mechanizm co w headerze,
stopce i breadcrumbie (CLAUDE.md sekcja 20).

### Trzy obszary widgetów

| Obszar | Gdzie widoczny |
|---|---|
| `cyber-shop-sidebar` | kolumna boczna, **wspólna** dla sklepu i kategorii |
| `cyber-shop-top` | pasek nad listą, **tylko** strona sklepu |
| `cyber-category-top` | pasek nad listą, **tylko** archiwa taksonomii |

Kolumna boczna jest jedna, bo zwykle trafia tam to samo drzewo kategorii. Paski
są osobne, bo filtry na stronie sklepu i w konkretnej kategorii rzadko mają być
identyczne.

### Pułapka: `woocommerce_sidebar` wciąga awaryjny plik WordPressa

Po zadeklarowaniu `add_theme_support( 'woocommerce' )` pod listą produktów —
i pod stroną produktu — pojawił się obcy blok: formularz wyszukiwania, lista
stron, archiwa i kategorie. **Nie były to widgety.** Nie pokazywały się w panelu
i nie dało się ich usunąć przez *Wygląd → Widgety*.

Łańcuch:

```
archive-product.php  →  do_action( 'woocommerce_sidebar' )
                     →  woocommerce_get_sidebar()
                     →  wc_get_template( 'global/sidebar.php' )
                     →  get_sidebar( 'shop' )
                     →  motyw nie ma sidebar-shop.php ani sidebar.php
                     →  wp-includes/theme-compat/sidebar.php   ← wpisane na sztywno
```

Plik awaryjny WordPressa zawiera `get_search_form()`, `wp_list_pages()`,
`wp_get_archives()` i `wp_list_categories()` — dokładnie to, co było widać.

**Rozwiązanie:** `remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 )`.
Układ ma własną kolumnę boczną wewnątrz siatki, więc hook WooCommerce jest
zbędny. Zdejmujemy go globalnie, nie tylko na archiwach — ten sam hook kończy
także `single-product.php`.

> **Reguła na przyszłość:** motyw bez `sidebar.php` nie jest „motywem bez
> kolumny bocznej" — jest motywem, któremu WordPress podstawi swoją. Zanim
> uznasz obcy blok za widget, sprawdź, czy nie pochodzi z `theme-compat`.

**Pasek widgetów wisi na `woocommerce_archive_description`, nie na
`woocommerce_before_shop_loop`.** Ten drugi odpala się dopiero wewnątrz warunku
`if ( woocommerce_product_loop() )` w `archive-product.php`, więc w **pustej
kategorii nie zadziałałby wcale** — a filtry są wtedy najbardziej potrzebne, bo
to nimi odwiedzający zdejmuje zawężenie, które nic nie znalazło.

Pasek narzędzi (sortowanie, widok, liczba na stronie) zostaje przy pętli
produktów celowo: bez produktów nie ma czego sortować.

### Skąd bierze się tekst nad listą

| Widok | Źródło |
|---|---|
| Strona sklepu | treść strony ustawionej jako **Strona sklepu** — wypisuje ją `woocommerce_product_archive_description`, mechanizm WooCommerce, którego nie trzeba było ruszać |
| Kategoria, tag, atrybut | pole ACF `cyber_category_content`; natywny opis terminu jest zdjęty z hooka i ukryty w panelu |

Brak tekstu jest poprawnym stanem — zostaje sama nazwa kategorii.

### Widok siatki i listy

**Oba widoki mają identyczny markup.** Różnią się wyłącznie regułami
w `assets/css/woocommerce-shop.css`, więc przełączenie to podmiana jednej klasy
— bez przeładowania i bez zapytania do serwera. Skrócony opis i przycisk zakupu
są w markupie zawsze; w siatce ukrywa je CSS.

Wybór ląduje w **ciasteczku**, nie w `localStorage`. Powód jest praktyczny:
serwer czyta ciasteczko i od razu renderuje właściwy układ. Gdyby wybór siedział
w `localStorage`, na każdym wejściu mignęłaby najpierw siatka, zanim skrypt
zdążyłby przełączyć na listę.

Wartość z ciasteczka przechodzi przez **białą listę** — to dane od użytkownika
i nie trafiają do klasy CSS bez sprawdzenia.

### Sortowanie i liczba produktów

WooCommerce nie ma sortowania alfabetycznego w domyślnej liście opcji, mimo że
obsługuje `orderby=title` w zapytaniu. Moduł dokłada je filtrem
`woocommerce_catalog_orderby` i ustawia jako domyślne
(`woocommerce_default_catalog_orderby`), zgodnie z projektem. Świadomie pominięte:
„Domyślne sortowanie" i „Ocena".

Liczba produktów na stronie **musi** przejść przez serwer, bo zmienia zapytanie.
Parametr z adresu przechodzi przez białą listę `12 / 24 / 48 / 96` —
`absint()` sam nie wystarczy, bo `per_page=100000` jest poprawną liczbą, a zabiłby
zapytanie. Formularz niesie pozostałe parametry w polach ukrytych
(`wc_query_string_form_fields`), żeby zmiana liczby nie kasowała sortowania.

### Pułapka: `get_main_tax_query()` nie działa w AJAX

Endpoint doładowywania początkowo zwracał **zero produktów**. Powód:
`WC()->query->get_main_tax_query()` opiera się na głównym zapytaniu strony,
którego w żądaniu AJAX nie ma — zwraca wtedy pustą tablicę, a pusty element
w `tax_query` wywraca całe zapytanie.

Wykluczenie produktów ukrytych w katalogu budujemy więc **jawnie**, klauzulą
`product_visibility NOT IN (exclude-from-catalog)`.

**Reguła na przyszłość:** każda funkcja WooCommerce opierająca się na *głównym
zapytaniu* jest w kontekście AJAX bezużyteczna. W endpointach buduj zapytanie od
zera zamiast pożyczać stan pętli.

Zabezpieczenia endpointu opisuje `docs/security.md`.

## Strona pojedynczego produktu

Piąty widok sklepu i — jak cztery poprzednie — **zero nadpisań szablonów**.
`content-single-product.php` zostaje nietknięty; cały układ wchodzi hookami.

### Kształt strony

```
.cyber-container
└── .cyber-product
    ├── .cyber-product__top              ← otwierany na woocommerce_before_single_product_summary @5
    │   ├── .cyber-product__media        ← @20, zamiast woocommerce_show_product_images
    │   │   ├── .cyber-product__stage    ← zdjęcie główne + plakietka promocji
    │   │   └── .cyber-product__gallery  ← pionowy pasek miniatur
    │   └── div.summary                  ← elementy z rejestru, na pozycjach z panelu
    │                                       (zamykane na woocommerce_after_single_product_summary @1)
    └── zakładki / upsell / podobne      ← te same hooki, priorytety z panelu
```

**Dlaczego `__top` jest osobnym opakowaniem.** Galeria i `div.summary` są
w szablonie WooCommerce **rodzeństwem**, a zaraz za nimi lecą zakładki. Bez tego
opakowania siatka dwukolumnowa objęłaby także zakładki i wrzuciła je do jednej
z kolumn. Opakowanie otwiera się priorytetem `5` na hooku przed podsumowaniem
i zamyka priorytetem `1` na hooku po nim — czyli dokładnie wokół tych dwóch
elementów.

**Opakowanie listy produktów jest zdejmowane.** `cyber_shop_wrapper_open()`
z `inc/woocommerce-shop.php` wisi na `woocommerce_before_main_content`
**globalnie** i ma kolumnę boczną z widgetami, która na produkcie byłaby pusta.
`cyber_product_setup_hooks()` zdejmuje je i wstawia własne. Działa, bo oba moduły
podpinają się do `wp` z tym samym priorytetem, a `inc/woocommerce-product.php`
ładuje się **po** `inc/woocommerce-shop.php` (`functions.php`).

### Dwie kolumny: flex, nie grid

Szerokości kolumn to dwa niezależne pola procentowe (40 i 60). W siatce CSS
druga ścieżka zjadłaby resztę miejsca **niezależnie od ustawienia**, a odstęp
między kolumnami wypchnąłby układ poza kontener. Stąd flex z odstępem podzielonym
po połowie na każdą kolumnę:

```css
gap: 48px;
.cyber-product__media  { flex: 0 1 calc(var(--cyber-product-col-image)   - 24px); }
.cyber-product__top .summary { flex: 0 1 calc(var(--cyber-product-col-summary) - 24px); }
```

40 + 60 daje wtedy dokładnie pełną szerokość, a inne ustawienie (np. 50/30) jest
respektowane jako świadomy wybór admina, nie po cichu naprawiane.

### Galeria — własna, nie WooCommerce

Domyślna galeria WooCommerce to **flexslider + photoswipe + zoom**, włączane
przez `add_theme_support( 'wc-product-gallery-*' )`. Motyw ich nie włącza
i włączać nie będzie: projekt wymaga pionowego paska miniatur przewijanego
przeciągnięciem, czego flexslider nie potrafi. Taniej zbudować pasek od zera niż
przestawiać bibliotekę, której i tak nie ładujemy.

WooCommerce mimo to kolejkuje `wc-single-product.js` na każdej stronie produktu.
Skrypt szuka `.woocommerce-product-gallery` — u nas takiej klasy nie ma, więc
nie robi nic. To nie jest przeoczenie, tylko akceptowany koszt jednego pliku.

**Przewijanie idzie transformem, nie natywnym scrollem.** Natywny pasek trzeba
by ukrywać osobnym hackiem w każdej przeglądarce, a i tak nie dałby przeciągania
myszą. Pointer Events obsługują mysz i palec **jednym** zestawem zdarzeń — nie
ma osobnej ścieżki dotykowej, która mogłaby się rozjechać z myszą.

`touch-action: none` na pasku wyłącza natywne przewijanie strony **wyłącznie nad
paskiem** — bez tego przeciągnięcie palcem w pionie przewijałoby stronę zamiast
galerii. Poza paskiem strona zachowuje się normalnie.

**Wybór zdjęcia rozstrzyga `pointerup`, a nie `click`** — i to nie jest
optymalizacja, tylko konieczność. Pasek przechwytuje wskaźnik
(`setPointerCapture`), żeby przeciąganie działało także po wyjechaniu kursorem
poza pasek. Przechwycenie **przekierowuje `pointerup` na element
przechwytujący**, więc przeglądarka wystawia potem `click` na pasku, nie na
miniaturze — `event.target.closest( '.cyber-product__thumb' )` zwróciłby `null`
i kliknięcie nigdy by nie zadziałało.

Stąd układ:

| Wejście | Ścieżka | Warunek |
|---|---|---|
| mysz, palec | `pointerdown` zapamiętuje miniaturę → `pointerup` wybiera | ruch ≤ **6px** |
| klawiatura | `click` z `event.detail === 0` | — |

Miniaturę czytamy na `pointerdown`, bo to **ostatnie zdarzenie wskaźnika
trafiające w prawdziwy element** — od `setPointerCapture()` wszystko celuje już
w pasek.

Próg 6px oddziela klik od przeciągnięcia: bez niego każde przesunięcie paska
podmieniałoby zdjęcie główne. Obie ścieżki wykluczają się same (`detail === 0`
zachodzi wyłącznie przy klawiaturze), więc nie ma stanu, w którym jedno
kliknięcie wybiera zdjęcie dwa razy.

Poniżej 479px CSS kładzie pasek poziomo (`flex-direction: row`). Skrypt odczytuje
to z wyliczonego stylu, więc kierunek przeciągania idzie za układem —
**bez drugiego progu breakpointu w JS** (CLAUDE.md sekcja 18).

### Elementy: pozycja = priorytet hooka

Pełna tabela elementów: `docs/components.md`. Mechanizm:

```
cyber_product_elements()            ← rejestr (inc/helpers.php)
        │
        ├─→ cyber_product_option_schema()   ← wpisy w schemacie opcji
        ├─→ pola ACF                        ← cyber_wc_product_show_* / _pos_*
        └─→ cyber_product_setup_hooks()     ← add_action( $hook, $cb, $pozycja )
```

Liczba z panelu trafia **wprost** do priorytetu `add_action()`. Nie ma tablicy
pośredniej, którą trzeba by trzymać w zgodzie z kodem, a wtyczka dopięta do tego
samego hooka dalej ląduje tam, gdzie każe jej własny priorytet.

### Trzy pułapki, które trzeba było obejść

| Rzecz | Dlaczego nie „po prostu" | Rozwiązanie |
|---|---|---|
| **SKU pod tytułem** | WooCommerce renderuje SKU **wewnątrz** bloku `product_meta`, razem z kategoriami i tagami | `cyber_product_sku()` i `cyber_product_meta()` — dwa osobne elementy, każdy z własną pozycją |
| **Dostępność nad przyciskiem** | `wc_get_stock_html()` woła szablon **przycisku zakupu**, więc samo wywołanie w nowym miejscu nie usunęłoby napisu ze starego | filtr `woocommerce_get_stock_html` zwraca pusty string — ale **tylko dla produktu, który jest treścią strony**; wariant podaje swoją dostępność przez tę samą funkcję i musi ją zachować. Własny markup wypisuje `cyber_product_stock()` |
| **Plus i minus przy ilości** | `global/quantity-input.php` obsługuje także koszyk | hooki `woocommerce_before/after_quantity_input_field` + warunek `cyber_is_product_page()` — zero nadpisań, zero podwójnego licznika w koszyku |

### Zakładki i ślad na przyszłość

Na tym etapie widoczny jest **wyłącznie długi opis**. Zamiast wycinać resztę na
sztywno, zestaw przechodzi przez własny filtr:

```php
add_filter( 'cyber_product_tabs', function ( $tabs, $all ) {
	$tabs['care'] = array(
		'title'    => 'Pielęgnacja',
		'priority' => 20,
		'callback' => 'moj_render_zakladki',
	);

	return $tabs;
}, 10, 2 );
```

Drugi argument to **pełny, oryginalny zestaw WooCommerce** — przywrócenie
wbudowanej zakładki („Informacje dodatkowe") to jedna linia, a nie odtwarzanie
jej callbacku.

Nagłówek `<h2>` wewnątrz panelu opisu jest wyciszany
(`woocommerce_product_description_heading`) — przy jednej zakładce byłby tym
samym napisem dwa razy pod sobą.

### Skala odstępów

Skala **6, 12, 24, 36, 48, 64, 94px** obowiązuje w całym motywie: żadna wartość
`padding` ani `margin` w `assets/css/` nie jest zapisana liczbą px — wszystkie
idą przez `var(--cyber-space-N)`.

Przy porządkowaniu istniejących arkuszy każdą wartość przypisano do
**najbliższego** progu (największa korekta: 6px, typowa: 2–4px). Jeden przypadek
był remisem — `18px` w nagłówku tabeli koszyka leży dokładnie między 12 a 24;
rozstrzygnięto w górę, żeby nagłówek zrównał się z wierszami tabeli, które szły
z 20px na 24px.

Poza skalą zostają wyłącznie dwie wartości ujemne o charakterze technicznym:
`margin: -1px` w `.screen-reader-text` (standardowy clip dostępności) oraz
`margin-bottom: -1px` nasuwające obramowanie aktywnej zakładki na obramowanie
kontenera. To korekty o grubość krawędzi, nie odstępy.

Same zmienne `--cyber-space-1` … `--cyber-space-7` są zadeklarowane na `:root`
w **`assets/css/main.css`**, nie tutaj. Powód jest praktyczny: ten arkusz
kolejkuje się wyłącznie na stronie produktu, więc gdyby trzymał definicje,
każdy inny moduł sięgający po skalę dostawałby niezdefiniowane zmienne — a
`padding: var(--cyber-space-3)` bez definicji nie jest błędem, tylko cicho
znika (CLAUDE.md sekcja 6).

## Sekcje — Flexible Content

Etap 4 z CLAUDE.md sekcja 17. Treść strony składana z klocków: jedno pole
`cyber_sections`, jeden rejestr, jeden wspólny szkielet.

### Przepływ

```
group_sections.json           ← pole Flexible Content (strony, wpisy)
        │
        │  layout klonuje wspólne pola z dwóch grup źródłowych
        ├──→ group_section_content.json    (WYSIWYG góra/dół)
        └──→ group_section_settings.json   (18 pól wyglądu)
        │
        ▼
cyber_render_sections()       ← pętla po wierszach
        │
        ├──→ cyber_section_types()        ← rejestr: klucz → szablon
        ├──→ cyber_section_attributes()   ← klasy + zmienne CSS
        │
        ▼
template-parts/sections/[szablon].php     ← dane jawnie w $args
        │
        ▼
.cyber-section + assets/css/sections.css
```

### Trzy grupy pól zamiast jednej

Wspólne części sekcji mają **jedną** definicję. Grupy `group_section_content`
i `group_section_settings` istnieją wyłącznie jako źródło dla pola **Clone** —
ich reguła lokalizacji (`options_page == cyber-clone-source`) celowo nie pasuje
do niczego, więc nie renderują się nigdzie w panelu.

Klon działa w trybie **seamless bez prefiksu nazw**, więc PHP czyta pola płasko
(`$row['cyber_section_bg_color']`). Sprawdzone: layout `basic` rozwija się do
**28 podpól** o płaskich nazwach.

Alternatywa — skopiowanie pól do każdego layoutu — to ta sama pułapka, co przy
kopiowaniu pól ACF: przy dwunastu sekcjach powstaje dwanaście definicji, z czego
połowa po roku różni się od reszty.

### Dlaczego renderer nie używa `have_rows()`

Idiom ACF (`have_rows()` + `get_sub_field()` wewnątrz szablonu) wymusza, żeby
plik widoku sam wołał ACF — czyli dokładnie to, czego zakazuje CLAUDE.md
sekcja 4. Renderer pobiera więc cały wiersz i przekazuje go jawnie w `$args`.
Koszt: kilka linii. Zysk: sekcję da się wyrenderować w innym kontekście, a plik
szablonu nie zależy od tego, czy akurat jesteśmy w pętli ACF.

### Wartości per instancja w atrybucie `style`

Udokumentowane odstępstwo od CLAUDE.md sekcja 6 — uzasadnienie i granice
opisuje sama sekcja 6. Tutaj tylko konsekwencja techniczna: **PHP nie generuje
żadnej reguły CSS ani media query**. Wypisuje wyłącznie zmienne, a arkusz je
konsumuje:

```css
.cyber-section { padding-top: var(--cyber-section-pt, 0px); }

@media (max-width: 767px) {
	.cyber-section {
		padding-top: var(--cyber-section-pt-m, var(--cyber-section-pt, 0px));
		background-image: var(--cyber-section-bg-image-mobile, var(--cyber-section-bg-image, none));
	}
}
```

Łańcuch zapasowy w `var()` robi tu realną robotę: zmienna mobilna powstaje
**tylko wtedy**, gdy pole ma wartość. Gdyby PHP wypisywał ją zawsze, sekcja bez
ustawionego odstępu mobilnego dostawałaby na telefonie zero zamiast odziedziczyć
wartość desktopową. To był błąd wychwycony na testach, nie teoria.

### Trzy szerokości zamiast jednej

`cyber_container_css()` wypisywał dotąd **jedną** zmienną
`--cyber-container-width` — wynik wyboru 100/80/60 w Global Options. Sekcja
ustawiona na 80% na stronie ustawionej na 60% nie miała z czego skorzystać.

Moduł wypisuje teraz wszystkie trzy obok siebie — `--cyber-width-100`,
`--cyber-width-80`, `--cyber-width-60` — a `--cyber-container-width` dalej
wskazuje wybór globalny i jest tym, co dziedziczy sekcja ustawiona na `inherit`.
To rozszerzenie istniejącego modułu, nie drugi, równoległy system szerokości.

### Sekcja z własną treścią: podział na dwa moduły

`cards` jest pierwszą sekcją, która ma coś w środkowym kontenerze, więc ustala
wzorzec dla kolejnych:

| Moduł | Odpowiada za |
|---|---|
| `inc/sections.php` | opakowanie, oba pola WYSIWYG, tło, szerokość, odstępy sekcji |
| `inc/sections-cards.php` | **wyłącznie** siatkę i elementy |

Moduł sekcji nie wie nic o kartach, a moduł kart nie wie nic o tle sekcji.
Dzięki temu zmiana w opakowaniu nie wymaga dotykania żadnej sekcji, a kolejna
sekcja z własną treścią dopisuje się obok, nie zamiast.

Ten sam podział obowiązuje w ACF: layout `cards` klonuje **cztery** rzeczy —
WYSIWYG górę, grupę `group_section_cards`, WYSIWYG dół i wspólne ustawienia
sekcji. Sprawdzone: rozwija się do **63 podpól** o płaskich nazwach.

**Barwy cienia i obramowania nie mają pól w sekcji.** Włącznik jest w panelu,
barwa przychodzi z zakładki Kolory (`--cyber-color-shadow`,
`--cyber-color-border-1`), a wartość cienia i grubość ramki stoją na sztywno
w arkuszu. To świadoma rezygnacja z kontroli na rzecz spójności: karty w całym
projekcie wyglądają tak samo, a zmiana palety działa wszędzie naraz. Tak samo
przycisk — cały wygląd z zakładki Przyciski, sekcja wybiera tylko rozmiar.

### Pułapka: klucz layoutu kasuje treść

ACF, nie znajdując layoutu o zapisanej nazwie, **pomija wiersz i przy
renderowaniu, i przy zapisie**
(`pro/fields/class-acf-field-flexible-content.php` — „bail early if no layout").

Skutek praktyczny: zmiana nazwy klucza albo odebranie layoutowi dostępności
w danym typie treści **kasuje treść** przy najbliższym zapisaniu wpisu, bez
ostrzeżenia i bez kosza. Dodawanie jest bezpieczne zawsze, odbieranie jest
migracją.

### Pułapka PHP: klucze tablicy wyglądające na liczbę

`cyber_section_widths()` ma klucze `'100'`, `'80'`, `'60'`. PHP zamienia takie
klucze na `int`, więc `array_keys()` zwraca `array( 'inherit', 100, 80, 60 )`,
a ścisłe `in_array( '80', ..., true )` w nie nie trafia — każda szerokość poza
`inherit` po cichu wracała do wartości domyślnej. Stąd `array_map( 'strval', … )`
przed porównaniem. Błąd wyszedł na testach jednostkowych wartości, nie w kodzie
z przeglądu.

## Edytor treści

Motyw używa **wyłącznie klasycznego edytora** (TinyMCE). Edytor blokowy jest wyłączony
dla wpisów, stron i każdego CPT — także tych rejestrowanych przez wtyczki.

| Element | Realizacja |
|---|---|
| Wyłączenie bloków | `use_block_editor_for_post_type` → zawsze `false` (`inc/editor.php`). Filtr `gutenberg_can_edit_post_type` obsłużony tak samo, na wypadek instalacji wtyczki Gutenberg. |
| Wtyczka Classic Editor | **Niepotrzebna.** Klasyczny edytor jest częścią rdzenia WordPressa — wystarczy odmówić użycia edytora blokowego. |
| Style bloków na froncie | `wp-block-library`, `wp-block-library-theme`, `wp-components`, `global-styles` i `classic-theme-styles` są usuwane z kolejki na `wp_enqueue_scripts` (priorytet 100). |
| Edytor widgetów | Również klasyczny — `use_widgets_block_editor` → `false`. To **osobny przełącznik**, którego filtr `use_block_editor_for_post_type` nie obejmuje. |
| Zakres | Globalny i bezwarunkowy. Wyjątek dla pojedynczego typu treści = zmiana wyłącznie w `cyber_disable_block_editor()`. |

**Konsekwencja do zapamiętania:** usunięcie `wp-block-library` zakłada, że na froncie
nie renderuje się żaden blok. Gdyby wtyczka zaczęła zwracać znaczniki blokowe, te style
trzeba przywrócić — inaczej ich HTML straci formatowanie.

**Blokowy edytor widgetów jest wyłączony** — i to nie z powodu samej spójności.
Zanim zapadła ta decyzja, widget dodany jako blok wypisywał na froncie markup
w rodzaju `wp-block-paragraph` i `wp-block-button`, podczas gdy `wp-block-library`,
`wp-block-library-theme` i `global-styles` były z frontu usunięte. Akapit to
przeżywał, bo to zwykły `<p>`, ale przycisk, kolumny czy grupa wychodziły gołe.

To była dokładnie ta sytuacja, przed którą ostrzegał akapit powyżej — i argument
rozstrzygnął się sam, gdy pojawiły się pierwsze widgety sklepu.

> Widgety dodane wcześniej jako bloki zostają w bazie jako instancje
> `widget_block`. Klasyczny ekran pokazuje je jako widget „Blok" z surowym
> markupem w polu tekstowym — nic nie ginie, ale warto je podmienić na klasyczne
> odpowiedniki.

## Co jeszcze nie istnieje

Zrealizowane i opisane w sekcjach powyżej:

- **Etapy 1–3** — Global Options wraz z generowaniem CSS, Top Header,
  Header Desktop, Header Mobile, Footer, Copyright.
- **Warstwa WooCommerce, poza kolejnością etapów** — okruszki, koszyk,
  zamówienie, lista produktów (sklep i kategorie) oraz strona pojedynczego
  produktu. Powstała na bieżące potrzeby sklepu, nie jako etap z sekcji 17.

Zgodnie z kolejnością budowy (CLAUDE.md sekcja 17) — świadomie **nie** zaimplementowane:

- **Etap 4** — Flexible Content i system sekcji. `template-parts/sections/` jest pusty.
  `template-parts/components/` ma już dwa komponenty ogólnego użytku (`button.php`,
  `social-icons.php`), ale żaden z nich nie jest layoutem ACF.
- **Etap 5** — szablony widoków w `templates/`. Katalog jest pusty; jedynym widokiem
  jest `index.php` w rootcie, wymagany przez WordPress fallback. `header.php`
  i `footer.php` w rootcie **nie** są już szkieletem — zbierają dane przez
  `cyber_get_option()` i przekazują je jawnie do `template-parts/`.
- **Etap 7** — podstawy SEO.
- **Etap 8** — audyt wydajności, dostępności i bezpieczeństwa wraz z weryfikacją
  PHPCS (ruleset `WordPress`).

**Etap 6 (formularze / AJAX) jest częściowo zrobiony**, wbrew kolejności z sekcji
17: istnieją **dwa endpointy AJAX** — zmiana ilości pozycji na stronie zamówienia
i doładowywanie produktów na liście. Oba powstały jako część widoków sklepu,
oba mają nonce i pełną sanityzację, oba są opisane w `docs/security.md`.
Nie ma natomiast żadnego formularza własnego motywu — i to zostaje na etap 6.

Osobno, poza kolejnością etapów: kolumny 2 i 3 stopki oraz część pól zakładki
„Kontakt” są **zarezerwowane**, nie zapomniane (CLAUDE.md sekcja 22).
