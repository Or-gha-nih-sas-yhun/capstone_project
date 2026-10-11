<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Loaded here as well as through composer's autoload.files so the
        // helpers exist even if `composer dump-autoload` has not been run
        // after pulling these changes.
        $helpers = app_path('Helpers/settings.php');

        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    public function boot()
    {
        // Make barangay identity available to every view without each
        // controller having to pass it.
        View::composer('*', function ($view) {
            $view->with('brgy', [
                'name'         => barangay_name(),
                'label'        => barangay_label(),
                'short'        => Setting::get('brand.short_name', barangay_label()),
                'location'     => barangay_location(),
                'municipality' => Setting::get('barangay.municipality', ''),
                'province'     => Setting::get('barangay.province', ''),
                'logo'         => setting_image('brand.logo'),
                'muni_logo'    => setting_image('brand.municipality_logo', 'assets/images/municipality_logo.png'),
            ]);
        });
    }
}
