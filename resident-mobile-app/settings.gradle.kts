pluginManagement {
    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }
}

dependencyResolutionManagement {
    repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS)
    repositories {
        google()
        mavenCentral()
    }
}

// Set brgyProjectName in gradle.properties to rebrand the generated APK name.
rootProject.name =
    (extra.properties["brgyProjectName"] as String?)?.trim()?.takeIf { it.isNotEmpty() }
        ?: "BarangayResidentPortal"

include(":app")
