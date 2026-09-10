# ACF Schema — Cyber Framework

> Ten plik jest źródłem prawdy dla wszystkich pól ACF w projekcie — zarówno dla
> człowieka, jak i dla Claude. **Każdy nowy moduł, grupa pól czy layout musi zostać
> tu opisany w tym samym commicie, w którym powstał w ACF.** Docelowo ten plik
> powinien być generowany/aktualizowany automatycznie na podstawie `acf-json/`
> (patrz sekcja "Utrzymanie" na końcu pliku).

Ostatnia aktualizacja: 2026-09-10
Moduły: **Global Options** — zakładki „Główne ustawienia strony”, „Ustawienia czcionki”, „Header Desktop”, „Header Mobile”, „Przyciski”, „Kolory”, „Kontakt”, „Social Media” i „Top Header”

---

## Wymaganie wstępne: rejestracja Options Page

ACF PRO nie pozwala utworzyć Options Page przez panel administracyjny — wymaga to
rejestracji w kodzie. Bez tego kroku nie da się ustawić lokalizacji grupy pól na
"Options Page equals ...".

```php
// inc/options.php
if ( function_exists( 'acf_add_options_page' ) ) {
    acf_add_options_page( array(
        'page_title' => 'Ustawienia Cyber Framework',
        'menu_title' => 'Cyber Framework',
        'menu_slug'  => 'cyber-settings',
        'capability' => 'manage_options',
        'redirect'   => false,
        'icon_url'   => 'dashicons-admin-customizer',
    ) );
}
```

---

## Field Group: Global Options

| | |
|---|---|
| Nazwa grupy | `Cyber Framework — Global Options` |
| Klucz grupy | `group_global_options` |
| Lokalizacja | Options Page equals **Ustawienia Cyber Framework** |
| Plik Local JSON | `acf-json/group_global_options.json` (zapisuje się automatycznie po pierwszym zapisie grupy w adminie ACF, jeśli katalog `acf-json/` istnieje i jest zapisywalny) |

### Zakładka: „Główne ustawienia strony”

Cel: kontrola maksymalnej szerokości kontenera strony na desktopie oraz responsywnych
marginesów bocznych. **Marginesy obowiązują niezależnie od wybranej opcji szerokości**
(100/80/60%) — to jeden, wspólny harmonogram breakpointów. Ustawienia dotyczą tylko
desktopu; tablet i mobile nie korzystają z pól `_80` / `_60` / `_100`.

Struktura wzorowana na istniejącym wdrożeniu (screenshot referencyjny): jedna grupa
pól `Ustawienia`, wiele zakładek. Ta zakładka jest pierwszą i jedyną realizowaną
w module 1 — pozostałe (Czcionka i przyciski, Nawigacja, Kolory, Kontakt, Stopka,
SEO itd.) to kolejne, nieplanowane jeszcze moduły.

**Sub-sekcja: Szerokość główna**

| Field Label | Field Name | Typ ACF | Domyślna wartość | Przeznaczenie |
|---|---|---|---|---|
| Szerokość główna | `cyber_page_width_type` | **Select** | `80` | Wybór maksymalnej szerokości kontenera na desktop. Instrukcja w polu: "Proszę wybrać szerokość główną strony." |

**Choices dla `cyber_page_width_type`:**
```
100 : Szerokość strony 100% ekranu
80  : Szerokość strony 80% ekranu
60  : Szerokość strony 60% ekranu
```

**Sub-sekcja: Szerokość strony** (instrukcja sekcji: "Ustaw szerokość w px")

| Field Label | Field Name | Typ ACF | Domyślna wartość | Append | Required | Przeznaczenie |
|---|---|---|---|---|---|---|
| Szerokość strony 60% | `cyber_page_width_60` | Number | `1150` | px | Tak | Wartość w px używana, gdy `cyber_page_width_type` = `60`. |
| Szerokość strony 80% | `cyber_page_width_80` | Number | `1500` | px | Tak | Wartość w px używana, gdy `cyber_page_width_type` = `80`. |
| Szerokość strony 100% | `cyber_page_width_100` | Number | *(puste)* | px | Nie | **Opcjonalny cap** dla opcji 100%. Instrukcja w polu: "Pozostaw puste, aby strona była na 100% szerokości przeglądarki." Jeśli puste → kontener = 100vw. Jeśli wypełnione → kontener = min(100vw, ta wartość) — przydatne jako zabezpieczenie na bardzo szerokich ekranach. |

**Sub-sekcja: Odstępy** (instrukcja sekcji: "Marginesy strony")

| Field Label | Field Name | Typ ACF | Domyślna wartość | Append | Przeznaczenie |
|---|---|---|---|---|---|
| Margines (do 980px) | `cyber_page_margin_desktop` | Number | `40` | px | Margines boczny między maks. szerokością kontenera a 980px. |
| Margines (980px–767px) | `cyber_page_margin_tablet` | Number | `30` | px | Margines boczny w zakresie 980px–767px. |
| Margines (767px–479px) | `cyber_page_margin_mobile_l` | Number | `30` | px | Margines boczny w zakresie 767px–479px. |
| Margines (poniżej 479px) | `cyber_page_margin_mobile_s` | Number | `20` | px | Margines boczny poniżej 479px. |

> **Potwierdzone:** `cyber_page_margin_tablet` i `cyber_page_margin_mobile_l` mają
> celowo tę samą wartość domyślną (30px) — nie jest to literówka (potwierdzone
> przez autora projektu).
>
> **Potwierdzone:** wybrano 4-polowy schemat breakpointów (980/767/479) zamiast
> bardziej granularnego wariantu ze screenshota referencyjnego (1600/1500/1366/
> /1024/768/479) — ta druga wersja **nie** jest używana w cyber-framework.

> **Decyzja projektowa:** to ustawienie jest **globalne dla całej witryny**, nie
> per-page. Zmiana wartości na stronie ustawień zmienia szerokość wszystkich stron
> na desktopie jednocześnie. W obecnym module **nie ma** mechanizmu nadpisania
> szerokości dla pojedynczej podstrony. Jeśli w przyszłości pojawi się potrzeba
> per-page override, wymaga to osobnego pola na poziomie Page (np. w grupie
> `group_page_settings` z warunkiem "jeśli puste, dziedzicz z Global Options") —
> to osobny, nieplanowany na razie moduł i nie należy go zakładać ani wdrażać
> bez wyraźnego zlecenia.

### Wykorzystanie w kodzie

Zaimplementowane — patrz „Stan: generowanie CSS” na końcu tego dokumentu.

### Zakładka: „Ustawienia czcionki”

Cel: jedno miejsce sterujące całą typografią motywu — wielkościami, skalowaniem
responsywnym i krojami. Zakładka realizuje **wariant B** z CLAUDE.md sekcja 19:
konkretne wartości istnieją wyłącznie dla desktopu, a mniejsze ekrany dostają je
przeliczone przez jedną skalę procentową na breakpoint.

**Sekcja: Wielkości czcionek (Desktop)**

Wszystkie pola: typ **Number**, append `px`, required, zakres **8–200 px**.
Obowiązują powyżej 980px; niżej przelicza je sekcja „Skalowanie responsywne”.

| Field Label | Field Name | Typ | Default | Przeznaczenie |
|---|---|---|---|---|
| Nagłówek H1 | `cyber_font_size_h1` | Number | `48` | `font-size` dla `h1`. |
| Nagłówek H2 | `cyber_font_size_h2` | Number | `40` | `font-size` dla `h2`. |
| Nagłówek H3 | `cyber_font_size_h3` | Number | `32` | `font-size` dla `h3`. |
| Nagłówek H4 | `cyber_font_size_h4` | Number | `26` | `font-size` dla `h4`. |
| Nagłówek H5 | `cyber_font_size_h5` | Number | `22` | `font-size` dla `h5`. |
| Nagłówek H6 | `cyber_font_size_h6` | Number | `18` | `font-size` dla `h6`. |
| Overtitle 1 | `cyber_font_size_overtitle_1` | Number | `16` | `font-size` dla klasy `.cyber-overtitle`. |
| Overtitle 2 | `cyber_font_size_overtitle_2` | Number | `14` | `font-size` dla klasy `.cyber-overtitle--secondary`. |
| Tekst (p, span, ul, li) | `cyber_font_size_text` | Number | `16` | `font-size` dla `body`, `p`, `span`, `ul`, `li`. |
| Linki (a) | `cyber_font_size_links` | Number | `16` | `font-size` dla `a` poza nagłówkami. |

