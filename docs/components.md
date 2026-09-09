# Komponenty i layouty — Cyber Framework

Ostatnia aktualizacja: 2026-09-09.

## Status

**Brak zdefiniowanych komponentów i layoutów Flexible Content.** System komponentów
to etap 4 w kolejności budowy (CLAUDE.md sekcja 17); obecnie zrealizowane są etapy 1 i 2.

Katalogi `template-parts/components/` i `template-parts/sections/` istnieją, ale są puste.

## Reguła, którą będzie realizować ten dokument

Relacja jest jednokierunkowa i jawna:

```
ACF layout → template-part → HTML → CSS
```

Każdy nowy layout Flexible Content musi trafić do tabeli poniżej **w tym samym commicie**,
w którym powstał.

## Layouty Flexible Content

| Nazwa layoutu (ACF) | Plik | Używane pola | Assety |
|---|---|---|---|
| *(brak)* | — | — | — |

## Komponenty reużywalne

| Komponent | Plik | Argumenty (`$args`) | Assety |
|---|---|---|---|
| *(brak)* | — | — | — |

## Zasady

1. Jeden layout ACF = jeden plik w `template-parts/sections/`.
2. Komponent nie sięga po stan globalny — dane dostaje jawnie przez `$args`
   w `get_template_part()` (CLAUDE.md sekcja 4).
3. Assety specyficzne dla komponentu kolejkowane są warunkowo, przy jego renderowaniu,
   nie globalnie (CLAUDE.md sekcja 10).
4. Escaping wykonywany bezpośrednio przy outpucie w pliku widoku.
