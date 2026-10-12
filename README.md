# Barangay Clearance & Certificate Management System

A Laravel web application and companion Android app for barangay document
services: resident records, certificate and clearance requests, payments,
Katarungang Pambarangay summons, equipment borrowing, and announcements.

The system is **not tied to one barangay**. Every barangay-specific detail —
name, municipality, province, seals, document wording, fees, puroks, tracking
number prefix, SMS signature — is configuration, not code. One deployment
serves one barangay; the same codebase serves any barangay.

---

## Requirements

- PHP 8.0+ with the usual Laravel extensions
- MySQL 5.7+ / MariaDB
- Composer
- Node.js (only to rebuild front-end assets)
- Android Studio (only to rebuild the resident APK)

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Set your database credentials in `.env`, then:

```bash
php artisan migrate
php artisan db:seed            # staff accounts + standard certificate types
php artisan barangay:setup     # name, municipality, province, puroks
```

`db:seed` creates `admin` and `staff1`, both with the password `password`.
**Change them before going live.**

To load sample officials and residents for a demo or a defense run:

```bash
php artisan db:seed --class=DemoDataSeeder
```

---

## Configuring the barangay

### `php artisan barangay:setup`

Interactive first-run setup. Asks for the barangay name, municipality or city,
province, region, email, tracking prefix, document office title, and the list
of puroks. Safe to re-run — it offers the current values as defaults.

```bash
php artisan barangay:setup --show   # print the current configuration
```

### Admin → Barangay Settings

Everything the command asks for, plus image uploads, editable from the web UI
by an admin at any time. Grouped into:

| Group | Contains |
| --- | --- |
| **Barangay Identity** | Name, municipality/city, province, region, address, email, contact, hall name, document office title, hearing venue |
| **Branding** | System title, short name, portal name, barangay seal, municipality seal, browser tab icon |
| **System** | Tracking number prefix, SMS signature, admin notification email, Android APK path |

Changes take effect immediately — the settings table is cached as a single
array and the cache is flushed on every write.

### Other admin-managed data

| What | Where |
| --- | --- |
| Officials (names printed on documents) | Admin → Officials |
| Certificate types, fees, processing days | Admin → Certificates |
| Custom document wording | Admin → Certificates → *Custom Document Wording* |
| Puroks / sitios | Admin → Puroks |

### What stays in `.env`

Only values needed before the database is reachable:

| Variable | Purpose |
| --- | --- |
| `APP_URL` | Public URL; the other two derive from it |
| `ADMIN_DOMAIN` | Admin subdomain. Defaults to `admin.` + the `APP_URL` host |
| `APP_DOMAIN` | Bare domain for CSP headers and the default no-reply address |
| `MOBILE_UA_TOKEN` | Must match `brgyUaToken` in the Android app |
| `MIGRATION_KEY` | Secret for `/run-migrations`. **Blank disables the route** |

---

## Custom document wording

By default each certificate prints through a built-in layout with standard
wording. To reword a document, or add a certificate type unique to your
barangay, fill in **Body Text** under Admin → Certificates. The document then
prints through the general letterhead layout using your text.

Placeholders are written in double braces:

```html
<p>This is to certify that <strong>{{full_name}}</strong>, {{age}} years of age,
{{civil_status}}, a resident of {{address}}, {{barangay_label}}, is a bona fide
resident for {{years_residency}} year(s).</p>

<p>Issued this {{day}} day of {{month_year}} at {{location}}.</p>
```

The editor lists every available placeholder. Resident-supplied values are
HTML-escaped before substitution, and only `<strong> <b> <em> <i> <u> <br>
<p> <span> <sup>` survive in the template, so a resident cannot inject markup
into an official document through their profile.

Clearing the field restores the built-in wording.

---

## Android resident app

See [`resident-mobile-app/README.md`](resident-mobile-app/README.md). All
branding lives in `resident-mobile-app/gradle.properties` — five properties
and a launcher icon.

The server accepts both the configured `MOBILE_UA_TOKEN` and the legacy token
`BrgyPiliApp`, so APKs already installed on residents' phones keep working
after an upgrade.

---

## How the configuration works

```
.env ──► config/barangay.php      domain, admin subdomain, UA token,
                                  migration key  (pre-database values)

settings table ──► Setting::get() ──► setting() / barangay_label() /
                                      barangay_location() / setting_image()
                                      …and $brgy in every view
```

- `app/Models/Setting.php` — key/value store, whole table cached as one array
- `app/Helpers/settings.php` — the `setting()` family, `is_mobile_app()`, `purok_rule()`
- `app/Providers/SettingsServiceProvider.php` — loads the helpers, shares `$brgy` with all views
- `app/Services/CertificateRenderer.php` — token substitution and escaping for custom wording
- `resources/views/print/partials/letterhead.blade.php` — the shared document header

Adding a new configurable value: add the key to the `defaults()` array in
`database/migrations/2026_10_09_000001_create_settings_table.php`, then list it
in `TEXT_FIELDS` or `IMAGE_FIELDS` in `app/Http/Controllers/SettingsController.php`
to expose it in the admin UI.

---

## Tests

```bash
./vendor/bin/phpunit
```

`tests/Feature/BarangayConfigurationTest.php` and
`tests/Feature/CertificateTemplateTest.php` assert that reconfiguring the
settings re-brands public pages and printed certificates, and that no Pili
identity leaks through.

> Known pre-existing failures, unrelated to barangay configuration:
> `AuthTest` does not apply the `RefreshDatabase` trait so its tables are never
> created; `ExampleTest` imports the trait without using it; two `RequestTest`
> cases do not supply the release date that approval now requires.

---

## Deployment notes

- `php artisan migrate` seeds the settings table with the values the code
  previously hard-coded, so an existing deployment behaves identically
  immediately after upgrading. Run `barangay:setup` to change them.
- Uploaded seals are written to `public/assets/uploads/branding/`. That
  directory and `storage/` and `bootstrap/cache/` must be writable. On Windows
  under OneDrive, clear the read-only attribute if PHP reports them unwritable.
- Leave `MIGRATION_KEY` blank in production unless you are actively using
  `/run-migrations`; the route returns 404 while it is unset.
