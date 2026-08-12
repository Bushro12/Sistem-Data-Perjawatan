# PRD — MySTAFF (Sistem Data Perjawatan)

| | |
|---|---|
| **Product name** | MySTAFF (SDP Perjawatan) |
| **Version** | 1.0 (draft) |
| **Date** | 12 August 2026 |
| **Status** | In development |
| **Stack** | Laravel 13 · Filament 5 · MySQL · Tailwind CSS 4 · Vite · maatwebsite/excel |

---

## 1. Background & Problem Statement

Human-resource (perjawatan) administration in the organisation is currently
managed through spreadsheets and paper records. Officers, vacancies, warrants
(waran), resignations (letak jawatan), and pensions (pencen) live in separate
sources, making it hard to:

- know how many posts (JIK) are filled, vacant, over- or under-staffed at a glance;
- control which PTJ (Pusat Tanggungjawab / responsibility centres) can see and edit data;
- produce standardised reports for upper management;
- keep track of end-of-service events (pension, resignation) and act on time.

MySTAFF replaces this with a single web application built on the Filament admin
panel, giving role-based, PTJ-scoped access to staffing data with live
dashboards, warrant tracking, notifications, and Excel exports.

---

## 2. Goals (Non-Negotiable)

1. **Single source of truth** for officers (pegawai), posts, and warrants.
2. **Role-based access control** (3 roles) with **PTJ-level data scoping** so a
   normal user only sees their own PTJ's data.
3. **Waran (warrant) management** with automatic JIK balancing: filled, vacant,
   over (Lebih), under (Kurang), balanced (Seimbang).
4. **End-of-service lifecycle**: pensions and resignations are recorded,
   announced via notifications, and automatically removed from the active
   officer list on their effective date.
5. **Standardised reports** downloadable as Excel for management.
6. **Announcements (Hebahan)** broadcast to all users via the dashboard and
   in-app notifications.

---

## 3. Users & Roles

| Role | Code | Access |
|---|---|---|
| **Superadmin** | `role = 1` | Full access to all data, all PTJs, all reference data, delete/restore anywhere. |
| **Admin** | `role = 2` | Full data access across all PTJs; deletion restricted (e.g. Bahagian delete denied by policy). |
| **User** | `role = 3` | Data scoped to their own `ptj_id` (global scopes on Pegawai, Waran, WaranJawatan, LetakJawatan, Pencen). May edit officers borrowed to their PTJ even if the officer's home PTJ differs. |

Each user belongs to a PTJ, has a name, `nokp` (IC), phone, avatar, status, and
role. New users receive a welcome email with a generated password; password
reset uses the user's IC number (not an email address).

---

## 4. Functional Requirements

### 4.1 Dashboard (Home)

- Live counts: total warans, warans with status **Lebih / Kurang / Seimbang**,
  total PTJs, total pegawai.
- Recent warans (latest 5) with drill-down.
- Waran distribution by Program (nama program, description, waran count).
- Recent published announcements (Hebahan) that are currently visible
  (not expired).
- Current date header (Malay locale).

### 4.2 Pegawai (Officer Records)

- Full CRUD + view page per officer.
- Fields:
  - Identity: `nama`, `nokp` (IC), `jantina`, `emel`
  - Placement: `ptj`, `bahagian`, `unit`, `subunit`, `parlimen`/`dun` (via unit/subunit)
  - Post: `jawatan` + `gred` (through `jawatan__greds` pivot, grouped by `kumpulan`)
  - Appointment: `tarikh_lantikan`, `tarikh_sah_jawatan`, `tarikh_pencen`, `tarikh_pinjam` (borrow date)
  - Status flags: `is_tetap` (permanent), `is_kontrak` (contract),
    `is_kontrak_interim`, `is_kup`, `is_kupj`, `is_jtw`,
    `is_kontrak_isi_tetap` (Kontrak Isi Tetap), `ada_unit`, `ada_subunit`
  - Pension option (`opsyen_pencen`)
