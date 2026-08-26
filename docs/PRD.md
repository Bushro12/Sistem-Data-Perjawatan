# Product Requirements Document (PRD)

## Sistem Data Perjawatan — MySTAFF

**Version:** 1.0  
**Date:** 19 Ogos 2026  
**Status:** Active Development  
**Platform:** Web (Laravel 11 + Filament v3 + MySQL)

---

## 1. Executive Summary

**Sistem Data Perjawatan** (MySTAFF) is a centralized personnel data management system built for **Jabatan Kesihatan Negeri (JKN) Kedah**. The system manages the complete lifecycle of government staff positioning (perjawatan), from warrant (waran) creation through personnel assignment, transfers, pension, and resignation.

The primary goal is to provide a single source of truth for staff warrant data, enabling accurate J/I/K (Jawatan-Isi-Kosong) reporting, personnel tracking across organizational hierarchies, and exportable Excel reports for ministry-level decision-making.

---

## 2. Problem Statement

| Problem | Impact |
|---|---|
| Staff warrant data is scattered across spreadsheets and manual records | Inconsistent data, duplication, errors |
| No centralized tracking of JIK (Jawatan/Isi/Kosong) status | Difficulty reporting staffing levels to ministry |
| Manual Excel report generation | Time-consuming, error-prone, outdated data |
| No audit trail for warrant changes | Accountability gaps |
| Users can only see data relevant to their PTJ | Siloed information without controlled access |

---

## 3. Goals & Objectives

1. **Centralize** all perjawatan data (warans, pegawai, pencen, letak jawatan) into a single system
2. **Automate** JIK calculations and Excel report generation
3. **Enforce** role-based access control (Superadmin, Admin, User/PTJ)
4. **Track** complete lifecycle: warrant creation → personnel placement → pension/resignation
5. **Report** accurately to ministry level with downloadable Excel exports

---

## 4. User Roles & Access Control

| Role | Code | Access Level |
|---|---|---|
| **Superadmin** | `role = 1` | Full access to all data across all PTJs. Can create/edit/delete warrants, manage users, view all reports. |
| **Admin** | `role = 2` | Same as Superadmin — full cross-PTJ access. Can manage warrants and pegawai. |
| **User (PTJ Staff)** | `role = 3` | Scoped to their assigned PTJ (`ptj_id`). Can view/edit data within their PTJ only. Cannot create/delete warrants. |

### Access Scoping Rules

- **Pegawai, Pencen, LetakJawatan, WaranJawatan** — all use `ptj_access` global scope: regular users see only records belonging to their PTJ (via `ptj_id` or waran_jawatan → ptj relationship)
- **Waran** — regular users see warans that have any `waran_jawatan` rows linked to their PTJ
- **User registration** — users with `role = 3` must have a valid `ptj_id`
- **Login** — email-based with Turnstile CAPTCHA verification

---

## 5. System Architecture

### 5.1 Technology Stack

| Layer | Technology |
|---|---|
| **Framework** | Laravel 11 |
| **Admin Panel** | Filament v3 (dark mode enabled, SPA navigation) |
| **Database** | MySQL 8.x (`sdperjawatan`) |
| **Frontend** | Blade templates, Tailwind CSS, Chart.js, ApexCharts |
| **Export** | Laravel Excel (Maatwebsite) |
| **Auth** | Filament auth with Turnstile CAPTCHA |
| **MCP** | Laravel Boost (development tooling) |

### 5.2 Deployment

- **URL**: `https://sistem-data-perjawatan.test`
- **PHP**: 8.3+ (CLI), 8.4 (Herd)
- **Database**: MySQL on port 3308

---

## 6. Module Requirements

### 6.1 Buku Waran (Warrant Book)

The core module. Manages government warrants (warans) — the official documents authorizing staffing positions.

#### 6.1.1 Waran

**Model**: `Waran`  
**Navigation Group**: Buku Waran  
**Icon**: `heroicon-o-document-text`

**Fields:**

| Field | Type | Description |
|---|---|---|
| `no_waran` | string, unique | Warrant number (e.g. "123/2026") |
| `jenis` | enum: `Tambah` / `Tolak` | Type: Add positions or Remove positions |
| `jik` | integer | Total Jawatan (J) count for this warrant |
| `catatan` | text, nullable | Notes |
| `parent_id` | FK → warans, nullable | Parent warrant (for versioning, currently unused) |

