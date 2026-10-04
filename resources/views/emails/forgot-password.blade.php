<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reset Your Password</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background-color: #f3f4f6;
      color: #1f2937;
      margin: 0;
      padding: 0;
      -webkit-text-size-adjust: none;
      -ms-text-size-adjust: none;
    }
    .email-wrapper {
      width: 100%;
      background-color: #f3f4f6;
      padding: 40px 0;
    }
    .email-content {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    }
    .email-header {
      background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
      padding: 32px;
      text-align: center;
    }
    .email-header h1 {
      color: #ffffff;
      font-size: 24px;
      font-weight: 800;
      margin: 0;
      letter-spacing: -0.5px;
    }
    .email-body {
      padding: 40px 32px;
      line-height: 1.6;
    }
    .email-body h2 {
      font-size: 20px;
      font-weight: 700;
      margin-top: 0;
      margin-bottom: 16px;
      color: #111827;
    }
    .email-body p {
      font-size: 16px;
      color: #4b5563;
      margin-top: 0;
      margin-bottom: 24px;
    }
    .btn-container {
      text-align: center;
      margin: 32px 0;
    }
    .btn-primary {
      background-color: #b91c1c;
      color: #ffffff !important;
      text-decoration: none;
      padding: 14px 30px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 8px;
      display: inline-block;
      box-shadow: 0 4px 6px rgba(185, 28, 28, 0.2);
    }
    .btn-primary:hover {
      background-color: #991b1b;
    }
    .email-footer {
      background-color: #f9fafb;
      padding: 24px 32px;
      text-align: center;
      border-top: 1px solid #f3f4f6;
    }
    .email-footer p {
      font-size: 12px;
      color: #9ca3af;
      margin: 0;
    }
    .text-muted {
      font-size: 14px;
      color: #9ca3af;
      word-break: break-all;
    }
  </style>
</head>
<body>
  <div class="email-wrapper">
    <div class="email-content">
      <div class="email-header">
        <h1>Barangay Pili Portal</h1>
      </div>
      <div class="email-body">
        <h2>Hello,</h2>
        <p>You are receiving this email because we received a password reset request for your account on the Barangay Pili Clearance & Certificate System.</p>
        <div class="btn-container">
          <a href="{{ $resetUrl }}" class="btn-primary" target="_blank">Reset Password</a>
        </div>
        <p>This password reset link will expire in 60 minutes.</p>
        <p>If you did not request a password reset, no further action is required.</p>
        <hr style="border: 0; border-top: 1px solid #f3f4f6; margin: 32px 0;">
        <p class="text-muted">If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
        <a href="{{ $resetUrl }}" style="color: #b91c1c;">{{ $resetUrl }}</a></p>
      </div>
      <div class="email-footer">
        <p>Barangay Pili, Madridejos, Cebu &bull; Official Clearance & Certificate System</p>
      </div>
    </div>
  </div>
</body>
</html>
