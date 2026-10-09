# System Configurability Across Barangays

Reference notes for the capstone paper and defense. Describes how the system
was generalized from a single-barangay implementation to a configurable one,
and what changed to make that true.

---

## 1. Statement of the change

The system was originally implemented for Barangay Pili, Madridejos, Cebu.
Barangay identity was embedded directly in the source: approximately 200
literal references across 30 files, including all six document templates,
seven email templates, every authentication page, the SMS notification text,
the tracking-number prefix, and the Android application.

The system now treats barangay identity as **runtime configuration**. One
deployment serves one barangay; the same codebase serves any barangay without
source modification. Configuration is performed either through a first-run
console command or through an administrator web interface.

### Deployment model

Single-tenant, multi-deployment. Each barangay installs its own instance with
its own database. This was chosen over multi-tenancy (one instance serving
many barangays simultaneously) because:

- Barangay records are custodial. A single shared database holding several
  barangays' resident data would require tenant scoping on every query, where
  one missed scope discloses another barangay's records.
- Barangays procure and host independently; a shared instance implies a shared
  operator, which does not match how barangay IT is actually funded.
- It required no change to the existing schema's primary keys or queries.

---

## 2. What is configurable

| Category | Items | Managed through |
| --- | --- | --- |
| Identity | Barangay name; municipality or city and its type; province; region; address; email; contact number; hall name; document office title; hearing venue | `barangay:setup`, Admin → Barangay Settings |
| Branding | System title; short name; portal name; barangay seal; municipality seal; browser icon | Admin → Barangay Settings |
| System | Tracking-number prefix; SMS signature; admin notification email; Android APK path | Admin → Barangay Settings |
| Documents | Certificate types, fees, processing days, requirements; document heading; **document body wording** | Admin → Certificates |
| Territory | Puroks / sitios | Admin → Puroks |
| Officials | Names and positions printed as signatories | Admin → Officials |
| Deployment | Domain, admin subdomain, mobile app token | `.env` |

Certificate types, fees and officials were already database-driven before this
work; identity, branding, document wording and puroks were not.

---

## 3. Architecture

### 3.1 Two configuration tiers

Values are split by **when they must be available**:

1. **Pre-database tier** (`.env` → `config/barangay.php`). Route domain
   registration, Content-Security-Policy headers, and the mobile user-agent
   check all execute before a database connection exists. These derive from
   `APP_URL` unless explicitly overridden.

2. **Runtime tier** (`settings` table). Everything a barangay secretary might
   reasonably change: names, seals, wording, prefixes.

### 3.2 The settings store

`settings` is a key/value table (`key`, `value`, `type`, `group`). The entire
table is read once per request and cached as a single array, so the ~40
`setting()` calls inside a document template cost one query on a cache miss
rather than one query each. Writing any setting flushes the cache, so a change
is visible on the next page load.

Reads degrade safely: if the table does not exist yet (fresh install, or
mid-migration) the accessor returns an empty set and every caller falls back to
its own default, rather than throwing.

### 3.3 Access layer

A helper layer keeps call sites readable and avoids repeating string keys:

| Helper | Returns |
| --- | --- |
| `setting($key, $default)` | Raw value, or the default if unset **or blank** |
| `barangay_name()` | `Pili` |
| `barangay_label()` | `Barangay Pili` |
| `barangay_location()` | `Pili, Madridejos, Cebu` |
| `barangay_province_line()` | `Province of Cebu` |
| `barangay_municipality_line()` | `Municipality of Madridejos` |
| `barangay_office_title()` | `Office of the Punong Barangay` |
| `setting_image($key, $fallback)` | Public URL of an uploaded or bundled image |
| `sms_signature()` | Attribution appended to outgoing SMS |
| `is_mobile_app()` | Whether the request came from the Android app |
| `purok_rule()` | Validation rules for a purok field |

A view composer also exposes a `$brgy` array to every template, so a view needs
no controller changes to display barangay details.

A deliberate detail: `setting()` treats an **empty stored value as unset**. A
barangay that leaves its contact number blank gets the caller's default rather
than a blank gap in a document letterhead.

### 3.4 Document letterhead

The four certificate templates that shared an identical header
(clearance, indigency, good moral, residency) now include a single partial,
`resources/views/print/partials/letterhead.blade.php`, driven entirely by
settings. Lines for province and municipality are omitted entirely when unset
rather than printing "Province of".

### 3.5 Configurable document wording

Certificates gained three nullable columns: `body_template`, `header_title`,
`signatory_position`.

When `body_template` is filled, the request prints through the general
letterhead layout using that text, overriding the built-in wording. This lets
a barangay reword an existing document or introduce a certificate type unique
to it without adding a Blade template. Clearing the field restores the built-in
wording.

Templates use `{{token}}` placeholders. `CertificateRenderer` resolves 21
tokens covering resident details, request details, barangay identity and dates.

**Security.** Resident-supplied values (name, address, purpose) are
HTML-escaped *before* substitution, so a resident cannot inject markup into an
official document by typing it into their profile. The template itself is
staff-authored, so a small formatting tag set is permitted
(`strong, b, em, i, u, br, p, span, sup`); everything else is stripped.
Unrecognized tokens are left visible rather than silently rendering empty, so
an author sees their own typo.

### 3.6 Puroks