**Sekcja: Skalowanie responsywne**

Wszystkie pola: typ **Number**, append `%`, required, zakres **10–200 %**.
Breakpointy zgodne z CLAUDE.md sekcja 18.

| Field Label | Field Name | Typ | Default | Przeznaczenie |
|---|---|---|---|---|
| Tablet (980–767px) | `cyber_font_scale_tablet` | Number | `90` | Skala WSZYSTKICH wielkości desktopowych w `@media (max-width: 980px)`. |
| Mobile (767–479px) | `cyber_font_scale_mobile` | Number | `80` | To samo w `@media (max-width: 767px)`. |
| Mobile small (poniżej 479px) | `cyber_font_scale_mobile_small` | Number | `70` | To samo w `@media (max-width: 479px)`. |

**Sekcja: Czcionki**

Oba pola: typ **Select**, required, `allow_null: 0`. Wartością pola jest **gotowy
stack CSS**, wstawiany wprost jako `font-family` — nie ma pośredniej mapy slug → stack.
Zestawy są dostępne lokalnie w systemie użytkownika; motyw **nie doładowuje plików
czcionek z zewnątrz** (CLAUDE.md sekcja 2 i 10).

| Field Label | Field Name | Typ | Default | Przeznaczenie |
|---|---|---|---|---|
| Czcionka nagłówków | `cyber_font_family_headings` | Select | stack systemowy | `font-family` dla `h1`–`h6`, `.cyber-overtitle`, `.cyber-overtitle--secondary`. |
| Czcionka tekstu | `cyber_font_family_text` | Select | stack systemowy | `font-family` dla `body`, `p`, `span`, `a`, `ul`, `li`. |

**Choices (identyczne dla obu pól):**

```
system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif : Systemowa — bezszeryfowa (domyślna)
Georgia, "Times New Roman", Times, serif                        : Georgia — szeryfowa
"Helvetica Neue", Helvetica, Arial, sans-serif                  : Helvetica / Arial — bezszeryfowa
```

> **Uwaga przy dodawaniu kroju.** Lista jest zamknięta w dwóch miejscach naraz:
> w `acf-json/group_global_options.json` (panel) i w `cyber_option_schema()`
> (walidacja przy odczycie). Stack dodany tylko w JSON zostanie odrzucony przez
> `cyber_get_option()` i podmieniony na wartość domyślną.

**Sekcja: Grubość czcionki**

Wszystkie pola: typ **Select**, required, `allow_null: 0`. Wartością pola jest
liczba wstawiana wprost jako `font-weight`.

| Field Label | Field Name | Typ | Default | Przeznaczenie |
|---|---|---|---|---|
| Grubość nagłówków | `cyber_font_weight_headings` | Select | `700` | `font-weight` dla `h1`–`h6`. |
| Grubość overtitle | `cyber_font_weight_overtitle` | Select | `600` | `font-weight` dla `.cyber-overtitle` i `.cyber-overtitle--secondary`. |
| Grubość tekstu | `cyber_font_weight_text` | Select | `400` | `font-weight` dla `body`, `p`, `span`, `ul`, `li`. |
| Grubość linków | `cyber_font_weight_links` | Select | `400` | `font-weight` dla `a` poza nagłówkami. |

**Choices (identyczne dla wszystkich czterech pól):**

```
300 : Light (300)
400 : Regular (400)
500 : Medium (500)
600 : SemiBold (600)
700 : Bold (700)
800 : ExtraBold (800)
```

> ### Grubość NIE podlega skalowaniu responsywnemu
>
> W odróżnieniu od `font-size`, grubość ma **jedną wartość dla wszystkich urządzeń**.
> Nie ma pól `cyber_font_weight_*_scale_tablet` ani odpowiedników dla pozostałych
> breakpointów, a `cyber_font_css()` wypisuje zmienne grubości **wyłącznie w bloku
> bazowym `:root`** — nie powtarzają się w żadnym `@media`. Uzasadnienie i status
> tego wyjątku: CLAUDE.md sekcja 19.

> **Uwaga o dostępnych grubościach.** `Georgia` i `Arial` realnie zawierają tylko
> Regular (400) i Bold (700). Wybranie 300, 500, 600 lub 800 dla tych krojów nie
> daje osobnego rysunku pisma — przeglądarka zaokrągli do najbliższej dostępnej
> grubości albo wygeneruje wariant syntetyczny. Pełny zakres ma praktycznie tylko
> stack systemowy. Pole nie blokuje takiego wyboru, bo to kwestia decyzji
> projektowej, a nie poprawności danych.

### Zakładka: „Header Desktop”

Cel: pełna kontrola nad wyglądem nagłówka na desktopie — logo, odstępy kontenera,
menu główne i podmenu. Widok mobilny (hamburger) **nie jest** częścią tego modułu.

**Sekcja: Logo**

| Field Label | Field Name | Typ | Return format | Default | Przeznaczenie |
|---|---|---|---|---|---|
| Logo | `cyber_header_logo` | Image | **`url`** | *(puste)* | Obraz linkowany do strony głównej. Puste = w headerze pojawia się tekstowa nazwa witryny. |

> **Precedens dla pól Image.** To pierwsze pole obrazu w projekcie. Przyjęty
> return format to **`url`** — pole zwraca sam adres pliku, a `cyber_get_option()`
> waliduje go typem `url` (`esc_url_raw`, pusta wartość jest dozwolona i znacząca).
> Kolejne pola Image trzymają się tego formatu, chyba że konkretny przypadek
> wymaga rozmiarów lub `srcset` — wtedy zmiana wymaga jawnej decyzji, bo dotyczy
> całej warstwy dostępu do obrazów.
>
> **Koszt tej decyzji:** mając sam URL, motyw nie zna wymiarów pliku, więc znacznik
> `<img>` nie ma `width`/`height` ani `srcset`. Przy dużym logo oznacza to możliwy
> CLS. Format `id` + `wp_get_attachment_image()` rozwiązałby to jednym wywołaniem —
> jeśli logo okaże się problemem wydajnościowym, to jest miejsce do zmiany.

**Sekcja: Układ i odstępy**

| Field Label | Field Name | Typ | Default | Przeznaczenie |
|---|---|---|---|---|
| Wyrównanie menu | `cyber_header_menu_alignment` | Select | `right` | Pozycja bloku menu w przestrzeni obok logo. Wartości zgodne z CLAUDE.md sekcja 20. |
| Padding górny | `cyber_header_padding_top` | Number (0–200 px) | `24` | `padding-top` na `.cyber-header__inner`. |
| Padding dolny | `cyber_header_padding_bottom` | Number (0–200 px) | `24` | `padding-bottom` na `.cyber-header__inner`. |

> **Padding poziomy nie ma pola — świadomie.** Header renderuje się wewnątrz
> `.cyber-container`, więc dziedziczy `--cyber-container-margin` z zakładki
> „Główne ustawienia strony”. Dzięki temu logo trzyma jedną pionową linię z treścią
> strony na każdym breakpoincie. Osobne pole pozwoliłoby te dwie wartości rozjechać,
> co w praktyce zawsze wygląda na błąd. Zmiana tego założenia wymaga decyzji,
> bo dotyczy wyrównania całej witryny, nie samego nagłówka.

**Sekcja: Menu główne**

Dotyczy pozycji pierwszego poziomu w menu przypisanym do lokalizacji `primary`
(CLAUDE.md sekcja 21).

| Field Label | Field Name | Typ | Default | Przeznaczenie |
|---|---|---|---|---|
| Odstęp między pozycjami | `cyber_header_menu_item_gap` | Number (0–200 px) | `32` | `gap` na `.cyber-menu` i na `.cyber-header__inner`. |
| Padding linku | `cyber_header_menu_link_padding` | Number (0–100 px) | `8` | `padding` na `.cyber-menu a` — powiększa obszar klikalny. |
| Wielkość czcionki | `cyber_header_menu_font_size` | Number (8–100 px) | `16` | `font-size` na `.cyber-menu a`. |
| Grubość czcionki | `cyber_header_menu_font_weight` | Select | `500` | `font-weight`. Choices reużyte z modułu „Ustawienia czcionki” (300–800). |
| Kolor linku | `cyber_header_menu_color` | Color Picker | `#1a1a1a` | Stan domyślny. |
| Kolor po najechaniu | `cyber_header_menu_color_hover` | Color Picker | `#0057ff` | `:hover` oraz `:focus-visible`. |
| Kolor aktywnej strony | `cyber_header_menu_color_active` | Color Picker | `#0057ff` | `.current-menu-item > a` i `.current-menu-ancestor > a`. |
| Wskaźnik podmenu | `cyber_header_submenu_indicator` | True/False (toggle) | `true` | Strzałka przy pozycji, która ma podmenu. Włącza modyfikator `.cyber-menu--with-indicator`; strzałka to `::after` na `.menu-item-has-children > a`, kolor z `currentColor`. Pole leży w sekcji menu głównego, bo dotyczy pozycji pierwszego poziomu, mimo `submenu` w nazwie. |

