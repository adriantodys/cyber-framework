# CLAUDE.md — Cyber Framework

Ten plik jest instrukcją operacyjną dla Claude (i każdego dewelopera) pracującego nad
customowym motywem WordPress **cyber-framework**. Obowiązuje przy każdej pracy nad
projektem — również po długiej przerwie. Pamięć konwersacji nie jest źródłem prawdy.
Źródłem prawdy są pliki w repozytorium: ten dokument, `acf-json/` oraz `docs/acf-schema.md`.

---

## 1. Przegląd projektu

- Customowy motyw WordPress budowany od zera, bez motywu nadrzędnego.
- Nazwa: `cyber-framework`.
- Czysty PHP + **ACF PRO** jako jedyny system zarządzania danymi.
- **Brak page buildera** (Elementor, Divi, Beaver Builder itp.).
- **Gutenberg wyłączony w całym motywie** — wpisy, strony i każdy CPT edytuje się
  klasycznym edytorem (TinyMCE). Wyłączenie realizuje `inc/editor.php` filtrem
  `use_block_editor_for_post_type`; wtyczka Classic Editor nie jest potrzebna.
  Ponowne włączenie bloków dla jakiegokolwiek typu treści wymaga wyraźnej zgody
  (patrz sekcja 15) — layout buduje ACF, a nie drugi system edycji.
- Cel: architektura czysta, bezpieczna, wydajna, zgodna z WordPress Coding Standards (WPCS).

## 2. Stack technologiczny i ograniczenia

- PHP zgodny z WPCS (docelowo weryfikowany przez PHPCS + ruleset `WordPress`).
- ACF PRO: pola, Options Pages, Flexible Content, Repeater, Blocks (jeśli kiedyś potrzebne
  — decyzja wymaga wyraźnej zgody, patrz sekcja 15).
- **Brak zbędnych bibliotek.** Każda dodana zależność (JS, PHP, Composer) musi być
  uzasadniona i zaakceptowana — Claude nie dodaje niczego "na wszelki wypadek".
- Brak jQuery, jeśli zadanie da się zrealizować czystym JS (chyba że coś w rdzeniu WP
  tego wymaga).

### Zależności twarde i miękkie

| Zależność | Rodzaj | Zachowanie przy braku |
|---|---|---|
| **ACF PRO** | twarda | Motyw renderuje się na wartościach domyślnych, admin dostaje `notice-error` (`inc/acf.php`). |
| **WooCommerce** | miękka | Funkcje sklepowe wyłączają się same, reszta witryny działa bez zmian (`inc/woocommerce.php`). |

Każda zależność miękka musi mieć **jeden plik**, który o niej decyduje. Moduły pytają
tam, zamiast wołać `class_exists()` u siebie — rozsypanie tego warunku po plikach
kończy się tym, że po wyłączeniu wtyczki część miejsc milknie cicho, a część głośno.

**Komunikat o braku zależności miękkiej ma trzy poziomy i żaden nie jest opcjonalny:**

1. **Gość witryny nie widzi nic.** Brak wtyczki nie jest jego problemem i nie ma
   prawa wyciec na front.
2. **Zalogowany administrator** widzi na froncie krótką podpowiedź w miejscu, które
   miało należeć do wtyczki — żeby wiedział, *dlaczego* jest tam pusto.
3. **W panelu** czeka pełne ostrzeżenie, ale tylko dla kogoś z odpowiednim
   `capability` i tylko wtedy, gdy funkcja faktycznie jest włączona. Sam brak
   wtyczki nie jest błędem i nie ma o czym informować.

Brak zależności miękkiej **nigdy** nie kończy się błędem krytycznym ani pustą stroną.

Nowy moduł korzystający z WooCommerce dopisuje się do filtra
`cyber_woocommerce_required_by` i dostaje komplet tych komunikatów bez własnego kodu.

#### Strony WooCommerce: klasyczny shortcode, zero nadpisań szablonów

Strony sklepowe (koszyk, zamówienie i kolejne) używają **klasycznych
shortcode'ów** (`[woocommerce_cart]`), nie bloków WooCommerce. Powód jest
wprost architektoniczny: motyw ma wyłączony edytor blokowy (sekcja 1), więc
bloku i tak nie dałoby się skonfigurować — redaktor zobaczyłby w TinyMCE kilka
kilobajtów surowego markupu blokowego i mógłby go zapisem uszkodzić.