**Computed Attributes (dynamic):**
- `jik_count` — count of jawatan per user's PTJ scope
- `isi_count` — count of filled positions (waran_jawatan with pegawai assigned)
- `kosong_count` — jik_count − isi_count
- `status_jik` — `Seimbang` (balanced), `Kurang` (under-filled), or `Lebih` (over-filled)

**Navigation Badge**: Shows count of warans with `Kurang`/`Lebih` status (red badge).

**Table Columns**: Bil, No Waran, Butiran, Aktiviti, Penempatan, J, I, K, Status (badge)

**Filters**: Program, Aktiviti, PTJ

**Actions**: Edit, Delete (with notification to Superadmin), View

**Relations**: `waranJawatan` (has many), `waranTolakJawatan` (has many)

#### 6.1.2 Waran Jawatan (Penempatan Waran)

**Model**: `WaranJawatan`  
**Navigation Group**: (appears as Relation Manager inside Waran)

Each `waran_jawatan` row represents a single jawatan (position) within a waran.

**Fields:**

| Field | Type | Description |
|---|---|---|
| `waran_id` | FK → warans | Parent waran |
| `jawatan_ids` | JSON array | Jawatan (position types) |
| `gred_ids` | JSON array | Gred (pay grades) |
| `butiran` | string, required | Description/details of the position |
| `aktiviti_id` | FK → aktivitis | Activity assignment |
| `ptj_id` | FK → ptjs | Pusat Tanggungjawab (Responsibility Center) |
| `bahagian_id` | FK → bahagians, nullable | Division |
| `pegawai_id` | FK → pegawais, nullable | Assigned personnel |
| `status` | enum: `active` / `pindaan nama` / `batal nama` / `removed` / `deleted` | Position status |
| `is_kup` | boolean | Khas Untuk Penyandang (Reserved for specific person) |
| `is_kupj` | boolean | KUP for position |
| `catatan_jawatan` | text, nullable | Notes |
| `tarikh_kuatkuasa` | date, nullable | Effective date of the warrant |
| `tbk` | integer, nullable | Tempat Bertukar Keluar (transfer indicator) |
| `tbk_gred_id` | FK → greds, nullable | TBK grade |
| `waran_tolak_id` | FK → warans, nullable | Linked tolak waran |

**Form Tabs:**
1. **Maklumat Waran** — Aktiviti, Butiran, Jawatan (multi-select), Gred (multi-select, cascading), PTJ, Bahagian, Status, Tarikh Kuatkuasa
2. **Nama Penyandang** — Pegawai (searchable, filtered by jawatan+gred), KUP checkbox, Catatan

**Infolist (View Modal):**
- Maklumat Waran tab — No Waran, Butiran, Aktiviti, Jawatan/Gred, PTJ, Bahagian, Status, Tarikh Kuatkuasa
- Nama Penyandang tab — Nama Pegawai, No K/P, Jawatan/Gred (with TBK indicator), PTJ asal, Bahagian asal, Unit asal, Subunit asal

**Status Management:**
- `active` — normal position
- `pindaan nama` — name amendment
- `batal nama` — name cancellation
- `removed` — position removed (soft deleted, linked to `waran_tolak_id`)

**Permissions by Role:**
- Superadmin/Admin: full CRUD
- User (role 3): can view and edit KUP status only. Cannot edit other fields when KUP is checked. Pegawai dropdown filtered to own PTJ.

**TBK (Tempat Bertukar Keluar):**
When a waran has multiple gred (e.g., U5, U6, U7) and a pegawai is assigned whose gred falls inside the range but is not the lowest, a TBK indicator is created. This affects JIK counting in reports.

#### 6.1.3 Tolak Waran Workflow

When `jenis = Tolak`:
- The waran represents removal of positions
- Active view shows jawatan candidates from existing active warans
- Inactive view shows already-removed jawatan (linked via `waran_tolak_id`)
- Actions: "Buang Jawatan" (marks as removed, sets `waran_tolak_id`), "Aktifkan Jawatan" (restores)

---

### 6.2 Pegawai (Personnel)

**Model**: `Pegawai`  
**Navigation Group**: Pegawai  
**Icon**: `heroicon-o-users`  
**Soft Deletes**: Yes

**Fields:**

