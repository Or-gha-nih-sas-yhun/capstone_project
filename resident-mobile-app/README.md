# Barangay Pili Resident Portal Android App

This is a Kotlin Android WebView app for the existing Laravel resident portal. It opens the live portal, so residents keep using the same login, certificate requests, borrow requests, summons, announcements, and profile pages.

The Android app identifies itself as `BrgyPiliApp`. The Laravel login endpoint rejects staff and administrator accounts for that client, and the WebView blocks `/admin` navigation. Admin and staff functions remain available only through the web application.

## Configure

Before building the APK, update `PORTAL_URL` in `app/build.gradle.kts`:

```kotlin
buildConfigField(
    "String",
    "PORTAL_URL",
    "\"https://your-domain.example.com/login\""
)
```

Use the public URL of the Laravel app. For local emulator testing, a typical Laragon URL may be:

```kotlin
"\"http://10.0.2.2/Brgy.pili_clearance/login\""
```

## Build APK

Open this folder in Android Studio and run:

```powershell
gradle assembleRelease
```

The APK will be generated in:

```text
app/build/outputs/apk/release/
```

After building, copy the signed APK to `public/downloads/resident-portal-app.apk` and update the download link in `resources/views/layouts/app.blade.php` if you want residents to download the APK directly.
