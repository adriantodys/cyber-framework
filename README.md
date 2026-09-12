# Cyber Framework

Customowy motyw WordPress budowany od zera: czysty PHP + **ACF PRO** jako jedyny system
zarządzania danymi. Bez motywu nadrzędnego, bez page buildera.

Zasady pracy nad projektem opisuje **[CLAUDE.md](CLAUDE.md)** — jest to dokument nadrzędny
i obowiązkowa lektura przed każdą zmianą.

## Wymagania

| | |
|---|---|
| WordPress | 6.4+ |
| PHP | 8.0+ |
| ACF PRO | wymagane (twarda zależność — bez niego motyw działa na wartościach domyślnych) |

## Instalacja

1. Skopiuj katalog motywu do `wp-content/themes/cyber-framework/`.
2. Zainstaluj i aktywuj **ACF PRO**.
3. Aktywuj motyw *Cyber Framework*.
4. Wejdź w **Custom Fields → Field Groups → Sync available** i zsynchronizuj grupę
   *Cyber Framework — Global Options*.
   Grupa jest już w repozytorium jako `acf-json/group_global_options.json` — nie trzeba
   klikać żadnego pola ręcznie.
5. Ustawienia znajdziesz w menu **Cyber Framework** (slug `cyber-settings`).

Jeśli pozycja *Sync* się nie pojawia, sprawdź, czy `acf-json/` jest zapisywalny
i czy motyw jest aktywny (ścieżki Local JSON rejestruje `inc/acf.php`).

## Struktura

```
cyber-framework/
├── acf-json/          ← źródło prawdy dla pól ACF (wersjonowane w Git)
├── assets/            ← css / js / images
├── docs/              ← dokumentacja (patrz niżej)
├── inc/               ← logika motywu
├── template-parts/    ← header/, footer/, components/; sections/ czeka na etap 4
├── templates/         ← szablony widoków (puste — etap 5)
├── functions.php      ← bootstrap, bez logiki
└── style.css          ← wyłącznie nagłówek motywu; style są w assets/css/
```

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
- [x] **Etap 2** — Global Options: Options Page + jedenaście zakładek (od „Główne ustawienia strony” po „Copyright”)
- [x] **Etap 3** — Header / Footer (ACF + template-parts): Top Header, Header Desktop, Header Mobile, Footer, Copyright
- [ ] **Etap 4** — system komponentów / Flexible Content
- [ ] **Etap 5** — szablony widoków (front-page, page, single, archive, 404, search)
- [ ] **Etap 6** — formularze / AJAX
- [ ] **Etap 7** — podstawy SEO
- [ ] **Etap 8** — audyt wydajności, dostępności, bezpieczeństwa + WPCS

`header.php` i `footer.php` w rootcie są już docelowe — zbierają dane przez
`cyber_get_option()` i przekazują je jawnie do `template-parts/` (CLAUDE.md sekcja 4).
**Tymczasowy szkielet** to wyłącznie `index.php`: wymagany przez WordPress fallback,
który zastąpią szablony z `templates/` w etapie 5.

Kolumny 2 i 3 stopki oraz część pól zakładki „Kontakt” są celowo puste —
to pola zarezerwowane pod przyszłe moduły, nie niedokończona praca
(CLAUDE.md sekcja 22).