- Soft-deletes; global scope hides officers outside the user's PTJ (unless the
  user is superadmin/admin).
- Officers selected in placement screens are shown as `Nama (IC)`.

### 4.3 Waran (Warrants) & WaranJawatan (Placements)

- Warrant header: `no_waran`, `jenis` (**Tambah** / **Tolak**), `jik`,
  `catatan`.
- **WaranJawatan** records link a warrant to: PTJ / Bahagian / Unit / Subunit,
  Aktiviti (activity under a Program), Jawatan+Gred set (`jawatan_ids`,
  `gred_ids`), optional Pegawai, `butiran`, `is_kup`, `catatan_jawatan`, and a
  lifecycle `status` (**active** / **removed**).
- A **Tolak** warrant references the original placement via `waran_tolak_id`.
- Computed per warrant (PTJ-aware for normal users):
  - `jik_count` — approved post count
  - `isi_count` — filled posts (active placements with an officer; for Tolak
    warrants: removed placements)
  - `kosong_count` = jik − isi
  - `status_jik` = **Kurang** (vacant) / **Lebih** (over) / **Seimbang**
- Dashboard and list views aggregate placements per Aktiviti, per PTJ, and per
  Butiran (grouped counts).
- Removing a placement is a state change (`status = removed`), not a hard delete,
  so history is preserved and calculations stay correct.
- **Nama Penyandang** module: shows the current occupant of each post
  (contract officers excluded from the selection list).

### 4.4 TBK (related placement record)

- `tbk`, linked to `waran_jawatan`, `gred`, `pegawai` — supplementary
  placement/entitlement data attached to a warrant placement.

### 4.5 Letak Jawatan (Resignation)

- Records: PTJ, jawatan+gred, `nama`, `nokp`, `tarikh_notis`, `tarikh_kuatkuasa`
  (effective date), `jenis_notis`, `alasan` (reason), appointment info
  (`tarikh_lantik`, `lantikan`), bond/loan flags (`ikatan_jpa`, `ikatan_bpl`,
  `pinjaman_lppsa`).
- View page per record.
- Scheduled cleanup removes the officer from the active Pegawai list once the
  effective date passes; notification sent ahead of the date.

### 4.6 Pencen (Pension)

- Records: PTJ, jawatan+gred, `opsyen_pencen`, `jenis_pencen`, `nama`, `nokp`,
  `jenis_lantikan`, `tarikh_lantikan`, `tarikh_sah_jawatan`, `umur_pencen`
  (pension age), `tarikh_pencen`, `tarikh_kuatkuasa`, `tempoh_perkhidmatan`
  (length of service), `catatan`.
- View page per record; same scheduled cleanup and notification behaviour as
  resignation.

### 4.7 Hebahan & Info (Announcements & Info Page)

- Hebahan CRUD: `tajuk`, `kandungan`, `tarikh_hebahan`, `lampiran`, `status`
  (draft/published), `dipaparkan_sehingga` (display-until date).
- Published, non-expired announcements appear on the dashboard.
- Public **Info** page (navigation group "Hebahan & Info") for static
  information content.

### 4.8 Reference Data (Kawalan)

CRUD + search + sort for all reference entities, mostly with PTJ/bahagian
dependencies:

- **PTJ** — nama, kod, alamat, pengarah, `is_jkn` flag, `rujukan_surat`,
  parlimen/dun
- **Bahagian → Unit → Subunit** — hierarchical, with parlimen/dun, soft deletes
- **Parlimen / DUN** — constituency lists
- **Jawatan** (positions) — kod, desc
- **Gred** (grades) — kod, desc
- **Kumpulan** (groups) — nama
- **Jawatan × Gred** pivot (`jawatan__greds`) — with `kumpulan_id`
- **Program → Aktiviti → Butiran** — programme/activity/budget-line hierarchy
- **JenisPencen** (pension type/category), **OpsyenPencen** (pension options)

### 4.9 Laporan (Reports)

