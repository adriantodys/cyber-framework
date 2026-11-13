# Cyber Framework

Customowy motyw WordPress budowany od zera: czysty PHP + **ACF PRO** jako jedyny system
zarządzania danymi. Bez page buildera. Od wersji 1.0.0 jest **motywem nadrzędnym**:
strona klienta używa motywu potomnego, a ten katalog pozostaje identyczny z tagiem
na GitHubie (zasady: CLAUDE.md sekcja 23, starter childa: `docs/child-theme.md`).

Zasady pracy nad projektem opisuje **[CLAUDE.md](CLAUDE.md)** — jest to dokument nadrzędny
i obowiązkowa lektura przed każdą zmianą.

## Wymagania

| | |
|---|---|
| WordPress | 6.5+ (testowane na 7.1) — od 6.5, bo slider ładuje skrypt przez Script Modules API (`wp_enqueue_script_module()`) |
| PHP | 8.0+ |
| ACF PRO | 6.2+ (twarda zależność — bez niego motyw działa na wartościach domyślnych; od 6.2, bo zapis grup pól z childa i modułów wraca do ich katalogów przez `acf/json/save_paths`) |
| WooCommerce | opcjonalne (zależność miękka — bez niego funkcje sklepu wyłączają się same) |

## Instalacja

1. Skopiuj katalog motywu do `wp-content/themes/cyber-framework/`.
2. Zainstaluj i aktywuj **ACF PRO**.
3. Aktywuj motyw *Cyber Framework*.
4. **Nowe wdrożenie z gotowym projektem graficznym — krok opcjonalny.**
   Wypełnij `default-acf.php` wartościami z projektu (kolory, czcionki, dane
   kontaktowe) i przenieś je do `acf-json/group_global_options.json`, **zanim**
   zsynchronizujesz pola. Dzięki temu krok 5 wciągnie od razu docelowe wartości,
   zamiast domyślnych motywu, i nie trzeba przeklikiwać dziewiętnastu zakładek.
   Pełny przebieg opisuje nagłówek tego pliku; zasady — CLAUDE.md sekcja 16.
5. Wejdź w **Custom Fields → Field Groups → Sync available** i zsynchronizuj
   **wszystkie** grupy *Cyber Framework — …* (19 plików w `acf-json/`) — nie trzeba
   klikać żadnego pola ręcznie. Są wśród nich: *Global Options*, *Sekcje* wraz
   z grupami źródłowymi *Sekcje: …* (pola klonowane do layoutów — bez nich sekcje
   nie mają pól), *Page header*, *Galeria*, *Opinia*, *Produkt* i *Kategoria
   produktu*. Dwie ostatnie dotyczą WooCommerce (produkt i taksonomia
   `product_cat`) i mają sens dopiero z tą wtyczką.
   **Aktualizacja istniejącej instalacji do tej wersji (2026-11-08):** kliknij
   **Sync** dla *Global Options* (pola logo zwracają teraz ID załącznika zamiast
   adresu — dane w bazie bez zmian) i raz **Zapisz ustawienia** w menu Cyber
   Framework, żeby opcje przełączyły się na `autoload` (jedno zapytanie zamiast
   setek pojedynczych `SELECT`-ów na każdym żądaniu).
6. Ustawienia znajdziesz w menu **Cyber Framework** (slug `cyber-settings`).
   Zapisz je raz — wartości trafią do `wp_options` i od tej chwili to one
   decydują o wyglądzie.
7. Jeśli robiłeś krok 4, posprzątaj: usuń `default-acf.php` i przywróć plik pól
   (`git checkout acf-json/`). Wygląd zostaje, bo mieszka już w bazie, a motyw
   wraca do stanu z repozytorium — bez forka na tego klienta.

Jeśli pozycja *Sync* się nie pojawia, sprawdź, czy `acf-json/` jest zapisywalny
i czy motyw jest aktywny (ścieżki Local JSON rejestruje `inc/acf.php`).

## Motyw potomny (strony klientów)

Parenta nie edytuje się na stronie klienta. Wszystko, co jest specyficzne dla
strony — CSS, nadpisane widoki, pola ACF, moduły — idzie do childa:

1. Utwórz child z plików ze `docs/child-theme.md` (`style.css` z `Template: cyber-framework`,
   `functions.php`, pusty katalog `acf-json/`, `CLAUDE.md`).
2. Aktywuj child. Parent sam kolejkuje `style.css` childa i czyta jego `acf-json/`.
3. Aktualizacja frameworka = podmiana katalogu `cyber-framework/` na nowy tag.
   Sprawdź w `CLAUDE.md` parenta, czy wersja nie wymaga kroków w panelu
   (np. Sync grupy pól).

