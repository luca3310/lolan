# CURL Kommandoer til Posts API

## 1. Hent alle posts (GET)

```bash
# Basis kald (henter alle posts med standard paginering)
curl -X GET "http://localhost/api/posts/"

# Med søgning og paginering
curl -X GET "http://localhost/api/posts/?page=1&search=post&limit=5"
```

## 2. Hent specifik post (GET)

```bash
# Hent post med ID 1
curl -X GET "http://localhost/api/posts/?id=1"
```

## 3. Opret ny post (POST)

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer password" \
  -d '{
    "title": "Min Første Post",
    "content": "Dette er indholdet af min første post."
  }' \
  http://localhost/api/posts/
```

## 4. Opdater post (PUT)

```bash
curl -X PUT \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer password" \
  -d '{
    "title": "Opdateret Post Titel",
    "content": "Dette er det opdaterede indhold af posten."
  }' \
  "http://localhost/api/posts/?id=1"
```

## 5. Opdater kun indhold (PATCH)

```bash
curl -X PATCH \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer password" \
  -d '{
    "content": "Dette er det nye indhold, uden at ændre titlen."
  }' \
  "http://localhost/api/posts/?id=1"
```

## 6. Slet post (DELETE)

```bash
curl -X DELETE \
  -H "Authorization: Bearer password" \
  "http://localhost/api/posts/?id=1"
```

## Eksempel på komplet workflow

```bash
# 1. Opret en ny post
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer password" \
  -d '{
    "title": "Test Post",
    "content": "Dette er en test post."
  }' \
  http://localhost/api/posts/

# 2. Hent alle posts for at se den nye post
curl -X GET "http://localhost/api/posts/"

# 3. Hent den specifikke post (erstat ID med det returnerede ID)
curl -X GET "http://localhost/api/posts/?id=1"

# 4. Opdater posten
curl -X PUT \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer password" \
  -d '{
    "title": "Opdateret Test Post",
    "content": "Dette er den opdaterede version."
  }' \
  "http://localhost/api/posts/?id=1"

# 5. Opdater kun indholdet
curl -X PATCH \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer password" \
  -d '{
    "content": "Kun indholdet er opdateret."
  }' \
  "http://localhost/api/posts/?id=1"

# 6. Slet posten
curl -X DELETE \
  -H "Authorization: Bearer password" \
  "http://localhost/api/posts/?id=1"
```

Bemærk:

- Alle endpoints bruger nu den samme base URL: `/api/posts/`
- Alle endpoints returnerer JSON-formateret data
- Alle write-operationer (POST, PUT, PATCH, DELETE) kræver Bearer token authentication
- Alle API kald skal bruge `/api/` prefix
- Query parametre tilføjes efter base URL'en (f.eks. /?id=1)
- ID'er i eksemplerne er sat til 1, men skal ændres til det faktiske post ID
- For GET endpoint (liste af posts):
  - page: Sidenummer (standard: 1)
  - perPage: Antal posts per side (standard: 10)
  - limit: Maksimalt antal posts at returnere totalt (valgfri)
  - search: Søg i post titler (valgfri)
- PATCH bruges når man kun vil opdatere enkelte felter (f.eks. kun content)
