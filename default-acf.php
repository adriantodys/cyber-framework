<?php
/**
 * Cyber Framework - wartosci domyslne konkretnego projektu.
 *
 * PLIK JEDNORAZOWY. Wypelnia sie go raz, na poczatku wdrozenia, wartosciami
 * z zatwierdzonego projektu graficznego - a po skonczonej konfiguracji kasuje.
 *
 * PRZEBIEG
 *
 *   1. WordPress + ACF PRO + motyw. Jeszcze BEZ synchronizacji pol.
 *   2. Wypelniasz ten plik. Pole, ktorego nie zmieniasz, mozesz zostawic albo
 *      usunac - brak klucza znaczy "zostaw wartosc motywu".
 *   3. Prompt: przenies wartosci domyslne z default-acf.php
 *      do acf-json/group_global_options.json.
 *      Kazda wartosc przechodzi walidacje przez cyber_option_schema();
 *      wartosc poza zakresem albo spoza listy zatrzymuje CALY zapis.
 *   4. Synchronizacja pol ACF w panelu.
 *   5. Ustawienia -> Zapisz. Wartosci laduja w wp_options i od tej chwili
 *      to ONE decyduja o wygladzie strony.
 *   6. Sprzatanie:  usun ten plik  +  git checkout acf-json/
 *      JSON moze wrocic do stanu z repozytorium, bo po kroku 5 jego wartosci
 *      domyslne sa juz nieistotne. Instalacja zostaje identyczna z frameworkiem.
 *
 * FORMAT WPISU
 *
 *   'klucz' => wartosc, // typ/zakres | opis pola
 *
 * Prefiks cyber_ jest opcjonalny - 'color_links' i 'cyber_color_links' znacza
 * to samo. Kolejnosc i nazwy sekcji odpowiadaja zakladkom w panelu WordPress.
 *
 * Wartosci wpisane ponizej to DOMYSLNE WARTOSCI MOTYWU - punkt wyjscia,
 * nie propozycja. Nadpisz te, ktore wynikaja z projektu.
 *
 * Cztery pola sa zakomentowane i oznaczone TYLKO PANEL: logo, logo stopki
 * i dwa odnosniki do stron. ACF trzyma tam ID zalacznika albo ID strony,
 * ktorego nie da sie znac przed wgraniem pliku - ustawia sie je w panelu.
 *
 * @package Cyber_Framework
 */

