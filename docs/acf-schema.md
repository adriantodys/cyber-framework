# ACF Schema — Cyber Framework

> Ten plik jest źródłem prawdy dla wszystkich pól ACF w projekcie — zarówno dla
> człowieka, jak i dla Claude. **Każdy nowy moduł, grupa pól czy layout musi zostać
> tu opisany w tym samym commicie, w którym powstał w ACF.** Docelowo ten plik
> powinien być generowany/aktualizowany automatycznie na podstawie `acf-json/`
> (patrz sekcja "Utrzymanie" na końcu pliku).

Ostatnia aktualizacja: 2026-09-10
Moduły: **Global Options** — zakładki „Główne ustawienia strony” i „Ustawienia czcionki”

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
| Funkcje budujące CSS | `cyber_container_css()` i `cyber_font_css()` — `inc/enqueue.php` |
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
| `--cyber-font-size-h1` … `-h6` | `cyber_font_size_h1` … `_h6` | tak |
| `--cyber-font-size-overtitle-1` | `cyber_font_size_overtitle_1` | tak |
| `--cyber-font-size-overtitle-2` | `cyber_font_size_overtitle_2` | tak |
| `--cyber-font-size-text` | `cyber_font_size_text` | tak |
| `--cyber-font-size-links` | `cyber_font_size_links` | tak |

Podkreślenia w nazwie pola stają się myślnikami w nazwie zmiennej
(`cyber_font_size_overtitle_1` → `--cyber-font-size-overtitle-1`). Mapa siedzi
w `cyber_font_size_map()`.

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
| `h1` … `h6` | `--cyber-font-size-h1` … `-h6` + `--cyber-font-family-headings` |
| `.cyber-overtitle` | `--cyber-font-size-overtitle-1` + `--cyber-font-family-headings` |
| `.cyber-overtitle--secondary` | `--cyber-font-size-overtitle-2` + `--cyber-font-family-headings` |
| `body`, `p`, `span`, `ul`, `li` | `--cyber-font-size-text` + `--cyber-font-family-text` |
| `a` | `--cyber-font-size-links` + `--cyber-font-family-text` |

Wyjątek: `a` i `span` **wewnątrz** nagłówków i overtitle mają `font-size: inherit`
i `font-family: inherit`. Bez tej reguły `<h1><a>…</a></h1>` skurczyłby się do
wielkości linku.

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
- 2026-09-10 — Nowa zakładka **„Ustawienia czcionki”** w `group_global_options`: 10 pól wielkości (desktop), 3 pola skalowania procentowego, 2 pola wyboru kroju. Wdrożona logika `cyber_font_css()`, wspólne `cyber_breakpoints()`, klasy `.cyber-overtitle` / `.cyber-overtitle--secondary`. Funkcja wypisująca przemianowana na `cyber_print_inline_css()`, znacznik `<style>` na `id="cyber-global-vars"`.
