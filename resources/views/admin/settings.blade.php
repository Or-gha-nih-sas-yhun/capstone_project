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

      {{-- ── Barangay Identity Card ── --}}
      <div class="card" style="margin-bottom:20px;">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
          <h5 style="margin:0;"><i class="fas fa-landmark" style="color:var(--primary);margin-right:8px;"></i>Barangay Identity</h5>
          <span id="psgc-status" style="font-size:12px;color:#059669;background:#ecfdf5;padding:4px 10px;border-radius:12px;font-weight:600;display:inline-flex;align-items:center;gap:6px;">
            <i class="fas fa-map-location-dot"></i> Philippine Geographic Standard
          </span>
        </div>
        <div class="card-body">
          <p style="margin:0 0 16px;color:#6b7280;font-size:13px;">
            Printed on every certificate, clearance and notice. Select your location from the linked Philippine geographic dropdowns below.
          </p>

          <div class="grid-2">
            {{-- Region --}}
            <div class="form-group">
              <label class="form-label" for="geo_region">Region</label>
              <select id="geo_region" name="settings[{{ SC::formName('barangay.region') }}]" class="form-control">
                <option value="">— Select Region —</option>
              </select>
              <small style="color:#9ca3af;font-size:12px;">Optional — e.g. Region VII (Central Visayas)</small>
            </div>

            {{-- Province --}}
            <div class="form-group">
              <label class="form-label" for="geo_province">Province *</label>
              <select id="geo_province" name="settings[{{ SC::formName('barangay.province') }}]" class="form-control" required>
                <option value="">— Select Province —</option>
              </select>
              <small style="color:#9ca3af;font-size:12px;">e.g. Cebu</small>
            </div>

            {{-- Municipality / City --}}
            <div class="form-group">
              <label class="form-label" for="geo_municipality">Municipality / City *</label>
              <select id="geo_municipality" name="settings[{{ SC::formName('barangay.municipality') }}]" class="form-control" required>
                <option value="">— Select Municipality / City —</option>
              </select>
              <small style="color:#9ca3af;font-size:12px;">e.g. Madridejos</small>
            </div>

            {{-- Type --}}
            <div class="form-group">
              <label class="form-label" for="geo_type">Type *</label>
              <select id="geo_type" name="settings[{{ SC::formName('barangay.municipality_type') }}]" class="form-control" required>
                <option value="Municipality" @selected($val('barangay.municipality_type') === 'Municipality')>Municipality</option>
                <option value="City" @selected($val('barangay.municipality_type') === 'City')>City</option>
              </select>
              <small style="color:#9ca3af;font-size:12px;">Shown on document letterheads (auto-detects with selection)</small>
            </div>

            {{-- Barangay Name --}}
            <div class="form-group">
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <label class="form-label" for="geo_barangay">Barangay Name *</label>
                <button type="button" id="btn-toggle-custom-barangay" style="background:none;border:none;color:var(--primary);font-size:11.5px;font-weight:600;cursor:pointer;padding:0 0 4px;" title="Switch between dropdown and custom text input">
                  <i class="fas fa-keyboard"></i> Type custom
                </button>
              </div>
              <select id="geo_barangay" name="settings[{{ SC::formName('barangay.name') }}]" class="form-control" required>
                <option value="">— Select Barangay —</option>
              </select>
              <input type="text" id="geo_barangay_custom" class="form-control" placeholder="Enter custom barangay name" style="display:none;margin-top:6px;">
              <small style="color:#9ca3af;font-size:12px;">Without the word "Barangay" — e.g. Pili</small>
            </div>

            {{-- Full Address --}}
            <div class="form-group">
              <label class="form-label" for="geo_address">Full Address</label>
              <input type="text" id="geo_address" name="settings[{{ SC::formName('barangay.address') }}]" class="form-control" value="{{ $val('barangay.address') }}">
              <small style="color:#9ca3af;font-size:12px;">Used as default address on new resident records (auto-updates with selection)</small>
            </div>

            {{-- Official Email --}}
            <div class="form-group">
              <label class="form-label">Official Email</label>
              <input type="email" name="settings[{{ SC::formName('barangay.email') }}]" class="form-control" value="{{ $val('barangay.email') }}">
              <small style="color:#9ca3af;font-size:12px;">Printed on certificates and used as the contact address</small>
            </div>

            {{-- Contact Number --}}
            <div class="form-group">
              <label class="form-label">Contact Number</label>
              <input type="text" name="settings[{{ SC::formName('barangay.contact') }}]" class="form-control" value="{{ $val('barangay.contact') }}">
              <small style="color:#9ca3af;font-size:12px;">Optional</small>
            </div>

            {{-- Barangay Hall Name --}}
            <div class="form-group">
              <label class="form-label">Barangay Hall Name</label>
              <input type="text" id="geo_hall_name" name="settings[{{ SC::formName('barangay.hall_name') }}]" class="form-control" value="{{ $val('barangay.hall_name') }}">
              <small style="color:#9ca3af;font-size:12px;">e.g. Barangay Pili Hall</small>
            </div>

            {{-- Office Title on Documents --}}
            <div class="form-group">
              <label class="form-label">Office Title on Documents</label>
              <input type="text" name="settings[{{ SC::formName('barangay.office_title') }}]" class="form-control" value="{{ $val('barangay.office_title') }}">
              <small style="color:#9ca3af;font-size:12px;">e.g. Office of the Punong Barangay</small>
            </div>

            {{-- Hearing Venue --}}
            <div class="form-group" style="grid-column: span 2;">
              <label class="form-label">Hearing Venue</label>
              <input type="text" id="geo_session_room" name="settings[{{ SC::formName('barangay.session_room') }}]" class="form-control" value="{{ $val('barangay.session_room') }}">
              <small style="color:#9ca3af;font-size:12px;">Printed on KP summons and hearing notices</small>
            </div>
          </div>
        </div>
      </div>

      {{-- ── Branding & System Cards ── --}}
      @foreach (['branding', 'system'] as $group)
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