Dodatkowo klasyczny markup jest **stabilniejszy do stylowania**: klasy
`.shop_table`, `.cart_totals`, `.quantity` nie zmieniły się od lat, podczas gdy
`.wc-block-components-*` to DOM renderowany Reactem, zmieniany między wersjami.

**Wygląd powstaje wyłącznie z hooków, filtrów i CSS. Żadnych nadpisań
szablonów WooCommerce w katalogu `woocommerce/`.** To świadoma decyzja o koszcie
utrzymania: nadpisany szablon przypina kopię do wersji wtyczki, po aktualizacji
WooCommerce zgłasza „template is out of date" w **WooCommerce → Status**, a
poprawki z rdzenia przestają docierać do tego pliku.

Nadpisanie pojedynczego szablonu jest dopuszczalne **tylko wtedy**, gdy efektu
nie da się osiągnąć hookiem ani CSS-em, i wymaga:

1. jawnej decyzji przed implementacją (sekcja 15),
2. odnotowania w `docs/architecture.md`, **którą wersję** pliku skopiowano
   (nagłówek `@version` z oryginału),
3. sprawdzenia tego wpisu przy każdej większej aktualizacji WooCommerce.

Etykiety WooCommerce zmieniamy filtrem `gettext` **zawężonym do konkretnego
widoku**. Ciągi w rodzaju „Total" czy „Subtotal" występują w dziesiątkach miejsc
wtyczki — w zamówieniach, mailach i panelu — więc globalna podmiana rozjechałaby
całą wtyczkę. Porównujemy ciąg **źródłowy (angielski)**, nie przetłumaczony,
żeby filtr działał niezależnie od wgranego tłumaczenia.

## 3. Struktura katalogów

```
cyber-framework/
├── CLAUDE.md
├── README.md
├── style.css
├── functions.php
│
├── acf-json/                      ← Local JSON, źródło prawdy dla pól ACF, wersjonowane w Git
│   └── group_global_options.json
│
├── docs/
│   ├── acf-schema.md              ← mapa wszystkich pól ACF (ten plik jest ŻYWYM dokumentem)
│   ├── architecture.md
│   ├── security.md
│   └── components.md
│
├── inc/
│   ├── setup.php                  ← theme supports, menus, image sizes
│   ├── enqueue.php                ← rejestracja/wersjonowanie assetów
│   ├── acf.php                    ← ustawienia ACF (json save/load path)
│   ├── options.php                ← rejestracja Options Page(s)
│   └── helpers.php                ← funkcje pomocnicze (np. cyber_get_option())
│
├── template-parts/
│   ├── header/
│   ├── footer/
│   ├── breadcrumb/                ← ścieżka okruszków (wariant zwykły i WooCommerce)
│   ├── components/                ← drobne, reużywalne elementy UI (przycisk, karta, badge)
│   └── sections/                  ← sekcje flexible content (1 layout ACF = 1 plik)
│
├── templates/                     ← page templates, front-page.php, single.php, archive.php...
│
└── assets/
    ├── css/
    ├── js/
    └── images/
```

## 4. Separacja danych / logiki / widoku

- **Dane** → ACF (pola, Options Pages, Flexible Content).
- **Logika** → `inc/` (pobieranie, przetwarzanie, walidacja, cache).
- **Widok** → `template-parts/` i `templates/` — wyłącznie wyświetlanie, zero logiki biznesowej.
- Widoki **nigdy** nie wywołują `get_field()` / `get_option()` bezpośrednio dla danych
  globalnych — zawsze przez dedykowany helper (np. `cyber_get_option( $key )`).
- Template part nie "sięga" samodzielnie po globalny stan aplikacji — dane powinny
  być przekazywane do niego jawnie (argumenty / `$args` w `get_template_part()`
  lub własny wrapper), żeby komponent dało się przetestować i użyć w innym kontekście.

## 5. ACF jako system danych — zasady

1. Wszystkie grupy pól synchronizowane jako **Local JSON** w `acf-json/` i commitowane do Git.
2. **`docs/acf-schema.md` jest obowiązkowym, żywym dokumentem.** Każda nowa grupa pól,
   layout czy field musi zostać tam opisany w tym samym commicie, w którym powstał.
3. Przed utworzeniem lub modyfikacją template'u zależnego od ACF — **najpierw** sprawdź
   `acf-json/` oraz `docs/acf-schema.md`.
