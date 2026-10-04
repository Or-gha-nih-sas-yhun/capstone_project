<!DOCTYPE html>
<html lang="en" style="background:#f1f5f9;">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Barangay Pili — Clearance & Certificate System</title>
  <meta name="description" content="Official online portal for Barangay Pili clearance and certificate requests.">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/pili_logo.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body style="background:#f1f5f9;">
  <div class="auth-page">
    <div class="auth-container">

      <!-- Left panel -->
      <div class="auth-left">
        <div class="brgy-seal" style="text-align: left; margin-bottom: 24px;">
          <img src="{{ asset('assets/images/pili_logo.png') }}" alt="Barangay Logo"
            style="width: 120px; height: auto; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));">
        </div>

        <h1>Barangay Pili<br>Streamlined Clearance and Certificate </h1>
        <p>Your one-stop portal for barangay clearances and certificates.</p>
        <div class="feature-list">
          <div class="feature-item"><i class="fas fa-file-shield"></i> Barangay Clearance</div>
          <div class="feature-item"><i class="fas fa-certificate"></i> Various Certificates</div>
          <div class="feature-item"><i class="fas fa-clock"></i> Real-Time Status Tracking</div>
          <div class="feature-item"><i class="fas fa-print"></i> Print Documents</div>
          <div class="feature-item"><i class="fas fa-lock"></i> Secure &amp; Private</div>
        </div>
        <div
          style="margin-top:32px;padding-top:20px;border-top:1px solid rgba(255,255,255,.2);font-size:12px;opacity:.6;">
          Barangay Pili, Madridejos ,Cebu &bull; v1.0.0
        </div>
      </div>

      <!-- Right panel -->
      <div class="auth-right">
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to your account or register as a new resident.</p>

        <!-- Tabs -->
        <div class="auth-tabs">
          <button class="auth-tab {{ !session('errors') && !session('reg_tab') ? 'active' : '' }}"
            onclick="switchTab('tab-login',this)" id="btn-login">
            <i class="fas fa-sign-in-alt"></i> Sign In
          </button>
          <button class="auth-tab {{ session('reg_tab') || ($errors->any() && old('email') === null) ? 'active' : '' }}"
            onclick="switchTab('tab-register',this)" id="btn-register">
            <i class="fas fa-user-plus"></i> Register
          </button>
        </div>

        @if (session('error'))
          <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif
        @if (session('success'))
          <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
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

        <!-- Login Tab -->
        <div id="tab-login" class="tab-panel"
          style="display:{{ !session('reg_tab') && !($errors->any() && old('first_name')) ? 'block' : 'none' }};">
          <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
              <label class="form-label" for="email">Email Address</label>
              <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address"
                required autocomplete="email" value="{{ old('email') }}">
            </div>
            <div class="form-group">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <label class="form-label" for="password" style="margin-bottom: 0;">Password</label>

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

            <!-- Google reCAPTCHA v2 -->
            <div style="margin: 16px 0; display: flex; justify-content: center;">
              <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100" style="margin-top:8px;">
              <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
              <a href="{{ route('password.request') }}" style="font-size: 13px;">Forgot Password?</a>

            </div>
          </form>
          <div
            style="margin-top: 16px; text-align: center; display: flex; flex-direction: column; gap: 8px; align-items: center;">
            <a href="{{ route('track') }}" class="btn btn-outline-secondary btn-sm"
              style="display:inline-flex; align-items:center; gap:8px; width: 100%; max-width: 220px; justify-content: center;">
              <i class="fas fa-search-location"></i> Track Request Status
            </a>
            <a href="{{ route('index') }}" class="btn btn-outline-secondary btn-sm"
              style="display:inline-flex; align-items:center; gap:8px; width: 100%; max-width: 220px; justify-content: center;">
              <i class="fas fa-home"></i> Back to Homepage
            </a>
          </div>
        </div>

        <!-- Register Tab -->
        <div id="tab-register" class="tab-panel"
          style="display:{{ session('reg_tab') || ($errors->any() && old('first_name')) ? 'block' : 'none' }};">

          <!-- Registration Wizard Steps Indicator -->
          <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px;">
            <div id="wiz-step-1-node" style="display: flex; align-items: center; gap: 8px;">
              <span id="wiz-step-1-circle"
                style="width: 28px; height: 28px; border-radius: 50%; background-color: #1e3a8a; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 13px;">1</span>
              <span id="wiz-step-1-label" style="font-size: 13px; font-weight: 600; color: #1e3a8a;">Info</span>
            </div>
            <div
              style="flex: 1; height: 2px; background-color: #e2e8f0; margin: 0 12px; transition: background-color 0.3s;"
              id="wiz-step-line-1"></div>
            <div id="wiz-step-2-node"
              style="display: flex; align-items: center; gap: 8px; opacity: 0.5; transition: opacity 0.3s;">
              <span id="wiz-step-2-circle"
                style="width: 28px; height: 28px; border-radius: 50%; background-color: #e5e7eb; color: #6b7280; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 13px;">2</span>
              <span id="wiz-step-2-label" style="font-size: 13px; font-weight: 500; color: #6b7280;">Password</span>
            </div>
            <div style="flex: 1; height: 2px; background-color: #e2e8f0; margin: 0 12px;"></div>
            <div style="display: flex; align-items: center; gap: 8px; opacity: 0.5;">
              <span
                style="width: 28px; height: 28px; border-radius: 50%; background-color: #e5e7eb; color: #6b7280; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 13px;">3</span>
              <span style="font-size: 13px; font-weight: 500; color: #6b7280;">Verify</span>
            </div>
          </div>

          <form method="POST" action="{{ route('register') }}" id="register-wizard-form">
            @csrf

            <!-- Step 1 Container: Personal Information -->
            <div id="register-step-1-container">
              <div class="grid-2">
                <div class="form-group">
                  <label class="form-label">First Name *</label>
                  <input type="text" name="first_name" class="form-control" required placeholder="Juan"
                    value="{{ old('first_name') }}" pattern="[A-Za-z\s\-\.,]+"
                    title="Only letters, spaces, hyphens, and periods are allowed"
                    oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
                </div>
                <div class="form-group">
                  <label class="form-label">Last Name *</label>
                  <input type="text" name="last_name" class="form-control" required placeholder="Dela Cruz"
                    value="{{ old('last_name') }}" pattern="[A-Za-z\s\-\.,]+"
                    title="Only letters, spaces, hyphens, and periods are allowed"
                    oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Middle Name</label>
                <input type="text" name="middle_name" class="form-control" placeholder="Optional"
                  value="{{ old('middle_name') }}" pattern="[A-Za-z\s\-\.,]+"
                  title="Only letters, spaces, hyphens, and periods are allowed"
                  oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
              </div>
              <div class="grid-2">
                <div class="form-group">
                  <label class="form-label">Gender *</label>
                  <select name="gender" class="form-select" required>
                    <option value="">Select</option>
                    <option {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                    <option {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                    <option {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Birthdate *</label>
                  <input type="date" name="birthdate" class="form-control" min="1900-01-01" max="{{ date('Y-m-d') }}"
                    value="{{ old('birthdate') }}" required>
                </div>

                <div class="form-group">
                  <label class="form-label">Civil Status *</label>
                  <select name="civil_status" class="form-select" required>
                    <option value="">Select</option>
                    <option {{ old('civil_status') === 'Single' ? 'selected' : '' }}>Single</option>
                    <option {{ old('civil_status') === 'Married' ? 'selected' : '' }}>Married</option>
                    <option {{ old('civil_status') === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                    <option {{ old('civil_status') === 'Separated' ? 'selected' : '' }}>Separated</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Contact Number</label>
                  <input type="text" name="contact_number" class="form-control" placeholder="09XXXXXXXXX"
                    value="{{ old('contact_number') }}">
                </div>

                <div class="form-group">
                  <label class="form-label">Purok</label>
                  <input type="text" name="purok" class="form-control" placeholder="e.g. Purok 1"
                    value="{{ old('purok') }}">
                </div>
                <div class="form-group">
                  <label class="form-label">Years of Residency</label>
                  <input type="number" name="years_of_residency" class="form-control" min="0"
                    value="{{ old('years_of_residency', 0) }}">
                </div>

                <div class="form-group">
                  <label class="form-label">Email Address *</label>
                  <input type="email" name="email" class="form-control" required placeholder="you@email.com"
                    value="{{ old('email') }}">
                </div>

                <div class="form-group">
                  <label class="form-label">Username *</label>
                  <input type="text" name="username" class="form-control" required placeholder="username"
                    value="{{ old('username') }}">
                </div>
              </div>

              <button type="button" class="btn btn-primary w-100" style="margin-top: 16px;"
                onclick="goToRegisterStep2()">
                Next Step: Confirm Password <i class="fas fa-arrow-right" style="margin-left: 6px;"></i>
              </button>
            </div>

            <!-- Step 2 Container: Password & Confirmation -->
            <div id="register-step-2-container" style="display: none;">
              <div class="form-group">
                <label class="form-label">Password *</label>
                <div style="position:relative;">
                  <input type="password" name="password" id="register_password" class="form-control"
                    placeholder="Min 6 characters" minlength="6">
                  <button type="button" onclick="toggleRegisterPw('register_password', 'reg-pw-icon-1')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#6b7280;">
                    <i class="fas fa-eye" id="reg-pw-icon-1"></i>
                  </button>
                </div>
              </div>
              <div class="form-group" style="margin-top: 16px;">
                <label class="form-label">Confirm Password *</label>
                <div style="position:relative;">
                  <input type="password" name="password_confirmation" id="register_password_confirmation"
                    class="form-control" placeholder="Re-enter your password" minlength="6">
                  <button type="button" onclick="toggleRegisterPw('register_password_confirmation', 'reg-pw-icon-2')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#6b7280;">
                    <i class="fas fa-eye" id="reg-pw-icon-2"></i>
                  </button>
                </div>
              </div>

              <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-outline-secondary" style="flex: 1; min-height: 42px;"
                  onclick="goToRegisterStep1()">
                  <i class="fas fa-arrow-left" style="margin-right: 6px;"></i> Back
                </button>
                <button type="submit" class="btn btn-primary" style="flex: 2; min-height: 42px;">
                  <i class="fas fa-user-plus" style="margin-right: 6px;"></i> Complete Step 2
                </button>
              </div>
            </div>

          </form>
        </div>

      </div><!-- /.auth-right -->
    </div><!-- /.auth-container -->
  </div><!-- /.auth-page -->

  <script src="{{ asset('assets/js/main.js') }}"></script>
  <script>
    function togglePw(id) {
      const el = document.getElementById(id);
      const icon = document.getElementById('pw-icon');
      if (el.type === 'password') { el.type = 'text'; icon.className = 'fas fa-eye-slash'; }
      else { el.type = 'password'; icon.className = 'fas fa-eye'; }
    }

    function toggleRegisterPw(id, iconId) {
      const el = document.getElementById(id);
      const icon = document.getElementById(iconId);
      if (el.type === 'password') { el.type = 'text'; icon.className = 'fas fa-eye-slash'; }
      else { el.type = 'password'; icon.className = 'fas fa-eye'; }
    }

    function goToRegisterStep2() {
      // Find all input/select fields in step 1
      const step1Container = document.getElementById('register-step-1-container');
      const fields = step1Container.querySelectorAll('input[required], select[required]');
      let allValid = true;

      // Validate all required fields in Step 1
      fields.forEach(field => {
        if (!field.reportValidity()) {
          allValid = false;
        }
      });

      if (!allValid) return;

      // Mark password inputs as required now that we're going to step 2
      document.getElementById('register_password').required = true;
      document.getElementById('register_password_confirmation').required = true;

      // Transition to step 2 UI
      document.getElementById('register-step-1-container').style.display = 'none';
      document.getElementById('register-step-2-container').style.display = 'block';

      // Update wizard header step 2 indicator
      document.getElementById('wiz-step-2-node').style.opacity = '1';
      document.getElementById('wiz-step-2-circle').style.backgroundColor = '#1e3a8a';
      document.getElementById('wiz-step-2-circle').style.color = 'white';
      document.getElementById('wiz-step-2-label').style.fontWeight = '600';
      document.getElementById('wiz-step-2-label').style.color = '#1e3a8a';
      document.getElementById('wiz-step-line-1').style.backgroundColor = '#16a34a';
    }

    function goToRegisterStep1() {
      // Un-require password fields since they shouldn't block validation in step 1 if the user goes back
      document.getElementById('register_password').required = false;
      document.getElementById('register_password_confirmation').required = false;

      // Transition back to step 1 UI
      document.getElementById('register-step-2-container').style.display = 'none';
      document.getElementById('register-step-1-container').style.display = 'block';

      // Revert wizard header step 2 indicator
      document.getElementById('wiz-step-2-node').style.opacity = '0.5';
      document.getElementById('wiz-step-2-circle').style.backgroundColor = '#e5e7eb';
      document.getElementById('wiz-step-2-circle').style.color = '#6b7280';
      document.getElementById('wiz-step-2-label').style.fontWeight = '500';
      document.getElementById('wiz-step-2-label').style.color = '#6b7280';
      document.getElementById('wiz-step-line-1').style.backgroundColor = '#e2e8f0';
    }

    // Client-side passwords match validation
    document.getElementById('register-wizard-form').addEventListener('submit', function (e) {
      const pw = document.getElementById('register_password').value;
      const confirmPw = document.getElementById('register_password_confirmation').value;
      if (pw !== confirmPw) {
        e.preventDefault();
        alert('Passwords do not match. Please re-enter.');
        document.getElementById('register_password_confirmation').focus();
      }
    });
  </script>
</body>

</html>