| Field | Type | Description |
|---|---|---|
| `ptj_id` | FK → ptjs | Responsibility center |
| `bahagian_id` | FK → bahagians | Division |
| `unit_id` | FK → units, nullable | Unit |
| `subunit_id` | FK → subunits, nullable | Sub-unit |
| `jawatan_gred_id` | FK → jawatan__greds | Position + Grade |
| `opsyen_pencen_id` | FK → opsyen_pencens, nullable | Pension option |
| `nama` | string, required | Full name (stored uppercase) |
| `nokp` | string, required | IC number (unique) |
| `jantina` | string, required | Gender |
| `tarikh_lantikan` | date, nullable | Appointment date |
| `tarikh_sah_jawatan` | date, nullable | Position confirmation date |
| `tarikh_pencen` | date, nullable | Pension date |
| `tarikh_pinjam` | date, nullable | Loan/transfer date |
| `tarikh_sandang` | date, nullable | Inauguration date (max: 31 Dec current year) |
| `is_tetap` | boolean | Permanent staff |
| `is_kontrak` | boolean | Contract staff |
| `is_kontrak_interim` | boolean | Interim contract |
| `is_kontrak_isi_tetap` | boolean | Contract filling permanent position |
| `is_kup` | boolean | Khas Untuk Penyandang |
| `is_kupj` | boolean | KUP for position |
| `is_jtw` | boolean | Jawatan Tanpa Waran (position without warrant) |
| `emel` | string, nullable | Email |
| `ada_unit` | boolean | Has unit |
| `ada_subunit` | boolean | Has subunit |

**Computed Fields (not saved):**
- **Tahun Khidmat Penempatan Semasa** — calculated from `tarikh_sandang` and current year (display-only)

**Form Tabs:**
1. **Maklumat Pegawai** — Nama, No K/P (auto-extracts DOB and gender), Jantina, Tarikh Lahir, Tarikh Lantikan, Tarikh Sah Jawatan, Opsyen Pencen
2. **Penempatan** — Tarikh Sandang, Tahun Khidmat Penempatan Semasa (computed), No Waran (readonly text), PTJ, Bahagian, Unit (cascading), Subunit (cascading), Jawatan/Gred
3. **Status** — is_tetap, is_kontrak, is_kontrak_interim, is_kontrak_isi_tetap, is_kup, is_kupj, is_jtw

**Table Columns**: Bil, Pegawai (name + IC + jawatan), PTJ (with "Pinjam" badge if different from waran PTJ), Waran (or "Jawatan tanpa waran"), Status (Lengkap/Tidak Lengkap badge)

**Status Logic (Lengkap/Tidak Lengkap):**
- **Tidak Lengkap** if: ptj_id null, bahagian_id null, (subunit_id null AND ada_unit=0), (unit_id null AND ada_subunit=0), OR (is_jtw=0 AND is_kontrak=0 AND no waran)
- **Lengkap** otherwise

**Navigation Badge**: Red badge showing count of "Tidak Lengkap" records

**Global Scope**: Users (role 3) see only pegawai from their PTJ OR pegawai linked to waran_jawatan in their PTJ.

---

### 6.3 Pencen (Pension / Service Termination)

**Model**: `Pencen`  
**Navigation Group**: Pegawai  
**Icon**: `heroicon-o-arrow-left-on-rectangle`

**Fields:**

| Field | Type | Description |
|---|---|---|
| `ptj_id` | FK → ptjs | Responsibility center |
| `jawatan_gred_id` | FK → jawatan__greds | Position + Grade |
| `opsyen_pencen_id` | FK → opsyen_pencens | Pension option |
| `jenis_pencen_id` | FK → jenis_pencens | Pension type |
| `nama` | string | Name |
| `nokp` | string | IC number |
| `jenis_lantikan` | string | Appointment type |
| `tarikh_lantikan` | date | Appointment date |
| `tarikh_sah_jawatan` | date | Position confirmation date |
| `umur_pencen` | integer | Pension age |
| `tarikh_pencen` | date | Pension date |
| `tarikh_kuatkuasa` | date | Effective date |
| `tempoh_perkhidmatan` | string | Service duration |
| `catatan` | text | Notes |

**Global Scope**: Users (role 3) see only their PTJ's records.

---

### 6.4 Letak Jawatan (Resignation)

**Model**: `LetakJawatan`  
**Navigation Group**: Pegawai