return array(

	/* ======================================================================
	 * GLOWNE USTAWIENIA STRONY
	 * ====================================================================== */

	/* --- Szerokosc glowna -------------------------------------------------- */
	'page_width_type'                         => '80',          // 100|80|60      | Szerokosc glowna

	/* --- Szerokosc strony -------------------------------------------------- */
	'page_width_60'                           => 1150,          // px 320-4000    | Szerokosc strony 60%
	'page_width_80'                           => 1500,          // px 320-4000    | Szerokosc strony 80%
	'page_width_100'                          => '',            // px 320-4000    | Szerokosc strony 100%

	/* --- Odstepy ----------------------------------------------------------- */
	'page_margin_desktop'                     => 40,            // px 0-200       | Margines (do 980px)
	'page_margin_tablet'                      => 30,            // px 0-200       | Margines (980px-767px)
	'page_margin_mobile_l'                    => 30,            // px 0-200       | Margines (767px-479px)
	'page_margin_mobile_s'                    => 20,            // px 0-200       | Margines (ponizej 479px)


	/* ======================================================================
	 * USTAWIENIA CZCIONKI
	 * ====================================================================== */

	/* --- Wielkosci czcionek (Desktop) -------------------------------------- */
	'font_size_h1'                            => 48,            // px 8-200       | Naglowek H1
	'font_size_h2'                            => 40,            // px 8-200       | Naglowek H2
	'font_size_h3'                            => 32,            // px 8-200       | Naglowek H3
	'font_size_h4'                            => 26,            // px 8-200       | Naglowek H4
	'font_size_h5'                            => 22,            // px 8-200       | Naglowek H5
	'font_size_h6'                            => 18,            // px 8-200       | Naglowek H6
	'font_size_overtitle_1'                   => 16,            // px 8-200       | Overtitle 1
	'font_size_overtitle_2'                   => 14,            // px 8-200       | Overtitle 2
	'font_size_text'                          => 16,            // px 8-200       | Tekst (p, span, ul, li)
	'font_size_links'                         => 16,            // px 8-200       | Linki (a)

	/* --- Skalowanie responsywne -------------------------------------------- */
	'font_scale_tablet'                       => 90,            // proc. 10-200   | Tablet (980-767px)
	'font_scale_mobile'                       => 80,            // proc. 10-200   | Mobile (767-479px)
	'font_scale_mobile_small'                 => 70,            // proc. 10-200   | Mobile small (ponizej 479px)

	/* --- Czcionki ---------------------------------------------------------- */
	'font_family_headings'                    => 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif', // system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif|Georgia, "Times New Roman", Times, serif|"Helvetica Neue", Helvetica, Arial, sans-serif | Czcionka naglowkow
	'font_family_text'                        => 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif', // system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif|Georgia, "Times New Roman", Times, serif|"Helvetica Neue", Helvetica, Arial, sans-serif | Czcionka tekstu

	/* --- Grubosc czcionki -------------------------------------------------- */
	'font_weight_headings'                    => '700',         // 300|400|500|600|700|800 | Grubosc naglowkow
	'font_weight_overtitle'                   => '600',         // 300|400|500|600|700|800 | Grubosc overtitle
	'font_weight_text'                        => '400',         // 300|400|500|600|700|800 | Grubosc tekstu
	'font_weight_links'                       => '400',         // 300|400|500|600|700|800 | Grubosc linkow


	/* ======================================================================
	 * HEADER DESKTOP
	 * ====================================================================== */

	/* --- Logo -------------------------------------------------------------- */
	// 'header_logo'                          => '',            // TYLKO PANEL    | Logo
	                                             // obrazek - ACF trzyma ID zalacznika

	/* --- Wariant ukladu ---------------------------------------------------- */
	'header_variant'                          => 'default',     // default|centered|cta|woocommerce | Wariant headera

	/* --- Przycisk CTA ------------------------------------------------------ */
	'header_cta_text'                         => '',            // tekst          | Tresc przycisku CTA
	'header_cta_url'                          => '',            // url            | Adres przycisku CTA

	/* --- Uklad i odstepy --------------------------------------------------- */
	'header_menu_alignment'                   => 'right',       // left|center|right | Wyrownanie menu
	'header_padding_top'                      => 24,            // px 0-200       | Padding gorny
	'header_padding_bottom'                   => 24,            // px 0-200       | Padding dolny
	'header_bg_color'                         => '#ffffff',     // kolor          | Tlo headera
	'header_sticky'                           => false,         // tekst          | Przyklejony header

	/* --- Menu glowne ------------------------------------------------------- */
	'header_menu_item_gap'                    => 32,            // px 0-200       | Odstep miedzy pozycjami
	'header_menu_link_padding'                => 8,             // px 0-100       | Padding linku
	'header_menu_font_size'                   => 16,            // px 8-100       | Wielkosc czcionki
	'header_menu_font_weight'                 => '500',         // 300|400|500|600|700|800 | Grubosc czcionki
	'header_menu_color'                       => '#1a1a1a',     // kolor          | Kolor linku
	'header_menu_color_hover'                 => '#0057ff',     // kolor          | Kolor po najechaniu
	'header_menu_color_active'                => '#0057ff',     // kolor          | Kolor aktywnej strony
	'header_submenu_indicator'                => true,          // tekst          | Wskaznik podmenu

	/* --- Podmenu ----------------------------------------------------------- */
	'header_submenu_alignment'                => 'left',        // left|center|right | Wyrownanie podmenu
	'header_submenu_item_gap'                 => 0,             // px 0-200       | Odstep miedzy pozycjami
	'header_submenu_link_padding'             => 10,            // px 0-100       | Padding linku
	'header_submenu_font_size'                => 15,            // px 8-100       | Wielkosc czcionki
	'header_submenu_font_weight'              => '400',         // 300|400|500|600|700|800 | Grubosc czcionki
	'header_submenu_color'                    => '#1a1a1a',     // kolor          | Kolor linku
	'header_submenu_color_hover'              => '#0057ff',     // kolor          | Kolor po najechaniu
	'header_submenu_color_active'             => '#0057ff',     // kolor          | Kolor aktywnej strony


	/* ======================================================================
	 * HEADER MOBILE
	 * ====================================================================== */

	/* --- Prog przelaczania ------------------------------------------------- */
	'header_mobile_breakpoint'                => 980,           // px 320-2000    | Prog menu mobilnego


	/* ======================================================================
	 * PRZYCISKI
	 * ====================================================================== */

	/* --- Trzy rozmiary przyciskow ------------------------------------------ */

	/* --- Przycisk Large (.btn-large) --------------------------------------- */
	'btn_large_padding_y'                     => 18,            // px 4-120       | Large - padding gora/dol
	'btn_large_padding_x'                     => 40,            // px 4-200       | Large - padding lewo/prawo
	'btn_large_font_size'                     => 18,            // px 8-100       | Large - wielkosc czcionki
	'btn_large_font_weight'                   => '600',         // 300|400|500|600|700|800 | Large - grubosc czcionki
	'btn_large_color'                         => '#ffffff',     // kolor          | Large - kolor tekstu
	'btn_large_color_hover'                   => '#ffffff',     // kolor          | Large - kolor tekstu po najechaniu
	'btn_large_bg_color'                      => '#0057ff',     // kolor          | Large - kolor tla
	'btn_large_bg_color_hover'                => '#0041c2',     // kolor          | Large - kolor tla po najechaniu

	/* --- Przycisk Medium (.btn-medium) ------------------------------------- */
	'btn_medium_padding_y'                    => 14,            // px 4-120       | Medium - padding gora/dol
	'btn_medium_padding_x'                    => 32,            // px 4-200       | Medium - padding lewo/prawo
	'btn_medium_font_size'                    => 16,            // px 8-100       | Medium - wielkosc czcionki
	'btn_medium_font_weight'                  => '600',         // 300|400|500|600|700|800 | Medium - grubosc czcionki
	'btn_medium_color'                        => '#ffffff',     // kolor          | Medium - kolor tekstu
	'btn_medium_color_hover'                  => '#ffffff',     // kolor          | Medium - kolor tekstu po najechaniu
	'btn_medium_bg_color'                     => '#0057ff',     // kolor          | Medium - kolor tla
	'btn_medium_bg_color_hover'               => '#0041c2',     // kolor          | Medium - kolor tla po najechaniu

	/* --- Przycisk Small (.btn-small) --------------------------------------- */
	'btn_small_padding_y'                     => 10,            // px 4-120       | Small - padding gora/dol
	'btn_small_padding_x'                     => 24,            // px 4-200       | Small - padding lewo/prawo
	'btn_small_font_size'                     => 14,            // px 8-100       | Small - wielkosc czcionki
	'btn_small_font_weight'                   => '600',         // 300|400|500|600|700|800 | Small - grubosc czcionki
	'btn_small_color'                         => '#ffffff',     // kolor          | Small - kolor tekstu
	'btn_small_color_hover'                   => '#ffffff',     // kolor          | Small - kolor tekstu po najechaniu
	'btn_small_bg_color'                      => '#0057ff',     // kolor          | Small - kolor tla
	'btn_small_bg_color_hover'                => '#0041c2',     // kolor          | Small - kolor tla po najechaniu


	/* ======================================================================
	 * KOLORY
	 * ====================================================================== */

	/* --- Kolory semantyczne ------------------------------------------------ */
	'color_headings'                          => '#111111',     // kolor          | Naglowki h1-h6
	'color_text'                              => '#333333',     // kolor          | Tekst
	'color_overtitle_1'                       => '#0057ff',     // kolor          | Overtitle 1
	'color_overtitle_2'                       => '#666666',     // kolor          | Overtitle 2
	'color_links'                             => '#0057ff',     // kolor          | Linki

	/* --- Kolory narzedziowe (utility) -------------------------------------- */
	'color_hover'                             => '#0041c2',     // kolor          | Kolor tekstu po najechaniu
	'color_border_1'                          => '#e0e0e0',     // kolor          | Obramowanie 1
	'color_border_2'                          => '#0057ff',     // kolor          | Obramowanie 2
	'color_shadow'                            => 'rgba(0,0,0,0.12)', // kolor          | Cien
	'color_shadow_hover'                      => 'rgba(0,0,0,0.2)', // kolor          | Cien po najechaniu


	/* ======================================================================
	 * KONTAKT
	 * ====================================================================== */

	/* --- Dane kontaktowe --------------------------------------------------- */
	'contact_address'                         => '',            // tekst wiel.    | Adres
	'contact_hours'                           => '',            // tekst wiel.    | Godziny otwarcia
	'contact_phone'                           => '',            // tekst          | Telefon
	'contact_email'                           => '',            // email          | Email
	'contact_nip'                             => '',            // tekst          | NIP
	'contact_krs'                             => '',            // tekst          | KRS
	'contact_regon'                           => '',            // tekst          | REGON


	/* ======================================================================
	 * SOCIAL MEDIA
	 * ====================================================================== */

	/* --- Profile spolecznosciowe ------------------------------------------- */
	'social_facebook'                         => '',            // url            | Facebook
	'social_instagram'                        => '',            // url            | Instagram
	'social_youtube'                          => '',            // url            | YouTube
	'social_x'                                => '',            // url            | X (Twitter)
	'social_linkedin'                         => '',            // url            | LinkedIn
	'social_tiktok'                           => '',            // url            | TikTok


	/* ======================================================================
	 * TOP HEADER
	 * ====================================================================== */

	/* --- Styl paska -------------------------------------------------------- */
	'topheader_bg_color'                      => '#111111',     // kolor          | Tlo Top Header
	'topheader_font_color'                    => '#ffffff',     // kolor          | Kolor tekstu i ikon
	'topheader_font_size'                     => 14,            // px 8-40        | Rozmiar czcionki

	/* --- Co pokazac na pasku ----------------------------------------------- */
	'topheader_show_phone'                    => true,          // tekst          | Telefon
	'topheader_show_email'                    => true,          // tekst          | Email
	'topheader_show_facebook'                 => true,          // tekst          | Facebook
	'topheader_show_instagram'                => true,          // tekst          | Instagram
	'topheader_show_youtube'                  => true,          // tekst          | YouTube
	'topheader_show_x'                        => true,          // tekst          | X (Twitter)
	'topheader_show_linkedin'                 => true,          // tekst          | LinkedIn
	'topheader_show_tiktok'                   => true,          // tekst          | TikTok


	/* ======================================================================
	 * FOOTER
	 * ====================================================================== */

	/* --- Footer - Kolumna 1 ------------------------------------------------ */
	// 'footer_logo'                          => '',            // TYLKO PANEL    | Logo stopki
	                                             // obrazek - ACF trzyma ID zalacznika
	'footer_content'                          => '',            // html           | Tresc kolumny 1

	/* --- Stylizacja Footer ------------------------------------------------- */
	'footer_bg_color'                         => '#ffffff',     // kolor          | Tlo Footer
	'footer_title_font_size'                  => 20,            // px 8-100       | Rozmiar tytulow (stopka)
	'footer_title_color'                      => '#111111',     // kolor          | Kolor tytulow (stopka)
	'footer_text_font_size'                   => 16,            // px 8-100       | Rozmiar tekstu (stopka)
	'footer_text_color'                       => '#333333',     // kolor          | Kolor tekstu (stopka)
	'footer_link_font_size'                   => 16,            // px 8-100       | Rozmiar linkow (stopka)
	'footer_link_color'                       => '#0057ff',     // kolor          | Kolor linkow (stopka)


	/* ======================================================================
	 * COPYRIGHT
	 * ====================================================================== */

	/* --- Tresc ------------------------------------------------------------- */
	'copyright_text'                          => '',            // tekst          | Tekst copyright (lewa kolumna)
	// 'copyright_privacy_link'               => '',            // TYLKO PANEL    | Strona: Polityka prywatnosci
	                                             // Page Link - ACF trzyma ID strony
	// 'copyright_cookies_link'               => '',            // TYLKO PANEL    | Strona: Polityka cookies
	                                             // Page Link - ACF trzyma ID strony

	/* --- Styl paska -------------------------------------------------------- */
	'copyright_bg_color'                      => '#111111',     // kolor          | Tlo Copyright
	'copyright_text_color'                    => '#ffffff',     // kolor          | Kolor tresci
	'copyright_link_color'                    => '#ffffff',     // kolor          | Kolor linkow
	'copyright_text_font_size'                => 14,            // px 8-40        | Rozmiar czcionki tresci
	'copyright_link_font_size'                => 14,            // px 8-40        | Rozmiar czcionki linkow


	/* ======================================================================
	 * BREADCRUMB
	 * ====================================================================== */

	/* --- Okruszki na stronach witryny -------------------------------------- */
	'breadcrumb_enable'                       => false,         // tekst          | Pokaz breadcrumb
	'breadcrumb_font_size'                    => 14,            // px 8-40        | Rozmiar czcionki
	'breadcrumb_color'                        => '#666666',     // kolor          | Kolor okruszkow
	'breadcrumb_color_active'                 => '#111111',     // kolor          | Kolor biezacej strony


	/* ======================================================================
	 * BREADCRUMB WOOCOMMERCE
	 * ====================================================================== */

	/* --- Okruszki na stronach sklepu --------------------------------------- */
	'breadcrumb_wc_enable'                    => false,         // tekst          | Pokaz breadcrumb WooCommerce
	'breadcrumb_wc_font_size'                 => 14,            // px 8-40        | Rozmiar czcionki
	'breadcrumb_wc_color'                     => '#666666',     // kolor          | Kolor okruszkow
	'breadcrumb_wc_color_active'              => '#111111',     // kolor          | Kolor biezacej strony


	/* ======================================================================
	 * WOOCOMMERCE
	 * ====================================================================== */

	/* --- Lista produktow --------------------------------------------------- */
	'wc_pagination_type'                      => 'pagination',  // pagination|loadmore | Sposob wczytywania produktow

	/* --- Kolory sklepu ----------------------------------------------------- */
	'wc_color_main'                           => '#d32f2f',     // kolor          | Glowny kolor sklepu

	/* --- Strona produktu - uklad ------------------------------------------- */
	'wc_product_col_image'                    => 40,            // proc. 20-80    | Szerokosc kolumny ze zdjeciami
	'wc_product_col_summary'                  => 60,            // proc. 20-80    | Szerokosc kolumny z informacjami
	'wc_product_media_height'                 => 500,           // px 200-1200    | Wysokosc sekcji ze zdjeciem i galeria
	'wc_product_thumb_height'                 => 158,           // px 60-400      | Wysokosc miniatury w galerii

	/* --- Strona produktu - elementy ---------------------------------------- */
	'wc_product_show_title'                   => true,          // tekst          | Tytul produktu
	'wc_product_pos_title'                    => 10,            // liczba 1-200   | Pozycja: Tytul produktu
	'wc_product_show_sku'                     => true,          // tekst          | SKU
	'wc_product_pos_sku'                      => 20,            // liczba 1-200   | Pozycja: SKU
	'wc_product_show_rating'                  => false,         // tekst          | Ocena i liczba opinii
	'wc_product_pos_rating'                   => 25,            // liczba 1-200   | Pozycja: Ocena i liczba opinii
	'wc_product_show_excerpt'                 => true,          // tekst          | Krotki opis
	'wc_product_pos_excerpt'                  => 30,            // liczba 1-200   | Pozycja: Krotki opis
	'wc_product_show_price'                   => true,          // tekst          | Cena
	'wc_product_pos_price'                    => 40,            // liczba 1-200   | Pozycja: Cena
	'wc_product_show_stock'                   => true,          // tekst          | Dostepnosc w magazynie
	'wc_product_pos_stock'                    => 50,            // liczba 1-200   | Pozycja: Dostepnosc w magazynie
	'wc_product_show_cart'                    => true,          // tekst          | Ilosc i przycisk Dodaj do koszyka
	'wc_product_pos_cart'                     => 60,            // liczba 1-200   | Pozycja: Ilosc i przycisk Dodaj do koszyka
	'wc_product_show_meta'                    => false,         // tekst          | Kategorie i tagi
	'wc_product_pos_meta'                     => 70,            // liczba 1-200   | Pozycja: Kategorie i tagi
	'wc_product_show_quantity'                => true,          // tekst          | Pole ilosci
	'wc_product_show_sale'                    => true,          // tekst          | Plakietka promocji
	'wc_product_show_tabs'                    => true,          // tekst          | Zakladki z opisem
	'wc_product_pos_tabs'                     => 10,            // liczba 1-200   | Pozycja: Zakladki z opisem
	'wc_product_show_upsells'                 => false,         // tekst          | Produkty polecane (upsell)
	'wc_product_pos_upsells'                  => 20,            // liczba 1-200   | Pozycja: Produkty polecane (upsell)
	'wc_product_show_related'                 => false,         // tekst          | Podobne produkty
	'wc_product_pos_related'                  => 30,            // liczba 1-200   | Pozycja: Podobne produkty

);
