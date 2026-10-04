<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Acknowledgment of Summon Request</title>
  <style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f7; }
    .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
    .header { background: #10b981; color: #ffffff; padding: 24px; text-align: center; }
    .header h2 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
    .content { padding: 30px; }
    .details-box { background: #f8fafc; border-radius: 6px; padding: 16px; border: 1px solid #e2e8f0; margin: 20px 0; }
    .details-row { display: flex; margin-bottom: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; }
    .details-row:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
    .details-label { width: 150px; font-weight: bold; color: #475569; }
    .details-value { flex: 1; color: #1e293b; }
    .footer { text-align: center; padding: 20px; font-size: 12px; color: #64748b; background: #f8fafc; border-top: 1px solid #e5e7eb; }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="header">
      <h2>Barangay Pili Mediation Services</h2>
    </div>
    <div class="content">
      <p>Dear {{ $summon->complainant_name }},</p>
      
      <p>Your request has been successfully reviewed and recorded in our barangay systems.</p>
      
      <p>A summon case has been prepared and will be sent to the respondent regarding the matter you raised.</p>
      
      <p><strong>Registered Case Details:</strong></p>
      <div class="details-box">
        <div class="details-row">
          <div class="details-label">Case Number:</div>
          <div class="details-value"><strong>{{ $summon->case_number }}</strong></div>
        </div>
        <div class="details-row">
          <div class="details-label">Respondent Name:</div>
          <div class="details-value">{{ $summon->respondent_name }}</div>
        </div>
        @if($summon->case_type === 'summon' && $summon->schedule_date)
          <div class="details-row">
            <div class="details-label">Mediation Schedule:</div>
            <div class="details-value">
              <strong>{{ \Carbon\Carbon::parse($summon->schedule_date)->format('F d, Y') }}</strong> at 
              <strong>{{ \Carbon\Carbon::parse($summon->schedule_date)->format('h:i A') }}</strong>
            </div>
          </div>
        @endif
        <div class="details-row">
          <div class="details-label">Nature of Case:</div>
          <div class="details-value">{{ $summon->nature_of_complaint ?? 'Dispute Resolution' }}</div>
        </div>
      </div>

      <p>You can track the ongoing status of your case and view any updated remarks by logging into your account on the Barangay Pili Resident Portal.</p>
      
      <p>Sincerely,</p>
      <p><strong>Office of the Punong Barangay</strong><br>Barangay Pili, Minalabac</p>
    </div>
    <div class="footer">
      This is an official automated notification. Please do not reply directly to this email.
    </div>
  </div>
</body>
</html>
