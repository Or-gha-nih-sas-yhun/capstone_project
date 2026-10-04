<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Security Verification (2FA) &mdash; Barangay Pili</title>
  <meta name="description" content="Email OTP Two-Factor Authentication for Barangay Pili Admin Portal">
  @php
    $assetBase = (str_contains(request()->getHost(), 'admin.') && !str_contains(request()->getHost(), 'localhost')) 
      ? 'https://brgypilieclearance.com' 
      : '';
  @endphp
  <link rel="icon" type="image/png" href="{{ $assetBase ? $assetBase . '/assets/images/pili_logo.png' : asset('assets/images/pili_logo.png') }}">
  <link rel="shortcut icon" href="{{ $assetBase ? $assetBase . '/favicon.ico' : asset('favicon.ico') }}">
  <link rel="stylesheet" href="{{ $assetBase ? $assetBase . '/assets/css/style.css' : asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .otp-digit {
      width: 48px;
      height: 52px;
      text-align: center;
      font-size: 22px;
      font-weight: 700;
      font-family: ui-monospace, monospace;
      color: #1e293b;
      background: #f8fafc;
      border: 1.5px solid #cbd5e1;
      border-radius: 10px;
      outline: none;
      transition: all 0.2s;
    }
    .otp-digit:focus {
      border-color: #b91c1c;
      box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.15);
      background: #ffffff;
    }
    .otp-digit.filled {
      border-color: #b91c1c;
      background: #fef2f2;
    }
    .timer-badge {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #b91c1c;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 12px;
      font-family: monospace;
      font-size: 13px;
    }
  </style>