**Sekcja: Podmenu**

| Field Label | Field Name | Typ | Default | Przeznaczenie |
|---|---|---|---|---|
| Wyrównanie podmenu | `cyber_header_submenu_alignment` | Select | `left` | Modyfikator `.cyber-submenu--*`, nakładany filtrem `nav_menu_submenu_css_class`. |
| Odstęp między pozycjami | `cyber_header_submenu_item_gap` | Number (0–200 px) | `0` | `gap` na `.cyber-submenu`. |
| Padding linku | `cyber_header_submenu_link_padding` | Number (0–100 px) | `10` | `padding` linku oraz `padding-block` całej listy. |
| Wielkość czcionki | `cyber_header_submenu_font_size` | Number (8–100 px) | `15` | `font-size`. |
| Grubość czcionki | `cyber_header_submenu_font_weight` | Select | `400` | `font-weight`, te same choices co wyżej. |
| Kolor linku | `cyber_header_submenu_color` | Color Picker | `#1a1a1a` | Stan domyślny. |
| Kolor po najechaniu | `cyber_header_submenu_color_hover` | Color Picker | `#0057ff` | `:hover` oraz `:focus-visible`. |
| Kolor aktywnej strony | `cyber_header_submenu_color_active` | Color Picker | `#0057ff` | `.current-menu-item > a`. |

**Choices wyrównania (oba pola Select, CLAUDE.md sekcja 20):**

```
left   : Do lewej
center : Do środka
right  : Do prawej
```

> **Wartości bez pola ACF.** Trzy rzeczy w headerze są zaszyte w `assets/css/main.css`
> jako świadome decyzje motywu, nie konfiguracja: maksymalna wysokość logo (`60px`),
> tło podmenu (`#fff`) i minimalna szerokość podmenu (`200px`). Nie mają pól,
> bo nie było ich w specyfikacji modułu — jeśli mają być edytowalne, to trzy kolejne
> pola (Number, Color Picker, Number) i trzy wpisy w `cyber_header_css_map()`.
> Tło podmenu jest z tej trójki najpilniejsze: bez niego lista rozwijana byłaby
> nieczytelna na tle treści.

### Zakładka: „Header Mobile”

Cel: próg, poniżej którego menu desktopowe zastępuje hamburger. **Jedno pole** —
menu mobilne dziedziczy kolory i typografię z zakładki „Header Desktop”
(`cyber_header_menu_color`, `cyber_header_menu_font_size`, …), żeby te same
ustawienia nie żyły w dwóch miejscach i nie rozjeżdżały się przy edycji.

| Field Label | Field Name | Typ | Default | Przeznaczenie |
|---|---|---|---|---|
| Próg menu mobilnego | `cyber_header_mobile_breakpoint` | Number (320–2000 px) | `980` | Granica `max-width` bloku `@media`, w którym menu desktopowe znika, a pojawia się hamburger. |

> ### To pole jest świadomym wyjątkiem od kanonicznych breakpointów
>
> **Nie „poprawiać" go do jednej z wartości z CLAUDE.md sekcja 18.** Próg
> przełączania header desktop/mobile ma być swobodnie konfigurowalny przez
> administratora — moment, w którym nawigacja przestaje się mieścić w jednej linii,
> zależy od liczby i długości pozycji menu, a nie od systemowej siatki layoutu.
> Zmiana z 980px na np. 767px jest przewidzianym scenariuszem użycia, nie błędem.
>
> Konsekwencja techniczna: `cyber_breakpoints()` **nie zna** tego progu, a moduł
> Header Mobile nie korzysta z tej funkcji. To jedyne takie miejsce w projekcie.

**Dlaczego cały blok `@media` powstaje w PHP.** Media query nie przyjmuje `var()`
jako wartości granicznej w przeglądarkach objętych wsparciem, więc próg nie może
być zmienną CSS jak reszta wartości w projekcie. `cyber_header_mobile_css()`
w `inc/enqueue.php` generuje więc komplet reguł jako gotowy string, wstrzykując
liczbę wprost. To jedyny fragment CSS generowany w całości — pozostałe moduły
wypisują wyłącznie wartości zmiennych.

### Zakładka: „Przyciski”

Trzy rozmiary — `large`, `medium`, `small` — po **8 pól każdy, razem 24**.
Nazwa rozmiaru jest jednocześnie modyfikatorem klasy (`.btn-large`), członem nazwy
pola (`cyber_btn_large_*`) i członem nazwy zmiennej (`--cyber-btn-large-*`).

**Choices `font_weight` są tymi samymi wartościami co w module „Ustawienia czcionki”.**
Po stronie PHP lista istnieje raz, w `cyber_font_weight_choices()` (`inc/helpers.php`),
i korzystają z niej pola typografii, nagłówka i przycisków. Po stronie panelu
te same wartości powtarza `acf-json/` — dodanie grubości wymaga zmiany w obu miejscach.

**Rozmiar `large` → klasa `.btn-large`**

| Field Label | Field Name | Typ | Default | Zmienna CSS |
|---|---|---|---|---|
| Large — padding góra/dół | `cyber_btn_large_padding_y` | Number (4–120 px) | `18` | `--cyber-btn-large-padding-y` |
| Large — padding lewo/prawo | `cyber_btn_large_padding_x` | Number (4–200 px) | `40` | `--cyber-btn-large-padding-x` |
| Large — wielkość czcionki | `cyber_btn_large_font_size` | Number (8–100 px) | `18` | `--cyber-btn-large-font-size` |
| Large — grubość czcionki | `cyber_btn_large_font_weight` | Select | `600` | `--cyber-btn-large-font-weight` |
| Large — kolor tekstu | `cyber_btn_large_color` | Color Picker | `#ffffff` | `--cyber-btn-large-color` |
| Large — kolor tekstu po najechaniu | `cyber_btn_large_color_hover` | Color Picker | `#ffffff` | `--cyber-btn-large-color-hover` |
| Large — kolor tła | `cyber_btn_large_bg_color` | Color Picker | `#0057ff` | `--cyber-btn-large-bg-color` |
| Large — kolor tła po najechaniu | `cyber_btn_large_bg_color_hover` | Color Picker | `#0041c2` | `--cyber-btn-large-bg-color-hover` |

**Rozmiar `medium` → klasa `.btn-medium`**

| Field Label | Field Name | Typ | Default | Zmienna CSS |
|---|---|---|---|---|
| Medium — padding góra/dół | `cyber_btn_medium_padding_y` | Number (4–120 px) | `14` | `--cyber-btn-medium-padding-y` |
| Medium — padding lewo/prawo | `cyber_btn_medium_padding_x` | Number (4–200 px) | `32` | `--cyber-btn-medium-padding-x` |
| Medium — wielkość czcionki | `cyber_btn_medium_font_size` | Number (8–100 px) | `16` | `--cyber-btn-medium-font-size` |
| Medium — grubość czcionki | `cyber_btn_medium_font_weight` | Select | `600` | `--cyber-btn-medium-font-weight` |
| Medium — kolor tekstu | `cyber_btn_medium_color` | Color Picker | `#ffffff` | `--cyber-btn-medium-color` |
| Medium — kolor tekstu po najechaniu | `cyber_btn_medium_color_hover` | Color Picker | `#ffffff` | `--cyber-btn-medium-color-hover` |
| Medium — kolor tła | `cyber_btn_medium_bg_color` | Color Picker | `#0057ff` | `--cyber-btn-medium-bg-color` |
| Medium — kolor tła po najechaniu | `cyber_btn_medium_bg_color_hover` | Color Picker | `#0041c2` | `--cyber-btn-medium-bg-color-hover` |

**Rozmiar `small` → klasa `.btn-small`**

