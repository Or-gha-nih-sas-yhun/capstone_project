<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Request for Summon Issuance</title>
  <style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f7; }
    .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
    .header { background: #1e3a8a; color: #ffffff; padding: 24px; text-align: center; }
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
      <h2>Barangay Pili Case Filing System</h2>
    </div>
    <div class="content">
      <p>Dear Admin,</p>
      
      <p>A new compliant request regarding a dispute or incident has been submitted in the system.</p>
      
      <p><strong>Details of the complaint:</strong></p>
      <div class="details-box">
        <div class="details-row">
          <div class="details-label">Case Number:</div>
          <div class="details-value"><strong>{{ $summon->case_number }}</strong></div>
        </div>
        <div class="details-row">
          <div class="details-label">Case Type:</div>
          <div class="details-value">{{ ucfirst($summon->case_type) }}</div>
        </div>
        <div class="details-row">
          <div class="details-label">Complainant:</div>
          <div class="details-value">{{ $summon->complainant_name }}</div>
        </div>
        <div class="details-row">
          <div class="details-label">Respondent:</div>
          <div class="details-value">{{ $summon->respondent_name }}</div>
        </div>
        <div class="details-row">
          <div class="details-label">Incident Date:</div>
          <div class="details-value">{{ $summon->incident_date ? \Carbon\Carbon::parse($summon->incident_date)->format('F d, Y h:i A') : 'N/A' }}</div>
        </div>
        <div class="details-row">
          <div class="details-label">Description:</div>
          <div class="details-value">{{ $summon->complain_details }}</div>
        </div>
      </div>

      <p>You can review this request, assign official conciliation hearings, and generate official Katarungang Pambarangay (KP) forms from the Admin Summons Portal.</p>
      
      <p>Thank you,</p>
      <p><em>Barangay Administrative Portal Automation</em></p>
    </div>
    <div class="footer">
      This is an automated administrative notification. Please do not reply directly to this email.
    </div>
  </div>
</body>
</html>