</head>
<body>
  <div class="auth-page">
    <div class="auth-container">

      <!-- Left panel -->
      <div class="auth-left">
        <div class="brgy-seal" style="text-align: left; margin-bottom: 24px;">
          <img src="{{ $assetBase ? $assetBase . '/assets/images/pili_logo.png' : asset('assets/images/pili_logo.png') }}" alt="Barangay Logo"
            style="width: 120px; height: auto; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));">
        </div>

        <h1>Barangay Pili<br>Two-Factor Security</h1>
        <p>Protecting official administrative systems with multi-factor authentication.</p>

        <div class="feature-list">
          <div class="feature-item"><i class="fas fa-shield-check"></i> High Security Clearance Protection</div>
          <div class="feature-item"><i class="fas fa-envelope-circle-check"></i> One-Time Security Code Verification</div>
          <div class="feature-item"><i class="fas fa-clock"></i> 10-Minute Expiry Limit</div>
          <div class="feature-item"><i class="fas fa-user-lock"></i> Session Encryption</div>
        </div>

        <div style="margin-top:32px;padding-top:20px;border-top:1px solid rgba(255,255,255,.2);font-size:12px;opacity:.8;">
          <i class="fas fa-shield-halved"></i> admin.brgypilieclearance.com &bull; Two-Factor Authentication
        </div>
      </div>

      <!-- Right panel -->
      <div class="auth-right">
        <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px;">
          <i class="fas fa-shield-halved"></i> Step 2 of 2
        </div>
        <h2>Verify Your Identity</h2>
        <p class="subtitle" style="margin-bottom: 16px;">
          A 6-digit security code was dispatched to:<br>
          <strong style="color: #0f172a;">{{ $maskedEmail }}</strong>
        </p>

        @if (session('error'))
          <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif
        @if (session('success'))
          <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.otp.verify') }}" id="otp-form">
          @csrf
          <input type="hidden" name="code" id="consolidated-code" required>

          <div style="display: flex; justify-content: center; gap: 8px; margin: 20px 0;">
            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code" autofocus>
            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]">
          </div>

          <div style="display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; color: #64748b; margin-bottom: 20px;">
            <i class="fas fa-stopwatch" style="color: #d97706;"></i>
            <span>Code expires in:</span>
            <span class="timer-badge" id="otp-countdown">10:00</span>
          </div>

          <button type="submit" class="btn btn-primary w-100" id="btn-verify">
            <i class="fas fa-check-circle"></i> Authorize &amp; Access Dashboard
          </button>
        </form>

        <div style="margin-top: 24px; text-align: center; display: flex; flex-direction: column; gap: 10px; align-items: center;">
          <form method="POST" action="{{ route('admin.otp.resend') }}" id="resend-form">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm" id="btn-resend" style="min-width: 180px;">
              <i class="fas fa-rotate-right"></i> <span id="resend-text">Resend Code</span>
            </button>
          </form>

          <a href="{{ route('admin.otp.cancel') }}" style="font-size: 13px; color: #64748b; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Cancel and return to login
          </a>
        </div>

      </div><!-- /.auth-right -->

    </div><!-- /.auth-container -->
  </div><!-- /.auth-page -->

  <script>
    // Segmented input auto-advance
    const digits = document.querySelectorAll('.otp-digit');
    const consolidatedInput = document.getElementById('consolidated-code');
    const form = document.getElementById('otp-form');

    function updateConsolidatedCode() {
      let code = '';
      digits.forEach(d => code += d.value.trim());
      consolidatedInput.value = code;
      return code;
    }

    digits.forEach((digit, index) => {
      digit.addEventListener('input', (e) => {
        const val = e.target.value.replace(/[^0-9]/g, '');
        e.target.value = val ? val.slice(-1) : '';

        if (e.target.value) {
          e.target.classList.add('filled');
          if (index < digits.length - 1) {
            digits[index + 1].focus();
          }
        } else {
          e.target.classList.remove('filled');
        }

        const code = updateConsolidatedCode();
        if (code.length === 6) {
          form.submit();
        }
      });

      digit.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !digit.value && index > 0) {
          digits[index - 1].focus();
          digits[index - 1].value = '';
          digits[index - 1].classList.remove('filled');
          updateConsolidatedCode();
        }
      });

      digit.addEventListener('paste', (e) => {
        e.preventDefault();
        const pastedData = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
        if (!pastedData) return;

        pastedData.split('').slice(0, 6).forEach((char, i) => {
          if (digits[i]) {
            digits[i].value = char;
            digits[i].classList.add('filled');
          }
        });

        const lastIndex = Math.min(pastedData.length, digits.length) - 1;
        if (lastIndex >= 0 && lastIndex < digits.length) {
          digits[lastIndex].focus();
        }

        const code = updateConsolidatedCode();
        if (code.length === 6) {
          form.submit();
        }
      });
    });

    // Countdown logic
    const expiresTimestamp = {{ $expiresTimestamp ?? (time() + 600) }} * 1000;
    const countdownEl = document.getElementById('otp-countdown');

    function updateExpiryCountdown() {
      const now = new Date().getTime();
      const diff = expiresTimestamp - now;

      if (diff <= 0) {
        countdownEl.innerText = 'EXPIRED';
        countdownEl.style.backgroundColor = '#fecaca';
        return;
      }

      const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((diff % (1000 * 60)) / 1000);
      countdownEl.innerText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    updateExpiryCountdown();
    setInterval(updateExpiryCountdown, 1000);

    // Resend cooldown
    const resendCooldownEnd = {{ $resendCooldownEnd ?? (time() + 60) }} * 1000;
    const btnResend = document.getElementById('btn-resend');
    const resendText = document.getElementById('resend-text');

    function updateResendCooldown() {
      const now = new Date().getTime();
      const diff = Math.ceil((resendCooldownEnd - now) / 1000);

      if (diff > 0) {
        btnResend.disabled = true;
        resendText.innerText = `Resend Code in ${diff}s`;
      } else {
        btnResend.disabled = false;
        resendText.innerText = 'Resend Code';
      }
    }
    updateResendCooldown();
    const resendInterval = setInterval(() => {
      updateResendCooldown();
      if (new Date().getTime() >= resendCooldownEnd) {
        clearInterval(resendInterval);
      }
    }, 1000);
  </script>
</body>
</html>
