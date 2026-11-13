# Motyw potomny i moduły — Cyber Framework

Ostatnia aktualizacja: 2026-11-13. Zasady: CLAUDE.md sekcja 23. Punkty
zaczepienia parenta: `docs/architecture.md`, „Motyw nadrzędny: punkty rozszerzeń”.

Ten dokument to **starter**: komplet plików minimalnego childa do skopiowania
oraz format modułu w bibliotece `cyber-modules`. Docelowo oba żyją w osobnych
repozytoriach (`cyber-framework-child`, `cyber-modules`); do tego czasu źródłem
jest ten plik.

## Minimalny child

```
klient-child/
├── style.css          nagłówek z Template: cyber-framework + CSS strony
├── functions.php      ładuje inc/ i modules/
├── CLAUDE.md          reguły pracy nad stroną (poniżej)
├── acf-json/          MUSI istnieć (pusty .gitkeep) — tu ACF zapisuje nowe grupy
├── inc/               logika strony (opcjonalnie)
├── template-parts/    nadpisane widoki (opcjonalnie, ta sama ścieżka co w parencie)
└── modules/           moduły z cyber-modules + MODULES.md z wersjami
```

### `style.css`

```css
/*
Theme Name:  Klient
Template:    cyber-framework
Version:     1.0.0
Description: Motyw potomny Cyber Framework dla strony klient.pl.
Text Domain: klient
*/

/*
 * Parent kolejkuje ten plik jako OSTATNI arkusz <head>. Reguły wygrywają
 * z parentem przy tej samej specyficznosci. Odstepy wylacznie przez
 * var(--cyber-space-N), breakpointy 980 / 767 / 479 (CLAUDE.md 6 i 18).
 */
```

### `functions.php`

```php
<?php
/**
 * Klient — motyw potomny Cyber Framework.
 *
 * Nie ma tu logiki: plik laduje inc/ strony i moduly z modules/.
 * Parent (../cyber-framework) jest tylko do odczytu.
 *
 * @package Klient
 */

defined( 'ABSPATH' ) || exit;

define( 'KLIENT_DIR', get_stylesheet_directory() );
define( 'KLIENT_URI', get_stylesheet_directory_uri() );

/**
 * Katalogi acf-json/ modulow — parent czyta je razem ze swoim i z childa.
 *
 * @param string[] $dirs Katalogi Local JSON.
 * @return string[]
 */
function klient_acf_json_dirs( $dirs ) {
	foreach ( glob( KLIENT_DIR . '/modules/*/acf-json', GLOB_ONLYDIR ) ?: array() as $dir ) {
		$dirs[] = $dir;
	}

	return $dirs;
}
add_filter( 'cyber_acf_json_dirs', 'klient_acf_json_dirs' );

/**
 * Laduje logike strony i bootstrap kazdego modulu.
 *
 * NA after_setup_theme, NIE przy ladowaniu pliku: WordPress wczytuje
 * functions.php childa PRZED functions.php parenta, wiec w tym momencie
 * zadna funkcja cyber_*() jeszcze nie istnieje. Na after_setup_theme
 * (priorytet 0) parent jest juz zaladowany, a hooki ACF (acf/init,
 * acf/include_fields) i enqueue jeszcze nie odpalily — modul zdazy sie
 * wszedzie zarejestrowac.
 *
 * Kolejnosc: najpierw inc/ (helpery strony), potem moduly — modul moze
 * korzystac z helperow strony, nie odwrotnie.
 *
 * @return void
 */
function klient_load() {
	// Wlasna domena tlumaczen childa (Text Domain ze style.css), osobna od parenta.
	load_child_theme_textdomain( 'klient', KLIENT_DIR . '/languages' );

	foreach ( glob( KLIENT_DIR . '/inc/*.php' ) ?: array() as $file ) {
		require_once $file;
	}

	foreach ( glob( KLIENT_DIR . '/modules/*/module.php' ) ?: array() as $file ) {
		require_once $file;
	}
}
add_action( 'after_setup_theme', 'klient_load', 0 );
```

