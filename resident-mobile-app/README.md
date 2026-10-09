# Barangay Resident Portal Android App

A Kotlin Android WebView app for the Laravel resident portal. It opens the live portal, so residents keep using the same login, certificate requests, borrow requests, summons, announcements, and profile pages.

The app appends a token to its WebView `User-Agent` so the server can tell it apart from an ordinary browser. The Laravel login endpoint rejects staff and administrator accounts for that client, and the WebView blocks `/admin` navigation. Admin and staff functions remain available only through the web application.

## Rebranding for another barangay

Everything barangay-specific lives in **`gradle.properties`**. No source file needs editing.

| Property | What it controls |
| --- | --- |
| `brgyAppName` | Name under the launcher icon |
| `brgyProjectName` | Generated APK filename |
| `brgyApplicationId` | Package name identifying the app on the device |
| `brgyPortalUrl` | Root URL of the deployed portal (must end with `/`) |
| `brgyUaToken` | `User-Agent` token; must match `MOBILE_UA_TOKEN` in the Laravel `.env` |

Steps:

1. Edit the five properties above in `gradle.properties`.
2. Replace the launcher icon under `app/src/main/res/mipmap-*/`.
3. Build (see below).

Two cautions:

- **`brgyApplicationId` identifies the app on the device.** Changing it produces a *separate* app, so residents with the previous one installed will not receive it as an update. Change it only for a fresh deployment at a different barangay.
- **`brgyUaToken` must match the server.** Set `MOBILE_UA_TOKEN` in the Laravel `.env` to the same value. The server also accepts the legacy token `BrgyPiliApp`, so APKs already installed at Barangay Pili keep working after the server is upgraded.

For local emulator testing, point `brgyPortalUrl` at the host machine:

```properties
brgyPortalUrl=http://10.0.2.2/
```

## Build APK

Open this folder in Android Studio, or from the command line:

```powershell
.\gradlew assembleRelease
```

The APK is written to:

```text
app/build/outputs/apk/release/
```

## Publishing the download link

Copy the signed APK into `public/downloads/`, then point the portal at it from
**Admin → Barangay Settings → System → Android App File**, e.g.:

```text
downloads/barangay-pili-resident-portal-v1.0.3.apk
```

The landing page download button reads that setting, so no view file needs editing.

## Internal package name

The Kotlin package is `com.barangayportal.residentportal`. That is an internal
identifier used for `R` and `BuildConfig` generation and is never shown to
residents — leave it alone when rebranding and change `brgyApplicationId`
instead.