4. **Claude nigdy nie zgaduje nazw pól.** Jeśli pole, którego potrzebuje logika, nie
   istnieje w schemacie — należy to zgłosić i zaproponować dodanie pola, a nie zakładać
   jego istnienia czy nazwy.
5. Pola grupowane w zakładki (Tab field) tam, gdzie poprawia to czytelność panelu.
6. Konwencja nazewnictwa pól: `cyber_[kontekst]_[nazwa]`, np. `cyber_page_width_type`.
7. Prefiks `cyber_` jest obowiązkowy dla **wszystkich** pól na Options Page — tam nazwa
   pola staje się bezpośrednio kluczem w `wp_options`, więc ryzyko kolizji z inną
   wtyczką jest realne.
8. Options Page **nie da się utworzyć przez UI ACF** — wymaga rejestracji w PHP
   (`acf_add_options_page()`, patrz `inc/options.php` i `docs/acf-schema.md`).
9. Pola responsywne projektujemy zgodnie z sekcją 18 (breakpointy) i sekcją 19
   (skalowanie procentowe zamiast pól per breakpoint).

### Odnośniki do stron witryny

Linki do stron prawnych (polityka prywatności, cookies) używają pola **Page Link**,
nie Link/URL — wybór istniejącej strony WP zamiast ręcznego wklejania adresu.
Etykiety tekstowe tych linków są **stałe w kodzie**, nie edytowalne przez ACF.

Ten wzorzec (Page Link + stała etykieta) jest **preferowany dla wszystkich przyszłych
odnośników do stron istniejących w strukturze witryny**, w odróżnieniu od linków
zewnętrznych, które nadal używają pola Link.

### Dwa wzorce kolorów

Pola kolorystyczne występują w projekcie w dwóch wzorcach:

- **semantyczne** — przypięte do tagów lub klas komponentów, aplikują się
  automatycznie (np. `cyber_color_headings` → `h1`–`h6`),
- **narzędziowe / utility** — stała nazwa klasy CSS, np. `.cyber-border-1`,
  nakładana ręcznie na dowolny element.

Kolejne pola kolorystyczne powinny być **jawnie zaklasyfikowane** do jednego
z tych wzorców przed implementacją, zamiast zakładać domyślnie jeden z nich.

Ten sam wzorzec (stała nazwa klasy w kodzie, kontekstowo ograniczona przez
konwencję, nie przez technikę) zastosowano też w module **Footer**:
`.cyber-footer-title`, `.cyber-footer-text`, `.cyber-footer-link` — przeznaczone
wyłącznie do użycia wewnątrz `.cyber-footer`, mimo że nic nie blokuje technicznie
użycia ich gdzie indziej.

Kolejne moduły z podobną potrzebą (style ograniczone kontekstowo do jednej sekcji
strony) powinny nazywać klasy z prefiksem tej sekcji — `cyber-[sekcja]-[rola]` —
zamiast reużywać globalne klasy narzędziowe z modułu Kolory.

#### Przezroczystość — obowiązkowa we wszystkich polach koloru

**Każde pole Color Picker w projekcie ma włączoną przezroczystość**
(`enable_opacity`) i używa typu schematu **`color_alpha`**, nie `color`.
Bez wyjątków, we wszystkich zakładkach.

Nowe pole koloru dodaje się od razu w tej postaci. Ustawienie samego
`enable_opacity` w ACF **bez** zmiany typu w `cyber_option_schema()` jest
pułapką, nie półśrodkiem: `color` waliduje przez `sanitize_hex_color()`, które
odrzuca `rgba()` — redaktor ustawi alfę, zapisze, a motyw po cichu podmieni
wartość na domyślną. Panel pokaże jedno, front drugie.

Typ `color` (sam HEX) zostaje w `cyber_validate_option_value()` bez
przypisanego pola, pod ewentualne przyszłe pole, które musi odrzucić kanał alfa.
Jego użycie wymaga uzasadnienia.

Biblioteka koloru z alfą **nie zwraca HEX-a**: przy pełnym kryciu daje
`rgb(r,g,b)`, poniżej — `rgba(r,g,b,a)`. Wartości zapisane wcześniej jako HEX
zostają w bazie nietknięte do czasu ponownego zapisu pola, więc w `wp_options`
oba formaty współistnieją. To stan normalny, nie niespójność do naprawienia.

## 6. Global Options — moduł nr 1

Pierwszy moduł projektu. Pełna specyfikacja pól znajduje się w `docs/acf-schema.md`
i musi być tam aktualizowana przy każdej zmianie.

