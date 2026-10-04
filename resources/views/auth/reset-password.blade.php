<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Barangay Pili — Reset Password</title>
  <meta name="description" content="Set a new password for your account.">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/pili_logo.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
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
        <div style="margin-top:32px;padding-top:20px;border-top:1px solid rgba(255,255,255,.2);font-size:12px;opacity:.6;">
          Barangay Pili, Madridejos ,Cebu &bull; v1.0.0
        </div>
      </div>

      <!-- Right panel -->
      <div class="auth-right">
        <h2>Reset Password</h2>
        <p class="subtitle">Set a new, secure password for your account.</p>

        @if (session('error'))
          <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
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

        <div class="tab-panel" style="display: block;">
          <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
              <label class="form-label" for="email">Email Address</label>
              <input type="email" id="email" name="email" class="form-control"
                placeholder="Enter your email" required autocomplete="email" value="{{ $email ?? old('email') }}" readonly>
            </div>

            <div class="form-group">
              <label class="form-label" for="password">New Password</label>
              <div style="position:relative;">
                <input type="password" id="password" name="password" class="form-control"
                  placeholder="At least 6 characters" required autocomplete="new-password" minlength="6">
                <button type="button" onclick="togglePw('password', 'pw-icon-1')"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#6b7280;">
                  <i class="fas fa-eye" id="pw-icon-1"></i>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="password_confirmation">Confirm Password</label>
              <div style="position:relative;">
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                  placeholder="Confirm new password" required autocomplete="new-password" minlength="6">
                <button type="button" onclick="togglePw('password_confirmation', 'pw-icon-2')"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#6b7280;">
                  <i class="fas fa-eye" id="pw-icon-2"></i>
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100" style="margin-top:16px;">
              <i class="fas fa-key"></i> Reset Password
            </button>
          </form>
          
          <div style="margin-top: 24px; text-align: center; border-top: 1px solid var(--gray-light); padding-top: 16px;">
            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm" style="display:inline-flex; align-items:center; gap:8px;">
              <i class="fas fa-arrow-left"></i> Cancel and Return
            </a>
          </div>
        </div>

      </div><!-- /.auth-right -->
    </div><!-- /.auth-container -->
  </div><!-- /.auth-page -->

  <script src="{{ asset('assets/js/main.js') }}"></script>
  <script>
    function togglePw(inputId, iconId) {
      const el = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      if (el.type === 'password') {
        el.type = 'text';
        icon.className = 'fas fa-eye-slash';
      } else {
        el.type = 'password';
        icon.className = 'fas fa-eye';
      }
    }
  </script>
</body>
</html>
