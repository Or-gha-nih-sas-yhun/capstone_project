<?php

/*
|--------------------------------------------------------------------------
| Barangay deployment configuration
|--------------------------------------------------------------------------
|
| Values that must resolve BEFORE the database is available — route domain
| registration, CSP headers, and the mobile user-agent check. Everything a
| barangay can change at runtime lives in the `settings` table instead and
| is read through the setting() helper.
|
*/

return [

    // Primary public domain, without scheme. Used for CSP allow-listing.
    // Derived from APP_URL unless APP_DOMAIN overrides it.
    'domain' => env('APP_DOMAIN')
        ?: (parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'),

    // Admin portal subdomain. Routes are registered against this.
    'admin_domain' => env('ADMIN_DOMAIN')
        ?: 'admin.' . (parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'),

    // Shared secret for the /run-migrations endpoint, for hosts without
    // shell access. Blank (the default) disables the route entirely.
    'migration_key' => env('MIGRATION_KEY'),

    // Token the Android wrapper appends to its User-Agent string. The
    // helper also accepts the legacy "BrgyPiliApp" value for already
    // installed apps.
    'mobile_ua_token' => env('MOBILE_UA_TOKEN', 'BrgyPortalApp'),

];