Zasada dostępu w kodzie:

```php
cyber_get_option( 'page_width_type' );
```

Widoki nie wołają `get_field( $key, 'option' )` bezpośrednio — zawsze przez helper
z `inc/helpers.php`, żeby w jednym miejscu móc dodać cache/transient bez zmiany
dziesiątek plików szablonów.

### Konwencja: ACF Options → CSS

Wartości z Global Options, które wpływają na wygląd frontu, są wypisywane jako
**CSS Custom Properties** w `<style>` w `wp_head` (funkcja w `inc/enqueue.php`).

- Nazwy zmiennych: prefiks `--cyber-`, np. `--cyber-container-width`,
  `--cyber-container-margin`.
- Elementy strukturalne (header, footer, kontener treści) konsumują te zmienne
  przez wspólną klasę `.cyber-container` — **nie** przez inline style w PHP.
- Wartości responsywne wypisujemy na progach z sekcji 18; przy wielu powiązanych
  wartościach obowiązuje skalowanie procentowe z sekcji 19.
- Moduł opisany **mapą pól** (klucz opcji → nazwa zmiennej + jednostka) **nie
  pisze własnej pętli** — wypisuje zmienne przez `cyber_css_vars_from_map()`.

Własna pętla jest dopuszczalna tylko wtedy, gdy moduł robi coś więcej niż proste
przepisanie wartości: skaluje na breakpointach (`cyber_font_css()`), wybiera
wartość zależnie od innego pola (`cyber_container_css()`), generuje cały blok
`@media` (`cyber_header_mobile_css()`) albo tworzy nazwy zmiennych mechanicznie
i mapy w ogóle nie potrzebuje (`cyber_colors_css()`). Skopiowanie pętli „bo tak
robi sąsiedni moduł" jest błędem — wcześniej istniały trzy jej identyczne kopie.

## 7. Komponenty i template parts

- `template-parts/sections/` — jeden plik = jeden layout Flexible Content.
- `template-parts/components/` — drobne, reużywalne elementy (przycisk, karta, badge, ikona).
- Każdy nowy layout ACF musi mieć swój odpowiednik pliku i musi być opisany
  w `docs/components.md` (nazwa layoutu → plik → pola, których używa).
- Relacja jest zawsze jednokierunkowa i jawna:

```
ACF layout → template-part → HTML → CSS
```

## 8. WordPress Coding Standards

- Docelowo weryfikacja przez PHPCS z ruleset `WordPress` / `WordPress-Extra`.
- Nazwy funkcji, hooków, klas — zawsze z prefiksem `cyber_` / `Cyber_`.
- Escaping wykonywany **bezpośrednio przy outpucie**, nigdy wcześniej i nigdy "na zapas".
- Spacing, wcięcia, dokumentacja funkcji (DocBlock) zgodnie z WPCS.

## 9. Bezpieczeństwo — zasada nadrzędna

Bezpieczeństwo ma pierwszeństwo przed wygodą i szybkością pisania kodu.

- **Escaping** (przy każdym wyjściu danych): `esc_html()`, `esc_attr()`, `esc_url()`,
  `wp_kses_post()` — dobrane do kontekstu, nigdy generyczne "na oko".
- **Sanitization** (przy każdym wejściu/zapisie): `sanitize_text_field()`,
  `sanitize_email()`, `absint()`, itd.
- **Validation**: sprawdzanie typu, zakresu i sensowności danych zanim zostaną użyte
  (np. czy liczba mieści się w rozsądnym zakresie px, czy wartość select istnieje
  wśród dozwolonych opcji).
- **Nonce**: `wp_nonce_field()` / `check_admin_referer()` / `wp_verify_nonce()`
  w każdym formularzu i każdym żądaniu AJAX.
- **Capabilities**: `current_user_can()` przed każdą akcją administracyjną lub zapisem.
- **AJAX**: nonce + capability check + sanitizacja wejścia + escaping wyjścia — zawsze
  wszystkie cztery elementy, nigdy część.
- **REST API**: `permission_callback` jest obowiązkowy. `__return_true` tylko dla
  endpointów jawnie i świadomie publicznych, z komentarzem wyjaśniającym dlaczego.
- Zero zaufania do `$_POST` / `$_GET` / `$_REQUEST` bez sanitizacji i walidacji.

## 10. Wydajność

