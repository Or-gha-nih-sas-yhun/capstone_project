<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Barangay Pili — Email Verification</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/images/pili_logo.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Custom style overrides for the progress bar */
    .steps-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 24px 0;
      padding: 0 10px;
    }
    .step-node {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      flex: 1;
    }
    .step-circle {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background-color: #e5e7eb;
      color: #9ca3af;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 14px;
      z-index: 2;
    }
    .step-node.completed .step-circle {
      background-color: #16a34a;
      color: white;
    }
    .step-node.active .step-circle {
      background-color: #1e3a8a;
      color: white;
      box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.2);
    }
    .step-label {
      font-size: 11px;
      color: #6b7280;
      margin-top: 6px;
      font-weight: 500;
      text-align: center;
    }
    .step-node.active .step-label {
      color: #1e3a8a;
      font-weight: 600;
    }
    .step-node.completed .step-label {
      color: #16a34a;
    }
    .step-line {
      position: absolute;
      top: 16px;
      left: 50%;
      width: 100%;
      height: 2px;
      background-color: #e5e7eb;
      z-index: 1;
    }
    .step-node.completed .step-line {
      background-color: #16a34a;
    }
    .step-node:last-child .step-line {
      display: none;
    }
    .code-input-group {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin: 24px 0;
    }
    .code-input-group input {
      width: 48px;
      height: 56px;
      font-size: 24px;
      font-weight: 700;
      text-align: center;
      border: 2px solid #d1d5db;
      border-radius: 8px;
      outline: none;
      transition: all 0.2s;
    }
    .code-input-group input:focus {
      border-color: #1e3a8a;
      box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.15);
    }
  </style>
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
        <h2>Verify Your Email</h2>
        <p class="subtitle">Complete the final step of your registration process.</p>

        <!-- Dynamic Steps Progress Bar -->
        <div class="steps-container">
          <div class="step-node completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Step 1<br>Information</div>
            <div class="step-line"></div>
          </div>
          <div class="step-node completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Step 2<br>Password</div>
            <div class="step-line"></div>
          </div>
          <div class="step-node active">
            <div class="step-circle">3</div>
            <div class="step-label">Step 3<br>Verification</div>
            <div class="step-line"></div>
          </div>
        </div>

        @if (session('error'))
          <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif
        @if (session('success'))
          <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        <div style="background-color: #f8fafc; border-radius: 8px; padding: 16px; margin-bottom: 24px; font-size: 14px; border: 1px solid #e2e8f0; color: #475569;">
          <p style="margin: 0 0 8px 0; font-weight: 600;"><i class="fas fa-paper-plane" style="color: #1e3a8a; margin-right: 6px;"></i> Code Sent</p>
          We sent a 6-digit verification code to: <br>
          <strong style="color: #0f172a; word-break: break-all;">{{ $email ?? 'your email' }}</strong>
        </div>

        <form method="POST" action="{{ route('verification.verify') }}" id="verify-form">
          @csrf
          <input type="hidden" name="email" value="{{ $email }}">
          
          <div class="form-group">
            <label class="form-label" style="text-align: center; display: block; margin-bottom: 8px;">Enter 6-Digit Code</label>
            <div class="code-input-group">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="otp-field">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="otp-field">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="otp-field">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="otp-field">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="otp-field">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="otp-field">
            </div>
            <input type="hidden" name="code" id="verification_code_val" required>
          </div>

          <button type="submit" class="btn btn-primary w-100" style="margin-top: 8px;">
            <i class="fas fa-shield-halved"></i> Verify Email Address
          </button>
        </form>

        <div style="margin-top: 24px; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px;">
          <form method="POST" action="{{ route('verification.resend') }}" style="display: inline-block;">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <p style="font-size: 13px; color: #64748b; margin: 0 0 12px 0;">Didn't receive the email code?</p>
            <button type="submit" class="btn btn-outline-secondary btn-sm" id="resend-btn" style="display: inline-flex; align-items: center; gap: 8px;">
              <i class="fas fa-rotate"></i> Resend Code
            </button>
          </form>
          <div style="margin-top: 16px;">
            <a href="{{ route('login') }}" style="font-size: 13px; color: #1e3a8a; font-weight: 500; text-decoration: none;">
              <i class="fas fa-arrow-left" style="font-size: 11px; margin-right: 4px;"></i> Back to Login Page
            </a>
          </div>
        </div>

      </div><!-- /.auth-right -->
    </div><!-- /.auth-container -->
  </div><!-- /.auth-page -->

  <script>
    // Autofocus next input & join input code into hidden input
    const inputs = document.querySelectorAll('.otp-field');
    const hiddenInput = document.getElementById('verification_code_val');
    const form = document.getElementById('verify-form');

    inputs.forEach((input, index) => {
      input.addEventListener('input', (e) => {
        // Only allow numbers
        input.value = input.value.replace(/[^0-9]/g, '');
        
        if (input.value && index < inputs.length - 1) {
          inputs[index + 1].focus();
        }
        updateHiddenValue();
      });

      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !input.value && index > 0) {
          inputs[index - 1].focus();
        }
      });
    });

    function updateHiddenValue() {
      let code = '';
      inputs.forEach(input => {
        code += input.value;
      });
      hiddenInput.value = code;
    }

    form.addEventListener('submit', (e) => {
      updateHiddenValue();
      if (hiddenInput.value.length !== 6) {
        e.preventDefault();
        alert('Please enter all 6 digits of the code.');
      }
    });

    // Handle paste events
    inputs[0].addEventListener('paste', (e) => {
      const data = e.clipboardData.getData('text').trim();
      if (data.length === 6 && /^\d+$/.test(data)) {
        inputs.forEach((input, index) => {
          input.value = data[index];
        });
        updateHiddenValue();
        inputs[5].focus();
        e.preventDefault();
      }
    });
  </script>
</body>
</html>
