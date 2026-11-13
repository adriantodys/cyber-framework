# Tłumaczenia motywu

Katalog na pliki `.po` / `.mo` domeny `cyber-framework`, ładowane przez
`load_theme_textdomain()` w `inc/setup.php` (akcja `after_setup_theme`).

Stringi źródłowe w kodzie są **po polsku**, więc dla witryny w języku polskim
plik tłumaczeń nie jest potrzebny — katalog jest pusty i to stan normalny.
Plik powstaje dopiero dla innego języka witryny:

1. Wygeneruj szablon: `wp i18n make-pot . languages/cyber-framework.pot`
   (WP-CLI) — zbiera wszystkie `__()`, `_e()`, `esc_html__()` z domeną
   `cyber-framework`.
2. Przetłumacz w Poedit do `languages/cyber-framework-{locale}.po` i zapisz
   (Poedit generuje `.mo` obok).
3. Nazwa pliku to **locale witryny** z Ustawienia → Ogólne, np.
   `cyber-framework-en_US.mo`, `cyber-framework-de_DE.mo`.

Motyw potomny ma własną domenę i własny katalog `languages/` — patrz
`docs/child-theme.md`.