**Fields:**

| Field | Type | Description |
|---|---|---|
| `ptj_id` | FK → ptjs | Responsibility center |
| `jawatan_gred_id` | FK → jawatan__greds | Position + Grade |
| `nama` | string | Name |
| `nokp` | string | IC number |
| `tarikh_notis` | date | Notice date |
| `tarikh_kuatkuasa` | date | Effective date |
| `jenis_notis` | string | Notice type |
| `alasan` | string | Reason |
| `tarikh_lantik` | date | Appointment date |
| `lantikan` | string | Appointing authority |
| `ikatan_jpa` | string | JPA binding |
| `ikatan_bpl` | string | BPL binding |
| `pinjaman_lppsa` | string | LPPSA loan |

**Global Scope**: Users (role 3) see only their PTJ's records.

---

### 6.5 Hebahan (Announcements)

**Model**: `Hebahan`  
**Navigation Group**: (appears on Dashboard)

**Fields:**

| Field | Type | Description |
|---|---|---|
| `tajuk` | string | Title |
| `kandungan` | text, nullable | Content (HTML) |
| `tarikh_hebahan` | date | Announcement date |
| `lampiran` | string, nullable | Attachment (file path) |
| `status` | enum: `published` / `draft` | Publication status |
| `dipaparkan_sehingga` | date, nullable | Display until date |

**Dashboard Display**: Shows up to 5 recent published announcements (where `dipaparkan_sehingga` is null or in the future).

---

### 6.6 Users

**Model**: `User`  
**Navigation Group**: Pengurusan  
**Icon**: `heroicon-o-cog-6-tooth`

**Fields:**

| Field | Type | Description |
|---|---|---|
| `name` | string | Full name (stored uppercase) |
| `email` | string, unique | Email (login credential) |
| `password` | string | Hashed password |
| `ptj_id` | FK → ptjs | Assigned PTJ |
| `nokp` | string | IC number |
| `phone_number` | string, nullable | Phone |
| `status` | string | Status |
| `role` | integer: 1, 2, 3 | Role (Superadmin/Admin/User) |
| `avatar` | string, nullable | Profile picture path |

**Avatar**: Uses Filament's `HasAvatar` interface. Falls back to `ui-avatars.com` if no avatar uploaded.

---

### 6.7 Reference Data Modules

These are CRUD-managed lookup tables:

| Module | Model | Table | Description |
|---|---|---|---|
| **PTJ** | `Ptj` | `ptjs` | Pusat Tanggungjawab (Responsibility Centers) |
| **Bahagian** | `Bahagian` | `bahagians` | Divisions (belongs to PTJ) |
| **Unit** | `Unit` | `units` | Units (soft deletes) |
| **Subunit** | `Subunit` | `subunits` | Sub-units |
| **Jawatan** | `Jawatan` | `jawatans` | Position titles |
| **Gred** | `Gred` | `greds` | Pay grades (U5–U41, C41, S41, etc.) |
| **Jawatan_Gred** | `Jawatan_Gred` | `jawatan__greds` | Pivot: Position + Grade combos |
| **Program** | `Program` | `programs` | Programs |
| **Aktiviti** | `Aktiviti` | `aktivitis` | Activities (belongs to Program) |
| **Kumpulan** | `Kumpulan` | `kumpulans` | Staff groups/categories |
| **Parlimen** | `Parlimen` | `parlimens` | Parliamentary constituencies |
| **Dun** | `Dun` | `duns` | State constituencies |
| **Jenis Pencen** | `JenisPencen` | `jenis_pencens` | Pension types |
| **Opsyen Pencen** | `OpsyenPencen` | `opsyen_pencens` | Pension options |

---

## 7. Organizational Hierarchy

```
PTJ (Pusat Tanggungjawab)
 └── Bahagian (Division)
      └── Unit
           └── Subunit
```

**Warans link to the hierarchy via:**
- `WaranJawatan.ptj_id` → PTJ
- `WaranJawatan.bahagian_id` → Bahagian

**Pegawai link to the hierarchy via:**
- `Pegawai.ptj_id` → PTJ
- `Pegawai.bahagian_id` → Bahagian
- `Pegawai.unit_id` → Unit
- `Pegawai.subunit_id` → Subunit

