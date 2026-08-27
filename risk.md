# Security Risk Register — Sistem Data Perjawatan (MySTAFF)

**Date:** 26 August 2026
**Scope:** Authentication & authorization, routes, Filament resources/policies, controllers,
imports/exports, console commands, Blade views, repository hygiene.
**Classification of data at risk:** Personnel records — names, IC numbers (`nokp`), placement,
pension, contract, and waran data (government PII).

Severity scale: **Critical > High > Medium > Low**.

---

## Risk Summary

| Severity | Count |
|----------|-------|
| Critical | 2 |
| High | 3 |
| Medium | 7 |
| Low / Informational | 3 |

---

## CRITICAL

### R-01 — Real personnel database dumps committed to git

| | |
|---|---|
| **Affected** | `dump-sdperjawatan-202604201638.sql` … `dump-sdperjawatan-202606031634.sql` (7 files), `DB_dump/dump-sdperjawatan-202606221623.sql`, `DB_dump/dump-sdperjawatan-202608040815.sql` |
| **Status** | Open |

Full SQL dumps containing `INSERT INTO pegawais ...` and related tables are tracked in
version control. They expose staff names, IC numbers, placements, pension and contract data to
anyone with repository access. Deleting the files now does **not** remove them from git history.

**Impact:** Mass disclosure of government personnel PII; permanent exposure via repo history;
regulatory/privacy breach if the repo is ever pushed off-site.

**Recommendation**
1. Remove the dump files from the working tree.
2. Add `*.sql` and `DB_dump/` to `.gitignore`.
3. Purge history with `git filter-repo` or BFG Repo-Cleaner, then force-push and have all clones re-sync.
4. Treat the data as breached if the repo was ever shared outside the team; rotate any credentials present in dumps.

---

### R-02 — Unauthenticated export endpoints (PII disclosure)

| | |
|---|---|
| **Affected** | `routes/web.php:22-26`; `app/Http/Controllers/LetakJawatanExportController.php`; `app/Http/Controllers/PenamatanPerkhidmatanExportController.php` |
| **Status** | Open |

`GET /export-letak-jawatan` and `GET /export-penamatan-perkhidmatan` have **no `auth`
middleware**, unlike the other four export routes. Neither controller performs an authorization
check internally, so any unauthenticated visitor can download staff movement and retirement reports.

Compounding factor: the `Pegawai` global scope (`app/Models/Pegawai.php:40`) applies PTJ
filtering only when a user is authenticated — an unauthenticated request sees everything.

**Impact:** Unauthenticated download of government HR reports (PII).

**Recommendation**
1. Add `->middleware('auth')` to both routes.
2. Add a role/policy check (consistent with the other export endpoints) inside each controller.
3. Consider one shared export policy covering all six export routes.

---

## HIGH

### R-03 — Privilege escalation: Admins can grant themselves Super Admin

| | |
|---|---|
| **Affected** | `app/Policies/UserPolicy.php:37-40`; `app/Filament/Resources/Users/Schemas/UserForm.php:72-80`; `app/Filament/Resources/Users/Pages/ListUsers.php:36-38`; `app/Filament/Imports/UserImporter.php:86-91,97-102` |
| **Status** | Open |

`UserPolicy::update()` allows role-2 Admins to edit users, but the `role` select in the user
form is not gated by role (only the *password* field is superadmin-only). An Admin can therefore
change any account — including their own — to Super Admin.

Additionally, the superadmin-only `ImportAction` accepts arbitrary `role` values per row
(including Super Admin), and `UserImporter::resolveRecord()` uses `firstOrNew(nokp)`, so a
re-import silently overwrites existing users' roles and passwords.

**Impact:** Full privilege escalation → complete control of personnel data, exports, user management.

**Recommendation**
1. Gate the `role` field to Super Admin only (`->visible(fn () => auth()->user()->role === 1)` on edit).
2. Restrict Admin updates to non-Super-Admin targets, ideally within their own PTJ.
3. Make the importer create-only (reject rows matching existing `nokp`) or forbid role escalation beyond the operator's own level.

---

### R-04 — Destructive scheduled commands running every 5 seconds

| | |
|---|---|
| **Affected** | `routes/console.php:10-14`; `app/Console/Commands/DeletePegawaiTamatPerkhidmatan.php`; `app/Console/Commands/DeletePegawaiByTarikhKuatkuasa.php` |
| **Status** | Open |

Two commands that soft-delete pegawai records (based on `tarikh_kuatkuasa` /
`tarikh_pencen` dates) are scheduled with `everyFiveSeconds()`. A date-boundary bug mass-deletes
records almost instantly and the schedule continuously hammers the database.

**Impact:** Accidental bulk soft-deletion of personnel records; DB load; hard-to-audit automation.

**Recommendation**
1. Reschedule to `->dailyAt('01:00')` or similar.
2. Add a grace window / dry-run flag and notify admins *before* deletion where feasible.

---

### R-05 — Authentication hardening gaps