| Field Label | Field Name | Typ | Default | Zmienna CSS |
|---|---|---|---|---|
| Small — padding góra/dół | `cyber_btn_small_padding_y` | Number (4–120 px) | `10` | `--cyber-btn-small-padding-y` |
| Small — padding lewo/prawo | `cyber_btn_small_padding_x` | Number (4–200 px) | `24` | `--cyber-btn-small-padding-x` |
| Small — wielkość czcionki | `cyber_btn_small_font_size` | Number (8–100 px) | `14` | `--cyber-btn-small-font-size` |
| Small — grubość czcionki | `cyber_btn_small_font_weight` | Select | `600` | `--cyber-btn-small-font-weight` |
| Small — kolor tekstu | `cyber_btn_small_color` | Color Picker | `#ffffff` | `--cyber-btn-small-color` |
| Small — kolor tekstu po najechaniu | `cyber_btn_small_color_hover` | Color Picker | `#ffffff` | `--cyber-btn-small-color-hover` |
| Small — kolor tła | `cyber_btn_small_bg_color` | Color Picker | `#0057ff` | `--cyber-btn-small-bg-color` |
| Small — kolor tła po najechaniu | `cyber_btn_small_bg_color_hover` | Color Picker | `#0041c2` | `--cyber-btn-small-bg-color-hover` |

> **Decyzja: kolor należy do rozmiaru, nie do wariantu.** Każdy rozmiar niesie własny
> komplet czterech kolorów, bo w tym projekcie rozmiar przycisku odpowiada konkretnemu
> stylowi, a nie samej geometrii. **Konsekwencja:** nie da się zrobić dużego przycisku
> w kolorystyce małego — wariant kolorystyczny niezależny od rozmiaru (`primary`
> / `secondary` jako druga oś klas) byłby przebudową modułu, nie dołożeniem pola.
> Decyzja podjęta świadomie przy wdrożeniu; zmiana wymaga osobnego zlecenia.

> **Dlaczego `color_hover` istnieje.** Bez niego redaktor mógł ustawić jasne tło hover
> pod jasnym tekstem i przycisk stawał się nieczytelny, bez możliwości naprawy z panelu
> (CLAUDE.md sekcja 11). Domyślnie kolor hover równa się bazowemu, więc pole nic nie
> zmienia, dopóki ktoś świadomie go nie ruszy.

> **`border-radius` nie ma pola — celowo.** Projekt nie ma jeszcze ustalonego promienia
> zaokrąglenia w żadnym komponencie, więc `.btn` używa `border-radius: 0` jako wartości
> domyślnej. To brak decyzji, nie decyzja — gdy promień zostanie ustalony projektowo,
> jest to jedno pole Number i jedna zmienna.

### Komponent `cyber_button()`

| | |
|---|---|
| Funkcja | `cyber_button( array $args )` — `inc/components.php` |
| Widok | `template-parts/components/button.php` |
| Zwraca | nic — **wypisuje** znacznik (spójnie z `get_template_part()`, którego używa header) |

| Argument | Typ | Domyślnie | Znaczenie |
|---|---|---|---|
| `text` | string | `''` | Tekst przycisku. **Wymagany.** |
| `url` | string | `''` | Adres docelowy. **Wymagany.** |
| `size` | string | `'medium'` | `large`, `medium` albo `small`. |
| `target` | string | `''` | Np. `_blank`. Pomijany, gdy pusty. |
| `rel` | string | `''` | Pomijany, gdy pusty. |

Zachowanie warte zapamiętania:

- **Pusty `text` albo pusty `url` = brak znacznika.** W sekcjach ACF przycisk bywa
  polem opcjonalnym i widok nie powinien musieć tego sprawdzać u siebie.
- **Nieznany `size`** → `_doing_it_wrong()` i fallback na `medium`, żeby literówka
  nie zamieniła się w cicho niestylowany przycisk.
- **`target="_blank"` bez `rel`** → automatycznie `rel="noopener"`. Współczesne
  przeglądarki domyślają się tego same, ale nie polegamy na tym (CLAUDE.md sekcja 9).
- Komponent **nie przyjmuje** kolorów ani wartości w pikselach — wyłącznie nazwę
  rozmiaru. Wygląd należy do Global Options, nie do miejsca wywołania.

Przykład:

```php
cyber_button(
	array(
		'text' => 'Zobacz ofertę',
		'url'  => get_permalink( $page_id ),
		'size' => 'large',
	)
);
```

### Zakładka: „Kolory”

Zakładka zawiera **dwa różne wzorce** kolorów. Różnica jest praktyczna, nie kosmetyczna
— decyduje o tym, czy redaktor musi cokolwiek zrobić, żeby kolor zadziałał
(CLAUDE.md sekcja 5).

| Wzorzec | Jak działa | Co musi zrobić redaktor |
|---|---|---|
| **Semantyczny** | Kolor przypięty do tagu lub klasy komponentu, aplikowany automatycznie. | Nic — działa po zapisaniu pola. |
| **Narzędziowy (utility)** | Kolor przypięty do **stałej nazwy klasy CSS**. | Musi dodać tę klasę do elementu ręcznie. |

**Sekcja: Kolory semantyczne**

Wszystkie pola: **Color Picker**, bez przezroczystości.

| Field Label | Field Name | Default | Stosowany do | Zmienna CSS |
|---|---|---|---|---|
| Nagłówki h1–h6 | `cyber_color_headings` | `#111111` | `h1`–`h6`. **Bez overtitle** — mają własne pola. | `--cyber-color-headings` |
| Tekst | `cyber_color_text` | `#333333` | `body`, `p`, `span`, `ul`, `li` | `--cyber-color-text` |
| Overtitle 1 | `cyber_color_overtitle_1` | `#0057ff` | `.cyber-overtitle` | `--cyber-color-overtitle-1` |
| Overtitle 2 | `cyber_color_overtitle_2` | `#666666` | `.cyber-overtitle--secondary` | `--cyber-color-overtitle-2` |
| Linki | `cyber_color_links` | `#0057ff` | `a` — selektor bazowy, globalnie | `--cyber-color-links` |

> **Linki bez wykluczeń — i dlaczego to wystarcza.** Reguła `a { color: … }` ma
> specyficzność `(0,0,1)`, czyli najniższą możliwą. Linki menu (`.cyber-menu a`,
> `(0,1,1)`) i przyciski (`.btn-large`, `(0,1,0)`) mają własne reguły z modułów
> Header i Przyciski i wygrywają w kaskadzie same z siebie. Konstrukcje typu
> `a:not(.cyber-menu a)` byłyby nie tylko zbędne, ale i kruche — każdy nowy
> komponent z własnym kolorem linku wymagałby dopisania kolejnego wyjątku.
>
> Skutek uboczny warty świadomości: **link wewnątrz nagłówka dostaje kolor linku**,
> nie koloru nagłówka. Reguła dziedziczenia dla `h1 :is(a, span)` wyrównuje krój,
> rozmiar i grubość, ale celowo **nie** kolor — inaczej link w nagłówku przestałby
> wyglądać jak link.

**Sekcja: Kolory narzędziowe (utility)**

**Nazwy klas są ustalone w kodzie i nie da się ich zmienić z panelu.** Nie ma pola
do wpisania nazwy klasy — takie pole prowadziłoby do literówek i klas bez
odpowiadających im reguł CSS. ACF ustawia wyłącznie barwę.

| Field Label | Field Name | Default | Klasa CSS do wpisania | Efekt |
|---|---|---|---|---|
| Kolor tekstu po najechaniu | `cyber_color_hover` | `#0041c2` | `cyber-hover-color` | `color` na `:hover` i `:focus-visible` |
| Obramowanie 1 | `cyber_color_border_1` | `#e0e0e0` | `cyber-border-1` | `border: 1px solid …` |
| Obramowanie 2 | `cyber_color_border_2` | `#0057ff` | `cyber-border-2` | `border: 1px solid …` |
| Cień | `cyber_color_shadow` | `rgba(0, 0, 0, 0.12)` | `cyber-shadow` | `box-shadow: 0 4px 12px …` |
| Cień po najechaniu | `cyber_color_shadow_hover` | `rgba(0, 0, 0, 0.2)` | `cyber-shadow-hover` | ten sam `box-shadow` na `:hover` |

> **Co jest stałe, a co konfigurowalne.** Pole ACF steruje **wyłącznie barwą**.
> Grubość i styl obramowania (`1px solid`) oraz offset i rozmycie cienia
> (`0 4px 12px`) są zapisane w CSS i nie mają pól — świadomie, żeby zakładka
> nie spuchła do zestawu suwaków do wszystkiego.

> **Pola cieni mają włączoną przezroczystość** (`enable_opacity`), więc zwracają
> `rgba()`, a nie HEX. Cień bez kanału alfa jest w praktyce bezużyteczny — wygląda
> jak czarna plama pod elementem. Konsekwencja po stronie walidacji: te dwa pola
> używają typu **`color_alpha`** w `cyber_option_schema()`, który przepuszcza HEX
> **albo** `rgb()` / `rgba()` o ścisłym wzorcu. Pozostałe pola kolorystyczne
> zostają przy typie `color` (sam HEX).

