<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Official Summon Notice</title>
  <style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f7; }
    .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
    .header { background: #dc2626; color: #ffffff; padding: 24px; text-align: center; }
    .header h2 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
    .content { padding: 30px; }
    .details-box { background: #fff5f5; border-radius: 6px; padding: 16px; border: 1px solid #fecaca; margin: 20px 0; }
    .details-row { display: flex; margin-bottom: 8px; border-bottom: 1px solid #fee2e2; padding-bottom: 8px; }
    .details-row:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
    .details-label { width: 150px; font-weight: bold; color: #991b1b; }
    .details-value { flex: 1; color: #7f1d1d; }
    .warning-text { color: #b91c1c; font-weight: bold; margin-top: 20px; border-left: 4px solid #b91c1c; padding-left: 12px; }
    .footer { text-align: center; padding: 20px; font-size: 12px; color: #64748b; background: #f8fafc; border-top: 1px solid #e5e7eb; }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="header">
      <h2>OFFICIAL SUMMON NOTICE</h2>
    </div>
    <div class="content">
      <p>Dear {{ $summon->respondent_name }},</p>
      
      <p>You are hereby summoned in relation to the official complaint filed by <strong>{{ $summon->complainant_name }}</strong> concerning: <em>{{ $summon->nature_of_complaint ?? 'Dispute / Incident Report' }}</em>.</p>
      
      <p><strong>Details of the summon for your appearance:</strong></p>
      <div class="details-box">
        <div class="details-row">
          <div class="details-label">Case Number:</div>
          <div class="details-value"><strong>{{ $summon->case_number }}</strong></div>
        </div>
        <div class="details-row">
          <div class="details-label">Date of Appearance:</div>
          <div class="details-value"><strong>{{ $summon->schedule_date ? \Carbon\Carbon::parse($summon->schedule_date)->format('F d, Y') : 'N/A' }}</strong></div>
        </div>
        <div class="details-row">
          <div class="details-label">Time of Appearance:</div>
          <div class="details-value"><strong>{{ $summon->schedule_date ? \Carbon\Carbon::parse($summon->schedule_date)->format('h:i A') : 'N/A' }}</strong></div>
        </div>
        <div class="details-row">
          <div class="details-label">Venue:</div>
          <div class="details-value">Barangay Pili Hall, Session Room</div>
        </div>
        <div class="details-row">
          <div class="details-label">Purpose:</div>
          <div class="details-value">Formal Conciliation / Amicable Mediation Hearing</div>
        </div>
      </div>

      <p class="warning-text">
        WARNING: Failure to comply and appear personally on this scheduled date without valid legal justification may result in the immediate forfeiture of your side, and the complainant will be issued a "Certificate to File Action" enabling them to file a formal lawsuit against you in court.
      </p>
      
      <p>Sincerely,</p>
      <p><strong>Office of the Punong Barangay / Lupon Chairman</strong><br>Barangay Pili, Minalabac</p>
    </div>
    <div class="footer">
      This is an official administrative notice of legal consequence. Please do not reply directly to this email.
    </div>
  </div>
</body>
</html>