- Minimalizacja liczby zapytań (rozważ cache/transient dla kosztownych operacji).
- Enqueue warunkowy — assets ładowane tylko tam, gdzie faktycznie są potrzebne,
  nie globalnie na każdej podstronie.
- Optymalizacja obrazów, `loading="lazy"` tam gdzie zasadne.
- Brak zbędnych bibliotek (patrz sekcja 2) — każdy dodatkowy plik JS/CSS to koszt.

## 11. Accessibility i SEO

- Semantyczny HTML5, poprawna hierarchia nagłówków (jeden `<h1>` na stronę, bez
  przeskakiwania poziomów).
- Stany focus widoczne, obsługa klawiatury, `alt` dla obrazów, `aria-*` tam gdzie
  natywny HTML nie wystarcza.
- Skip-link do treści głównej.
- Podstawowe meta tagi (title, description), przygotowanie pod strukturalne dane
  (Schema.org) tam, gdzie ma to sens biznesowy.

## 12. Wersjonowanie assetów

- Wersja pliku w `wp_enqueue_style()` / `wp_enqueue_script()` generowana przez
  `filemtime()`, nie przez sztywno wpisany numer wersji — eliminuje problemy z cache
  po każdej zmianie pliku.

## 13. Prefiksowanie

| Element | Prefiks |
|---|---|
| Funkcje | `cyber_` |
| Klasy | `Cyber_` |
| Hooki (actions/filters) | `cyber_` |
| Stałe | `CYBER_` |
| Pola ACF (Options Page) | `cyber_` |
| Text domain | `cyber-framework` |

## 14. Dokumentacja

- `docs/architecture.md` — opis architektury i przepływu danych.
- `docs/acf-schema.md` — pełna, aktualna mapa wszystkich pól ACF (patrz sekcja 5, pkt 2).
- `docs/components.md` — mapa layoutów Flexible Content → pliki → pola.
- `docs/security.md` — checklisty bezpieczeństwa dla formularzy/AJAX/REST.
- Każda zmiana w ACF **musi** iść w parze ze zmianą w `docs/acf-schema.md` w tym samym commicie.

## 15. Zasady podejmowania decyzji przez Claude

- Zawsze sprawdź `acf-json/` i `docs/acf-schema.md` **przed** pracą z polami — nie
  zakładaj struktury na podstawie nazwy czy kontekstu.
- Jeśli specyfikacja w zadaniu jest niejasna lub niekompletna — zaproponuj rozsądne,
  jawnie nazwane założenie i działaj, chyba że błędne założenie oznaczałoby przebudowę
  architektury lub dotyczy bezpieczeństwa/danych — wtedy zapytaj, zanim zaczniesz.
- Nie dodawaj nowych bibliotek/zależności bez wyraźnej zgody.
- Jeśli `docs/acf-schema.md` i rzeczywisty kod/ACF się rozjeżdżają — zgłoś rozbieżność
  wprost, nie "napraw" jej cicho po swojemu.
- Trzymaj się kolejności budowy z sekcji 16. Nie przeskakuj etapów bez wyraźnego
  potwierdzenia, nawet jeśli technicznie dałoby się to zrobić szybciej w innej kolejności.

## 16. Obowiązkowy output po każdej pracy z ACF

Za każdym razem, gdy Claude tworzy lub zmienia moduł związany z ACF (nowa grupa pól,
nowy layout, nowe pole w istniejącej grupie), musi dostarczyć **wszystkie trzy**
poniższe elementy — nie wystarczy sam kod/JSON bez opisu:

1. **Plik `acf-json/*.json`** gotowy do wgrania i zsynchronizowania w ACF
   (jeśli moduł tego wymaga), **albo** jawna informacja, że pola trzeba dodać
   ręcznie w UI (z podaniem powodu, np. brak jeszcze zarejestrowanej lokalizacji).
2. **Zaktualizowany `docs/acf-schema.md`** — tabela z Field Label / Field Name /
   Typ / Default / Przeznaczenie dla każdego nowego/zmienionego pola, w tym samym
   kroku, w którym powstał kod (nie "później").
3. **Czytelne podsumowanie w odpowiedzi dla użytkownika** — krótka lista "co się
   zmieniło i co musisz zrobić w adminie WP" (np. "wgraj plik X do acf-json/,
   kliknij Sync" albo "nic nie musisz robić ręcznie, JSON zrobi to sam po synchronizacji").