A `puroks` table replaces free-text entry, driving dropdowns in resident
registration, resident records and the resident profile.

Three decisions worth defending:

- **`residents.purok` remains a string, not a foreign key.** Historical records
  stay readable after a purok is renamed or retired, and no existing resident
  query needed rewriting.
- **The creating migration adopts existing data**: it inserts every distinct
  `purok` value already present in `residents`, so no existing record points at
  a purok absent from the list.
- **A purok still referenced by residents is deactivated, not deleted.** It
  disappears from dropdowns while existing records keep their value. Renaming a
  purok propagates to the residents that use it.

Validation accepts any name in the table including inactive ones, so a resident
whose purok was later retired can still have their record edited. Where no
puroks are defined, the field degrades to free text so a fresh install works.

### 3.7 Android application

All branding moved to `resident-mobile-app/gradle.properties`: application
name, Gradle project name, application ID, portal URL, and user-agent token.
Rebranding is an edit to one file plus a launcher icon.

The user-agent token was centralized into `is_mobile_app()`, which had been
duplicated as a literal string comparison in eight places. It accepts the
configured token **and** the legacy `BrgyPiliApp` value, so APKs already
installed on residents' phones continue to work after the server is upgraded.

---

## 4. Defects found and corrected

These were pre-existing and surfaced while tracing the hard-coded values.

1. **Contradictory jurisdiction on official documents.** The Katarungang
   Pambarangay summons form printed *Province of Camarines Sur, Municipality of
   Minalabac*, while the clearance, indigency, good moral and residency
   certificates printed *Province of Cebu, Municipality of Madridejos*. Two
   summons notification emails also used Minalabac. All jurisdiction text now
   resolves from one source.

2. **Inconsistent office title.** The clearance printed *Office of the Punong
   Barangay*; the other certificates printed *Office of the Barangay Captain*.
   These denote the same office under RA 7160. Now a single configurable value.

3. **Malformed names on every printed document.** `Resident::full_name`
   concatenated first, middle, last and suffix with spaces and trimmed only the
   ends, producing a double space inside the name whenever a middle name or
   suffix was absent — e.g. `MARIA  SANTOS`. Blank parts are now removed before
   joining.

4. **Publicly reachable migration endpoint with a hard-coded key.**
   `/run-migrations` accepted `?key=pili2026`, a secret readable by anyone with
   access to the source, and would run migrations on the production database.
   The key now comes from `MIGRATION_KEY` and the route returns 404 while that
   is unset, which is the default. Comparison uses `hash_equals`.

5. **Local `.env` pointed at an unrelated project's database.**
   `DB_DATABASE=laravel` named a database containing another application's
   tables (appointments, billing accounts, clients, equipment). Repointed to a
   dedicated database.

---

## 5. Verification

26 automated tests were added across two files.

`tests/Feature/BarangayConfigurationTest.php` (17 tests)

- Migration seeds the previously hard-coded values, so an upgrade is behavior-preserving
- Blank and missing settings fall back to caller defaults
- Writing a setting is immediately visible (cache invalidation)
- Public pages re-brand and show no trace of the former barangay
- **A printed certificate re-brands**: letterhead, jurisdiction and office title all follow configuration
- Settings form validates and persists; tracking prefix rejects invalid input
- Settings page is admin-only
- Purok rename propagates to residents; in-use purok deactivates; unused purok deletes
- Purok dropdown replaces free text once puroks exist
- Migration endpoint is closed by default and rejects the formerly hard-coded key
- Mobile detection honours both the configured and legacy tokens

`tests/Feature/CertificateTemplateTest.php` (9 tests)

- Token substitution across resident, request, barangay and date tokens
- Blank template renders nothing; unknown token stays visible
- **Resident-supplied markup is escaped** — no tag can form from profile data
- Disallowed tags in the template are stripped
- Custom wording overrides built-in text; absent template leaves the document unchanged
- Custom wording follows barangay settings
- Blank submission clears the override rather than storing an empty string

Manual verification: configuration was switched to two fictitious barangays
and all public pages and printed certificates were re-rendered and inspected.
The only remaining references to the original barangay were the configured
logo and APK *filenames* — themselves settings — and the intentional legacy
compatibility token.

### Pre-existing test failures (not introduced by this work)

Confirmed identical on a clean checkout of the prior commit: 9 errors and 4
failures. `AuthTest` does not apply the `RefreshDatabase` trait, so its tables
are never created; `ExampleTest` imports the trait without using it; two
`RequestTest` cases do not supply the release date that approval now requires.
These are independent of barangay configuration and remain open.

---

## 6. Suggested framing for the paper

- **Scope.** From "a system for Barangay Pili" to "a configurable system for
  barangay document services, deployed and validated at Barangay Pili."
- **Contribution.** Generalizability is defensible on its own: the artifact is
  reusable by other barangays without developer involvement, which is the
  practical barrier to adoption for most barangay systems.
- **Data flow diagrams.** A configuration process and a settings data store now
  belong on the Level 1 DFD: an administrator supplies barangay configuration,
  which is read by the document generation, notification, and registration
  processes. The existing `docs/dfd/` diagrams predate this and need redrawing.
- **Limitation to state honestly.** One deployment serves one barangay.
  Serving several barangays from a single instance would require tenant
  scoping throughout and was explicitly out of scope.
