# Bezpieczeństwo — Cyber Framework

Ostatnia aktualizacja: 2026-09-09.
Dokument nadrzędny: CLAUDE.md sekcja 9. Tutaj są checklisty do odhaczania przy review.

## Stan obecny (v0.1.0)

Motyw **nie zawiera** formularzy front-endowych, endpointów AJAX ani REST. Powierzchnia
ataku ogranicza się do:

- wyświetlania danych z ACF (rozwiązanie: escaping przy outputcie + walidacja w helperze),
- strony ustawień ACF w adminie (rozwiązanie: `capability` = `manage_options`; nonce
  i zapis obsługuje ACF).

Checklisty poniżej obowiązują od momentu, w którym pojawi się pierwszy formularz.

## Checklista: output (obowiązuje już teraz)

- [ ] Każda zmienna w HTML przechodzi przez funkcję escapującą **dobraną do kontekstu**:
      `esc_html()` (tekst), `esc_attr()` (atrybut), `esc_url()` (URL),
      `wp_kses_post()` (dozwolony HTML z edytora).
- [ ] Escaping wykonywany **w miejscu outputu**, nie „na zapas" w warstwie logiki.
- [ ] Ciągi tłumaczone: `esc_html__()` / `esc_html_e()` / `esc_attr__()`, nie samo `__()`.
- [ ] `printf`/`sprintf` z placeholderami numerowanymi + komentarz `translators:`.

## Checklista: input / zapis

- [ ] `sanitize_text_field()`, `sanitize_email()`, `absint()`, `esc_url_raw()` —
      dobrane do typu danych.
- [ ] Walidacja **po** sanitizacji: typ, zakres, przynależność do listy dozwolonych wartości.
- [ ] Zero zaufania do `$_POST` / `$_GET` / `$_REQUEST` bez obu powyższych kroków.
- [ ] Zapytania do bazy: `$wpdb->prepare()` zawsze, bez wyjątków.

## Checklista: formularz

- [ ] `wp_nonce_field( 'cyber_akcja', 'cyber_nonce' )` w formularzu.
- [ ] `check_admin_referer()` / `wp_verify_nonce()` przy obsłudze.
- [ ] `current_user_can()` przed każdą akcją zapisu lub administracyjną.
- [ ] Przekierowanie po POST (PRG), żeby odświeżenie nie powtórzyło akcji.

## Checklista: AJAX

Wszystkie cztery elementy, nigdy część:

- [ ] **Nonce** — `check_ajax_referer( 'cyber_akcja', 'nonce' )`.
- [ ] **Capability** — `current_user_can()`; dla akcji publicznych świadoma decyzja
      + komentarz, dlaczego brak sprawdzenia jest bezpieczny.
- [ ] **Sanitizacja wejścia** — każdego pola z osobna.
- [ ] **Escaping wyjścia** — również w odpowiedzi JSON, jeśli trafi do DOM.
- [ ] Zarejestrowane osobno `wp_ajax_` i `wp_ajax_nopriv_` — świadomie, nie odruchowo.

## Checklista: REST API

- [ ] `permission_callback` **zawsze** obecny — brak jest błędem, nie niedopatrzeniem.
- [ ] `__return_true` wyłącznie dla endpointów jawnie publicznych, z komentarzem
      wyjaśniającym decyzję.
- [ ] `args` z `sanitize_callback` i `validate_callback` dla każdego parametru.

## Checklista: pliki i uprawnienia

- [ ] Każdy plik PHP zaczyna się od `defined( 'ABSPATH' ) || exit;`.
- [ ] Brak `eval()`, brak `extract()`, brak `unserialize()` na danych z zewnątrz.
- [ ] Upload plików: `wp_handle_upload()` + `wp_check_filetype_and_ext()`, nigdy ręcznie.
