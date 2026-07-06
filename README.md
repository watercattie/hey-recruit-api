# Heyrecruit API - Take-Home Challenge

**Zeitraum:** 03.03.2026 - 05.03.2026
**Zeitaufwand:** ~12 Stunden

---

## Quick Start

```bash
# Repository klonen
git clone <repo-url>
cd hey-recruit-api

# Dependencies installieren
composer install

# Datenbank (SQLite) initialisieren
bin/cake migrations migrate
bin/cake migrations seed

# Tests ausführen
vendor/bin/phpunit          # 88 Tests, 216 Assertions
vendor/bin/phpstan analyse  # Level 5, keine Errors
vendor/bin/phpcs            # CakePHP Standard

# Development Server
bin/cake server             # http://localhost:8765
```

**API-Dokumentation:** http://localhost:8765/swagger.html (Swagger UI)

**Voraussetzungen:** PHP 8.1+, Composer (kein MySQL nötig - SQLite)

---

## Projekt-Struktur

```
src/
├── Controller/
│   └── Api/V2/ApplicantJobsController.php   # REST-Endpoints
├── Dto/                                      # Data Transfer Objects
│   ├── ApplicantJobUpsertRequestDto.php
│   ├── ApplicantRequestDto.php
│   ├── ApplicantUpsertResult.php
│   ├── ApplicantJobUpsertResult.php
│   └── UpsertResultDto.php
├── Enum/
│   └── ApplicationStatus.php                 # Status: new, screening, etc.
├── Repository/                               # Datenbankzugriff
│   ├── ApplicantRepository.php
│   └── ApplicantJobRepository.php
├── Service/
│   ├── ApplicantJobUpsertService.php        # Orchestriert Upsert-Workflow
│   ├── ApplicantJobTransformer.php          # Entity → API Response
│   └── AuditLogService.php
├── Validator/                                # Validierung (getrennt!)
│   ├── RequestValidator.php                  # Schema: required, format, enum
│   └── BusinessValidator.php                 # Business: Job existiert?
├── Model/
│   ├── Entity/                              # CakePHP Entities
│   └── Table/                               # CakePHP Table Classes
└── Error/
    ├── ApiExceptionRenderer.php
    └── Exception/ValidationException.php
```

### Architektur-Pattern

| Pattern                  | Zweck                                       |
| ------------------------ | ------------------------------------------- |
| **Repository**           | Kapselt Datenbankzugriff, testbar           |
| **DTO**                  | Typisierte Datenobjekte, keine Magic Arrays |
| **Validator (2-stufig)** | Schema vs. Business-Logik getrennt          |
| **Transformer**          | Entity → API Response Mapping               |
| **Action Injection**     | DI direkt in Controller-Methoden            |

---

## API Endpoints

### Authentication

Alle Endpoints erfordern Bearer Token im Authorization Header:

```
Authorization: Bearer <64-char-token>
```

Token ist Company-gebunden → automatische Mandanten-Isolation.

---

### GET /api/v2/applicant-jobs

Listet alle Bewerbungen der Company.

**Response 200:**

```json
{
    "data": [
        {
            "id": 1,
            "status": "screening",
            "applied_at": "2026-03-01T10:00:00+00:00",
            "created_at": "2026-03-01T10:00:00+00:00",
            "updated_at": "2026-03-01T12:00:00+00:00",
            "applicant": {
                "id": 1,
                "external_id": "EXT-123",
                "email": "max@example.com",
                "first_name": "Max",
                "last_name": "Mustermann"
            },
            "job": {
                "id": 1,
                "external_id": "JOB-456",
                "title": "Senior Developer"
            }
        }
    ]
}
```

---

### GET /api/v2/applicant-jobs/{id}

Gibt eine einzelne Bewerbung zurück.

**Response 200:** Wie oben, aber einzelnes Objekt in `data`.

**Response 404:**

```json
{
    "error": {
        "code": "NOT_FOUND",
        "message": "Applicant job not found"
    }
}
```

---

### POST /api/v2/applicant-jobs

Erstellt oder aktualisiert Bewerber + Bewerbung (Upsert).

**Request:**

```json
{
    "applicant": {
        "external_id": "EXT-123",
        "email": "max@example.com",
        "first_name": "Max",
        "last_name": "Mustermann",
        "phone": "+49 123 456"
    },
    "job_id": 1,
    "status": "new",
    "applied_at": "2026-03-01T10:00:00Z"
}
```