Elementy reużywalne (popup koszyka, pasek promocji) są **modułami** w
`child/modules/<nazwa>/`, kopiowanymi z repozytorium `cyber-modules`. Format
modułu i instalacja: `docs/child-theme.md`.

## Struktura

```
cyber-framework/
├── acf-json/          ← źródło prawdy dla pól ACF (wersjonowane w Git)
├── assets/            ← css / js / images / vendor (biblioteki zewnętrzne)
├── docs/              ← dokumentacja (patrz niżej)
├── inc/               ← logika motywu
├── template-parts/    ← header/, footer/, breadcrumb/, page-header/, blog/, go-to-top/, components/, sections/
├── templates/         ← szablony widoków: blog, pojedynczy wpis, pojedyncza galeria (etap 5 w toku)
├── woocommerce/       ← nadpisania szablonów wtyczki (dwa, rejestr w CLAUDE.md sekcja 2)
├── default-acf.php    ← formularz wdrożeniowy: wartości domyślne dla nowego projektu
├── functions.php      ← bootstrap, bez logiki
└── style.css          ← wyłącznie nagłówek motywu; style są w assets/css/
```

## Tłumaczenia

Stringi motywu są po polsku i opakowane w `__()` z domeną `cyber-framework`.
Domena jest rejestrowana przez `load_theme_textdomain()`, a pliki `.mo` trafiają
do `languages/` tylko wtedy, gdy witryna ma inny język niż polski — instrukcja
w `languages/README.md`.

## Dokumentacja

| Plik | Zawartość |
|---|---|
| [docs/acf-schema.md](docs/acf-schema.md) | Pełna mapa pól ACF — **źródło prawdy**, aktualizowane w tym samym commicie co zmiana pola. |
| [docs/architecture.md](docs/architecture.md) | Przepływ danych, kolejność ładowania modułów, stałe. |
| [docs/components.md](docs/components.md) | Mapa layoutów Flexible Content → pliki → pola. |
| [docs/security.md](docs/security.md) | Checklisty bezpieczeństwa (output, input, formularze, AJAX, REST). |

## Dostęp do ustawień globalnych w kodzie

Widoki **nigdy** nie wołają `get_field()` bezpośrednio. Zawsze przez helper
(klucz bez prefiksu `cyber_`):

```php
cyber_get_option( 'page_width_type' ); // '80'
cyber_get_option( 'page_width_80' );   // 1500
```

Helper waliduje wartość (zakres / lista dozwolonych) i podstawia wartość domyślną,
gdy pole jest puste lub ACF niedostępne. Pełna lista kluczy: `docs/acf-schema.md`.

## Stan realizacji

Kolejność budowy: CLAUDE.md sekcja 17.

- [x] **Etap 1** — szkielet motywu (`style.css`, `functions.php`, `inc/setup.php`, `inc/enqueue.php`)
- [x] **Etap 2** — Global Options: Options Page + dziewiętnaście zakładek, 242 pola
- [x] **Etap 3** — Header / Footer (ACF + template-parts): Top Header, Header Desktop, Header Mobile, Footer, Copyright
- [~] **Etap 4** — system komponentów / Flexible Content: 11 layoutów sekcji (w tym sekcje globalne); brakuje filtrowania layoutów per typ treści
- [~] **Etap 5** — szablony widoków: są blog (lista i archiwa), pojedynczy wpis i pojedyncza galeria; brakuje page, front page, 404, search i archiwów CPT
- [~] **Etap 6** — formularze / AJAX: dwa endpointy AJAX istnieją (ilość w zamówieniu, doładowywanie produktów), własnych formularzy motywu jeszcze nie ma
- [ ] **Etap 7** — podstawy SEO
- [ ] **Etap 8** — audyt wydajności, dostępności, bezpieczeństwa + WPCS

**Poza kolejnością etapów — warstwa WooCommerce.** Powstała na bieżące potrzeby
sklepu: okruszki, koszyk, zamówienie, lista produktów (sklep i kategorie) oraz
strona pojedynczego produktu. Wszystko z hooków i CSS; nadpisania szablonów
wtyczki są **dwa** i mają rejestr w CLAUDE.md sekcja 2. WooCommerce jest
zależnością **miękką** — bez wtyczki te funkcje wyłączają się same, a reszta
witryny działa bez zmian.

`header.php` i `footer.php` w rootcie są już docelowe — zbierają dane przez
`cyber_get_option()` i przekazują je jawnie do `template-parts/` (CLAUDE.md sekcja 4).
**Tymczasowy szkielet** to wyłącznie `index.php`: wymagany przez WordPress fallback,
który zastąpią szablony z `templates/` w etapie 5.

Kolumny 2 i 3 stopki oraz część pól zakładki „Kontakt” są celowo puste —
to pola zarezerwowane pod przyszłe moduły, nie niedokończona praca
(CLAUDE.md sekcja 22).
