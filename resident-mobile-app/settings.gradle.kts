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

<<<<<<< HEAD
rootProject.name = "BarangayPiliResidentPortal"
=======
// Set brgyProjectName in gradle.properties to rebrand the generated APK name.
rootProject.name =
    (extra.properties["brgyProjectName"] as String?)?.trim()?.takeIf { it.isNotEmpty() }
        ?: "BarangayResidentPortal"

>>>>>>> origin/master
include(":app")
