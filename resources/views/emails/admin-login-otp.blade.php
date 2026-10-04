<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Login Verification Code</title>
  <style>
    body {
      font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
      background-color: #0f172a;
      color: #334155;
      margin: 0;
      padding: 0;
      -webkit-text-size-adjust: none;
      -ms-text-size-adjust: none;
    }
    .email-wrapper {
      width: 100%;
      background-color: #0f172a;
      padding: 40px 16px;
      box-sizing: border-box;
    }
    .email-card {
      max-width: 580px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
    }
    .email-header {
      background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
      padding: 32px 24px;
      text-align: center;
      border-bottom: 3px solid #d97706;
    }
    .email-header h1 {
      color: #ffffff;
      font-size: 22px;
      font-weight: 800;
      margin: 12px 0 0;
      letter-spacing: 0.5px;
    }
    .badge {
      display: inline-block;
      padding: 4px 12px;
      background-color: rgba(217, 119, 6, 0.2);
      border: 1px solid #d97706;
      color: #fbbf24;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      border-radius: 20px;
    }
    .email-body {
      padding: 36px 32px;
      line-height: 1.6;
    }
    .email-body h2 {
      font-size: 19px;
      font-weight: 700;
      margin: 0 0 12px;
      color: #0f172a;
    }
    .email-body p {
      font-size: 15px;
      color: #475569;
      margin: 0 0 16px;
    }
    .otp-container {
      text-align: center;
      margin: 28px 0;
      padding: 24px 20px;
      background: #f8fafc;
      border: 2px dashed #cbd5e1;
      border-radius: 14px;
    }
    .otp-label {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #64748b;
      margin-bottom: 8px;
    }
    .otp-code {
      font-family: 'Courier New', Courier, monospace;
      font-size: 38px;
      font-weight: 900;
      letter-spacing: 10px;
      color: #1e3a8a;
      margin: 0;
    }
    .otp-expiry {
      margin-top: 10px;
      font-size: 13px;
      color: #dc2626;
      font-weight: 600;
    }
    .security-notice {
      background-color: #fef2f2;
      border-left: 4px solid #ef4444;
      padding: 14px 16px;
      border-radius: 6px;
      margin-top: 24px;
    }
    .security-notice p {
      font-size: 13px;
      color: #991b1b;
      margin: 0;
    }
    .meta-table {
      width: 100%;
      margin-top: 20px;
      font-size: 13px;
      border-collapse: collapse;
      color: #64748b;
    }
    .meta-table td {
      padding: 6px 0;
    }
    .meta-table td:first-child {
      font-weight: 600;
      width: 120px;
    }
    .email-footer {
      background-color: #f8fafc;
      padding: 20px 32px;
      text-align: center;
      border-top: 1px solid #e2e8f0;
    }
    .email-footer p {
      font-size: 12px;
      color: #94a3b8;
      margin: 4px 0;
    }
  </style>
</head>
<body>
  <div class="email-wrapper">
    <div class="email-card">
      <div class="email-header">
        <span class="badge">Official Security Notice</span>
        <h1>Barangay Pili Administrative Portal</h1>
      </div>
      <div class="email-body">
        <h2>Two-Factor Authentication Code</h2>
        <p>Hello <strong>{{ $user->username }}</strong>,</p>
        <p>A sign-in request to the <strong>Barangay Pili Administrative Portal</strong> was initiated with your credentials. Please use the one-time security code below to complete your login:</p>
        
        <div class="otp-container">
          <div class="otp-label">Your One-Time Password (OTP)</div>
          <div class="otp-code">{{ $code }}</div>
          <div class="otp-expiry">&#9201; Expires in 10 minutes</div>
        </div>

        <div class="security-notice">
          <p><strong>Security Warning:</strong> Never share this code with anyone. Barangay Pili IT and administrators will never ask for your verification code. If you did not initiate this sign-in attempt, someone may have obtained your password &mdash; please change your password immediately.</p>
        </div>

        <table class="meta-table">
          <tr>
            <td>Portal:</td>
            <td>admin.brgypilieclearance.com</td>
          </tr>
          <tr>
            <td>IP Address:</td>
            <td>{{ request()->ip() }}</td>
          </tr>
          <tr>
            <td>Date &amp; Time:</td>
            <td>{{ now()->setTimezone('Asia/Manila')->format('M d, Y h:i:s A') }} PHT</td>
          </tr>
        </table>
      </div>
      <div class="email-footer">
        <p>&copy; {{ date('Y') }} Barangay Pili, Madridejos, Cebu. All rights reserved.</p>
        <p>Automated security notification &bull; Do not reply to this email.</p>
      </div>
    </div>
  </div>
</body>
</html>