**Program hierarchy:**
```
Program
 └── Aktiviti
      └── WaranJawatan (via aktiviti_id)
```

---

## 8. Laporan (Reports)

**Page**: `Report` (Filament Page with custom table)  
**Navigation Group**: Laporan  
**Icon**: `heroicon-o-presentation-chart-bar`

### Available Reports

| ID | Report Name | Export Route | Parameters |
|---|---|---|---|
| 1 | Data Keseluruhan Mengikut PTJ | `export.dataKeseluruhan` | None |
| 2 | Data Perjawatan Kontrak | `export.dataKontrak` | None |
| 3 | Data Mengikut Kumpulan Mengikut PTJ | `report.kosong.export` | None |
| 4 | Data Keseluruhan Mengikut Jawatan | *(not implemented)* | — |
| 5 | Laporan JIK Mengikut Jawatan | `export.jikByJawatan` | `jawatan_id` (required select) |
| 6 | Laporan JIK Mengikut Gred | *(not implemented)* | — |

### 8.1 Data Keseluruhan Mengikut PTJ (Report 1)

**Controller**: `DataKeseluruhanExportController`  
**Export Class**: `DataKeseluruhanExport`

Generates an Excel report showing all personnel across all PTJs with their warrant assignments.

### 8.2 Data Perjawatan Kontrak (Report 2)

**Controller**: `DataKontrakExportController`  
**Export Class**: `DataKontrakExport`

Generates an Excel report counting only contract staff (`is_kontrak = 1`).

**Format:**
- Title: `DATA PERJAWATAN KONTRAK JKN KEDAH SEHINGGA <tarikh>`
- Header: BIL | PTJ | JUMLAH JAWATAN | 16 fixed jawatan columns
- Sections per Program, with per-unit rows
- JUMLAH subtotal per program
- JUMLAH KESELURUHAN grand total

**16 Template Columns:**
PEGAWAI PERUBATAN UD43, PEGAWAI PERUBATAN UD41, PEGAWAI FARMASI UF48, PEGAWAI SAINS MIKROBIOLOGI C41, PEGAWAI PSIKOLOGI S41, PKP U41, PPP U41, JTMP U29, FISIOTERAPI U41, JURURAWAT U41, PPPK U29, JURU XRAY U41, PENOLONG JURUTERA JA29, PEGAWAI PERGIGIAN UG41, JURUTEKNOLOGI PERGIGIAN U41, U41 (catch-all)

### 8.3 Laporan JIK Mengikut Jawatan (Report 5)

**Controller**: `JikByJawatanExportController`  
**Export Class**: `JikByJawatanExport`

Generates an Excel report showing J/I/K breakdown per jawatan, grouped by gred.

**Parameters**: Jawatan ID (user selects from dropdown)

**Format:**
- Title: `MAKLUMAT PERJAWATAN : <jawatan>`
- Header: `DATA SEHINGGA : <tarikh>`
- Columns: BIL | PTJ | then one J/I/K group per gred of the selected jawatan
- Sections per Program with per-PTJ rows
- JUMLAH subtotal per program
- JUMLAH KESELURUHAN grand total

**JIK Counting Rules:**
- **J (Jawatan)** — count of waran_jawatan rows for that jawatan+gred
- **I (Isi)** — count of waran_jawatan rows with a pegawai assigned (tetap or kontrak-interim)
- **K (Kosong)** — J − I

**Multi-Gred Waran Rules:**
- Single-gred waran: counts in its own gred
- Multi-gred waran **with** pegawai: counts J/I/K **only in the pegawai's own gred**
- Multi-gred waran **without** pegawai: counts in the **lowest gred** of the range (J=1, I=0, K=0)

### 8.4 Excel Styling

All exports use consistent styling:
- Teal/dark header with white text
- Light blue section rows
- Pink JUMLAH rows
- Blue JUMLAH KESELURUHAN grand total
- Thin borders, merged cells
- Zeros written as real numbers (via `WithStrictNullComparison`)

---

## 9. Dashboard

**Page**: `Dashboard` (custom Filament Page)

### Widgets

1. **Stats Cards** (3 cards):
   - Jumlah Perjawatan Mengikut Waran (total warans)
   - Pengisian Semasa (total filled positions)
   - Kekosongan (total vacancies)

2. **Hebahan Terkini** — latest 5 published announcements

3. **Status Waran** — ApexCharts donut chart showing filled vs. vacant percentages