@section('scripts')
<script>
(function() {
  const initialData = {
    region: @json($val('barangay.region') ?: 'Region VII (Central Visayas)'),
    province: @json($val('barangay.province') ?: 'Cebu'),
    municipality: @json($val('barangay.municipality') ?: 'Madridejos'),
    municipalityType: @json($val('barangay.municipality_type') ?: 'Municipality'),
    barangay: @json($val('barangay.name') ?: 'Pili'),
    address: @json($val('barangay.address') ?: '')
  };

  const REGIONS = [
    { code: '010000000', name: 'Region I (Ilocos Region)' },
    { code: '020000000', name: 'Region II (Cagayan Valley)' },
    { code: '030000000', name: 'Region III (Central Luzon)' },
    { code: '040000000', name: 'Region IV-A (CALABARZON)' },
    { code: '170000000', name: 'MIMAROPA Region' },
    { code: '050000000', name: 'Region V (Bicol Region)' },
    { code: '060000000', name: 'Region VI (Western Visayas)' },
    { code: '070000000', name: 'Region VII (Central Visayas)' },
    { code: '080000000', name: 'Region VIII (Eastern Visayas)' },
    { code: '090000000', name: 'Region IX (Zamboanga Peninsula)' },
    { code: '100000000', name: 'Region X (Northern Mindanao)' },
    { code: '110000000', name: 'Region XI (Davao Region)' },
    { code: '120000000', name: 'Region XII (SOCCSKSARGEN)' },
    { code: '160000000', name: 'Region XIII (Caraga)' },
    { code: '130000000', name: 'NCR (National Capital Region)' },
    { code: '140000000', name: 'CAR (Cordillera Administrative Region)' },
    { code: '150000000', name: 'BARMM (Bangsamoro Autonomous Region)' }
  ];

  const OFFLINE_PROVINCES = {
    '070000000': [
      { code: '071200000', name: 'Bohol' },
      { code: '072200000', name: 'Cebu' },
      { code: '074600000', name: 'Negros Oriental' },
      { code: '076100000', name: 'Siquijor' }
    ]
  };

  const OFFLINE_MUNICIPALITIES = {
    '072200000': [
      { code: '072201000', name: 'Alcantara' }, { code: '072202000', name: 'Alcoy' },
      { code: '072203000', name: 'Alegria' }, { code: '072204000', name: 'Aloguinsan' },
      { code: '072205000', name: 'Argao' }, { code: '072206000', name: 'Asturias' },
      { code: '072207000', name: 'Badian' }, { code: '072208000', name: 'Balamban' },
      { code: '072209000', name: 'Bantayan' }, { code: '072210000', name: 'Barili' },
      { code: '072211000', name: 'City of Bogo' }, { code: '072212000', name: 'Boljoon' },
      { code: '072213000', name: 'Borbon' }, { code: '072214000', name: 'City of Carcar' },
      { code: '072215000', name: 'Carmen' }, { code: '072216000', name: 'Catmon' },
      { code: '072217000', name: 'City of Cebu' }, { code: '072218000', name: 'Compostela' },
      { code: '072219000', name: 'Consolacion' }, { code: '072220000', name: 'Cordova' },
      { code: '072221000', name: 'Daanbantayan' }, { code: '072222000', name: 'Dalaguete' },
      { code: '072223000', name: 'Danao City' }, { code: '072224000', name: 'Dumanjug' },
      { code: '072225000', name: 'Ginatilan' }, { code: '072226000', name: 'City of Lapu-Lapu' },
      { code: '072227000', name: 'Liloan' }, { code: '072228000', name: 'Madridejos' },
      { code: '072229000', name: 'Malabuyoc' }, { code: '072230000', name: 'City of Mandaue' },
      { code: '072231000', name: 'Medellin' }, { code: '072232000', name: 'Minglanilla' },
      { code: '072233000', name: 'Moalboal' }, { code: '072234000', name: 'City of Naga' },
      { code: '072235000', name: 'Oslob' }, { code: '072236000', name: 'Pilar' },
      { code: '072237000', name: 'Pinamungajan' }, { code: '072238000', name: 'Poro' },
      { code: '072239000', name: 'Ronda' }, { code: '072240000', name: 'Samboan' },
      { code: '072241000', name: 'San Fernando' }, { code: '072242000', name: 'San Francisco' },
      { code: '072243000', name: 'San Remigio' }, { code: '072244000', name: 'Santa Fe' },
      { code: '072245000', name: 'Santander' }, { code: '072246000', name: 'Sibonga' },
      { code: '072247000', name: 'Sogod' }, { code: '072248000', name: 'Tabogon' },
      { code: '072249000', name: 'Tabuelan' }, { code: '072250000', name: 'City of Talisay' },
      { code: '072251000', name: 'City of Toledo' }, { code: '072252000', name: 'Tuburan' },
      { code: '072253000', name: 'Tudela' }
    ]
  };

  const OFFLINE_BARANGAYS = {
    '072228000': [
      'Bunakan', 'Kangwayan', 'Kaongkod', 'Kodia', 'Maalat', 'Malbago',
      'Mancilang', 'Pili', 'Poblacion', 'San Agustin', 'Tabagak', 'Talangnan',
      'Tarong', 'Tugas'
    ],
    '072209000': [
      'Atop-atop', 'Baigad', 'Baod', 'Binaobao', 'Botigues', 'Doong',
      'Guiwanon', 'Hilotongan', 'Kabac', 'Kabangbang', 'Kampingganon',
      'Kangkaibe', 'Lipayran', 'Luyongbaybay', 'Mojon', 'Obo-ob', 'Patao',
      'Poblacion', 'Sillon', 'Suba', 'Sulangan', 'Sungko', 'Tamiao', 'Ticad'
    ],
    '072244000': [
      'Balidbid', 'Hagdan', 'Hilantagaan', 'Kinatarkan', 'Langub',
      'Maricaban', 'Okoy', 'Poblacion', 'Pooc', 'Talisay'
    ]
  };

  const regionSelect = document.getElementById('geo_region');
  const provinceSelect = document.getElementById('geo_province');
  const munSelect = document.getElementById('geo_municipality');
  const typeSelect = document.getElementById('geo_type');
  const brgySelect = document.getElementById('geo_barangay');
  const brgyCustomInput = document.getElementById('geo_barangay_custom');
  const btnToggleCustom = document.getElementById('btn-toggle-custom-barangay');
  const addressInput = document.getElementById('geo_address');
  const hallNameInput = document.getElementById('geo_hall_name');
  const sessionRoomInput = document.getElementById('geo_session_room');
  const statusBadge = document.getElementById('psgc-status');

  const cache = {};

  async function fetchJson(url) {
    if (cache[url]) return cache[url];
    try {
      const ctrl = new AbortController();
      const timeout = setTimeout(() => ctrl.abort(), 4500);
      const res = await fetch(url, { signal: ctrl.signal });
      clearTimeout(timeout);
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      cache[url] = data;
      return data;
    } catch (err) {
      console.warn('PSGC API request failed for ' + url, err);
      return null;
    }
  }

  function setStatus(text, icon = 'fa-map-location-dot', color = '#059669', bg = '#ecfdf5') {
    if (!statusBadge) return;
    statusBadge.innerHTML = `<i class="fas ${icon}"></i> ${text}`;
    statusBadge.style.color = color;
    statusBadge.style.backgroundColor = bg;
  }

  function initRegions() {
    regionSelect.innerHTML = '<option value="">— Select Region —</option>';
    let matchedCode = null;

    REGIONS.forEach(r => {
      const opt = document.createElement('option');
      opt.value = r.name;
      opt.dataset.code = r.code;
      opt.textContent = r.name;
      if (initialData.region && (r.name.toLowerCase() === initialData.region.toLowerCase() || r.name.toLowerCase().includes(initialData.region.toLowerCase()))) {
        opt.selected = true;
        matchedCode = r.code;
      }
      regionSelect.appendChild(opt);
    });

    if (initialData.region && !matchedCode) {
      const customOpt = document.createElement('option');
      customOpt.value = initialData.region;
      customOpt.textContent = initialData.region;
      customOpt.selected = true;
      regionSelect.appendChild(customOpt);
    }

    return matchedCode || (regionSelect.selectedOptions[0] ? regionSelect.selectedOptions[0].dataset.code : null);
  }

  async function loadProvinces(regionCode, selectedName) {
    provinceSelect.innerHTML = '<option value="">— Select Province —</option>';
    if (!regionCode) return null;

    setStatus('Loading provinces...', 'fa-spinner fa-spin', '#2563eb', '#eff6ff');

    let list = OFFLINE_PROVINCES[regionCode] || null;
    if (!list) {
      const url = regionCode === '130000000'
        ? 'https://psgc.gitlab.io/api/regions/130000000/districts/'
        : `https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`;
      list = await fetchJson(url);
    }

    setStatus('Philippine Geographic Standard', 'fa-map-location-dot', '#059669', '#ecfdf5');

    let matchedCode = null;
    if (Array.isArray(list) && list.length > 0) {
      list.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.name;
        opt.dataset.code = p.code;
        opt.textContent = p.name;
        if (selectedName && p.name.toLowerCase() === selectedName.toLowerCase()) {
          opt.selected = true;
          matchedCode = p.code;
        }
        provinceSelect.appendChild(opt);
      });
    }

    if (selectedName && !matchedCode) {
      const customOpt = document.createElement('option');
      customOpt.value = selectedName;
      customOpt.textContent = selectedName;
      customOpt.selected = true;
      provinceSelect.appendChild(customOpt);
    }

    return matchedCode || (provinceSelect.selectedOptions[0] ? provinceSelect.selectedOptions[0].dataset.code : null);
  }

  async function loadMunicipalities(provCode, selectedName) {
    munSelect.innerHTML = '<option value="">— Select Municipality / City —</option>';
    if (!provCode) return null;

    setStatus('Loading municipalities...', 'fa-spinner fa-spin', '#2563eb', '#eff6ff');

    let list = OFFLINE_MUNICIPALITIES[provCode] || null;
    if (!list) {
      const url = `https://psgc.gitlab.io/api/provinces/${provCode}/cities-municipalities/`;
      list = await fetchJson(url);
    }

    setStatus('Philippine Geographic Standard', 'fa-map-location-dot', '#059669', '#ecfdf5');

    let matchedCode = null;
    if (Array.isArray(list) && list.length > 0) {
      list.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.name;
        opt.dataset.code = m.code;
        opt.textContent = m.name;
        if (selectedName && m.name.toLowerCase() === selectedName.toLowerCase()) {
          opt.selected = true;
          matchedCode = m.code;
        }
        munSelect.appendChild(opt);
      });
    }

    if (selectedName && !matchedCode) {
      const customOpt = document.createElement('option');
      customOpt.value = selectedName;
      customOpt.textContent = selectedName;
      customOpt.selected = true;
      munSelect.appendChild(customOpt);
    }

    return matchedCode || (munSelect.selectedOptions[0] ? munSelect.selectedOptions[0].dataset.code : null);
  }

  async function loadBarangays(munCode, selectedName) {
    brgySelect.innerHTML = '<option value="">— Select Barangay —</option>';
    if (!munCode) return;

    setStatus('Loading barangays...', 'fa-spinner fa-spin', '#2563eb', '#eff6ff');

    let list = OFFLINE_BARANGAYS[munCode] || null;
    if (!list) {
      const url = `https://psgc.gitlab.io/api/cities-municipalities/${munCode}/barangays/`;
      const fetched = await fetchJson(url);
      if (Array.isArray(fetched)) {
        list = fetched.map(b => b.name);
      }
    }

    setStatus('Philippine Geographic Standard', 'fa-map-location-dot', '#059669', '#ecfdf5');

    let matched = false;
    if (Array.isArray(list) && list.length > 0) {
      list.forEach(name => {
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name;
        if (selectedName && name.toLowerCase() === selectedName.toLowerCase()) {
          opt.selected = true;
          matched = true;
        }
        brgySelect.appendChild(opt);
      });
    }

    if (selectedName && !matched) {
      const customOpt = document.createElement('option');
      customOpt.value = selectedName;
      customOpt.textContent = selectedName;
      customOpt.selected = true;
      brgySelect.appendChild(customOpt);
    }
  }

  function syncAddressFields() {
    const brgy = (brgySelect.style.display !== 'none' ? brgySelect.value : brgyCustomInput.value).trim();
    const mun = munSelect.value.trim();
    const prov = provinceSelect.value.trim();

    if (brgy && mun && prov) {
      const newAddress = `Barangay ${brgy}, ${mun}, ${prov}`;
      if (!addressInput.value || addressInput.value.startsWith('Barangay ')) {
        addressInput.value = newAddress;
      }

      if (hallNameInput && (!hallNameInput.value || (hallNameInput.value.startsWith('Barangay ') && hallNameInput.value.endsWith(' Hall')))) {
        hallNameInput.value = `Barangay ${brgy} Hall`;
      }

      if (sessionRoomInput && (!sessionRoomInput.value || sessionRoomInput.value.includes('Hall, Session Room'))) {
        sessionRoomInput.value = `Barangay ${brgy} Hall, Session Room`;
      }
    }
  }

  function detectMunicipalityType(name) {
    if (!name) return;
    const lower = name.toLowerCase();
    if (lower.startsWith('city of ') || lower.endsWith(' city') || lower.includes(' city')) {
      typeSelect.value = 'City';
    } else {
      typeSelect.value = 'Municipality';
    }
  }

  regionSelect.addEventListener('change', async function() {
    const opt = regionSelect.selectedOptions[0];
    const code = opt ? opt.dataset.code : null;
    await loadProvinces(code, null);
    munSelect.innerHTML = '<option value="">— Select Municipality / City —</option>';
    brgySelect.innerHTML = '<option value="">— Select Barangay —</option>';
  });

  provinceSelect.addEventListener('change', async function() {
    const opt = provinceSelect.selectedOptions[0];
    const code = opt ? opt.dataset.code : null;
    await loadMunicipalities(code, null);
    brgySelect.innerHTML = '<option value="">— Select Barangay —</option>';
  });

  munSelect.addEventListener('change', async function() {
    const opt = munSelect.selectedOptions[0];
    const code = opt ? opt.dataset.code : null;
    detectMunicipalityType(munSelect.value);
    await loadBarangays(code, null);
  });

  brgySelect.addEventListener('change', function() {
    syncAddressFields();
  });

  if (btnToggleCustom) {
    let customMode = false;
    btnToggleCustom.addEventListener('click', function() {
      customMode = !customMode;
      if (customMode) {
        brgySelect.style.display = 'none';
        brgySelect.removeAttribute('name');
        brgyCustomInput.style.display = 'block';
        brgyCustomInput.setAttribute('name', 'settings[{{ SC::formName('barangay.name') }}]');
        brgyCustomInput.value = brgySelect.value || '';
        brgyCustomInput.focus();
        btnToggleCustom.innerHTML = '<i class="fas fa-list"></i> Select from list';
      } else {
        brgyCustomInput.style.display = 'none';
        brgyCustomInput.removeAttribute('name');
        brgySelect.style.display = 'block';
        brgySelect.setAttribute('name', 'settings[{{ SC::formName('barangay.name') }}]');
        if (brgyCustomInput.value) {
          let exists = Array.from(brgySelect.options).find(o => o.value.toLowerCase() === brgyCustomInput.value.toLowerCase());
          if (!exists) {
            exists = document.createElement('option');
            exists.value = brgyCustomInput.value;
            exists.textContent = brgyCustomInput.value;
            brgySelect.appendChild(exists);
          }
          exists.selected = true;
        }
        btnToggleCustom.innerHTML = '<i class="fas fa-keyboard"></i> Type custom';
      }
    });

    brgyCustomInput.addEventListener('input', syncAddressFields);
  }

  async function init() {
    const regionCode = initRegions();
    const provCode = await loadProvinces(regionCode, initialData.province);
    const munCode = await loadMunicipalities(provCode, initialData.municipality);
    await loadBarangays(munCode, initialData.barangay);

    if (initialData.municipalityType) {
      typeSelect.value = initialData.municipalityType;
    }
  }

  init();
})();
</script>
@endsection