| Feld                    | Pflicht | Default |
| ----------------------- | ------- | ------- |
| `applicant.external_id` | Ja\*    | -       |
| `applicant.email`       | Ja\*    | -       |
| `job_id`                | Ja      | -       |
| `status`                | Nein    | `"new"` |
| `applied_at`            | Nein    | now     |

\*Mindestens `external_id` ODER `email` erforderlich.

**Response 200:**

```json
{
    "data": {
        "result": "created",
        "applicant_job_id": 1,
        "applicant_id": 1
    }
}
```

| Result    | Bedeutung                                       |
| --------- | ----------------------------------------------- |
| `created` | Neuer Bewerber und/oder neue Bewerbung erstellt |
| `updated` | Bestehende Bewerbung aktualisiert               |
| `noop`    | Keine Änderung (Daten identisch)                |

**Status-Werte:** `new`, `screening`, `interview`, `offer`, `hired`, `rejected`

---

### Error Responses

| Code | Bedeutung                           |
| ---- | ----------------------------------- |
| 400  | Bad Request (leerer Body)           |
| 401  | Unauthorized (Token fehlt/ungültig) |
| 404  | Not Found                           |
| 422  | Validation Error                    |

**Validation Error Format:**

```json
{
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Validation failed",
        "details": {
            "job_id": ["job_id is required"],
            "applicant.external_id": ["external_id or email is required"]
        }
    }
}
```

---

## Datenbank-Design

### ER-Diagramm

```
┌───────────────┐
│   companies   │
│───────────────│
│ id (PK)       │
│ name          │
└───────┬───────┘
        │ 1:n
        ├────────────────────┬────────────────────┐
        ▼                    ▼                    ▼
┌───────────────┐    ┌───────────────┐    ┌───────────────┐
│  api_tokens   │    │  applicants   │    │     jobs      │
│───────────────│    │───────────────│    │───────────────│
│ id (PK)       │    │ id (PK)       │    │ id (PK)       │
│ company_id    │    │ company_id    │    │ company_id    │
│ token (64)    │    │ external_id   │    │ external_id   │
│ name          │    │ email         │    │ title         │
│ is_active     │    │ first_name    │    │ status        │
└───────┬───────┘    │ last_name     │    └───────┬───────┘
        │            └───────┬───────┘            │
        │ 1:n                │ 1:n            1:n │
        ▼                    └─────────┬──────────┘
┌───────────────┐                      ▼
│  audit_logs   │            ┌─────────────────────┐
│───────────────│            │   applicant_jobs    │
│ id (PK)       │            │─────────────────────│
│ api_token_id  │◄─SET NULL  │ id (PK)             │
│ entity_type   │            │ applicant_id (FK)   │
│ entity_id     │            │ job_id (FK)         │
│ action        │            │ status              │
│ result        │            │ applied_at          │
└───────────────┘            └─────────────────────┘
                                   UNIQUE(applicant_id, job_id)
```

### Tabellen

| Tabelle          | Besonderheiten                                             |
| ---------------- | ---------------------------------------------------------- |
| `companies`      | Mandanten                                                  |
| `api_tokens`     | UNIQUE(token), FK → companies CASCADE                      |
| `applicants`     | UNIQUE(company_id, external_id), UNIQUE(company_id, email) |
| `jobs`           | UNIQUE(company_id, external_id)                            |
| `applicant_jobs` | UNIQUE(applicant_id, job_id) - 1 Bewerbung pro Job         |
| `audit_logs`     | FK → api_tokens SET NULL                                   |

### Eindeutigkeitsstrategie

#### Wann ist ein Applicant eindeutig?

**Primär:** `company_id` + `external_id`
**Fallback:** `company_id` + `email`

**Begründung:**

- Externe Systeme (ATS, HR-Tools) haben eigene IDs die stabil bleiben
- Wenn `external_id` vorhanden → eindeutig identifizierbar
- Wenn nur `email` vorhanden → Fallback, aber Email kann sich ändern
- Company-Scope ist immer dabei → Mandanten-Isolation

**Lookup-Reihenfolge im Code:**

```php
// ApplicantRepository::findByIdentifier()
1. WHERE company_id = ? AND external_id = ?
2. Falls nicht gefunden: WHERE company_id = ? AND email = ?
```