Obie sekcje obsługuje jedna funkcja `cyber_colors_css()` — z punktu widzenia
generatora nie ma między nimi różnicy, obie to zmienne. Nazwa zmiennej powstaje
mechanicznie z nazwy pola (`color_overtitle_1` → `--cyber-color-overtitle-1`),
więc nie istnieje osobna mapa, która mogłaby się rozjechać z listą pól.

### Zakładka: „Kontakt”

> ### Pola przygotowane pod przyszłe wykorzystanie
>
> **Obecnie niepodłączone do żadnego widoku frontendowego.** Moduł nie ma zmiennych
> CSS, markupu ani wpisu w `cyber_print_inline_css()` — to wyłącznie warstwa danych.
> Brak wykorzystania jest zamierzony, nie jest niedopatrzeniem. Przewidywane
> zastosowania: stopka, strona kontaktowa, dane strukturalne Schema.org/LocalBusiness,
> ewentualne formularze. Szczegóły i zakaz usuwania przy refaktoryzacji:
> CLAUDE.md sekcja 22.

| Field Label | Field Name | Typ ACF | Walidacja | Przeznaczenie |
|---|---|---|---|---|
| Adres | `cyber_contact_address` | Textarea (3 wiersze) | — | Ulica, numer, kod, miasto. Wieloliniowy. |
| Godziny otwarcia | `cyber_contact_hours` | Textarea (4 wiersze) | — | Np. „Pon–Pt: 9:00–17:00”, każdy zakres w osobnej linii. |
| Telefon | `cyber_contact_phone` | **Text** | cyfry, spacje, myślniki; opcjonalny `+` na początku | Numer kontaktowy. |
| Email | `cyber_contact_email` | **Email** | format adresu — wbudowana walidacja ACF + `is_email()` przy odczycie | Adres kontaktowy. |
| NIP | `cyber_contact_nip` | **Text** (maxlength 10) | dokładnie 10 cyfr | Identyfikator podatkowy. |
| KRS | `cyber_contact_krs` | **Text** (maxlength 10) | dokładnie 10 cyfr | Numer w rejestrze sądowym. |
| REGON | `cyber_contact_regon` | **Text** (maxlength 14) | 9 albo 14 cyfr | Numer statystyczny. |

> **Dlaczego Text, a nie Number.** Typ Number zjadłby **zera wiodące** (REGON
> `012345678` stałby się `12345678`) oraz **znak `+`** w numerze telefonu.
> To dane identyfikacyjne, nie liczby do liczenia — nikt ich nie dodaje ani
> nie sortuje numerycznie.

**Walidacja działa w dwóch warstwach, z jednego źródła wzorców:**

| Warstwa | Gdzie | Co robi |
|---|---|---|
| Zapis w panelu | `cyber_contact_validate_value()` — `inc/contact.php`, filtr `acf/validate_value/name=…` | Blokuje zapis i pokazuje redaktorowi komunikat, np. „NIP musi mieć dokładnie 10 cyfr”. |
| Odczyt w kodzie | `cyber_validate_option_value()` — typ `text` z kluczem `pattern` | Wartość niepasująca do wzorca wraca jako pusta. |

Same wzorce żyją **raz**, w `cyber_contact_patterns()` (`inc/helpers.php`). Rozjechanie
się tych dwóch list oznaczałoby, że redaktor zapisuje wartość, której motyw potem
nie przyjmuje — a komunikat o błędzie nigdy by się nie pojawił.

Wszystkie pola są **opcjonalne i `nullable`**: pusta wartość jest poprawna i znacząca
(witryna, która nie podaje KRS, to normalny przypadek), więc `cyber_get_option()`
zwraca dla nich `''`, a nie wartość domyślną.

Nowe typy w schemacie wprowadzone przez ten moduł: **`text`** (z opcjonalnym kluczem
`pattern`), **`textarea`** (`sanitize_textarea_field()`, zachowuje znaki nowej linii)
i **`email`** (`sanitize_email()` + `is_email()`).

### Zakładka: „Social Media”

> ### Pola przygotowane pod przyszłe wykorzystanie
>
> **Obecnie niepodłączone do żadnego widoku frontendowego.** Tak jak zakładka
> „Kontakt”: brak zmiennych CSS, markupu i wpisu w `cyber_print_inline_css()`.
> Przewidywane zastosowania: stopka, top header, dane strukturalne Schema.org
> (`sameAs`). Zakaz usuwania przy refaktoryzacji: CLAUDE.md sekcja 22.

Wszystkie pola: typ ACF **URL**, opcjonalne, `nullable`. Puste pole znaczy
„firma nie prowadzi tego kanału”, a nie „brak konfiguracji” — dlatego
`cyber_get_option()` zwraca dla nich `''`, nie wartość domyślną.

| Field Label | Field Name | Typ ACF | Placeholder w panelu |
|---|---|---|---|
| Facebook | `cyber_social_facebook` | URL | `https://www.facebook.com/twojafirma` |
| Instagram | `cyber_social_instagram` | URL | `https://www.instagram.com/twojafirma` |
| YouTube | `cyber_social_youtube` | URL | `https://www.youtube.com/@twojafirma` |
| X (Twitter) | `cyber_social_x` | URL | `https://x.com/twojafirma` |
| LinkedIn | `cyber_social_linkedin` | URL | `https://www.linkedin.com/company/twojafirma` |
| TikTok | `cyber_social_tiktok` | URL | `https://www.tiktok.com/@twojafirma` |

**Walidacja bez nowego kodu.** Typ ACF URL sprawdza format w panelu, a po stronie
odczytu pola używają istniejącego typu schematu **`url`** — tego samego, który
obsługuje logo nagłówka (`esc_url_raw()`). Adres bez protokołu albo z protokołem
spoza białej listy WordPressa (np. `javascript:`) wraca jako pusty. Moduł nie ma
własnego pliku w `inc/` — nie było czego dokładać.

> **Nazwa pola vs nazwa serwisu.** `cyber_social_x` celowo nie nazywa się
> `cyber_social_twitter`: nazwa pola staje się kluczem w `wp_options` i zmiana jej
> później oznacza utratę zapisanej wartości. Etykieta w panelu brzmi „X (Twitter)”,
> żeby redaktor wiedział, o co chodzi, niezależnie od tego, jak serwis nazywa się
> w danym roku.

### Zakładka: „Top Header”

Cienki pasek nad nagłówkiem. **Pierwszy moduł, który konsumuje pola z zakładek
„Kontakt" i „Social Media"** — nie ma własnych pól na telefon, email ani adresy
profili, tylko czyta istniejące przez `cyber_get_option()` (CLAUDE.md sekcja 22).

**Sekcja: Styl paska**

| Field Label | Field Name | Typ | Default | Zmienna CSS |
|---|---|---|---|---|
| Tło Top Header | `cyber_topheader_bg_color` | Color Picker | `#111111` | `--cyber-topheader-bg` |
| Kolor tekstu i ikon | `cyber_topheader_font_color` | Color Picker | `#ffffff` | `--cyber-topheader-color` |
| Rozmiar czcionki | `cyber_topheader_font_size` | Number (8–40 px) | `14` | `--cyber-topheader-font-size` |

> **Ikony nie mają własnego pola koloru.** SVG używa `fill="currentColor"`, więc
> dziedziczy `--cyber-topheader-color` razem z tekstem. Osobne pole pozwoliłoby
> rozjechać ikony z tekstem na tym samym pasku, co zawsze wygląda na błąd.

**Sekcja: Co pokazać na pasku**

Wszystkie pola: **True/False (toggle)**, domyślnie **włączone**.

