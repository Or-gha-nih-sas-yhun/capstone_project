<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>KP Form - {{ strtoupper($formType) }}</title>
  <style>
    @media print {
      body { margin: 0; padding: 20px; font-family: 'Georgia', 'Times New Roman', serif; font-size: 14px; line-height: 1.6; color: #000; }
      .no-print { display: none; }
    }
    body { font-family: 'Georgia', 'Times New Roman', serif; font-size: 14px; line-height: 1.6; padding: 40px; max-width: 800px; margin: 0 auto; background: #fff; color: #333; }
    .header { text-align: center; margin-bottom: 30px; font-family: 'Arial', sans-serif; }
    .header h5 { margin: 2px 0; font-size: 12px; font-weight: normal; text-transform: uppercase; letter-spacing: 1px; }
    .header h4 { margin: 4px 0; font-size: 14px; text-transform: uppercase; }
    .header h3 { margin: 6px 0; font-size: 16px; font-weight: bold; color: #1a365d; }
    .divider { border-bottom: 2px double #000; margin: 15px 0; }
    .form-title { text-align: center; margin: 25px 0; }
    .form-title h2 { margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 1px; text-decoration: underline; }
    .form-number { text-align: right; font-weight: bold; font-family: sans-serif; font-size: 12px; }
    .case-box { display: grid; grid-template-columns: 1fr 100px 1fr; margin-bottom: 30px; border: 1px solid #000; padding: 15px; background: #fafafa; }
    .party { padding: 10px; }
    .versus { display: flex; align-items: center; justify-content: center; font-style: italic; font-weight: bold; font-size: 16px; }
    .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    .details-table th, .details-table td { border: 1px solid #000; padding: 10px; text-align: left; vertical-align: top; }
    .details-table th { background-color: #f2f2f2; font-weight: bold; width: 250px; }
    .paragraph-content { margin: 20px 0; text-align: justify; text-indent: 40px; }
    .signature-area { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; margin-top: 60px; }
    .sig-block { text-align: center; }
    .sig-line { border-top: 1px solid #000; width: 80%; margin: 40px auto 5px auto; }
    .sig-title { font-size: 11px; text-transform: uppercase; color: #555; }
    .sig-name { font-weight: bold; text-transform: uppercase; }
    .print-banner { background: #f3f4f6; padding: 12px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e5e7eb; }
    .btn { background: #1a365d; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-family: sans-serif; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .btn-secondary { background: #6b7280; }
    .btn:hover { opacity: 0.9; }
  </style>
</head>
<body>

  <!-- Controls (Hidden on Print) -->
  <div class="print-banner no-print">
    <div>
      <span style="font-family: sans-serif; font-size: 13px;">Document: <strong>KP Form {{ substr($formType, 2) }} - {{ $summon->case_number }}</strong></span>
    </div>
    <div style="display:flex; gap:8px;">
      <button onclick="window.print()" class="btn">Print Document</button>
      <a href="{{ route('admin.summons') }}" class="btn btn-secondary">Back to List</a>
    </div>
  </div>

  <!-- Document Form Number -->
  <div class="form-number">
    @if($formType === 'kp7')
      KP Form No. 7
    @elseif($formType === 'kp8')
      KP Form No. 8
    @elseif($formType === 'kp9')
      KP Form No. 9
    @elseif($formType === 'kp20')
      KP Form No. 20
    @endif
  </div>

  <!-- Official Header -->
  <div class="header">
    <h5>Republic of the Philippines</h5>
    <h5>Province of Camarines Sur</h5>
    <h5>Municipality of Minalabac</h5>
    <h3>Barangay Pili</h3>
    <div class="divider"></div>
    <h4>Office of the Lupon Tagapamayapa / Punong Barangay</h4>
  </div>

  <!-- Case Presentation Block -->
  <div class="case-box">
    <div class="party">
      <strong>COMPLAINANT/S:</strong><br>
      <span class="sig-name">{{ $summon->complainant_name }}</span><br>
      @if($summon->complainantResident)
        <small style="color:#666;">Purok {{ $summon->complainantResident->purok ?? '—' }}, Barangay Pili</small><br>
      @endif
      <small>Contact: {{ $summon->complainant_contact ?? 'N/A' }}</small>
    </div>
    <div class="versus">
      -vs-
    </div>
    <div class="party">
      <strong>RESPONDENT/S:</strong><br>
      <span class="sig-name">{{ $summon->respondent_name }}</span><br>
      @if($summon->respondentResident)
        <small style="color:#666;">Purok {{ $summon->respondentResident->purok ?? '—' }}, Barangay Pili</small><br>
      @endif
      <small>Contact: {{ $summon->respondent_contact ?? 'N/A' }}</small>
    </div>
  </div>

  <div style="text-align: right; margin-bottom: 20px;">
    <strong>Barangay Case No:</strong> <u>{{ $summon->case_number }}</u><br>
    <strong>For:</strong> <u>{{ $summon->nature_of_complaint ?? 'Dispute / Incident Report' }}</u>
  </div>

  <!-- Title based on Form Type -->
  <div class="form-title">
    @if($formType === 'kp7')
      <h2>COMPLAINT FORM</h2>
    @elseif($formType === 'kp8')
      <h2>NOTICE OF HEARING</h2>
      <p style="margin-top: 5px;">(TO COMPLAINANT)</p>
    @elseif($formType === 'kp9')
      <h2>SUMMON TO RESPONDENT</h2>
    @elseif($formType === 'kp20')
      <h2>CERTIFICATE TO FILE ACTION</h2>
    @endif
  </div>

  <!-- Content based on Form Type -->
  @if($formType === 'kp7')
    <p class="paragraph-content">
      I/We hereby complain against the above-named respondent/s for violating my/our rights and causing injury in the following manner and details:
    </p>
    <div style="padding: 15px; border: 1px dashed #000; background: #fafafa; margin: 20px 0; font-family: monospace; white-space: pre-wrap;">{{ $summon->complain_details }}</div>
    <table class="details-table">
      <tr>
        <th>Date & Time of Incident</th>
        <td>{{ $summon->incident_date ? \Carbon\Carbon::parse($summon->incident_date)->format('F d, Y h:i A') : 'N/A' }}</td>
      </tr>
      <tr>
        <th>Location of Incident</th>
        <td>{{ $summon->incident_location ?? 'Barangay Pili' }}</td>
      </tr>
    </table>
    <p class="paragraph-content">
      THEREFORE, I/We pray that the respondent/s be summoned and that after due hearing/mediation, a just and lawful resolution of this dispute be implemented.
    </p>

  @elseif($formType === 'kp8')
    <p class="paragraph-content">
      TO THE COMPLAINANT/S: You are hereby notified and requested to appear before this Office on the scheduled date and time below for the mediation/conciliation of your filed complaint against the above-named respondent/s.
    </p>
    <table class="details-table">
      <tr>
        <th>Scheduled Hearing Date</th>
        <td>{{ $summon->schedule_date ? \Carbon\Carbon::parse($summon->schedule_date)->format('F d, Y') : 'N/A' }}</td>
      </tr>
      <tr>
        <th>Scheduled Hearing Time</th>
        <td>{{ $summon->schedule_date ? \Carbon\Carbon::parse($summon->schedule_date)->format('h:i A') : 'N/A' }}</td>
      </tr>
      <tr>
        <th>Venue of Hearing</th>
        <td>Barangay Pili Hall, Session Room</td>
      </tr>
    </table>
    <p class="paragraph-content" style="font-weight: bold; color: #dc2626;">
      WARNING: Failure to appear on the scheduled hearing without valid justification may result in the dismissal of your complaint and/or appropriate legal penalties.
    </p>

  @elseif($formType === 'kp9')
    <p class="paragraph-content">
      TO THE RESPONDENT/S: You are hereby summoned and strictly required to appear personally before this Office on the specified schedule below to answer the formal complaint filed against you by the above-named complainant/s.
    </p>
    <div style="padding: 15px; border: 1px dashed #000; background: #fafafa; margin: 20px 0;">
      <strong>Brief of Complaint:</strong><br>
      <div style="font-family: monospace; white-space: pre-wrap; margin-top: 5px;">{{ $summon->complain_details }}</div>
    </div>
    <table class="details-table">
      <tr>
        <th>Scheduled Hearing Date</th>
        <td>{{ $summon->schedule_date ? \Carbon\Carbon::parse($summon->schedule_date)->format('F d, Y') : 'N/A' }}</td>
      </tr>
      <tr>
        <th>Scheduled Hearing Time</th>
        <td>{{ $summon->schedule_date ? \Carbon\Carbon::parse($summon->schedule_date)->format('h:i A') : 'N/A' }}</td>
      </tr>
      <tr>
        <th>Venue of Hearing</th>
        <td>Barangay Pili Hall, Session Room</td>
      </tr>
    </table>
    <p class="paragraph-content" style="font-weight: bold; color: #dc2626;">
      WARNING: Your failure to appear in person without valid cause may result in the forfeiture of your right to present your side, and the complainant/s may be issued a clearance to file action against you in court.
    </p>

  @elseif($formType === 'kp20')
    <p class="paragraph-content">
      This is to certify that:
    </p>
    <ol style="margin-left: 20px; line-height: 1.8;">
      <li>There has been a personal confrontation between the complainant/s and respondent/s before the Punong Barangay / Lupon Tagapamayapa;</li>
      <li>A standard amicable settlement/conciliation was conscientiously tried and explored, but the parties failed to arrive at any agreement; and</li>
      <li>Therefore, the corresponding complaint may now be formally filed in the appropriate Government Office or Court of Law.</li>
    </ol>
    <div style="padding: 15px; border: 1px dashed #000; background: #fafafa; margin: 20px 0;">
      <strong>Mediation History Notes:</strong><br>
      @if($summon->hearings->isEmpty())
        <span style="font-style:italic;">No hearings recorded on system logs.</span>
      @else
        <ul style="margin: 5px 0 0 20px; padding: 0;">
          @foreach($summon->hearings as $h)
            <li>Hearing #{{ $h->hearing_number }} ({{ $h->schedule_date->format('M d, Y') }}): {{ $h->remarks ?? 'No remarks recorded.' }}</li>
          @endforeach
        </ul>
      @endif
    </div>
    <p class="paragraph-content">
      Given and signed this <u>{{ date('jS') }}</u> day of <u>{{ date('F, Y') }}</u> at Barangay Pili, Minalabac, Camarines Sur.
    </p>
  @endif

  <!-- Signatures Block -->
  <div class="signature-area">
    <div class="sig-block">
      <div class="sig-line"></div>
      <div class="sig-name">{{ $summon->complainant_name }}</div>
      <div class="sig-title">Complainant / Affiant</div>
    </div>
    <div class="sig-block">
      <div class="sig-line"></div>
      <div class="sig-name">{{ $captainName ?? 'PUNONG BARANGAY' }}</div>
      <div class="sig-title">Punong Barangay / Lupon Chairman</div>
    </div>
  </div>

  @if($autoPrint)
  <script>
    window.onload = function() {
      window.print();
    }
  </script>
  @endif
</body>
</html>
