# Event Manager

Wtyczka do zarządzania wydarzeniami oraz rejestracji uczestników.
- Dane rejestracji są przechowywane w `post_meta` w polu `event_registrations` (tablica).
- Wtyczka zawiera również AJAX wyszukiwarkę eventów (po mieście i zakresie dat) dostępną jako shortcode.

## Instalacja

### Wymagania
- WordPress: 6.0+ (zalecane 6.5+)
- PHP: 8.0+
- Wtyczka: Advanced Custom Fields (ACF) (free lub PRO)

### Kroki instalacji
1. Skopiuj folder `event-manager` do: `wp-content/plugins/`
2. W panelu WP przejdź do **Plugins** i aktywuj **Event Manager**
3. Upewnij się, że ACF jest zainstalowane i aktywne
4. W panelu pojawi się CPT **Events** oraz taksonomia **Cities**

### Aktywacja / pierwsze uruchomienie
Po aktywacji wtyczki:
- Dodaj event w **Events → Add New Event**
- Ustaw pola ACF (na dole okna edycji eventu, w Meta Boxes):
  - Event start date & time (wymagane)
  - Event participant limit (wymagane)
  - Event description (opcjonalnie)
- Przypisz miasto (taksonomia)

## Funkcjonalność
- CPT `event` + taksonomia `city`
- Pola ACF dla eventu (data, limit, opis)
- Widok single eventu z formularzem zapisu
- Rejestracja uczestników przez AJAX
- Walidacja i sanityzacja po stronie PHP
- Nonce dla bezpieczeństwa
- Obsługa limitu miejsc oraz duplikatów email
- (Ważne) Rejestracja uczestników dostępna tylko dla zalogowanych użytkowników
- Wyszukiwarka eventów (miasto + zakres dat) dostępna jako shortcode:
  - shortcode: 
  ``` text
  [em_event_search]
  ```

### Wyszukiwarka (AJAX)
- Renderuje formularz wyszukiwania eventów za pomocą shortcode
- Filtry
  - miasto (taksonomia `city`)
  - zakres dat (pole ACF `event_start_datetime`)
- Wyniki ładowane dynamicznie bez przeładowania strony (AJAX)

#### Przykład użycia:
1. Utwórz nową stronę (np. „Events search”)
2. Dodaj blok Gutenberg **Paragraph**
3. Wklej shortcode:
```text 
[em_event_search] 
```

## AJAX Endpoints

### `POST /wp-admin/admin-ajax.php?action=register_event`

Rejestruje uczestnika na event. Dane trafiają do `post_meta` pod kluczem `event_registrations`.

#### Parametry (POST)
- `action`: `register_event`
- `nonce`: nonce wygenerowany w PHP (przekazywany do JS jako `EM_AJAX.nonce`)
- `event_id`: ID eventu (integer)
- `name`: imię/nazwa (string)
- `email`: email (string)

#### Przykładowy success (JSON)
```json
{
  "success": true,
  "data": {
    "message": "Successfully registered!",
    "current_count": 4,
    "limit": 10
  }
}
```

Przykładowy error (JSON)
```json
{
  "success": false,
  "data": {
    "message": "This event is full."
  }
}
```
### `POST /wp-admin/admin-ajax.php?action=em_search_events`

Zwraca listę eventów w postaci HTML (renderowany po stronie PHP), na podstawie filtrów.

#### Parametry (POST)
- `action`: `em_search_events`
- `nonce`: nonce (jak wyżej)
- `city`: slug miasta (string, opcjonalnie)
- `date_from`: `YYYY-MM-DD` (string, opcjonalnie)
- `date_to`: `YYYY-MM-DD` (string, opcjonalnie)

#### Przykładowy success (JSON)
```json
{
  "success": true,
  "data": {
    "html": "<ul class=\"em-results\">...</ul>"
  }
}
```
Przykładowy error (JSON)
```json
{
  "success": false,
  "data": {
    "message": "Security check failed."
  }
}
```

## Znane ograniczenia / TODO 
- Podgląd listy rejestracji w WP
- Paginacja wyników wyszukiwarki

## WordPress Playground (Test Environment)

Projekt zawiera gotowe środowisko testowe oparte o WordPress Playground.

Środowisko:
- instaluje WordPress
- instaluje i aktywuje ACF
- instaluje i aktywuje wtyczkę Event Manager
- importuje przykładowe dane (eventy, miasta, stronę wyszukiwania)

### Uruchomienie

Kliknij w link poniżej, aby uruchomić środowisko testowe w przeglądarce:

https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/Kodecky/event-manager/main/playground/blueprint.json
