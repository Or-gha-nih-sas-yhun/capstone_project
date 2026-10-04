<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal Login &mdash; Barangay Pili</title>
  <meta name="description"
    content="Official administrative login portal for Barangay Pili clearance and certificate system.">
  @php
    $assetBase = (str_contains(request()->getHost(), 'admin.') && !str_contains(request()->getHost(), 'localhost'))
      ? 'https://brgypilieclearance.com'
      : '';
  @endphp
  <link rel="icon" type="image/png"
    href="{{ $assetBase ? $assetBase . '/assets/images/pili_logo.png' : asset('assets/images/pili_logo.png') }}">
  <link rel="shortcut icon" href="{{ $assetBase ? $assetBase . '/favicon.ico' : asset('favicon.ico') }}">
  <link rel="stylesheet" href="{{ $assetBase ? $assetBase . '/assets/css/style.css' : asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>
  <div class="auth-page">
    <div class="auth-container">

      <!-- Left panel -->
      <div class="auth-left">
        <div class="brgy-seal" style="text-align: left; margin-bottom: 24px;">
          <img
            src="{{ $assetBase ? $assetBase . '/assets/images/pili_logo.png' : asset('assets/images/pili_logo.png') }}"
            alt="Barangay Logo"
            style="width: 120px; height: auto; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));">
        </div>

        <h1>Barangay Pili<br>Administrative Portal</h1>
        <p>Authorized access for Barangay Officials and Administrative Staff.</p>

        <div class="feature-list">
          <div class="feature-item"><i class="fas fa-file-shield"></i> Clearance &amp; Certificate Processing</div>
          <div class="feature-item"><i class="fas fa-users"></i> Resident Records Management</div>
          <div class="feature-item"><i class="fas fa-hand-holding-dollar"></i> Payments &amp; Revenue Monitoring</div>
          <div class="feature-item"><i class="fas fa-scale-balanced"></i> Barangay Summons &amp; KP Cases</div>
          <div class="feature-item"><i class="fas fa-key"></i> Two-Factor Security (2FA OTP)</div>
        </div>

        <div
          style="margin-top:32px;padding-top:20px;border-top:1px solid rgba(255,255,255,.2);font-size:12px;opacity:.8;">
          <i class="fas fa-shield-halved"></i> admin.brgypilieclearance.com &bull; Secure Portal
        </div>
      </div>

      <!-- Right panel -->
      <div class="auth-right">
        <div
          style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px;">
          <i class="fas fa-lock"></i> Official Access
        </div>
        <h2>Admin Sign In</h2>
        <p class="subtitle">Enter your official credentials to access the management dashboard.</p>

        @if (session('error'))
          <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif
        @if (session('success'))
          <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('info'))
          <div class="alert alert-info"
            style="background-color:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:8px;font-size:13.5px;margin-bottom:16px;">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
          </div>
        @endif
        @if ($errors->any())
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <ul style="margin: 0; padding-left: 20px;">
              @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" id="adminLoginForm">
          @csrf

          {{-- Hidden geolocation fields (populated by JS on page load) --}}
          <input type="hidden" name="lat" id="adminLat">
          <input type="hidden" name="lng" id="adminLng">

          <div class="form-group">
            <label class="form-label" for="email">Official Email Address</label>
            <input type="email" id="email" name="email" class="form-control"
              placeholder="Enter your official email address" required autocomplete="email" value="{{ old('email') }}"
              autofocus>
          </div>

          <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <label class="form-label" for="password" style="margin-bottom: 0;">Password</label>
              <a href="{{ route('password.request') }}" style="font-size: 12.5px;">Forgot Password?</a>
            </div>
            <div style="position:relative; margin-top: 6px;">
              <input type="password" id="password" name="password" class="form-control"
                placeholder="Enter your password" required autocomplete="current-password">
              <button type="button" onclick="togglePw('password')"
                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#6b7280;">
                <i class="fas fa-eye" id="pw-icon"></i>
              </button>
            </div>
          </div>

          <!-- Google reCAPTCHA v2 Widget -->
          <div style="margin: 18px 0; display: flex; justify-content: center;">
            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
          </div>

          <div
            style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; font-size: 13px; color: #4b5563;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="checkbox" name="remember" style="accent-color: #b91c1c;">
              <span>Keep me signed in</span>
            </label>
          </div>

          <!-- Location Access Notice (Mandatory for Admin Portal) -->
          <div id="locationNotice"
            style="display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:12px;color:#1e40af;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 12px;margin-bottom:14px;transition:all 0.3s ease;">
            <div style="display:flex;align-items:center;gap:8px;">
              <i id="locationIcon" class="fas fa-spinner fa-spin" style="color:#2563eb;font-size:14px;"></i>
              <span id="locationStatus">Checking location permission (Required for Admin login)...</span>
            </div>
            <button type="button" id="retryLocationBtn" onclick="requestAdminLocation()"
              style="display:none;background:#fee2e2;color:#991b1b;border:1px solid #f87171;border-radius:6px;padding:4px 8px;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;">
              <i class="fas fa-rotate-right"></i> Retry
            </button>
          </div>

          <button type="submit" id="adminSubmitBtn" class="btn btn-primary w-100" style="margin-top:4px;opacity:0.65;cursor:not-allowed;" disabled>
            <i class="fas fa-lock"></i> Waiting for Location Permission...
          </button>
        </form>

        <div
          style="margin-top: 20px; text-align: center; display: flex; flex-direction: column; gap: 8px; align-items: center;">

        </div>

      </div><!-- /.auth-right -->

    </div><!-- /.auth-container -->
  </div><!-- /.auth-page -->

  <script src="{{ $assetBase ? $assetBase . '/assets/js/main.js' : asset('assets/js/main.js') }}"></script>
  <script>
    function togglePw(id) {
      const el = document.getElementById(id);
      const icon = document.getElementById('pw-icon');
      if (el.type === 'password') {
        el.type = 'text';
        icon.className = 'fas fa-eye-slash';
      } else {
        el.type = 'password';
        icon.className = 'fas fa-eye';
      }
    }

    // ── Geolocation capture for mandatory admin login access ──
    const latField = document.getElementById('adminLat');
    const lngField = document.getElementById('adminLng');
    const statusEl = document.getElementById('locationStatus');
    const noticeEl = document.getElementById('locationNotice');
    const iconEl = document.getElementById('locationIcon');
    const submitBtn = document.getElementById('adminSubmitBtn');
    const retryBtn = document.getElementById('retryLocationBtn');

    function setLocationPrompting() {
      noticeEl.style.borderColor = '#bfdbfe';
      noticeEl.style.background = '#eff6ff';
      noticeEl.style.color = '#1e40af';
      iconEl.className = 'fas fa-spinner fa-spin';
      iconEl.style.color = '#2563eb';
      statusEl.textContent = 'Requesting location access (Required for Admin login)...';
      retryBtn.style.display = 'none';
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking Location Permission...';
      submitBtn.style.opacity = '0.65';
      submitBtn.style.cursor = 'not-allowed';
    }

    function setLocationSuccess(pos) {
      latField.value = pos.coords.latitude.toFixed(6);
      lngField.value = pos.coords.longitude.toFixed(6);
      noticeEl.style.borderColor = '#bbf7d0';
      noticeEl.style.background = '#f0fdf4';
      noticeEl.style.color = '#166534';
      iconEl.className = 'fas fa-circle-check';
      iconEl.style.color = '#16a34a';
      statusEl.innerHTML = '<strong>Location verified.</strong> You may now proceed to log in.';
      retryBtn.style.display = 'none';
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fas fa-key"></i> Proceed to Security OTP Verification';
      submitBtn.style.opacity = '1';
      submitBtn.style.cursor = 'pointer';
    }

    function setLocationError(msg) {
      latField.value = '';
      lngField.value = '';
      noticeEl.style.borderColor = '#fca5a5';
      noticeEl.style.background = '#fef2f2';
      noticeEl.style.color = '#991b1b';
      iconEl.className = 'fas fa-triangle-exclamation';
      iconEl.style.color = '#dc2626';
      statusEl.innerHTML = '<strong>Access Blocked:</strong> ' + msg;
      retryBtn.style.display = 'inline-block';
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-ban"></i> Location Permission Required';
      submitBtn.style.opacity = '0.65';
      submitBtn.style.cursor = 'not-allowed';
    }

    function requestAdminLocation() {
      if (!navigator.geolocation) {
        setLocationError('Geolocation is not supported by your browser.');
        return;
      }

      setLocationPrompting();

      navigator.geolocation.getCurrentPosition(
        function (pos) {
          setLocationSuccess(pos);
        },
        function (err) {
          let reason = 'Please allow location permission in your browser to log in.';
          if (err.code === err.PERMISSION_DENIED) {
            reason = 'Location permission denied. You cannot log in without granting location access.';
          } else if (err.code === err.POSITION_UNAVAILABLE) {
            reason = 'Location unavailable. Please check your device location settings.';
          } else if (err.code === err.TIMEOUT) {
            reason = 'Location request timed out. Please click Retry.';
          }
          setLocationError(reason);
        },
        { timeout: 10000, maximumAge: 0, enableHighAccuracy: true }
      );
    }

    // Initialize location check on load
    requestAdminLocation();

    // Prevent submission if location not set
    document.getElementById('adminLoginForm').addEventListener('submit', function (e) {
      if (!latField.value || !lngField.value) {
        e.preventDefault();
        alert('Location access is strictly mandatory for administrative access. Please grant location permissions in your browser.');
        requestAdminLocation();
      }
    });
  </script>
</body>

</html>