Zasada nadrzędna: użytkownik nigdy nie powinien się domyślać, jakie pola istnieją
ani co ma zrobić w panelu WordPress — ta informacja ma być podana wprost, za każdym
razem, bez pytania o to.

### Wzorzec paska dwustronnego

Sekcja Copyright (`template-parts/footer/copyright.php`) jest strukturalnie tożsama
z Top Header (cienki pasek, dwie strony flex) — każdy kolejny moduł o podobnym
kształcie (pasek informacyjny podzielony na dwie części) powinien **reużywać ten sam
wzorzec markupu i CSS**, zamiast tworzyć nowy.

## 17. Kolejność budowy motywu

1. Szkielet motywu: `style.css`, `functions.php`, `inc/setup.php`, `inc/enqueue.php`.
2. **Global Options** (moduł 1): rejestracja Options Page + pierwsza zakładka
   "Szerokość strony" (patrz `docs/acf-schema.md`).

   **Stan: zakończone.** W praktyce moduł 1 rozrósł się do jedenastu zakładek:
   Główne ustawienia strony, Ustawienia czcionki, Header Desktop, Header Mobile,
   Przyciski, Kolory, Kontakt, Social Media, Top Header, Footer, Copyright.
   To zamyka listę modułów podstawowych ustawień globalnych.
3. Header / Footer (ACF + template-parts).

   **Stan: zrealizowane przy okazji etapu 2.** Istnieją `template-parts/header/`
   (`header.php`, `top-header.php`) i `template-parts/footer/` (`footer.php`,
   `copyright.php`) wraz z logiką w `inc/header.php` i `inc/footer.php`.
   Do uzupełnienia zostaje zawartość kolumn 2 i 3 stopki (sekcja 22) — czyli
   drobne dopełnienie, nie praca od zera.
4. System komponentów / Flexible Content dla stron podstawowych.
5. Szablony kluczowych widoków: front page, page, single, archive, 404, search.
6. Formularze / AJAX (jeśli dotyczy) — pełne zabezpieczenie zgodnie z sekcją 9.
7. Podstawy SEO (meta, struktura nagłówków, dane strukturalne jeśli zasadne).
8. Audyt wydajności, dostępności i bezpieczeństwa + weryfikacja WPCS przed wdrożeniem.

## 18. Kanoniczne breakpointy

Projekt używa **jednego, wspólnego zestawu breakpointów dla wszystkich modułów** —
marginesów, wielkości czcionek, odstępów i wszystkiego, co jeszcze powstanie:

| Zakres | Warunek CSS | Nazwa w kodzie i w polach ACF |
|---|---|---|
| Desktop | powyżej 980px (brak media query — wartość bazowa) | `desktop` |
| Tablet | 980px–767px | `tablet` |
| Mobile | 767px–479px | `mobile` |
| Mobile small | poniżej 479px | `mobile_small` |

Każdy nowy moduł, który potrzebuje wartości responsywnych, **musi** używać tych samych
czterech progów i nie definiuje własnych. Jeśli pojawi się uzasadniona potrzeba innego
breakpointu — trzeba to **jawnie zgłosić i uzasadnić, zanim zostanie dodany**
(patrz sekcja 15). Nowy próg dodany po cichu w jednym module rozjeżdża cały layout,
bo moduły przestają się przełączać w tym samym momencie.

W CSS progi zapisujemy jako granice domknięte od góry: `max-width: 980px`,
`max-width: 767px`, `max-width: 479px`.

> **Wyjątek.** Próg przełączania header desktop/mobile (`cyber_header_mobile_breakpoint`)
> jest świadomym wyjątkiem od tego systemu i pozostaje niezależnie konfigurowalny
> przez admina.

> **Precedens: cudzy próg załatw cudzą zmienną.** Przyklejony header
> (`.cyber-header--sticky`) musi uwzględnić wysokość paska administratora
> WordPressa, która zmienia się przy 782px — progu spoza tego zestawu. Nie
> kosztowało to nowego breakpointu: pozycję daje
> `top: var(--wp-admin--admin-bar--height, 0px)`, czyli zmienna, którą WordPress
> sam ustawia w `admin-bar.css`, a `0px` obsługuje wylogowanych.
>
> Zanim dołożysz próg pod komponent, którego nie kontrolujesz, sprawdź, czy ten
> komponent nie wystawia własnej zmiennej. Zwykle wystawia.

## 19. Konwencja: skalowanie wartości responsywnych (wariant B)

