# Architektura — Cyber Framework

Ostatnia aktualizacja: 2026-09-10 (stan: etap 1 i 2 z CLAUDE.md sekcja 17).

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
| 1 | `inc/helpers.php` | Schemat opcji, walidacja, `cyber_get_option()`. Musi być pierwszy — reszta może z niego korzystać. |
| 2 | `inc/acf.php` | Ścieżki Local JSON (save/load), ostrzeżenie o braku ACF PRO. Musi być przed ładowaniem pól przez ACF. |
| 3 | `inc/options.php` | `acf_add_options_page()` na hooku `acf/init`. |
| 4 | `inc/setup.php` | `add_theme_support()`, menu, rozmiary obrazków. |
| 5 | `inc/enqueue.php` | Rejestracja assetów, wersjonowanie przez `filemtime()`. |
| 6 | `inc/editor.php` | Wyłączenie edytora blokowego (Gutenberg) dla wszystkich typów treści. |

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

Zgodnie z kolejnością budowy (CLAUDE.md sekcja 17) — świadomie **nie** zaimplementowane:

- **Etap 3** — Header / Footer sterowane ACF. `header.php` i `footer.php` w rootcie to
  tymczasowy szkielet (branding + skip-link), potrzebny tylko po to, by motyw dało się
  aktywować. Docelowo `template-parts/header/` i `template-parts/footer/`.
- **Etap 4** — Flexible Content i system komponentów. `template-parts/sections/`
  i `template-parts/components/` są puste.
- **Etap 5** — szablony widoków w `templates/`. Obecnie istnieje wyłącznie `index.php`
  jako wymagany przez WordPress fallback.
- **Generowanie CSS z Global Options** — wstrzymane do decyzji, patrz `docs/acf-schema.md`.
