<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Lets a barangay configure its own identity, branding and system values
 * without touching code. Admin-only.
 *
 * Setting keys contain dots, which Laravel treats as path separators in
 * validation rules and in $request->input()/file(). The form therefore posts
 * each key with "." rewritten to "__" (see formName()/settingKey()), which is
 * reversible because no setting key contains a double underscore.
 */
class SettingsController extends Controller
{
    /** Text settings the form may write: key => [group, validation rules]. */
    private const TEXT_FIELDS = [
        'barangay.name'              => ['identity', 'required|string|max:120'],
        'barangay.municipality'      => ['identity', 'required|string|max:120'],
        'barangay.municipality_type' => ['identity', 'required|in:Municipality,City'],
        'barangay.province'          => ['identity', 'required|string|max:120'],
        'barangay.region'            => ['identity', 'nullable|string|max:120'],
        'barangay.address'           => ['identity', 'nullable|string|max:255'],
        'barangay.email'             => ['identity', 'nullable|email|max:150'],
        'barangay.contact'           => ['identity', 'nullable|string|max:60'],
        'barangay.hall_name'         => ['identity', 'nullable|string|max:150'],
        'barangay.office_title'      => ['identity', 'nullable|string|max:150'],
        'barangay.session_room'      => ['identity', 'nullable|string|max:150'],

        'brand.app_title'            => ['branding', 'nullable|string|max:200'],
        'brand.short_name'           => ['branding', 'nullable|string|max:60'],
        'brand.portal_name'          => ['branding', 'nullable|string|max:120'],

        'system.tracking_prefix'     => ['system', 'required|string|max:12|regex:/^[A-Za-z0-9]+$/'],
        'system.sms_signature'       => ['system', 'nullable|string|max:60'],
        'system.admin_email'         => ['system', 'nullable|email|max:150'],
        'system.apk_path'            => ['system', 'nullable|string|max:255'],
    ];

    /** Image settings, uploaded as files: key => group. */
    private const IMAGE_FIELDS = [
        'brand.logo'              => 'branding',
        'brand.municipality_logo' => 'branding',
        'brand.favicon'           => 'branding',
    ];

    public static function formName(string $key): string
    {
        return str_replace('.', '__', $key);
    }

    private static function settingKey(string $formName): string
    {
        return str_replace('__', '.', $formName);
    }

    public function index()
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()->keyBy('key');

        return view('admin.settings', [
            'settings'    => $settings,
            'textFields'  => self::TEXT_FIELDS,
            'imageFields' => self::IMAGE_FIELDS,
        ]);
    }

    public function update(Request $request)
    {
        $rules = [];
        $names = [];

        foreach (self::TEXT_FIELDS as $key => [$group, $rule]) {
            $field = 'settings.' . self::formName($key);
            $rules[$field] = $rule;
            $names[$field] = $this->label($key);
        }

        foreach (array_keys(self::IMAGE_FIELDS) as $key) {
            $field = 'images.' . self::formName($key);
            $rules[$field] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048';
            $names[$field] = $this->label($key);
        }

        $request->validate($rules, [], $names);

        $posted = $request->input('settings', []);

        foreach (self::TEXT_FIELDS as $key => [$group, $rule]) {
            $field = self::formName($key);

            if (array_key_exists($field, $posted)) {
                Setting::put($key, trim((string) $posted[$field]), 'string', $group);
            }
        }

        $uploads = $request->file('images', []);

        foreach (self::IMAGE_FIELDS as $key => $group) {
            $file = $uploads[self::formName($key)] ?? null;

            if (! $file) {
                continue;
            }

            $dir = public_path('assets/uploads/branding');
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            $filename = self::formName($key) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);

            $previous = Setting::get($key);
            Setting::put($key, 'assets/uploads/branding/' . $filename, 'image', $group);

            // Only remove a file this feature uploaded before — never the
            // bundled default assets, which remain the fallback.
            if ($previous && str_starts_with($previous, 'assets/uploads/branding/')) {
                @unlink(public_path($previous));
            }
        }

        Setting::put('system.setup_complete', '1', 'boolean', 'system');

        ActivityLog::log('UPDATE_SETTINGS', 'Settings', 'Updated barangay configuration');

        return redirect()->route('admin.settings')
            ->with('success', 'Barangay settings saved. Documents and notifications now use the new details.');
    }

    private function label(string $key): string
    {
        $bare = preg_replace('/^(barangay|brand|system)\./', '', $key);

        return ucwords(str_replace('_', ' ', $bare));
    }
}