> **Dlaczego nie od razu.** `add_filter()` na poziomie pliku childa działa
> (to WordPress, nie parent), ale wywołanie `cyber_*()` albo `CYBER_*` przy
> ładowaniu `functions.php` childa kończy się błędem krytycznym — parent
> jeszcze nie istnieje. Stąd `after_setup_theme`. Schemat opcji parenta
> liczy się dopiero przy pierwszym `cyber_get_option()`, czyli po tej akcji,
> więc filtr `cyber_option_schema` z modułu zdąży.

### `CLAUDE.md` childa

```markdown
# CLAUDE.md — Klient (motyw potomny Cyber Framework)

Ten katalog to motyw potomny. Parent leży w `../cyber-framework` i jest
**tylko do odczytu** — jego CLAUDE.md (sekcje 1–22) obowiązuje w całości,
sekcja 23 opisuje podział parent / child. Przeczytaj go przed pracą:
`../cyber-framework/CLAUDE.md`, `../cyber-framework/docs/architecture.md`
(„Motyw nadrzędny: punkty rozszerzeń”).

## Reguły tej strony

0. **Kolejność ładowania.** WordPress wczytuje `functions.php` childa przed
   parentem. Kod, który woła `cyber_*()`, musi stać w funkcji podpiętej do
   `after_setup_theme` (tak robi `klient_load()`), nigdy na poziomie pliku.
1. **Każdy nowy plik, pole, styl i sekcja powstaje tutaj.** Edycja pliku
   w `../cyber-framework` jest błędem — jeśli parent naprawdę wymaga zmiany,
   zgłoś to i zatrzymaj się; zmiana idzie osobnym commitem do repozytorium
   parenta z nową wersją.
2. **Prefiks `klient_`** dla funkcji, pól ACF (`cyber_klient_…` na Options
   Page, `klient_…` w grupach wpisów), klas CSS, uchwytów assetów.
3. **Nadpisanie widoku parenta** = kopia pod tą samą ścieżką z komentarzem
   w nagłówku, *dlaczego* parent nie wystarczył. Najpierw sprawdź, czy nie
   wystarczy filtr, akcja albo CSS.
4. **Moduły** w `modules/<nazwa>/` są kopiami z `cyber-modules` — nie
   edytuje się ich w miejscu. Poprawka idzie do biblioteki, potem nowa kopia
   i wpis wersji w `modules/MODULES.md`.
5. **Pola ACF** zapisują się do `acf-json/` tego katalogu. Grupa parenta
   zapisana w panelu wraca do parenta — to sygnał, że zmiana nie należy do
   tej strony. Każda nowa grupa jest opisana w `docs/acf-schema.md` childa.
6. Wartości Global Options parenta ustawia się w panelu albo procedurą
   `default-acf.php` parenta (CLAUDE.md 16) — bez commitowania parenta.

## Parent

Wersja parenta, z którą strona była testowana: `cyber-framework 1.0.0`.
Po aktualizacji parenta sprawdź jego CLAUDE.md i README (kroki w panelu).
```

## Moduł — format

```
cyber-modules/
└── cart-upsell-popup/
    ├── module.json        nazwa, wersja, wymagany parent, zależności (woocommerce)
    ├── module.php         bootstrap: hooki parenta i WordPressa, enqueue warunkowy
    ├── acf-json/          grupy modułu (klucz group_mod_cartpopup_…)
    ├── default-acf.php    wartości domyślne pól Global Options modułu (jeśli ma)
    ├── assets/css, js
    ├── template-parts/
    └── README.md          co robi, jak włączyć, co ustawić w panelu, zmiany wersji
```

### `module.json`

```json
{
    "name": "cart-upsell-popup",
    "title": "Popup produktów po dodaniu do koszyka",
    "version": "1.0.0",
    "requires": { "cyber-framework": ">=1.0.0", "woocommerce": true },
    "prefix": "mod_cartpopup"
}
```