4. **Jumlah Pengisian Waran Perjawatan Mengikut Program** — Chart.js bar chart showing waran count per program

5. **Recent Warans** — last 5 created warans

---

## 10. Authentication & Security

### Login Flow
1. User enters email + password
2. Turnstile CAPTCHA verifies
3. Session created via Filament auth

### Password Reset
- Custom `RequestPasswordReset` page
- Uses Filament's `ResetPassword` notification

### Profile
- Custom `EditProfile` page
- Avatar upload (stored in `public` disk)

### Session
- SPA mode enabled
- Database notifications (30s polling)

---

## 11. Notification System

### Triggered Notifications

| Event | Recipients | Type |
|---|---|---|
| Waran created | Superadmins | Database notification |
| Waran deleted | Superadmins | Database notification |
| Penempatan added | PTJ Users (matched ptj_id) | Database notification |
| Status penempatan changed | Superadmins/Admins | Database notification |
| Pegawai deleted | Superadmins/Admins | Database notification |

### Logging
All create/edit/delete actions logged via `Log::info()` with user_id and record_id.

---

## 12. Data Relationships (ERD Summary)

```
┌─────────┐    ┌──────────────┐    ┌──────────┐
│ Waran   │───<│ WaranJawatan │>───│ Pegawai  │
└─────────┘    └──────────────┘    └──────────┘
                    │    │                │
                    │    │                ├── ptj_id → Ptj
                    │    │                ├── bahagian_id → Bahagian
                    │    │                ├── unit_id → Unit
                    │    │                ├── subunit_id → Subunit
                    │    │                ├── jawatan_gred_id → Jawatan_Gred
                    │    │                └── opsyen_pencen_id → OpsyenPencen
                    │    │
                    │    ├── aktiviti_id → Aktiviti → Program
                    │    ├── ptj_id → Ptj
                    │    ├── bahagian_id → Bahagian
                    │    └── jawatan_ids (JSON) → Jawatan
                    │         gred_ids (JSON) → Gred
                    │
                    ├── waran_tolak_id → Waran (tolak workflow)
                    └── waran_id → Waran

┌──────────┐    ┌──────────┐
│ Pencen   │>───│ Ptj      │
└──────────┘    └──────────┘
│ jawatan_gred_id → Jawatan_Gred
│ opsyen_pencen_id → OpsyenPencen
│ jenis_pencen_id → JenisPencen

┌──────────────┐    ┌──────────┐
│ LetakJawatan │>───│ Ptj      │
└──────────────┘    └──────────┘
│ jawatan_gred_id → Jawatan_Gred

┌─────────┐    ┌──────────┐    ┌─────────┐
│ Program │───<│ Aktiviti │>───│WarjJawtn│
└─────────┘    └──────────┘    └─────────┘

┌──────────┐    ┌────────────────┐    ┌──────┐
│ Jawatan  │───<│ Jawatan_Gred   │>───│ Gred │
└──────────┘    └────────────────┘    └──────┘

┌──────────┐    ┌──────────┐
│ Bahagian │>───│ Ptj      │
└──────────┘    └──────────┘
│ subunits (has many)

┌──────────┐
│ Unit     │
│ soft_deletes │
└──────────┘
│ subunits (has many)

┌──────────┐
│ Subunit  │
└──────────┘

┌──────────┐    ┌──────────┐    ┌──────────┐
│ User     │>───│ Ptj      │    │ Tbk      │
└──────────┘    └──────────┘    └──────────┘
│ role: 1/2/3                    │ waran_jawatan_id
                                │ pegawai_id
                                │ gred_id (TBK gred)
                                │ tbk (position in range)
```

---

## 13. Key Business Rules

