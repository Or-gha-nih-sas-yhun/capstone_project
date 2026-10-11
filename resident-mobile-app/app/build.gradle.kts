plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

<<<<<<< HEAD
android {
    namespace = "com.barangaypili.residentportal"
=======
// Branding values live in gradle.properties so this app can be rebuilt for
// another barangay without editing any source file. See that file for the
// full list.
fun brgy(key: String, fallback: String): String =
    (project.findProperty(key) as String?)?.trim()?.takeIf { it.isNotEmpty() } ?: fallback

android {
    // Internal package used for R and BuildConfig generation. This is not
    // the user-visible app identity — that is applicationId below.
    namespace = "com.barangayportal.residentportal"
>>>>>>> origin/master
    compileSdk = 35

    buildFeatures {
        buildConfig = true
    }

    defaultConfig {
<<<<<<< HEAD
        applicationId = "com.barangaypili.residentportal"
=======
        applicationId = brgy("brgyApplicationId", "com.barangaypili.residentportal")
>>>>>>> origin/master
        minSdk = 26
        targetSdk = 35
        versionCode = 4
        versionName = "1.0.3"

<<<<<<< HEAD
        buildConfigField(
            "String",
            "PORTAL_URL",
            "\"https://brgypilieclearance.com/\""
=======
        // Launcher label, generated from gradle.properties rather than being
        // hard-coded in strings.xml.
        resValue("string", "app_name", brgy("brgyAppName", "Barangay Resident Portal"))

        buildConfigField(
            "String",
            "PORTAL_URL",
            "\"${brgy("brgyPortalUrl", "http://localhost/")}\""
        )

        buildConfigField(
            "String",
            "UA_TOKEN",
            "\"${brgy("brgyUaToken", "BrgyPortalApp")}\""
>>>>>>> origin/master
        )
    }

    buildTypes {
        release {
            isMinifyEnabled = false
            signingConfig = signingConfigs.getByName("debug")
            isCrunchPngs = false
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_1_8
        targetCompatibility = JavaVersion.VERSION_1_8
    }

    kotlinOptions {
        jvmTarget = "1.8"
    }
}
