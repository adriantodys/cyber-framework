# Bezpieczeństwo — Cyber Framework

Ostatnia aktualizacja: 2026-11-08 (endpoint `cyber_shop_more` bez nonce — uzasadnienie niżej).
Dokument nadrzędny: CLAUDE.md sekcja 9. Tutaj są checklisty do odhaczania przy review.

## Stan obecny (v0.1.0)

Motyw nie zawiera endpointów REST. Powierzchnia ataku to:

- wyświetlanie danych z ACF (rozwiązanie: escaping przy outputcie + walidacja w helperze),
- strona ustawień ACF w adminie (rozwiązanie: `capability` = `manage_options`; nonce
  i zapis obsługuje ACF),
- **dwa endpointy AJAX** — zmiana ilości pozycji koszyka na stronie zamówienia
  oraz doładowywanie kolejnych produktów na liście sklepu.

### Endpoint AJAX: `cyber_checkout_qty`

`inc/woocommerce-checkout.php`, funkcja `cyber_wc_checkout_update_quantity()`.
Zarejestrowany dla zalogowanych **i** niezalogowanych (`wp_ajax_nopriv_`), bo
koszyk prowadzą także goście.

| Warstwa | Realizacja |
|---|---|
| Nonce | `check_ajax_referer( 'cyber_checkout_qty', 'nonce' )` na pierwszej linii |
| Sanitizacja | klucz przez `sanitize_text_field( wp_unslash() )`, ilość przez `absint()` |
| Walidacja | pozycja musi istnieć w koszyku **tej sesji**; ilość ≥ 1, przycięta do `get_max_purchase_quantity()`, sprawdzona przez `has_enough_stock()` |
| Escaping | odpowiedź to JSON z liczbą i przetłumaczonym komunikatem, nie HTML |

> **Dlaczego nie ma `current_user_can()`.** Checklista poniżej wymaga sprawdzenia
> uprawnień przy każdej akcji AJAX. To odstępstwo jest **świadome i konieczne**:
> zakupy bez konta są normalnym scenariuszem sklepu, więc żadna sensowna
> capability tu nie istnieje.
>
> Endpoint nie jest przez to otwarty. Operuje wyłącznie na koszyku **z sesji
> osoby wykonującej żądanie** — nie przyjmuje identyfikatora użytkownika ani
> zamówienia, więc nie da się nim sięgnąć cudzych danych. Nonce chroni przed
> wykonaniem żądania z obcej strony.
>
> Każdy kolejny publiczny endpoint musi mieć tak samo **jawnie uzasadniony**
> brak sprawdzenia uprawnień — albo je zawierać.

Sprawdzone realnymi żądaniami: brak nonce → `403`, zły nonce → `403`,
nieistniejący klucz pozycji → błąd, ilość `0` → odrzucona.

### Endpoint AJAX: `cyber_shop_more`

`inc/woocommerce-shop.php`, funkcja `cyber_shop_load_more_products()`.
Zarejestrowany dla zalogowanych i niezalogowanych — lista produktów jest publiczna.

| Warstwa | Realizacja |
|---|---|
| Nonce | **brak — świadomie** (od 2026-11-08, uzasadnienie niżej) |
| Sanitizacja | `absint()` dla numerów, `sanitize_key()` dla sortowania i taksonomii |
| Walidacja | strona ≥ 2; liczba na stronie i sortowanie z **białej listy**; taksonomia musi należeć do produktów (`get_object_taxonomies( 'product' )`), a termin istnieć |
| Escaping | odpowiedź to JSON; markup produktów buduje szablon WooCommerce, który escapuje u siebie |

> **Zapytanie jest budowane od zera, nie przyjmowane od przeglądarki.** Endpoint
> dostaje wyłącznie identyfikatory — numer strony, liczbę na stronie, sortowanie,
> taksonomię i termin — a `WP_Query` składa z nich serwer. Przyjęcie gotowej
> tablicy argumentów pozwoliłoby czytać dowolne wpisy, także robocze i prywatne.
>
> Sprawdzenie taksonomii jest tu **granicą bezpieczeństwa, nie kosmetyką**: bez
> niego żądanie z `taxonomy=category` wyciągnęłoby wpisy bloga przez endpoint
> sklepu. Potwierdzone testem — takie żądanie kończy się błędem.

> **Dlaczego nie ma `current_user_can()`.** To publiczna lista produktów, ta sama,
> którą każdy widzi bez logowania. Endpoint niczego nie zapisuje i zwraca
> wyłącznie to, co i tak jest dostępne pod adresem kategorii. Uzasadnienie
> wymagane regułą przy poprzednim endpoincie.

> **Dlaczego nie ma nonce.** Nonce chroni przed wykonaniem w imieniu
> użytkownika akcji, która **zmienia stan** (CSRF). Ten endpoint stanu nie ma:
> nie zapisuje, nie czyta sesji, nie przyjmuje identyfikatora użytkownika —
> zwraca publiczny HTML listy produktów. Podrobione żądanie dostaje dokładnie
> to samo, co każdy odwiedzający pod adresem kategorii, więc nonce niczego tu
> nie chronił.
>
> Miał za to realny koszt: przy **cache pełnych stron** token zapisany
> w HTML-u (`wp_localize_script`) wygasa po 12–24 h, a strona z cache serwuje
> go dalej — przycisk „Pokaż więcej" dostawał `403` u każdego, kto trafił na
> stronę starszą niż doba. Dlatego nonce usunięto razem z `wp_create_nonce()`
> i polem `nonce` w `assets/js/shop.js`. Sanitizacja i białe listy zostają
> w całości — to one, nie nonce, są granicą bezpieczeństwa tego endpointu.
>
> **Endpoint zamówienia (`cyber_checkout_qty`) nonce ZACHOWUJE**: zmienia
> ilość pozycji w koszyku, czyli stan, a WooCommerce i tak blokuje cache
> koszyka i zamówienia (`DONOTCACHEPAGE`, nagłówki no-cache), więc problem
> wygasania tam nie występuje. Różnica między oboma endpointami jest więc
> zasadą, nie wyjątkiem: **nonce przy zapisie, brak nonce przy publicznym
> odczycie bez stanu** — zawsze z jawnym uzasadnieniem w docblocku handlera.

Sprawdzone realnymi żądaniami (przed usunięciem nonce): obca taksonomia → błąd,
strona `1` → odrzucona, poprawne żądanie → 3 produkty i `has_more: false`.
Po zmianie żądanie bez pola `nonce` jest poprawne i zwraca listę.

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

- [ ] **Nonce** — `check_ajax_referer( 'cyber_akcja', 'nonce' )` przy każdej akcji,
      która zmienia stan. Publiczny odczyt bez stanu (jak `cyber_shop_more`) może
      go nie mieć — wyłącznie ze świadomą decyzją i komentarzem w docblocku,
      dlaczego nie ma czego podrobić.
- [ ] **Capability** — `current_user_can()`; dla akcji publicznych świadoma decyzja
      + komentarz, dlaczego brak sprawdzenia jest bezpieczny.
- [ ] **Sanitizacja wejścia** — każdego pola z osobna.
- [ ] **Escaping wyjścia** — również w odpowiedzi JSON, jeśli trafi do DOM.

Do tego rejestracja:

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
