<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Barangay Pili — Forgot Password</title>
  <meta name="description" content="Request a link to reset your account password.">
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
        <h2>Forgot Password</h2>
        <p class="subtitle">Enter your registered email address and we'll send you a link to reset your password.</p>

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

        <div class="tab-panel" style="display: block;">
          <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
              <label class="form-label" for="email">Email Address</label>
              <input type="email" id="email" name="email" class="form-control"
                placeholder="Enter your registered email" required autocomplete="email" value="{{ old('email') }}">
            </div>
            
            <button type="submit" class="btn btn-primary w-100" style="margin-top:16px;">
              <i class="fas fa-paper-plane"></i> Send Password Reset Link
            </button>
          </form>
          
          <div style="margin-top: 24px; text-align: center; border-top: 1px solid var(--gray-light); padding-top: 16px;">
            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm" style="display:inline-flex; align-items:center; gap:8px;">
              <i class="fas fa-arrow-left"></i> Back to Login
            </a>
          </div>
        </div>

      </div><!-- /.auth-right -->
    </div><!-- /.auth-container -->
  </div><!-- /.auth-page -->

  <script src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>