### `module.php` — szkielet

```php
<?php
/**
 * Modul: popup produktow po dodaniu do koszyka.
 *
 * Zamkniety: nie dotyka parenta ani innych modulow, z parentem rozmawia
 * przez filtry i akcje (CLAUDE.md parenta, sekcja 23).
 *
 * @package Cyber_Modules\CartUpsellPopup
 */

defined( 'ABSPATH' ) || exit;

define( 'MOD_CARTPOPUP_DIR', __DIR__ );
define( 'MOD_CARTPOPUP_URI', get_stylesheet_directory_uri() . '/modules/cart-upsell-popup' );

// Zaleznosc miekka — jeden predykat parenta, nie wlasny class_exists().
if ( ! function_exists( 'cyber_is_woocommerce_active' ) || ! cyber_is_woocommerce_active() ) {
	return;
}

/**
 * Klucze Global Options modulu (pole ACF: cyber_mod_cartpopup_limit).
 *
 * @param array $extra Schemat dokladany do parenta.
 * @return array
 */
function mod_cartpopup_schema( $extra ) {
	$extra['mod_cartpopup_limit'] = array(
		'type'    => 'int',
		'default' => 6,
		'min'     => 1,
		'max'     => 12,
	);

	return $extra;
}
add_filter( 'cyber_option_schema', 'mod_cartpopup_schema' );

/**
 * Markup popupu na koncu <body>.
 *
 * @return void
 */
function mod_cartpopup_render() {
	if ( ! is_product() && ! is_shop() && ! is_product_category() ) {
		return;
	}

	load_template(
		MOD_CARTPOPUP_DIR . '/template-parts/popup.php',
		false,
		array( 'limit' => cyber_get_option( 'mod_cartpopup_limit' ) )
	);
}
add_action( 'cyber_body_end', 'mod_cartpopup_render' );

/**
 * Assety tylko tam, gdzie popup moze sie pojawic (CLAUDE.md sekcja 10).
 *
 * @return void
 */
function mod_cartpopup_assets() {
	if ( ! is_product() && ! is_shop() && ! is_product_category() ) {
		return;
	}

	wp_enqueue_style(
		'mod-cartpopup',
		MOD_CARTPOPUP_URI . '/assets/css/popup.css',
		array( 'cyber-main' ),
		(string) filemtime( MOD_CARTPOPUP_DIR . '/assets/css/popup.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'mod_cartpopup_assets' );
```

### Instalacja modułu na stronie

1. Skopiuj katalog modułu z `cyber-modules` (z konkretnego taga) do
   `child/modules/<nazwa>/`.
2. Dopisz wiersz do `child/modules/MODULES.md`: nazwa, wersja, data, tag.
3. Jeśli moduł ma `acf-json/`: **Custom Fields → Sync** dla jego grup.
   Jeśli ma `default-acf.php`: procedura jak dla parenta (CLAUDE.md 16),
   tylko w katalogu modułu.
4. Ustawienia modułu w panelu według jego `README.md`.

Aktualizacja = podmiana katalogu na nowszy tag i nowy wiersz w `MODULES.md`.
Poprawka zrobiona na stronie wraca do `cyber-modules` jako commit; w childzie
zostaje dopiero jej wersja z biblioteki.

## Checklista przed oddaniem strony

- [ ] `git status` w `cyber-framework/` jest czysty i wskazuje tag z GitHuba.
- [ ] Wszystkie zmiany strony leżą w childzie; `git log` childa je opisuje.
- [ ] `child/acf-json/` zawiera każdą grupę strony; w `cyber-framework/acf-json/`
      nie ma nowych ani zmienionych plików.
- [ ] `modules/MODULES.md` wymienia każdy moduł z wersją.
- [ ] Panel nie pokazuje ostrzeżenia o katalogu `acf-json/` childa.
