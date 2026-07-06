Modul 1 - Take-Home Work Sample (6-10
Stunden)
Ziel
Du implementierst eine API-Funktionalität in CakePHP 5 – inklusive eigenständigem
Datenbank-Design. Du erhältst kein vorgegebenes Datenbankschema. Du entwirfst selbst:
● Tabellen
● Beziehungen
● Constraints
● Indizes
● Eindeutigkeitsstrategien
Wir möchten sehen, wie Du als Tech Lead Datenmodelle strukturierst.
Ausgangssituation
Heyrecruit ist eine Multi-Tenant SaaS-Plattform im Recruiting-Bereich.
Externe Systeme sollen Bewerber und Bewerbungen über eine API synchronisieren können.
Rahmenbedingungen:
● Keine Duplikate
● Mandantenfähigkeit (Company-Isolation)
● Nachvollziehbarkeit (Audit Logging)
● Sicherer Zugriff (API-Token)
● Spätere Anpassbarkeit des Unique-Modells
Die Anforderungen sind bewusst nicht vollständig spezifiziert.
Teil A - Strukturierung & Delivery (Pflicht)
Bitte liefere im README:
1) Rückfragen & Annahmen
Formuliere:
● Maximal 10 Rückfragen
● Klare Annahmen, falls unbeantwortet
Wir bewerten hier Dein Denken unter Unsicherheit.
2) Scope Definition
Beschreibe:
● Was ist v1?
● Was wäre v2?
● Was kommt später?
● Was würdest Du bewusst nicht bauen?
Begründe Deine Priorisierung.
3) Sprint-Plan & Ticket-Schnitt
Erstelle eine sprintreife Ticketliste für 1-2 Sprints mit:
● Titel
● Ziel
● Akzeptanzkriterien
● Risiken
● Abhängigkeiten
Hier prüfen wir Deine Delivery-Kompetenz.
4) Risk Log
Bitte bewerte kurz:
● Security Risiken
● Data Integrity Risiken
● Performance Risiken
● Wartbarkeit
● Skalierbarkeit
Teil B - Datenbank-Design (Pflicht)
Du entwirfst selbst das vollständige Datenbankschema.
Anforderungen an Dein Datenmodell
Dein Schema muss folgende Konzepte abbilden:
● Companies
● API Tokens
● Applicants
● Jobs
● ApplicantJobs (Bewerbungen)
● Audit Logs
Erwartet wird:
1) Tabellenstruktur
● Tabellen
● Felder
● Datentypen
● Primary Keys
2) Beziehungen
● Foreign Keys
● Kardinalitäten
● Cascade-Regeln (falls sinnvoll)
3) Eindeutigkeitsstrategie
Du definierst selbst:
● Wann ist ein Applicant eindeutig?
● Wann ist eine Bewerbung eindeutig?
● Welche Unique Constraints setzt Du?
● Welche Business-Logik ergänzt Du im Code?
Diese Entscheidungen müssen im README erklärt werden.
4) Migrationen
Erstelle Migrationen (CakePHP Migrations oder SQL) für:
● Tabellen
● Constraints
● Indizes
Teil C - API-Implementierung (CakePHP 5)
Implementiere folgende Endpunkte:
1) GET
/api/v2/applicant-jobs/ids?job_id=...
→ Liefert eine Liste von applicant_job_ids für ein bestimmtes Job-Posting
2) GET
/api/v2/applicant-jobs/view?id=...
→ Liefert Details einer Bewerbung inklusive Applicant-Daten
3) POST
/api/v2/applicant-jobs/upsert
→ Erstellt oder aktualisiert Applicant + ApplicantJob ohne Duplikate.
Du definierst selbst:
● JSON Payload
● Update-Strategie
● Unique-Strategie
● Noop-Logik (falls nichts geändert wurde)
Diese Logik muss dokumentiert werden.
Technische Mindestanforderungen
Authentifizierung
● API Token
● Company Isolation
● Kein Zugriff ohne gültiges Token
Datenintegrität
● Keine Duplikate
● Eindeutigkeitsstrategie sauber dokumentiert
● DB-Level + Code-Level Absicherung
Audit Logging
Jede Upsert-Operation erzeugt:
● Timestamp
● Actor / Token
● Action
● Result (created/updated/noop)
● Optional: Metadaten
Tests
Mindestens 3-5 Tests:
● Unauthorized Request
● Create
● Update
● Validation Fail
● Audit Log geschrieben
AI Usage Section (Pflicht)
Im README bitte offenlegen:
● Welche KI-Tools wurden genutzt?
● Wofür?
● Welche Teile wurden manuell überarbeitet?
● Wo bestehen Unsicherheiten?
Transparenz ist wichtiger als Einschränkung.
Abgabe
● Git Repository
● README mit:
○ Setup Anleitung
○ API Dokumentation
○ Scope & Tickets
○ Risk Log
○ Designentscheidungen
○ Datenbank-Design-Erklärung
○ AI Usage Section
Modul 2 - Live Defense (60 Minuten)
Im Interview gehst Du durch Dein Repository.
Wir erwarten:
● Erklärung des Request-Flows
● Begründung Deiner Unique-Strategie
● Erklärung Deiner DB-Entscheidungen
● Diskussion von Edge Cases
● Diskussion möglicher Race Conditions
Abgabe
Bitte stelle sicher, dass Deine Lösung spätestens bis Freitag, 18:00 Uhr vollständig im
Git-Repository committed und gepusht ist (inkl. Code, Migrationen, Tests und README).
Wichtig:
● Wenn Du kürzer brauchst, ist das völlig okay.
● Wenn Du länger brauchst, ist das ebenfalls okay.
Wir bitten Dich in beiden Fällen, im README kurz zu vermerken:
● wann Du gestartet hast,
● wann Du abgeschlossen hast,
● und grob, wie viel Zeit Du insgesamt investiert hast.
Die Zeitangabe ist kein Knock-out-Kriterium - sie hilft uns nur, Aufwand und Ergebnis fair
einzuordnen.