#### Wann ist eine Bewerbung (ApplicantJob) eindeutig?

**Constraint:** `applicant_id` + `job_id`

**Begründung:**

- Ein Bewerber kann sich nur einmal auf einen Job bewerben
- Bei erneutem Upsert wird die bestehende Bewerbung aktualisiert
- Re-Bewerbungen nach Absage wären v2 mit `application_round`

#### Unique Constraints (DB-Level)

```sql
-- applicants
UNIQUE(company_id, external_id)
UNIQUE(company_id, email)

-- applicant_jobs
UNIQUE(applicant_id, job_id)

-- api_tokens
UNIQUE(token)

-- jobs
UNIQUE(company_id, external_id)
```

#### Business-Logik im Code

| Stelle                   | Prüfung                                       |
| ------------------------ | --------------------------------------------- |
| `RequestValidator`       | Mindestens `external_id` OR `email` vorhanden |
| `BusinessValidator`      | Job existiert und gehört zur Company          |
| `ApplicantRepository`    | Lookup erst external_id, dann email           |
| `ApplicantJobRepository` | Lookup via applicant_id + job_id              |

#### Warum DB-Level UND Code-Level?

- **DB-Level:** Letzte Verteidigungslinie bei Race Conditions
- **Code-Level:** Saubere Fehlermeldungen, kein DB-Exception-Handling in Business-Logik

---

## Upsert-Logik

```
┌─────────────────────────────────────────────────────────────────┐
│                    POST /api/v2/applicant-jobs                   │
└───────────────────────────┬─────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│  1. RequestValidator.validateApplicantJobUpsert()               │
│     → Schema: required, format, enum → 422 bei Fehler           │
└───────────────────────────┬─────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│  2. ApplicantJobUpsertRequestDto.fromArray()                    │
│     → Reines Mapping, keine Validierung                         │
└───────────────────────────┬─────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│  3. BusinessValidator.validateApplicantJobUpsert()              │
│     → Job existiert? Gehört zu Company? → 422 bei Fehler        │
└───────────────────────────┬─────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│  4. ApplicantJobUpsertService.upsert()  [Transaktion]           │
│     ├─ ApplicantRepository.upsert()                             │
│     │  └─ Find by external_id OR email, create/update           │
│     ├─ ApplicantJobRepository.upsert()                          │
│     │  └─ Find by applicant_id + job_id, create/update          │
│     └─ AuditLogService.log()                                    │
└───────────────────────────┬─────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│  5. Response: { result, applicant_job_id, applicant_id }        │
└─────────────────────────────────────────────────────────────────┘
```

---

## Tests

```bash
# Alle Tests
vendor/bin/phpunit

# Mit Coverage
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html tmp/coverage

# Einzelne Test-Klasse
vendor/bin/phpunit tests/TestCase/Controller/Api/V2/ApplicantJobsUpsertTest.php
```

**Test-Struktur:**

```
tests/TestCase/
├── Controller/Api/V2/
│   ├── ApplicantJobsGetTest.php      # GET Endpoints
│   └── ApplicantJobsUpsertTest.php   # POST Endpoint
├── Repository/
│   ├── ApplicantRepositoryTest.php
│   └── ApplicantJobRepositoryTest.php
├── Service/
│   ├── ApplicantJobUpsertServiceTest.php
│   └── ApplicantJobTransformerTest.php
└── Validator/
    ├── RequestValidatorTest.php       # Schema-Validierung
    └── BusinessValidatorTest.php      # Business-Regeln
```

---

## Designentscheidungen

| Entscheidung                | Begründung                                              |
| --------------------------- | ------------------------------------------------------- |
| **SQLite**                  | Zero-Config für Challenge, kein DB-Server nötig         |
| **Repository Pattern**      | Testbar, austauschbar, klar getrennt                    |
| **2-stufige Validierung**   | Schema (Format) vs. Business (DB-Zugriff) getrennt      |
| **Typed DTOs statt Arrays** | IDE-Support, keine Magic Strings, refactoring-safe      |
| **Enum für Status**         | Single Source of Truth, keine duplizierten Konstanten   |
| **Action Injection**        | Wie NestJS - Dependencies pro Action, nicht Constructor |
| **Hard Delete**             | Einfacher, Audit Log speichert History                  |
| **Klartext-Token**          | MVP-Fokus, Hashing wäre v2                              |

