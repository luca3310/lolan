# CURL Kommandoer til Posts API

## 1. Hent alle posts (GET)

```bash
# Basis kald (henter alle posts med standard paginering)
curl -X GET "http://localhost/api/posts/getPosts/"

# Med søgning og paginering
curl -X GET "http://localhost/api/posts/getPosts/?page=1&search=post&limit=5"
```

## 2. Hent specifik post (GET)

```bash
# Hent post med ID 1
curl -X GET "http://localhost/api/posts/getPost/" \
  -H "Authorization: Bearer password" \
  -G \
  -d "id=1"
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
  http://localhost/api/posts/addPost/
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
  "http://localhost/api/posts/updatePost/?id=1"
```

## 5. Slet post (DELETE)

```bash
curl -X DELETE \
  -H "Authorization: Bearer password" \
  "http://localhost/api/posts/deletePost/?id=1"
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
  http://localhost/api/posts/addPost/

# 2. Hent alle posts for at se den nye post
curl -X GET "http://localhost/api/posts/getPosts/"

# 3. Hent den specifikke post (erstat ID med det returnerede ID)
curl -X GET "http://localhost/api/posts/getPost/" \
  -H "Authorization: Bearer password" \
  -G \
  -d "id=1"

# 4. Opdater posten
curl -X PUT \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer password" \
  -d '{
    "title": "Opdateret Test Post",
    "content": "Dette er den opdaterede version."
  }' \
  "http://localhost/api/posts/updatePost/?id=1"

# 5. Slet posten
curl -X DELETE \
  -H "Authorization: Bearer password" \
  "http://localhost/api/posts/deletePost/?id=1"
```

Bemærk:

- Alle endpoints returnerer JSON-formateret data
- Alle endpoints kræver nu authentication med Bearer token
- Alle API kald skal bruge `/api/` prefix
- Husk at tilføje en afsluttende skråstreg (/) i URL'erne
- Query parametre skal tilføjes efter skråstregen (f.eks. /?id=1)
- ID'er i eksemplerne er sat til 1, men skal ændres til det faktiske post ID
- For getPosts endpoint:
  - page: Sidenummer (standard: 1)
  - limit: Antal posts per side (standard: 10)
  - search: Søg i post titler (valgfri)