| Field Name | Steruje | Pole źródłowe |
|---|---|---|
| `cyber_topheader_show_phone` | telefonem | `cyber_contact_phone` (zakładka „Kontakt") |
| `cyber_topheader_show_email` | emailem | `cyber_contact_email` (zakładka „Kontakt") |
| `cyber_topheader_show_facebook` | ikoną Facebooka | `cyber_social_facebook` |
| `cyber_topheader_show_instagram` | ikoną Instagrama | `cyber_social_instagram` |
| `cyber_topheader_show_youtube` | ikoną YouTube | `cyber_social_youtube` |
| `cyber_topheader_show_x` | ikoną X | `cyber_social_x` |
| `cyber_topheader_show_linkedin` | ikoną LinkedIn | `cyber_social_linkedin` |
| `cyber_topheader_show_tiktok` | ikoną TikToka | `cyber_social_tiktok` |

> ### Logika widoczności to koniunkcja
>
> Element pojawia się na pasku **tylko wtedy, gdy oba warunki są spełnione**:
>
> ```
> przełącznik = true   ORAZ   pole źródłowe niepuste
> ```
>
> Przełącznik działa **niezależnie** od tego, czy pole danych jest wypełnione —
> to dwa osobne stany. Wyłączony przełącznik ukrywa element mimo wypełnionego pola;
> puste pole ukrywa go mimo włączonego przełącznika.
>
> **Zaimplementowane w PHP** (`cyber_top_header_data()` w `inc/header.php`),
> nie przez conditional logic ACF. ACF potrafiłby ukryć pole w panelu, ale to jest
> decyzja widoku, a nie kształtu danych — te same pola konsumuje w przyszłości
> stopka, z własnym zestawem przełączników.
>
> Gdy nie ma czego pokazać, **pasek nie renderuje się wcale** —
> `cyber_top_header_has_content()` sprawdza to przed `get_template_part()`.
> Pusty pasek byłby kolorową belką bez treści.

**Wartości stałe, bez pól ACF:** padding pionowy paska (`8px`), odstęp między
ikonami (`12px`), rozmiar ikony (`16px`) i przezroczystość na hover (`0.7`).
To proporcje paska, nie konfiguracja.

**Telefon w dwóch postaciach:** widoczny tekst zachowuje zapis redaktora
(`+48 500-600-700`), a `href` jest czyszczony do `tel:+48500600700`
(`preg_replace( '/[^0-9+]/', '', … )`) — inaczej część telefonów nie zadzwoni.

### Ikony social media

`cyber_get_social_icon( $platform )` w `inc/helpers.php` zwraca **inline SVG**
dla platform z `cyber_social_platforms()`. Bez biblioteki zewnętrznej, bez fontu
ikon, bez `<img>` (CLAUDE.md sekcja 2).

| | |
|---|---|
| viewBox | `0 0 20 20` — jednakowy dla wszystkich sześciu |
| Kolor | `fill="currentColor"` / `stroke="currentColor"`, zero barw w atrybutach |
| Dostępność | `aria-hidden="true"` i `focusable="false"` na `<svg>` — nazwa dostępna jest na linku (`aria-label`), nie na ikonie |
| Nieznana platforma | pusty string, bez błędu |

Kształty są **proste i jednokolorowe**, celowo nie są odwzorowaniem oficjalnych
logotypów w barwach marek — mają tworzyć spójną, lekką ikonografię paska.

### Zasada dostępu w kodzie

Widoki **nie** wywołują `get_field( $key, 'option' )` bezpośrednio. Zawsze przez
helper:

```php
cyber_get_option( 'page_width_type' ); // zwraca np. '80'
```

Helper definiowany w `inc/helpers.php`, wewnętrznie owija `get_field()` z options
context i miejscem na ewentualny cache.

---

## Odwzorowanie w kodzie — stan wdrożenia v0.1.0

Grupa pól jest już zapisana jako Local JSON: **`acf-json/group_global_options.json`**.
Nie trzeba jej klikać w UI — wystarczy Sync (patrz README, sekcja „Instalacja”).

### Klucze pól (field keys)

| Field Name | Field Key | Typ |
|---|---|---|
| — (zakładka) | `field_cyber_tab_page_main` | Tab |
| — (nagłówek sekcji) | `field_cyber_msg_width_main` | Message |
| `cyber_page_width_type` | `field_cyber_page_width_type` | Select |
| — (nagłówek sekcji) | `field_cyber_msg_width_px` | Message |
| `cyber_page_width_60` | `field_cyber_page_width_60` | Number |
| `cyber_page_width_80` | `field_cyber_page_width_80` | Number |
| `cyber_page_width_100` | `field_cyber_page_width_100` | Number |
| — (nagłówek sekcji) | `field_cyber_msg_margins` | Message |
| `cyber_page_margin_desktop` | `field_cyber_page_margin_desktop` | Number |
| `cyber_page_margin_tablet` | `field_cyber_page_margin_tablet` | Number |
| `cyber_page_margin_mobile_l` | `field_cyber_page_margin_mobile_l` | Number |
| `cyber_page_margin_mobile_s` | `field_cyber_page_margin_mobile_s` | Number |

Zakładka „Ustawienia czcionki” (klucz pola = `field_` + nazwa pola, bez wyjątków):

| Field Name | Field Key | Typ |
|---|---|---|
| — (zakładka) | `field_cyber_tab_font` | Tab |
| — (nagłówek sekcji) | `field_cyber_msg_font_sizes` | Message |
| `cyber_font_size_h1` … `cyber_font_size_h6` | `field_cyber_font_size_h1` … `_h6` | Number |
| `cyber_font_size_overtitle_1` | `field_cyber_font_size_overtitle_1` | Number |
| `cyber_font_size_overtitle_2` | `field_cyber_font_size_overtitle_2` | Number |
| `cyber_font_size_text` | `field_cyber_font_size_text` | Number |
| `cyber_font_size_links` | `field_cyber_font_size_links` | Number |
| — (nagłówek sekcji) | `field_cyber_msg_font_scale` | Message |
| `cyber_font_scale_tablet` | `field_cyber_font_scale_tablet` | Number |
| `cyber_font_scale_mobile` | `field_cyber_font_scale_mobile` | Number |
| `cyber_font_scale_mobile_small` | `field_cyber_font_scale_mobile_small` | Number |
| — (nagłówek sekcji) | `field_cyber_msg_font_family` | Message |
| `cyber_font_family_headings` | `field_cyber_font_family_headings` | Select |
| `cyber_font_family_text` | `field_cyber_font_family_text` | Select |
| — (nagłówek sekcji) | `field_cyber_msg_font_weight` | Message |
| `cyber_font_weight_headings` | `field_cyber_font_weight_headings` | Select |
| `cyber_font_weight_overtitle` | `field_cyber_font_weight_overtitle` | Select |
| `cyber_font_weight_text` | `field_cyber_font_weight_text` | Select |
| `cyber_font_weight_links` | `field_cyber_font_weight_links` | Select |

Zakładka „Header Desktop” (klucz pola = `field_` + nazwa pola):

| Field Name | Field Key | Typ |
|---|---|---|
| — (zakładka) | `field_cyber_tab_header` | Tab |
| — (nagłówki sekcji) | `field_cyber_msg_header_logo`, `_layout`, `_menu`, `_submenu` | Message |
| `cyber_header_logo` | `field_cyber_header_logo` | Image |
| `cyber_header_menu_alignment` | `field_cyber_header_menu_alignment` | Select |
| `cyber_header_padding_top` / `_bottom` | `field_cyber_header_padding_top` / `_bottom` | Number |
| `cyber_header_menu_item_gap` | `field_cyber_header_menu_item_gap` | Number |
| `cyber_header_menu_link_padding` | `field_cyber_header_menu_link_padding` | Number |
| `cyber_header_menu_font_size` | `field_cyber_header_menu_font_size` | Number |
| `cyber_header_menu_font_weight` | `field_cyber_header_menu_font_weight` | Select |
| `cyber_header_menu_color` / `_hover` / `_active` | `field_cyber_header_menu_color` / `_hover` / `_active` | Color Picker |
| `cyber_header_submenu_indicator` | `field_cyber_header_submenu_indicator` | True/False |
| `cyber_header_submenu_alignment` | `field_cyber_header_submenu_alignment` | Select |
| `cyber_header_submenu_item_gap` | `field_cyber_header_submenu_item_gap` | Number |
| `cyber_header_submenu_link_padding` | `field_cyber_header_submenu_link_padding` | Number |
| `cyber_header_submenu_font_size` | `field_cyber_header_submenu_font_size` | Number |
| `cyber_header_submenu_font_weight` | `field_cyber_header_submenu_font_weight` | Select |
| `cyber_header_submenu_color` / `_hover` / `_active` | `field_cyber_header_submenu_color` / `_hover` / `_active` | Color Picker |

Zakładka „Header Mobile”:

| Field Name | Field Key | Typ |
|---|---|---|
| — (zakładka) | `field_cyber_tab_header_mobile` | Tab |
| — (nagłówek sekcji) | `field_cyber_msg_header_mobile` | Message |
| `cyber_header_mobile_breakpoint` | `field_cyber_header_mobile_breakpoint` | Number |

### Założenia przyjęte przy wdrożeniu (do akceptacji lub zmiany)

Poniższe rzeczy nie były opisane w specyfikacji modułu. Zostały wdrożone jako
jawnie nazwane założenia — jeśli któreś jest niezgodne z zamysłem, zmiana jest
tania (JSON + `cyber_option_schema()` w `inc/helpers.php`).

1. **Sub-sekcje jako pola typu Message.** „Szerokość główna”, „Szerokość strony”
   i „Odstępy” to wizualne nagłówki sekcji w panelu, zrealizowane polami typu
   Message (bez `name`, więc nie zapisują niczego do `wp_options`). Instrukcje
   sekcji („Ustaw szerokość w px”, „Marginesy strony”) są treścią tych pól.
2. **Zakres wartości (`min` / `max`).** Specyfikacja nie podawała granic, a sekcja 9
   CLAUDE.md wymaga walidacji zakresu. Przyjęto:
   - szerokości (`cyber_page_width_60/80/100`): **320–4000 px**,
   - marginesy (`cyber_page_margin_*`): **0–200 px**.

   Te same granice obowiązują w dwóch miejscach: w polach ACF (walidacja w panelu)
   oraz w `cyber_option_schema()` (walidacja przy odczycie — wartość spoza zakresu
   jest odrzucana na rzecz wartości domyślnej).
3. **Brak conditional logic na polach szerokości.** Wszystkie trzy pola
   (`60` / `80` / `100`) są zawsze widoczne, niezależnie od wyboru
   w `cyber_page_width_type` — zgodnie z tabelami powyżej (`Required: Tak`
   dla 60 i 80 obowiązuje bezwarunkowo).
4. **Select jest `required`.** Pole ma wartość domyślną `80` i nie dopuszcza pustej
   wartości (`allow_null: 0`).

### Odczyt w kodzie

Helper przyjmuje klucz **bez** prefiksu `cyber_`:

```php
cyber_get_option( 'page_width_type' );      // '80' | '100' | '60'
cyber_get_option( 'page_width_80' );        // int, np. 1500
cyber_get_option( 'page_width_100' );       // int albo '' (brak limitu)
cyber_get_option( 'page_margin_mobile_s' ); // int, np. 20
```

Gwarancje `cyber_get_option()`:

- wartość spoza listy dozwolonych / spoza zakresu → wartość domyślna ze schematu,
- pole puste → wartość domyślna (wyjątek: `page_width_100`, gdzie pustka jest
  znacząca i zwracana jest pusta wartość `''`),
- brak aktywnego ACF PRO → wartości domyślne (motyw działa, traci konfigurowalność),
- nieznany klucz → `_doing_it_wrong()`, żeby literówka nie zamieniła się w ciche `null`.

### Stan: generowanie CSS

**Zaimplementowane.** Podejście zatwierdzone: inline `<style>` w `wp_head`
(nie plik generowany) — patrz CLAUDE.md sekcja 6, „Konwencja: ACF Options → CSS".

| | |
|---|---|
| Funkcje budujące CSS | `cyber_container_css()`, `cyber_font_css()`, `cyber_header_css()`, `cyber_header_mobile_css()`, `cyber_button_css()`, `cyber_colors_css()`, `cyber_top_header_css()` — `inc/enqueue.php` |
| Funkcja wypisująca | `cyber_print_inline_css()`, hook `wp_head` priorytet 20 |
| Znacznik w HTML | jeden `<style id="cyber-global-vars">` dla całego motywu |
| Breakpointy | `cyber_breakpoints()` — `inc/helpers.php` (CLAUDE.md sekcja 18) |
| Konsument | `assets/css/main.css` — żaden szablon PHP nie zawiera inline `style=""` |

Kolejne moduły dopisują własną funkcję budującą CSS i doklejają ją
w `cyber_print_inline_css()`. **Nie rejestrują własnego hooka** — motyw wypisuje
dokładnie jeden blok `<style>`.

Mapowanie pól na CSS:

- `cyber_page_width_type` = `60` / `80` → `--cyber-container-width` = wartość
  odpowiednio z `cyber_page_width_60` / `cyber_page_width_80` w px.
- `cyber_page_width_type` = `100` → jeśli `cyber_page_width_100` jest puste,
  `--cyber-container-width: 100%`; jeśli wypełnione, wartość w px działa jako cap
  (`.cyber-container` ma `width: 100%`, więc `max-width` daje efekt `min(100%, cap)`).
- Marginesy trafiają do `--cyber-container-margin`: wartość bazowa
  z `cyber_page_margin_desktop`, a następnie trzy `@media (max-width: …)`
  dla 980 / 767 / 479 px. Obowiązują niezależnie od wybranej szerokości.

**Przyjęte założenie (granice breakpointów).** Schemat opisuje zakresy słownie
(„980px–767px"), co nie rozstrzyga, do którego zakresu należy sama wartość graniczna.
Przyjęto granice domknięte od góry: `max-width: 980px` / `767px` / `479px`, czyli
dokładnie 980px korzysta już z marginesu tabletowego. Zmiana tej interpretacji to
edycja jednej tablicy `$breakpoints` w `cyber_container_css()`.

Statyczne wartości w `assets/css/main.css` zostają jako warstwa awaryjna (motyw bez
ACF PRO albo z wyłączonym hookiem nadal ma sensowny kontener).

#### Zmienne generowane przez moduł czcionek

| Zmienna CSS | Źródło | Skalowana? |
|---|---|---|
| `--cyber-font-family-headings` | `cyber_font_family_headings` | nie |
| `--cyber-font-family-text` | `cyber_font_family_text` | nie |
| `--cyber-font-weight-headings` | `cyber_font_weight_headings` | nie |
| `--cyber-font-weight-overtitle` | `cyber_font_weight_overtitle` | nie |
| `--cyber-font-weight-text` | `cyber_font_weight_text` | nie |
| `--cyber-font-weight-links` | `cyber_font_weight_links` | nie |
| `--cyber-font-size-h1` … `-h6` | `cyber_font_size_h1` … `_h6` | tak |
| `--cyber-font-size-overtitle-1` | `cyber_font_size_overtitle_1` | tak |
| `--cyber-font-size-overtitle-2` | `cyber_font_size_overtitle_2` | tak |
| `--cyber-font-size-text` | `cyber_font_size_text` | tak |
| `--cyber-font-size-links` | `cyber_font_size_links` | tak |

Podkreślenia w nazwie pola stają się myślnikami w nazwie zmiennej
(`cyber_font_size_overtitle_1` → `--cyber-font-size-overtitle-1`). Mapa siedzi
w `cyber_font_size_map()`.

#### Zmienne generowane przez moduł Header Desktop

Mapa pól na zmienne siedzi w `cyber_header_css_map()`. Żadna z tych wartości
**nie jest skalowana** przez breakpointy — moduł opisuje wyłącznie widok desktopowy.

| Zmienna CSS | Źródło |
|---|---|
| `--cyber-header-padding-top` / `-bottom` | `cyber_header_padding_top` / `_bottom` |
| `--cyber-header-menu-gap` | `cyber_header_menu_item_gap` |
| `--cyber-header-menu-link-padding` | `cyber_header_menu_link_padding` |
| `--cyber-header-menu-font-size` | `cyber_header_menu_font_size` |
| `--cyber-header-menu-font-weight` | `cyber_header_menu_font_weight` |
| `--cyber-header-menu-color` / `-hover` / `-active` | `cyber_header_menu_color` / `_hover` / `_active` |
| `--cyber-header-submenu-gap` | `cyber_header_submenu_item_gap` |
| `--cyber-header-submenu-link-padding` | `cyber_header_submenu_link_padding` |
| `--cyber-header-submenu-font-size` | `cyber_header_submenu_font_size` |
| `--cyber-header-submenu-font-weight` | `cyber_header_submenu_font_weight` |
| `--cyber-header-submenu-color` / `-hover` / `-active` | `cyber_header_submenu_color` / `_hover` / `_active` |

**Wyrównanie i wskaźnik podmenu nie są zmiennymi CSS.** `cyber_header_menu_alignment`,
`cyber_header_submenu_alignment` i `cyber_header_submenu_indicator`
trafiają do markupu jako modyfikator klasy
(`.cyber-menu--center`, `.cyber-submenu--right`) — uzasadnienie w CLAUDE.md sekcja 20.
Modyfikator menu głównego nakłada `cyber_header_menu_args()`, modyfikator podmenu —
filtr `nav_menu_submenu_css_class` w `inc/header.php` (zamiast własnego Walkera).
Ta sama funkcja dokłada `.cyber-menu--with-indicator`, gdy toggle wskaźnika jest
włączony — obecność pseudoelementu to obecność reguły, a nie jej wartość, więc
zmienna CSS nic by tu nie dała.

#### Mechanizm skalowania (wariant B)

Wartość dla breakpointu liczona jest **w PHP**, nie przez `calc()` w CSS:

```
wartość_breakpointu = max( 1, round( wartość_desktop * skala / 100 ) )
```

Do przeglądarki trafiają gotowe liczby w px. Przykład dla domyślnych ustawień
(`h1 = 48px`, skale 90 / 80 / 70):

| Breakpoint | Media query | Obliczenie | Wynik |
|---|---|---|---|
| Desktop | — (wartość bazowa) | — | 48px |
| Tablet | `max-width: 980px` | `round(48 × 0,90)` | 43px |
| Mobile | `max-width: 767px` | `round(48 × 0,80)` | 38px |
| Mobile small | `max-width: 479px` | `round(48 × 0,70)` | 34px |

Zabezpieczenie `max( 1, … )` istnieje po to, żeby ekstremalna kombinacja małej
wartości i niskiej skali nie dała `font-size: 0px`, czyli niewidocznego tekstu.

**Konsekwencja do zapamiętania:** skala działa na wszystko naraz. Nie da się
zmniejszyć samego `h1` na mobile, zostawiając tekst bez zmian — to świadomy koszt
wariantu B (CLAUDE.md sekcja 19). Per-elementowa kontrola wymaga uzasadnienia
i osobnej decyzji, nie jest domyślna.

#### Konsumpcja zmiennych w CSS

`assets/css/main.css`, sekcja „Typografia”:

| Selektor | Zmienne |
|---|---|
| `h1` … `h6` | `--cyber-font-size-h1` … `-h6`, `--cyber-font-family-headings`, `--cyber-font-weight-headings` |
| `.cyber-overtitle` | `--cyber-font-size-overtitle-1`, `--cyber-font-family-headings`, `--cyber-font-weight-overtitle` |
| `.cyber-overtitle--secondary` | `--cyber-font-size-overtitle-2`, `--cyber-font-family-headings`, `--cyber-font-weight-overtitle` |
| `body`, `p`, `span`, `ul`, `li` | `--cyber-font-size-text`, `--cyber-font-family-text`, `--cyber-font-weight-text` |
| `a` | `--cyber-font-size-links`, `--cyber-font-family-text`, `--cyber-font-weight-links` |

Nagłówki i overtitle mają wspólny **krój**, ale osobną **grubość**, dlatego reguła
kroju rozpada się na dwa selektory: `h1`–`h6` oraz `.cyber-overtitle` wraz
z wariantem `--secondary`.

Wyjątek: `a` i `span` **wewnątrz** nagłówków i overtitle mają `font-size`,
`font-family` i `font-weight` ustawione na `inherit`. Bez tej reguły
`<h1><a>…</a></h1>` skurczyłby się do wielkości i grubości linku.

Klasy `.cyber-overtitle` i `.cyber-overtitle--secondary` są **nowe** — wprowadzone
razem z tym modułem, bo ACF definiuje dwie wielkości overtitle, a motyw nie miał
dla nich żadnego znacznika. Nie ma jeszcze template-partu, który je wypisuje;
pojawią się w komponentach etapu 4.

---

## Inne grupy pól

*(brak — cała konfiguracja globalna mieści się w `group_global_options`)*

---

## Utrzymanie tego pliku

- Ten dokument jest ręcznie utrzymywany na starcie projektu. Docelowo warto rozważyć
  prosty skrypt (`tools/generate-schema.php` lub polecenie WP-CLI), który generuje
  tabele powyżej bezpośrednio z plików w `acf-json/`, żeby wyeliminować ryzyko
  rozjechania się dokumentacji z rzeczywistą konfiguracją.
- Do czasu powstania takiego generatora: **każda zmiana pola w ACF = ręczna aktualizacja
  tego pliku w tym samym commicie.**

## Historia zmian

- 2026-09-09 — Utworzono moduł Global Options, zakładka „Szerokość strony” (pierwszy moduł projektu).
- 2026-09-09 — Wdrożenie v0.1.0: dodano `acf-json/group_global_options.json`, helper `cyber_get_option()` z walidacją oraz sekcję „Odwzorowanie w kodzie”. Plik przeniesiony z roota do `docs/` zgodnie z CLAUDE.md sekcja 3.
- 2026-09-10 — Zatwierdzono i wdrożono generowanie CSS z Global Options (inline `<style>` w `wp_head`). Bez zmian w polach ACF — wyłącznie warstwa logiki i widoku.
- 2026-09-10 — Nowa zakładka **„Top Header”**: 3 pola stylu + 8 przełączników widoczności. **Pierwszy moduł konsumujący pola z „Kontakt" i „Social Media"** przez `cyber_get_option()`. Nowy `template-parts/header/top-header.php`, `cyber_top_header_data()` w `inc/header.php`, własne inline SVG w `cyber_get_social_icon()`.
- 2026-09-10 — Nowa zakładka **„Social Media”**: 6 pól URL (Facebook, Instagram, YouTube, X, LinkedIn, TikTok) — **bez logiki frontendowej**, zarezerwowane na przyszłość (CLAUDE.md sekcja 22). Bez nowego pliku w `inc/` — walidację pokrywa istniejący typ schematu `url`.
- 2026-09-10 — Nowa zakładka **„Kontakt”**: 7 pól danych kontaktowych (adres, godziny, telefon, email, NIP, KRS, REGON) — **bez logiki frontendowej**, świadomie zarezerwowane na przyszłość (CLAUDE.md sekcja 22). Nowy `inc/contact.php` z walidacją przy zapisie, nowe typy schematu `text` / `textarea` / `email`.
- 2026-09-10 — Nowa zakładka **„Kolory”**: 5 pól semantycznych (auto) i 5 narzędziowych (stałe klasy `.cyber-hover-color`, `.cyber-border-1`, `.cyber-border-2`, `.cyber-shadow`, `.cyber-shadow-hover`). Nowy typ walidacji `color_alpha` dla pól cieni z `enable_opacity`.
- 2026-09-10 — Nowa zakładka **„Przyciski”**: 3 rozmiary × 8 pól (geometria, grubość, 4 kolory) = 24 pola. Komponent `cyber_button()` w `inc/components.php` + `template-parts/components/button.php`, sekcja CSS w `main.css`. Lista grubości wydzielona do `cyber_font_weight_choices()` i reużyta przez wszystkie moduły.
- 2026-09-10 — Nowa zakładka **„Header Mobile”**: jedno pole `cyber_header_mobile_breakpoint` (Number, default 980), świadomy wyjątek od kanonicznych breakpointów. Hamburger + panel mobilny w `template-parts/header/header.php`, blok `@media` generowany przez `cyber_header_mobile_css()`, obsługa w `assets/js/header.js` (czysty JS, enqueue warunkowy).
- 2026-09-10 — Header Desktop: nowe pole `cyber_header_submenu_indicator` (True/False, default `true`) — strzałka przy pozycjach z podmenu. Nowy typ walidacji `bool` w `cyber_option_schema()`.
- 2026-09-10 — Nowa zakładka **„Header Desktop”**: pole Image (logo, return format `url` — precedens dla pól obrazu), wyrównanie, paddingi kontenera, 7 pól menu głównego i 8 pól podmenu (Number / Select / Color Picker). Nowy `inc/header.php`, markup w `template-parts/header/header.php`, sekcja CSS w `main.css`. Menu korzysta z istniejącej lokalizacji `primary`.
- 2026-09-10 — Rozszerzenie zakładki „Ustawienia czcionki” o sekcję **„Grubość czcionki”**: 4 pola Select (`cyber_font_weight_headings` / `_overtitle` / `_text` / `_links`). Grubość celowo bez skalowania responsywnego — jedna wartość dla wszystkich breakpointów.
- 2026-09-10 — Nowa zakładka **„Ustawienia czcionki”** w `group_global_options`: 10 pól wielkości (desktop), 3 pola skalowania procentowego, 2 pola wyboru kroju. Wdrożona logika `cyber_font_css()`, wspólne `cyber_breakpoints()`, klasy `.cyber-overtitle` / `.cyber-overtitle--secondary`. Funkcja wypisująca przemianowana na `cyber_print_inline_css()`, znacznik `<style>` na `id="cyber-global-vars"`.
