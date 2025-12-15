# Event Manager

Wtyczka do zarządzania wydarzeniami oraz rejestracji uczestników.
Dane rejestracji są przechowywane w `post_meta` w polu `event_registrations` (tablica).

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

## Znane ograniczenia / TODO 
- Wyszukiwarka eventów po mieście / dacie - AJAX
- Podgląd listy rejestracji w WP