---

## Teil A - Strukturierung & Delivery

### 1. Rückfragen & Annahmen

| #   | Bereich           | Frage                                                                                  | Annahme                                                                                                                             |
| --- | ----------------- | -------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| 1   | **Eindeutigkeit** | Woran erkennen wir beim Upsert, dass es der gleiche Bewerber ist?                      | **`external_id` primär, `email` als Fallback.** Drittsysteme haben eigene IDs, die stabil sind. Email kann sich ändern.             |
| 2   | **Eindeutigkeit** | Kann sich ein Bewerber mehrfach auf denselben Job bewerben (Re-Bewerbung nach Absage)? | **Nein, 1 Bewerbung pro Job.** Upsert aktualisiert bestehende. Re-Bewerbungen wären v2 mit `application_round`.                     |
| 3   | **Token**         | Ein API-Token pro Company oder mehrere (z.B. pro Integration)?                         | **Mehrere Tokens pro Company.** Token-Rotation möglich, Integrationen im Audit unterscheidbar, einzelne Tokens deaktivierbar.       |
| 4   | **Löschung**      | Soft Delete oder Hard Delete? Bei Soft Delete: Wie mit Unique Constraints?             | **Hard Delete.** Audit Log speichert History. Soft Delete + Unique Constraints ist komplex (keine Partial Indexes in SQLite/MySQL). |
| 5   | **API Design**    | RESTful Routes (`GET /applicant-jobs/{id}`) oder RPC-style (`GET /view?id=`)?          | **RESTful.** ID im Pfad, konform zu REST-Standards. Upsert bleibt POST (Client kennt unsere ID nicht).                              |
| 6   | **Validierung**   | Welche Felder sind Pflicht beim Upsert? Nur Identifier oder auch Name/Kontakt?         | **Mindestens `external_id` ODER `email` für Applicant.** `job_id` ist Pflicht. Rest optional.                                       |
| 7   | **Status**        | Gibt es einen definierten Bewerbungsstatus-Workflow?                                   | **Nein, freies Feld.** Werte: new, screening, interview, offer, hired, rejected. Keine Workflow-Validierung in v1.                  |
| 8   | **Audit**         | Was wird im Audit Log gespeichert? Nur Result oder auch Payload?                       | **Beides.** `result` (created/updated/noop), `action`, `entity_type/id`. Payload optional für Debugging.                            |
| 9   | **Fehlerfall**    | Wie reagieren bei ungültiger `job_id`? 404 oder 422?                                   | **422 Validation Error.** Job existiert nicht = ungültige Eingabe, nicht "Resource nicht gefunden".                                 |
| 10  | **Performance**   | Erwartete Datenmenge pro Company?                                                      | **~1.000-10.000 Bewerbungen.** Standard-Indizes reichen. Keine Partitionierung nötig.                                               |

---

### 2. Scope Definition

**v1 (Diese Challenge):**

- 6 Tabellen: companies, api_tokens, applicants, jobs, applicant_jobs, audit_logs
- Token-Auth mit Mandanten-Isolation
- 3 Endpoints: GET /list, GET /{id}, POST /upsert
- Audit Logging bei jeder Upsert-Operation
- Integration + Unit Tests

**v2 (Nächste Iteration):**

- Token Hashing (SHA256) für Production Security
- Pagination für Listen-Endpoints (Cursor-based)
- Rate Limiting pro Token
- Row-Locking (FOR UPDATE) gegen Race Conditions bei Parallel-Upserts

**v3+ (Später):**

- Re-Bewerbungen ermöglichen (`application_round`)
- Bulk-Import Endpoint für Massen-Sync
- Webhooks für Änderungsbenachrichtigungen
- Token-Scopes (read-only vs. write)
- Soft Delete mit Audit-Trail

**Bewusst nicht gebaut:**

| Feature         | Begründung                            |
| --------------- | ------------------------------------- |
| User-Management | Nur API-Tokens, keine Login-UI nötig  |
| Jobs-CRUD       | Jobs existieren bereits (Seed-Daten)  |
| Frontend        | Reine API-Challenge                   |
| Soft Delete     | Hard Delete + Audit Log reicht für v1 |
| Bulk Operations | Einzelner Upsert deckt Use-Case ab    |

**Priorisierungs-Begründung:** v1 liefert den Kern-Use-Case: "Externes System synct Bewerber, ohne Duplikate, nachvollziehbar." Alles andere ist Optimierung.