A "Senarai Laporan" page listing available reports with a **Muat Turun**
(download) action; some reports prompt for parameters (e.g. select Jawatan for
JIK-by-Jawatan). Excel generation via maatwebsite/excel:

1. Data Keseluruhan Mengikut PTJ
2. Data Perjawatan Kontrak
3. Data Mengikut Kumpulan Mengikut PTJ
4. Data Keseluruhan Mengikut Jawatan
5. Laporan JIK Mengikut Jawatan (parameterised)
6. Laporan JIK Mengikut Gred

Reports must record the generation timestamp (cetakan dijana) and be
PTJ-scoped for normal users.

### 4.10 Users & Administration

- User CRUD (Kawalan group); create → welcome email with password.
- Bulk user import from Excel (`UserImporter`).
- User export.
- Profile page: change password.
- Login / forgot-password flows customised to the organisation (IC-based
  reset, rate-limited).
- Audit-friendly delete/restore via soft deletes and per-resource policies.

### 4.11 Notifications

- Database notifications (30s polling) for:
  - Pegawai approaching pension / end of service
  - Pegawai resignations (letak jawatan) with effective dates
  - Warrant placement status updates (active/removed)
- Requires the queue worker to run (`php artisan queue:work`).

### 4.12 Scheduled Jobs

- `DeletePegawaiByTarikhKuatkuasa` — remove officers whose end-of-service
  (resignation/pension) effective date has passed.
- `DeletePegawaiTamatPerkhidmatan` — clean up end-of-service officers.
- Run via `php artisan schedule:work`.

---

## 5. Non-Functional Requirements

| Area | Requirement |
|---|---|
| **Performance** | Filament SPA mode; pagination options up to 100 rows; preloaded/searchable relation selects. |
| **Security** | Role + policy-based authorisation on every resource; global PTJ scopes at the model level (defence in depth); soft deletes everywhere for recoverability. |
| **Localisation** | UI in Bahasa Malaysia (labels, dates via `locale('ms')`); domain data in Malay. |
| **Branding** | MySTAFF brand, custom logo/favicon, Public Sans font, teal primary, dark mode supported. |
| **Email** | SMTP (Gmail) for welcome email, password reset, notifications; queue worker required. |
| **Data** | MySQL; DB dumps used for backup/transfer; seed data for reference tables. |
| **Dev workflow** | `composer run setup` bootstraps a fresh environment (migrate + build); Pint for code style; Pest for tests; Laravel Pail for logs. |

---

## 6. Acceptance Criteria (High-Level)

- A normal user (role 3) sees only their PTJ's pegawai, waran, letak jawatan,
  and pencen records in every list, search, report, and export.
- Creating a warrant with placements updates JIK status correctly
  (Kurang/Lebih/Seimbang) and the dashboard reflects it immediately.
- Marking a placement removed decrements filled count but keeps history.
- A recorded resignation/pension generates a notification and removes the
  officer from Pegawai after the effective date via the scheduled job.
- Each report downloads as a valid Excel file and respects the PTJ scope.
- New user creation sends the welcome email with credentials.
- UAT checklists exist and pass for each resource and role
  (see `UAT_Bahagian_Resource_Role2.md` pattern).

---

## 7. Out of Scope (Current Version)

- Self-service officer/mobile app (web admin only).
- Integration with external HR/payroll systems or ePangkat/HRMIS.
- Automatic gender inference from IC number (KIV).
- Full audit-trail UI (only soft-delete recoverability today).
- Custom JIK report builder beyond the fixed report list (custom filters
  by program/PTJ/skim are planned).

---

## 8. Open Questions / Backlog

- Confirm exact meaning and use of TBK module with the business owner.
- Define required fields and layout for "cetakan dijana" (generated-print
  header) on reports.
- Confirm whether normal users may edit borrowed officers from other PTJs
  (in progress per meeting notes).
- Add JIK report customisation by program, PTJ, and skim.
- Gender auto-detection from IC (KIV).
