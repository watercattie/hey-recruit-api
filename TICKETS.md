# Sprint-Plan: Heyrecruit API

**Aufwand:** ~11h | **Sprint:** 1

---

# Epic: API-Synchronisation für externe Systeme

Externe Systeme sollen Bewerber und Bewerbungen über eine REST-API synchronisieren können – sicher, mandantenisoliert und ohne Duplikate.

**Business Value:** Kunden können ihre ATS/HR-Systeme anbinden und Bewerberdaten automatisch synchronisieren.

---

## Story 1: API-Zugriff ermöglichen

**Als** externes System  
**möchte ich** mich mit einem API-Token authentifizieren,  
**um** sicher auf die Heyrecruit-API zugreifen zu können.

### Akzeptanzkriterien
- [ ] Mit gültigem Token kann ich API-Requests machen
- [ ] Ohne/mit ungültigem Token erhalte ich einen Fehler
- [ ] Mein Token-Zugriff wird geloggt

---

### Tasks

#### Task 1.1: DB-Schema (~1.5h)
Migrationen für alle Tabellen mit Constraints.

**Tabellen:**
- `companies` (id, name)
- `api_tokens` (company_id, token, is_active) → UNIQUE(token)
- `applicants` (company_id, external_id, email) → UNIQUE(company_id, external_id/email)
- `jobs` (company_id, external_id, title) → UNIQUE(company_id, external_id)
- `applicant_jobs` (applicant_id, job_id, status) → UNIQUE(applicant_id, job_id)
- `audit_logs` (api_token_id, entity_type, action, result)

**Done wenn:** `bin/cake migrations migrate` erstellt alle Tabellen mit FK + Unique Constraints

---

#### Task 1.2: ORM + Testdaten (~1.5h)
Table-Klassen mit Associations, Validation, Seeder.

**Associations:**
- Company hasMany → ApiTokens, Applicants, Jobs
- ApplicantJob belongsTo → Applicant, Job

**Validation:**
- Applicant: `external_id` OR `email` required
- ApplicantJob: `status` ∈ {new, screening, interview, offer, hired, rejected}

**Seeder:** 2 Companies, je 2 Tokens, 5 Jobs, 10 Applicants

**Done wenn:** `$applicantJob->applicant` lädt korrekt

---

#### Task 1.3: Token-Auth (~2h)
Bearer Token Middleware mit Company-Context.

**Flow:**
```
Authorization: Bearer <token>
├─ fehlt/ungültig/inaktiv → 401
└─ gültig → company_id verfügbar, last_used_at updaten
```

**Done wenn:** `$this->getCompanyId()` im Controller verfügbar

**Risiko:** Token in Logs → Logger-Config prüfen

---

## Story 2: Bewerbungen abrufen

**Als** externes System  
**möchte ich** Bewerbungen zu einem Job abrufen,  
**um** diese in meinem System anzeigen zu können.

### Akzeptanzkriterien
- [ ] Ich kann alle Bewerbungen zu einem Job auflisten
- [ ] Ich kann Details einer Bewerbung inkl. Bewerber-Daten abrufen
- [ ] Ich sehe nur Daten meiner eigenen Company

---

### Tasks

#### Task 2.1: List-Endpoint (~1h)
```
GET /api/v2/applicant-jobs?job_id=123
→ {"data": [{"id": 1, "status": "new", "applied_at": "..."}]}
```

- Company-Filter über Applicant-Join
- Ohne job_id → 400

**Done wenn:** Nur eigene Company-Daten zurückgegeben

---

#### Task 2.2: Detail-Endpoint (~1h)
```
GET /api/v2/applicant-jobs/123
→ {"data": {"id": 123, "applicant": {...}, "job": {...}}}
```

- Applicant + Job eager-loaden
- Fremde Company → 404 (nicht 403!)

**Done wenn:** Vollständige Daten mit Applicant + Job

**Risiko:** N+1 Queries → `contain()` nutzen

---

## Story 3: Bewerbungen synchronisieren

**Als** externes System  
**möchte ich** Bewerber und Bewerbungen erstellen/aktualisieren,  
**um** Daten ohne Duplikate zu übertragen.

### Akzeptanzkriterien
- [ ] Neuer Bewerber + Bewerbung wird erstellt
- [ ] Existierender Bewerber wird erkannt (via external_id oder email)
- [ ] Existierende Bewerbung wird aktualisiert (oder "noop" wenn keine Änderung)
- [ ] Jede Operation wird im Audit-Log protokolliert

---

### Tasks

#### Task 3.1: Upsert-Endpoint (~1h)
```
POST /api/v2/applicant-jobs
{
  "applicant": {"external_id": "EXT-123", "email": "max@example.com"},
  "job_id": 789,
  "status": "new"
}
→ {"data": {"result": "created", "applicant_job_id": 123, "applicant_id": 456}}
```

- Schema-Validation (job_id required, email Format)
- Business-Validation (Job existiert + gehört Company)

**Done wenn:** 422 bei Validation-Fehlern mit Details

---

#### Task 3.2: Upsert-Logik (~2h)
Applicant + ApplicantJob upsert in Transaktion.

**Logik:**
1. Applicant suchen: erst `external_id`, dann `email`
2. Applicant erstellen oder aktualisieren
3. ApplicantJob suchen: `applicant_id` + `job_id`
4. ApplicantJob erstellen oder aktualisieren
5. Result: `created` | `updated` | `noop`

**Done wenn:** Alle 5 Result-Szenarien funktionieren

**Risiko:** Race Condition → UNIQUE Constraint + Retry

---

#### Task 3.3: Audit-Logging (~1h)
Jede Operation loggen.

**Felder:** api_token_id, entity_type, entity_id, action, result, payload

**Done wenn:** Nach jedem Upsert Eintrag in audit_logs

---

## Definition of Done (alle Stories)

- [ ] Akzeptanzkriterien erfüllt
- [ ] Tests geschrieben und grün
- [ ] PHPStan Level 5 ohne Fehler
- [ ] README: API-Doku + Designentscheidungen
