@extends('layouts.app')

@section('title', 'Barangay Settings')

@php
  use App\Http\Controllers\SettingsController as SC;

  // Human labels and hints for each configurable key.
  $meta = [
    'barangay.name' => ['Barangay Name', 'Without the word "Barangay" — e.g. Pili'],
    'barangay.municipality' => ['Municipality / City', 'e.g. Madridejos'],
    'barangay.municipality_type' => ['Type', 'Shown on document letterheads'],
    'barangay.province' => ['Province', 'e.g. Cebu'],
    'barangay.region' => ['Region', 'Optional — e.g. Region VII (Central Visayas)'],
    'barangay.address' => ['Full Address', 'Used as the default address on new resident records'],
    'barangay.email' => ['Official Email', 'Printed on certificates and used as the contact address'],
    'barangay.contact' => ['Contact Number', 'Optional'],
    'barangay.hall_name' => ['Barangay Hall Name', 'e.g. Barangay Pili Hall'],
    'barangay.office_title' => ['Office Title on Documents', 'e.g. Office of the Punong Barangay'],
    'barangay.session_room' => ['Hearing Venue', 'Printed on KP summons and hearing notices'],
    'brand.app_title' => ['System Title', 'Shown in page titles and email subjects'],
    'brand.short_name' => ['Short Name', 'Shown in the sidebar — e.g. Brgy. Pili'],
    'brand.portal_name' => ['Portal Name', 'Used in resident-facing emails'],
    'system.tracking_prefix' => ['Tracking Number Prefix', 'Letters/numbers only — e.g. PILI gives PILI-20260730-A1B2C3'],
    'system.sms_signature' => ['SMS Signature', 'Appended to outgoing text messages'],
    'system.admin_email' => ['Admin Notification Email', 'Receives new case filings'],
    'system.apk_path' => ['Android App File', 'Path under public/ for the download link'],
  ];

  $imageMeta = [
    'brand.logo' => ['Barangay Seal / Logo', 'Appears on letterheads, the sidebar and as the watermark'],
    'brand.municipality_logo' => ['Municipality / City Seal', 'Appears on the right of document letterheads'],
    'brand.favicon' => ['Browser Tab Icon', 'Small square image'],
  ];

  $groupTitles = [
    'identity' => ['Barangay Identity', 'fa-landmark', 'Printed on every certificate, clearance and notice.'],
    'branding' => ['Branding', 'fa-palette', 'Names and images shown across the portal.'],
    'system' => ['System', 'fa-sliders', 'Tracking numbers, SMS and notification targets.'],
  ];

  $val = fn($key) => old('settings.' . SC::formName($key), $settings[$key]->value ?? '');
@endphp

@section('content')
  <div style="max-width:1000px;margin:0 auto;">

    <div class="card" style="margin-bottom:20px;">
      <div class="card-body" style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
        <div
          style="width:64px;height:64px;flex-shrink:0;border-radius:12px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;overflow:hidden;">
          <img src="{{ setting_image('brand.logo') }}" alt="Current barangay seal"
            style="width:100%;height:100%;object-fit:contain;">
        </div>
        <div style="flex:1;min-width:220px;">
          <div style="font-size:20px;font-weight:800;">{{ barangay_label() }}</div>
          <div style="color:#6b7280;font-size:13.5px;">{{ barangay_location() }}</div>
        </div>
        <div style="font-size:13px;color:#6b7280;max-width:340px;">
          <i class="fas fa-circle-info" style="color:var(--primary);margin-right:5px;"></i>
          These values replace every hard-coded barangay detail in documents, emails and SMS.
        </div>
      </div>
    </div>

    @if (session('success'))
      <div class="alert alert-success" style="margin-bottom:16px;">
        <i class="fas fa-circle-check" style="margin-right:6px;"></i>{{ session('success') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger" style="margin-bottom:16px;">
        <strong><i class="fas fa-triangle-exclamation" style="margin-right:6px;"></i>Please correct the
          following:</strong>
        <ul style="margin:8px 0 0 18px;">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
      @csrf

      @foreach (['identity', 'branding', 'system'] as $group)
        @php([$title, $icon, $blurb] = $groupTitles[$group])
        <div class="card" style="margin-bottom:20px;">
          <div class="card-header">
            <h5><i class="fas {{ $icon }}" style="color:var(--primary);margin-right:8px;"></i>{{ $title }}</h5>
          </div>
          <div class="card-body">
            <p style="margin:0 0 14px;color:#6b7280;font-size:13px;">{{ $blurb }}</p>

            <div class="grid-2">
              @foreach ($textFields as $key => $spec)
                @continue($spec[0] !== $group)
                @php([$label, $hint] = $meta[$key] ?? [$key, ''])
                @php($required = str_contains($spec[1], 'required'))
                <div class="form-group">
                  <label class="form-label">{{ $label }}@if ($required)
                      *
                    @endif</label>

                  @if ($key === 'barangay.municipality_type')
                    <select name="settings[{{ SC::formName($key) }}]" class="form-control" required>
                      @foreach (['Municipality', 'City'] as $opt)
                        <option value="{{ $opt }}" @selected($val($key) === $opt)>{{ $opt }}</option>
                      @endforeach
                    </select>
                  @else
                    <input type="text" name="settings[{{ SC::formName($key) }}]" class="form-control"
                      value="{{ $val($key) }}" @required($required)>
                  @endif

                  @if ($hint)
                    <small style="color:#9ca3af;font-size:12px;">{{ $hint }}</small>
                  @endif
                </div>
              @endforeach

              @if ($group === 'branding')
                @foreach ($imageFields as $key => $imgGroup)
                  @php([$label, $hint] = $imageMeta[$key])
                  <div class="form-group">
                    <label class="form-label">{{ $label }}</label>
                    <div style="display:flex;gap:10px;align-items:center;">
                      <div
                        style="width:46px;height:46px;flex-shrink:0;border:1px solid #e5e7eb;border-radius:8px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                        <img src="{{ setting_image($key, 'assets/images/pili_logo.png') }}" alt="{{ $label }} preview"
                          style="width:100%;height:100%;object-fit:contain;">
                      </div>
                      <input type="file" name="images[{{ SC::formName($key) }}]" class="form-control"
                        accept="image/*">
                    </div>
                    <small style="color:#9ca3af;font-size:12px;">{{ $hint }} — leave empty to keep the current
                      image.</small>
                  </div>
                @endforeach
              @endif
            </div>
          </div>
        </div>
      @endforeach

      <div class="card">
        <div class="card-body" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-floppy-disk" style="margin-right:6px;"></i>Save Settings
          </button>
          <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Cancel</a>
          <span style="color:#6b7280;font-size:12.5px;">
            Saved changes take effect immediately on all new documents and messages.
          </span>
        </div>
      </div>
    </form>
  </div>
@endsection