Dotyczy pól, które definiują **wiele powiązanych wartości liczbowych** na desktopie
(np. wielkości czcionek `h1`–`h6`, `overtitle`, `text`, `links`).

**Nie tworzymy osobnych pól per breakpoint dla każdego elementu** — 10 elementów
× 4 breakpointy = 40 pól to panel nie do utrzymania i nie do wypełnienia.

Zamiast tego:

1. Pola z konkretnymi wartościami istnieją **wyłącznie dla desktopu**.
2. Responsywność daje **jedno pole procentowe na breakpoint**, skalujące
   **wszystkie** wartości desktopowe danego modułu naraz:

   - `cyber_[moduł]_scale_tablet`
   - `cyber_[moduł]_scale_mobile`
   - `cyber_[moduł]_scale_mobile_small`

   Nazwy breakpointów w polach są zgodne z sekcją 18.

Ten wzorzec jest **domyślny dla wszystkich przyszłych modułów** z wieloma wartościami
liczbowymi (fonty, odstępy między sekcjami itp.). Odstępstwo — czyli kontrola
per-elementowa na każdym breakpoincie — wymaga **jawnego uzasadnienia przed
implementacją**, a nie założenia z góry, że dany przypadek jest wyjątkiem.

### Wyjątek: wartości niepodlegające skalowaniu

Grubość czcionki (`font-weight`) jest wyjątkiem od zasady skalowania responsywnego
opisanej w tej sekcji — nie podlega przeliczaniu per breakpoint, ponieważ zmiana
grubości nie jest praktyką responsywnego web designu w tym projekcie. Kolejne pola
tego typu (np. `letter-spacing`, jeśli powstanie) powinny być każdorazowo jawnie
zaklasyfikowane jako **skalowane** albo **stałe**, zamiast domyślnie zakładać
jeden wzorzec.

## 20. Konwencja pól wyrównania (alignment)

Każde pole typu Select służące do wyboru wyrównania elementu używa **identycznego
zestawu wartości i identycznych etykiet**:

| Wartość | Etykieta |
|---|---|
| `left` | Do lewej |
| `center` | Do środka |
| `right` | Do prawej |

Kolejne moduły (stopka, sidebar, sekcje Flexible Content) **reużywają ten zestaw**
i nie definiują własnego — żadnych `start`/`end`, `l`/`c`/`r` ani wariantów
z dodatkową opcją „justify" bez wyraźnej decyzji.

W CSS wyrównanie jest **modyfikatorem klasy**, nie zmienną CSS: `.cyber-menu--left`,
`.cyber-submenu--center` itd. Powód: wyrównanie zmienia układ (`justify-content`,
`align-items`, `text-align` naraz), a nie pojedynczą wartość — zmienna CSS
wymuszałaby wypisywanie trzech różnych właściwości sterowanych jednym stringiem.

### Warianty pozycji komponentów

Komponenty, które w przyszłości mogą zyskać alternatywne warianty wizualne
(np. mobile menu wysuwane z lewej/prawej zamiast rozwijane w dół), powinny być
od razu budowane z **wymienną klasą modyfikującą na głównym wrapperze**, zamiast
wartości wpisanych na sztywno w bazowym selektorze — tak, żeby dodanie wariantu
wymagało tylko nowej klasy i pola ACF, a nie przepisania komponentu.

#### Wrapper każdego modułu niesie klasę wariantu

Zasada nie ogranicza się do komponentów, którym wariant już zaplanowano —
obowiązuje **każdy wrapper modułu**. Klasę buduje `cyber_variant_class()`
(`inc/components.php`), które sanityzuje wartość i sprowadza pustą lub
niepoprawną do `default`:

```
cyber-header     cyber-header--default
cyber-topheader  cyber-topheader--default
cyber-footer     cyber-footer--default
cyber-copyright  cyber-copyright--default
```

Wariant `default`, którego nie celuje żadna reguła CSS, **nie jest martwym
kodem** — to punkt zaczepienia. Dodany teraz kosztuje jedną klasę w markupie;
dodany po napisaniu sekcji Flexible Content oznacza rewizję każdego selektora,
który zakłada gołe `.cyber-header`.

#### Stan to nie wariant

Przełącznik niezależny od układu (np. przyklejony header) dostaje **własną klasę
obok** modyfikatora wariantu, nigdy kolejną jego wartość:

```html
<header class="cyber-header cyber-header--default cyber-header--sticky">
```

