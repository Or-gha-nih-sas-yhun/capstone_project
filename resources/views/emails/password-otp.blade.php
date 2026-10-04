<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Confirm Your Password Change</title>
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
      background: linear-gradient(135deg, #1e3a8a 0%, #d97706 100%);
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
    .code-container {
      text-align: center;
      margin: 32px 0;
      padding: 20px;
      background-color: #fffbeb;
      border: 2px dashed #d97706;
      border-radius: 12px;
    }
    .verification-code {
      font-size: 36px;
      font-weight: 800;
      letter-spacing: 6px;
      color: #b45309;
      margin: 0;
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
  </style>
</head>
<body>
  <div class="email-wrapper">
    <div class="email-content">
      <div class="email-header">
        <h1>Barangay Pili Portal</h1>
      </div>
      <div class="email-body">
        <h2>Confirm Your Password Change</h2>
        <p>A request was made to change your Barangay Pili Portal account password. If you initiated this change, please use the 6-digit confirmation code below to authorize and complete the process:</p>
        
        <div class="code-container">
          <div class="verification-code">{{ $code }}</div>
        </div>

        <p>This code is highly sensitive and should never be shared with anyone. If you did not request a password change, please log in to your account and review your security settings immediately.</p>
      </div>
      <div class="email-footer">
        <p>&copy; {{ date('Y') }} Barangay Pili. All rights reserved.</p>
      </div>
    </div>
  </div>
</body>
</html>