---

### 3. Sprint-Plan & Tickets

**Gesamtaufwand:** ~12 Stunden

| #   | Ticket                                | Aufwand | Abhängigkeiten |
| --- | ------------------------------------- | ------- | -------------- |
| 1   | Projekt-Setup & Datenbank-Migrationen | 2.5h    | -              |
| 2   | ORM Models, Entities & Seeder         | 1.5h    | Ticket 1       |
| 3   | API Token Authentifizierung           | 1.5h    | Ticket 2       |
| 4   | GET Endpoints (index + view)          | 2h      | Ticket 3       |
| 5   | POST Upsert & Audit Logging           | 3.5h    | Ticket 3       |
| 6   | Finalisierung & Dokumentation         | 1h      | Ticket 4, 5    |

**Detaillierte Tickets:** Siehe [TICKETS.md](TICKETS.md) - vollständige Tickets mit Akzeptanzkriterien, SQL, JSON-Beispielen und Tests.

---

### 4. Risk Log

#### Security Risiken

| Risiko                   | Wahrscheinlichkeit | Impact | Mitigation                                             |
| ------------------------ | ------------------ | ------ | ------------------------------------------------------ |
| Company Isolation Bypass | Mittel             | Hoch   | Alle Queries filtern nach `company_id` aus Token       |
| Token Leak               | Niedrig            | Hoch   | Nur `token_id` im Audit Log, nie Token selbst          |
| Brute-Force Token        | Niedrig            | Mittel | 64-char Token = 2^256 Möglichkeiten; v2: Rate Limiting |

#### Data Integrity Risiken

| Risiko                    | Wahrscheinlichkeit | Impact | Mitigation                                                     |
| ------------------------- | ------------------ | ------ | -------------------------------------------------------------- |
| Doppelte Bewerbungen      | Mittel             | Mittel | UNIQUE Constraint auf `(applicant_id, job_id)`                 |
| Doppelte Bewerber         | Mittel             | Mittel | UNIQUE auf `(company_id, external_id)` + `(company_id, email)` |
| Race Condition bei Upsert | Niedrig            | Mittel | v2: Row-Locking (FOR UPDATE)                                   |

#### Performance Risiken

| Risiko           | Wahrscheinlichkeit | Impact  | Mitigation                        |
| ---------------- | ------------------ | ------- | --------------------------------- |
| Langsame Queries | Niedrig            | Mittel  | Indizes auf FKs und Lookup-Felder |
| Große Listen     | Mittel             | Niedrig | v2: Pagination mit Cursor         |

#### Wartbarkeit

| Aspekt          | Bewertung | Begründung                                         |
| --------------- | --------- | -------------------------------------------------- |
| Code-Struktur   | ✅ Gut    | Repository + Service + DTO Pattern, klare Trennung |
| Testbarkeit     | ✅ Gut    | 89 Tests, DI ermöglicht Mocking                    |
| Erweiterbarkeit | ✅ Gut    | Neue Endpoints folgen gleichem Pattern             |

#### Skalierbarkeit

| Aspekt                 | Bewertung  | Begründung                                            |
| ---------------------- | ---------- | ----------------------------------------------------- |
| Horizontale Skalierung | ✅ Möglich | Stateless API, DB ist Single Point                    |
| DB-Skalierung          | ⚠️ v2      | Read Replicas bei Bedarf, Partitionierung nicht nötig |
| Audit Log Wachstum     | ⚠️ v2      | Retention Policy nach 90 Tagen                        |

---

## AI Usage

- **Tool:** GitHub Copilot (Claude Opus 4)
- **Verwendung:**
    - Design-Diskussion & Architektur-Entscheidungen
    - Code-Generierung (Controller, Services, Repositories, Tests)
    - Refactoring (Repository Pattern, Validierung trennen, DTOs)
    - CakePHP 5 Konventionen (kein Vorwissen vorhanden)
- **Zeitersparnis:** ~50% durch schnellere Iteration
- **Manuell überarbeitet:**
    - Architektur-Reviews und Anpassungen
    - Test-Logik und Edge Cases
    - Dokumentation
- **Unsicherheiten:**
    - CakePHP 5 DI Container-Konfiguration (wenig Dokumentation)
    - Best Practices für Action Injection in CakePHP