1. **Waran JIK Counting**: J = total jawatan, I = filled (with pegawai), K = J − I
2. **Multi-Gred Waran**: When a waran spans multiple gred (e.g. U5–U7), the single post counts in exactly one column — the pegawai's gred if assigned, otherwise the lowest gred
3. **KUP (Khas Untuk Penyandang)**: When KUP is checked, the waran_jawatan becomes locked — role 3 users cannot edit pegawai assignment
4. **Pinjam Indicator**: If a pegawai's `ptj_id` differs from the waran_jawatan's `ptj_id` (and not kontrak), show "Pinjam" badge
5. **Lengkap/Tidak Lengkap**: A pegawai record is incomplete if missing PTJ, Bahagian, Unit/Subunit, or waran assignment (unless is_jtw or is_kontrak)
6. **TBK (Tempat Bertukar Keluar)**: Created automatically when a multi-gred waran has a pegawai whose gred is inside the range but not the lowest
7. **Tolak Workflow**: Tolak warans track which jawatan were removed; jawatan can be restored ("Aktifkan Jawatan")
8. **Tarikh Sandang Max**: DatePicker limited to 31 December of current year
9. **Tahun Khidmat**: Computed field = current year − tarikh_sandang year (not saved in DB)
10. **Name Normalization**: Pegawai and User names are stored uppercase

---

## 14. Export & Reporting Architecture

### Export Routes (web.php)

All export routes are behind `auth` middleware:

| Route | Controller | Export Class |
|---|---|---|
| `/export-data-keseluruhan` | `DataKeseluruhanExportController@export` | `DataKeseluruhanExport` |
| `/export-data-kontrak` | `DataKontrakExportController@export` | `DataKontrakExport` |
| `/export-jik-by-jawatan` | `JikByJawatanExportController@export` | `JikByJawatanExport` |
| `/export-letak-jawatan` | `LetakJawatanExportController@export` | `LetakJawatanExport` |
| `/export-penamatan-perkhidmatan` | `PenamatanPerkhidmatanExportController@export` | `PenamatanPerkhidmatanExport` |
| `/export-users` | `UserExportController@export` | `UserExport` |

### Filament Imports/Exports

- User import/export via Filament's built-in `Import`/`Export` system
- Files stored in `imports/` and `exports/` tables

---

## 15. Non-Functional Requirements

### Performance
- Pagination: default 5 records per page
- Waran table uses eager loading (`waranJawatan.ptj`, `waranJawatan.aktiviti`)
- Dashboard queries optimized with aggregation

### Data Integrity
- Soft deletes on `Pegawai` and `Unit`
- Waran Jawatan uses soft deletes + status tracking (active/removed/deleted)
- Unique constraint on `no_waran` and `nokp` (users)

### Accessibility
- All form labels in Bahasa Malaysia
- Consistent UI via Filament's design system
- Dark mode enabled

### Internationalization
- Date format: `d F Y` (e.g. "19 Ogos 2026")
- Malay locale for date display

---

## 16. Future Enhancements (Out of Scope)

| Feature | Priority | Notes |
|---|---|---|
| Report 4: Data Keseluruhan Mengikut Jawatan | Medium | Route defined but not implemented |
| Report 6: Laporan JIK Mengikut Gred | Medium | Route defined but not implemented |
| Automated waran versioning | Low | Parent/child waran relationship stubbed |
| Email notifications | Low | Currently database-only |
| Audit trail dashboard | Medium | Log entries exist but no UI |
| Mobile responsive optimization | Medium | Current UI is desktop-focused |
| API endpoints for external integration | Low | Currently web-only |
| Batch import of waran data | Medium | Manual entry only |
| Real-time WebSocket updates | Low | Currently polling-based |

---

## 17. Glossary

| Term | Definition |
|---|---|
| **PTJ** | Pusat Tanggungjawab — Responsibility Center (e.g. hospital, health office) |
| **Waran** | Official government warrant authorizing staffing positions |
| **JIK** | Jawatan-Isi-Kosong — Position-Filled-Vacant |
| **J** | Jawatan — total authorized positions |
| **I** | Isi — filled positions |
| **K** | Kosong — vacant positions |
| **KUP** | Khas Untuk Penyandang — Reserved for a specific person |
| **TBK** | Tempat Bertukar Keluar — Transfer indicator for multi-gred positions |
| **Pinjam** | Loan/transfer — pegawai assigned to a different PTJ than their home PTJ |
| **JTW** | Jawatan Tanpa Waran — Position without a warrant |
| **Bahagian** | Division within a PTJ |
| **Gred** | Civil service pay grade (e.g. U5, U41, C41) |
| **Skim** | Service scheme/category |
| **Tolak** | Removal/revocation of positions |
| **Pindaan Nama** | Name amendment on a warrant |
| **Batal Nama** | Name cancellation on a warrant |

---

*Document generated from codebase analysis of Sistem Data Perjawatan (MySTAFF) v1.0*