Dzięki temu obie osie mnożą się swobodnie, zamiast wymuszać nazwy w rodzaju
`--centered-sticky`, a potem `--centered-sticky-transparent`.

Każdy nowy przełącznik wizualny trzeba **jawnie zaklasyfikować przed
implementacją**: wariant (wyklucza się z pozostałymi wariantami) albo stan
(łączy się z każdym wariantem).

## 21. Lokalizacja głównego menu

Menu główne ma w tym projekcie **jedną, docelową lokalizację: `primary`**
(etykieta „Menu glowne"), zarejestrowaną w `inc/setup.php` przez `register_nav_menus()`.
Stała `CYBER_HEADER_MENU_LOCATION` w `inc/header.php` trzyma ten slug w jednym miejscu.

Kolejne moduły **nie rejestrują nowej lokalizacji** dla menu głównego i nie tworzą
lokalizacji konkurencyjnej „bo tak wygodniej w tym widoku" — sięgają po `primary`.
Druga zarejestrowana lokalizacja to `footer` i dotyczy wyłącznie stopki.

Nowa lokalizacja menu (np. menu boczne w panelu klienta) wymaga takiego samego
jawnego uzasadnienia jak nowy breakpoint (sekcja 18) — inaczej redaktor dostaje
listę lokalizacji, z których połowa nic nie robi.

## 22. Pola zarezerwowane na przyszłość

Poniższe moduły to **wyłącznie warstwa danych** — świadomie bez logiki frontendowej.
Ich brak wykorzystania na danym etapie jest zamierzony, nie jest błędem ani
zaniedbaniem.

**Nie usuwać tych pól przy porządkowaniu ani refaktoryzacji jako rzekomo
„nieużywanych".**

Przy planowaniu kolejnych modułów sprawdź, czy dany moduł powinien konsumować te pola
przez `cyber_get_option()`, zamiast tworzyć nowe, równoległe pola dla tych samych
danych.

### Kontakt

`cyber_contact_*`, zakładka Global Options → Kontakt.

**Wykorzystywane od modułu Top Header:** `cyber_contact_phone`, `cyber_contact_email`.

**Nadal zarezerwowane:** `cyber_contact_address`, `cyber_contact_hours`,
`cyber_contact_nip`, `cyber_contact_krs`, `cyber_contact_regon`.
Przewidywane zastosowania: **stopka**, strona kontaktowa, dane strukturalne
Schema.org/LocalBusiness, możliwe formularze.

### Social Media

`cyber_social_*`, zakładka Global Options → Social Media. Adresy URL profili
społecznościowych (Facebook, Instagram, YouTube, X, LinkedIn, TikTok).

**Wykorzystywane od modułu Top Header** — wszystkie sześć pól. Nadal przewidywane:
**stopka** i dane strukturalne Schema.org (`sameAs`); oba mają czytać te same pola,
nie tworzyć własnych.

### Kolumny 2 i 3 stopki

Kolumny 2 i 3 modułu Footer (`template-parts/footer/footer.php`) są świadomie puste,
bez pól ACF — zarezerwowane pod przyszłą zawartość (np. menu stopki, newsletter).

Przy planowaniu kolejnych modułów sprawdź najpierw, czy pasują właśnie tam, zamiast
tworzyć nową sekcję w innym miejscu strony.

### Komponent ikon social media

Ikony social media renderowane są przez wspólny komponent
`template-parts/components/social-icons.php`, używany zarówno przez **Top Header**
(z respektowaniem wyłączników `cyber_topheader_show_*`), jak i **Footer**
(bez wyłączników — liczy się tylko czy pole URL jest wypełnione).

Kolejne miejsca potrzebujące tej samej listy ikon mają **reużywać ten komponent**,
nie duplikować pętli.

### Ikony

Ikony są **własnymi, inline SVG** — jeden rejestr `cyber_icons()` w `inc/helpers.php`,
odczytywany przez `cyber_get_icon()` (dowolna ikona) i `cyber_get_social_icon()`
(wyłącznie platformy społecznościowe). Nie używamy biblioteki zewnętrznej
(np. Font Awesome) ani fontu ikon.

Kolejne miejsca w projekcie potrzebujące ikon (np. stopka) mają **reużywać te same
funkcje**, nie duplikować SVG. Nowa ikona = nowy wpis w `cyber_icons()`, nie nowy
plik ani nowa zależność.
