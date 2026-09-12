# Architektura — Cyber Framework

Ostatnia aktualizacja: 2026-09-12 (stan: etapy 1–3 z CLAUDE.md sekcja 17; Global Options ma jedenaście zakładek — od „Główne ustawienia strony” po „Copyright”; wprowadzone warianty komponentów i przyklejony header).

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

## Edytor treści

Motyw używa **wyłącznie klasycznego edytora** (TinyMCE). Edytor blokowy jest wyłączony
dla wpisów, stron i każdego CPT — także tych rejestrowanych przez wtyczki.

| Element | Realizacja |
|---|---|
| Wyłączenie bloków | `use_block_editor_for_post_type` → zawsze `false` (`inc/editor.php`). Filtr `gutenberg_can_edit_post_type` obsłużony tak samo, na wypadek instalacji wtyczki Gutenberg. |
| Wtyczka Classic Editor | **Niepotrzebna.** Klasyczny edytor jest częścią rdzenia WordPressa — wystarczy odmówić użycia edytora blokowego. |
| Style bloków na froncie | `wp-block-library`, `wp-block-library-theme`, `wp-components`, `global-styles` i `classic-theme-styles` są usuwane z kolejki na `wp_enqueue_scripts` (priorytet 100). |
| Zakres | Globalny i bezwarunkowy. Wyjątek dla pojedynczego typu treści = zmiana wyłącznie w `cyber_disable_block_editor()`. |

**Konsekwencja do zapamiętania:** usunięcie `wp-block-library` zakłada, że na froncie
nie renderuje się żaden blok. Gdyby kiedyś włączono blokowy edytor widgetów albo wtyczka
zaczęła zwracać znaczniki blokowe, te style trzeba przywrócić — inaczej ich HTML straci
formatowanie. Blokowy edytor widgetów (`use_widgets_block_editor`) **nie** jest obecnie
wyłączany — to osobna decyzja, nieobjęta tą zmianą.

## Co jeszcze nie istnieje

Etapy 1–3 są zrealizowane i opisane w sekcjach powyżej: Global Options wraz
z generowaniem CSS, Top Header, Header Desktop, Header Mobile, Footer i Copyright.

Zgodnie z kolejnością budowy (CLAUDE.md sekcja 17) — świadomie **nie** zaimplementowane:

- **Etap 4** — Flexible Content i system sekcji. `template-parts/sections/` jest pusty.
  `template-parts/components/` ma już dwa komponenty ogólnego użytku (`button.php`,
  `social-icons.php`), ale żaden z nich nie jest layoutem ACF.
- **Etap 5** — szablony widoków w `templates/`. Katalog jest pusty; jedynym widokiem
  jest `index.php` w rootcie, wymagany przez WordPress fallback. `header.php`
  i `footer.php` w rootcie **nie** są już szkieletem — zbierają dane przez
  `cyber_get_option()` i przekazują je jawnie do `template-parts/`.
- **Etapy 6–8** — formularze / AJAX, podstawy SEO oraz audyt wydajności, dostępności
  i bezpieczeństwa wraz z weryfikacją PHPCS (ruleset `WordPress`).

Osobno, poza kolejnością etapów: kolumny 2 i 3 stopki oraz część pól zakładki
„Kontakt” są **zarezerwowane**, nie zapomniane (CLAUDE.md sekcja 22).