| | |
|---|---|
| **Affected** | `app/Filament/Pages/Auth/Login.php:34,43-55`; `app/Filament/Pages/Auth/RequestPasswordReset.php:27-58`; `.env.example` |
| **Status** | Open |

1. Login identity is the IC number (`nokp`) and password reset identifies accounts purely by
   `nokp` lookup — the IC becomes a single high-value secret; compromise of an IC + weak
   password yields full account takeover.
2. Cloudflare Turnstile **fails open** when keys are unset (`config('services.turnstile.secret_key')`
   truthy check) — in production without keys, CAPTCHA protection is silently disabled on both
   login and password reset.
3. Base Filament login throttling should be confirmed active after the custom override.

**Recommendation**
1. Enforce password complexity (`Password::defaults()`); consider a second identifier factor.
2. Fail closed on missing Turnstile keys in production (env guard), fail open only locally.
3. Verify rate limiting fires on the custom login page.

---

## MEDIUM

### R-06 — XSS via raw JSON injection in dashboard script block

`resources/views/filament/pages/dashboard.blade.php:250,258` uses
`{!! json_encode(...) !!}` inside `<script>`. A `</script>` sequence in `desc_program` breaks out
of the script context. Reference-table data makes this low likelihood but the fix is trivial:
use `@json(...)` instead.

### R-07 — Public avatar storage and third-party name leak

`app/Filament/Pages/Auth/EditProfile.php:51-62` uploads avatars to `disk('public')`
(unauthenticated `/storage` access); `app/Models/User.php:69` falls back to ui-avatars.com,
sending staff names to an external service. Use a private disk with signed URLs or a
controller-mediated route, and replace the external avatar fallback with a local initial-based render.

### R-08 — No password policy

No `Password::defaults()` rules anywhere; generated passwords use `Str::random(10)`
(`UserForm.php:89`). Add minimum-length and (optionally) compromised-password checks for
create/edit/import paths.

### R-09 — Debug mode / transport hardening posture

`.env.example` ships `APP_DEBUG=true`; no `URL::forceScheme('https')`, TrustProxies, or HSTS
configuration exists. Ensure production sets debug off, forces HTTPS, secures session cookies,
and enables HSTS at the web server.

### R-10 — Sensitive data in logs and notifications

`app/Filament/Resources/Pegawais/Pages/EditPegawai.php:61` logs full attribute diffs (names, ICs);
database notifications broadcast staff details to role 1–2 users. Keep `laravel.log` private,
apply retention policy per government guidelines.

### R-11 — Dead unprotected controller

`app/Http/Controllers/WaranJawatanController.php` is routed nowhere (only `routes/web.php`
exists) yet performs unauthenticated writes/deletes if ever registered. Delete it or add auth +
policy before wiring it up. Related: export controllers throw 500s on malformed query params
(`Carbon::create(null, …)`) — validate inputs.

### R-12 — Data-integrity defects enabling confusion

1. `PegawaiKontrak::pegawai()` relation points at wrong foreign key `'ptj_id'`
   (`app/Models/PegawaiKontrak.php:27`) — mis-joins records.
2. `nokp` uniqueness rule commented out in `PegawaiForm.php:53` — duplicate identities possible.

Not direct vulnerabilities, but they undermine audit trails and record trustworthiness.

---

## LOW / INFORMATIONAL

- **R-13** `Log::info('Pegawai Created'…)` style entries include staff names — minor privacy noise (see R-10).
- **R-14** `SESSION_ENCRYPT=false` default — acceptable on trusted network; enable in production if policy requires.
- **R-15** Health endpoint `/up` publicly reachable — standard, confirm no info leakage when combined with debug mode.

---

## Controls Verified as Present (no action)

| Control | Evidence |
|---|---|
| CSRF protection | `PreventRequestForgery` middleware in `AppPanelProvider` |
| Session hijack mitigation | `AuthenticateSession` middleware; logout invalidates session + regenerates token |
| SQL injection | Eloquent/binding used throughout; `whereRaw` usages are constant subqueries or bound params (`WaransTable.php:166-177`, `UserImporter.php:70`) |
| Authorization policies | 18 model policies incl. `UserPolicy`, `PegawaiPolicy`; PTJ-scoping global scope on `Pegawai` |
| User enumeration resistance | Password reset returns generic success regardless of account existence |
| Secret hygiene | `.env` not tracked (only `.env.example`, no secrets committed outside SQL dumps) |
| Password storage | `'password' => 'hashed'` cast; bcrypt rounds 12 |

---

## Remediation Priority

| Priority | Risks | Target |
|---|---|---|
| Immediate (today) | R-01, R-02 | Remove dumps + purge history; lock down export routes |
| This week | R-03, R-04, R-05 | Role gating, scheduler cadence, auth hardening |
| Next sprint | R-06 – R-12 | XSS, avatar storage, password policy, config posture, cleanup |
| Backlog | R-13 – R-15 | Hygiene items |
