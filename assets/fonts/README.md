# Czcionki motywu

Pliki `woff2` hostowane **lokalnie**, bez połączenia z Google Fonts. Strona
działa bez zewnętrznego serwera, a przeglądarka odwiedzającego nie wysyła
żądania do `fonts.gstatic.com` (czyli nie przekazuje tam swojego adresu IP).

Deklaracje `@font-face` są w `assets/css/fonts.css`; arkusz ładuje się wyłącznie
wtedy, gdy w Global Options → Ustawienia czcionki wybrano rodzinę z motywu
(`inc/fonts.php`).

## Rodziny

| Rodzina | Katalog | Zakres grubości | Licencja | Źródło plików |
|---|---|---|---|---|
| Space Grotesk | `space-grotesk/` | 300–700 | SIL Open Font License 1.1 | Google Fonts (`fonts.googleapis.com/css2`) |
| Manrope | `manrope/` | 200–800 | SIL Open Font License 1.1 | Google Fonts |
| Geist | `geist/` | 100–900 | SIL Open Font License 1.1 | Google Fonts |

Pełny tekst licencji: <https://openfontlicense.org/> — OFL pozwala na
hostowanie plików we własnym serwisie bez dodatkowych warunków.

## Co leży w katalogach

Po **jednym pliku zmiennym na podzbiór znaków**:

- `[slug]-latin.woff2` — alfabet łaciński podstawowy,
- `[slug]-latin-ext.woff2` — rozszerzony (polskie znaki diakrytyczne).

Jeden plik obejmuje cały zakres grubości rodziny, więc wybór wagi w zakładce
„Ustawienia czcionki” nie pobiera kolejnych plików. Przeglądarka pobiera
wyłącznie te podzbiory, których faktycznie używa treść strony
(`unicode-range` w `fonts.css`).

Razem: 6 plików, około 124 kB.

## Nowa czcionka

1. Plik `woff2` do `assets/fonts/[slug]/`.
2. Blok `@font-face` w `assets/css/fonts.css`.
3. Jeden wiersz w `cyber_font_families()` (`inc/fonts.php`).
4. Wiersz w tabeli czcionek w `CLAUDE.md` — razem z licencją.

Pole w panelu, walidacja i warunkowe ładowanie arkusza biorą się z rejestru,
więc nic więcej nie trzeba zmieniać.
