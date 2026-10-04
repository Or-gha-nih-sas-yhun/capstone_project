@php
  $fullName = strtoupper($certReq->resident->full_name);
  $age = $certReq->resident->age;
  $civilStatus = $certReq->resident->civil_status;
  $address = $certReq->resident->address;
  $yearsOfResidency = $certReq->resident->years_of_residency;
  $purok = $certReq->resident->purok;
  $purpose = $certReq->purpose;
  $certName = $certReq->certificate->name;
  $certLower = strtolower($certName);

  // Generate body text depending on certificate type
  if (str_contains($certLower, 'clearance')) {
    $bodyText = "This is to certify that <strong>$fullName</strong>, {$age} years of age, {$civilStatus}, 
      a resident of {$address}, Barangay Pili, is known to be a person of <strong>GOOD MORAL CHARACTER</strong> 
      and has no derogatory record on file in this barangay. This clearance is issued upon the request of the above-named 
      person for the purpose of <strong>" . strtoupper($purpose) . "</strong>.";
  } elseif (str_contains($certLower, 'indigency')) {
    $bodyText = "This is to certify that <strong>$fullName</strong>, {$age} years of age, {$civilStatus}, 
      a resident of {$address}, Barangay Pili, belongs to the <strong>INDIGENT SECTOR</strong> of our community 
      and is financially incapable. This certification is issued upon request for the purpose of 
      <strong>" . strtoupper($purpose) . "</strong>.";
  } elseif (str_contains($certLower, 'residency')) {
    $bodyText = "This is to certify that <strong>$fullName</strong>, {$age} years of age, {$civilStatus}, 
      is a <strong>BONA FIDE RESIDENT</strong> of {$address}, Barangay Pili, for 
      {$yearsOfResidency} year(s). This certification is issued upon request for the purpose of 
      <strong>" . strtoupper($purpose) . "</strong>.";
  } elseif (str_contains($certLower, 'business')) {
    $bodyText = "This is to certify that <strong>$fullName</strong>, a resident of {$address}, Barangay Pili, 
      has been granted <strong>BARANGAY BUSINESS CLEARANCE</strong> and has no pending case or complaint in this barangay. 
      This clearance is issued for the purpose of <strong>" . strtoupper($purpose) . "</strong>.";
  } elseif (str_contains($certLower, 'jobseeker')) {
    $bodyText = "This is to certify that <strong>$fullName</strong>, {$age} years of age, a resident of 
      {$address}, Barangay Pili, is a <strong>FIRST TIME JOBSEEKER</strong> as defined under 
      Republic Act No. 11261. This certification is issued for the purpose of 
      <strong>" . strtoupper($purpose) . "</strong>.";
  } else {
    $bodyText = "This is to certify that <strong>$fullName</strong>, {$age} years of age, {$civilStatus}, 
      a resident of {$address}, Barangay Pili, is hereby issued this <strong>{$certName}</strong> 
      upon request for the purpose of <strong>" . strtoupper($purpose) . "</strong>.";
  }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>{{ $certName }} — {{ $fullName }}</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&family=Inter:wght@400;600;700;800&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: #f0f0f0;
    }

    .page {
      width: 215.9mm;
      min-height: 279.4mm;
      background: #fff;
      margin: 20px auto;
      padding: 15mm 15mm;
      position: relative;
      display: flex;
      gap: 12px;
    }

    .no-print {
      text-align: center;
      padding: 16px;
      background: #1a56db;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .no-print button {
      background: rgba(255, 255, 255, .2);
      border: 1px solid rgba(255, 255, 255, .4);
      color: #fff;
      padding: 8px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
    }

    .no-print a {
      color: #fff;
      font-size: 13px;
      opacity: .8;
    }

    /* Left Sidebar */
    .sidebar {
      width: 90px;
      background: #f5e6d3;
      border: 2px solid #8b7355;
      padding: 12px 8px;
      position: relative;
    }

    .sidebar h3 {
      font-size: 9px;
      font-weight: 700;
      text-transform: uppercase;
      text-align: center;
      margin-bottom: 10px;
      color: #5d4037;
      letter-spacing: 0.5px;
    }

    .officials-list {
      font-size: 7.5px;
      line-height: 1.3;
      color: #5d4037;
    }

    .official-group {
      margin-bottom: 8px;
      padding-bottom: 8px;
      border-bottom: 1px solid rgba(93, 64, 55, 0.3);
    }

    .official-group:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .official-group strong {
      display: block;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 2px;
    }

    .official-name {
      font-size: 7px;
      font-weight: 600;
    }

    /* Main Content */
    .content {
      flex: 1;
    }

    /* Header */
    .doc-header {
      text-align: center;
      border-bottom: 2px solid #333;
      padding-bottom: 10px;
      margin-bottom: 12px;
      position: relative;
    }

    .header-seals {
      display: flex;
      justify-content: center;
      gap: 60px;
      margin-bottom: 8px;
      align-items: center;
    }

    .header-seals img {
      width: 50px;
      height: 50px;
      object-fit: contain;
    }

    .doc-header .republic {
      font-size: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #444;
      margin: 0 0 1px 0;
    }

    .doc-header .province {
      font-size: 8.5px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #444;
      margin: 0;
    }

    .doc-header .municipality {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #444;
      margin: 0;
    }

    .doc-header .brgy-name {
      font-size: 18px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #333;
      margin: 2px 0;
    }

    /* Office title */
    .office-title {
      text-align: center;
      font-size: 13px;
      font-weight: 700;
      text-transform: capitalize;
      font-family: 'Lora', serif;
      color: #333;
      margin: 8px 0;
      border-bottom: 2px solid #333;
      padding-bottom: 6px;
    }

    /* Certificate Title */
    .cert-title {
      text-align: center;
      margin: 10px 0;
    }

    .cert-title h1 {
      font-size: 14px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #333;
    }

    /* Body */
    .doc-body {
      font-size: 10.5px;
      line-height: 1.6;
      text-align: justify;
      margin-bottom: 12px;
    }

    .doc-body p {
      margin-bottom: 6px;
    }

    /* Date and signatures */
    .cert-footer {
      margin-top: 14px;
      font-size: 10px;
    }

    .issued-date {
      margin-bottom: 10px;
      line-height: 1.4;
    }

    .issued-date strong {
      font-weight: 700;
    }

    .sig-section {
      display: flex;
      justify-content: center;
      gap: 40px;
      margin-top: 16px;
    }

    .sig-block {
      text-align: center;
      width: 100px;
    }

    .sig-line {
      border-top: 1px solid #333;
      height: 30px;
      margin-bottom: 2px;
    }

    .sig-name {
      font-size: 8.5px;
      font-weight: 700;
      text-transform: uppercase;
    }

    .sig-pos {
      font-size: 8px;
      color: #666;
    }

    /* Footer stamp */
    .doc-stamp {
      text-align: center;
      font-size: 8px;
      color: #888;
      margin-top: 10px;
      padding-top: 8px;
      border-top: 1px solid #ddd;
    }

    /* Watermark */
    .watermark {
      position: absolute;
      top: 50%;
      right: 5%;
      transform: translateY(-50%) rotate(-25deg);
      font-size: 60px;
      font-weight: 900;
      color: rgba(0, 0, 0, .03);
      white-space: nowrap;
      pointer-events: none;
      z-index: 0;
    }

    @media print {
      body {
        background: #fff;
      }

      .no-print {
        display: none !important;
      }

      .page {
        margin: 0;
        box-shadow: none;
      }
    }
  </style>
</head>

<body {!! $autoPrint ? 'onload="window.print()"' : '' !!}>

  <!-- Print toolbar (Admin/Staff only) -->
  <div class="no-print">
    <span><i class="fas fa-file-alt"></i> {{ $certName }} — {{ $fullName }}</span>
    <button onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    <button onclick="window.close()" style="background:#fee2e2; color:#b91c1c; margin-left: 10px;">Close</button>
  </div>

  <!-- Document -->
  <div class="page">
    <!-- Header -->
    <div class="doc-header">
      <div class="header-seals">
        <img src="{{ asset('assets/images/pili_logo.png') }}" alt="Barangay Pili Seal">
        <img src="{{ asset('assets/images/municipality_logo.png') }}" alt="Municipality Seal">
      </div>
      <div class="republic">Republic of the Philippines</div>
      <div class="province">Province of Cebu</div>
      <div class="municipality">Municipality of Madridejos</div>
      <div class="brgy-name">BARANGAY PILI</div>
      <div style="font-size: 8px; color: #1a56db; margin-top: 2px;">Email: brgy.pili.mad@gmail.com</div>
    </div>

    <!-- Office Title -->
    <div class="office-title">Office of the Barangay Captain</div>

    <!-- Left Sidebar with Officials -->
    <div class="sidebar">
      <h3>SANGGUNIANG<br>BARANGAY</h3>
      <div class="officials-list">
        @php
          $captainOfficial = $captain ?? ($officials->first(function($o) {
            $pos = strtolower($o->position ?? '');
            return str_contains($pos, 'captain') || str_contains($pos, 'punong') || str_contains($pos, 'chairman') || str_contains($pos, 'chairperson') || str_contains($pos, 'kapitan');
          }) ?? $officials->first());

          $kagawadsList = $kagawads ?? $officials->filter(function($o) {
            $pos = strtolower($o->position ?? '');
            return str_contains($pos, 'kagawad') || str_contains($pos, 'councilor') || str_contains($pos, 'konsehal') || str_contains($pos, 'member');
          });

          $skOfficial = $skChairman ?? $officials->first(function($o) {
            $pos = strtolower($o->position ?? '');
            return str_contains($pos, 'sk') || str_contains($pos, 'kabataan');
          });

          $secOfficial = $secretary ?? $officials->first(function($o) {
            $pos = strtolower($o->position ?? '');
            return str_contains($pos, 'sec') || str_contains($pos, 'kalihim');
          });

          $treasOfficial = $treasurer ?? $officials->first(function($o) {
            $pos = strtolower($o->position ?? '');
            return str_contains($pos, 'treas') || str_contains($pos, 'ingat-yaman');
          });

          $otherOfficials = $officials->reject(function($o) use ($captainOfficial, $secOfficial, $treasOfficial, $skOfficial, $kagawadsList) {
            return ($captainOfficial && $o->id === $captainOfficial->id)
                || ($secOfficial && $o->id === $secOfficial->id)
                || ($treasOfficial && $o->id === $treasOfficial->id)
                || ($skOfficial && $o->id === $skOfficial->id)
                || $kagawadsList->contains('id', $o->id);
          });
        @endphp

        <div class="official-group">
          <strong>{{ $captainOfficial ? $captainOfficial->name : ($captainName ?? 'PUNONG BARANGAY') }}</strong>
          <div class="official-name">Punong barangay</div>
        </div>

        @if($kagawadsList->isNotEmpty() || $otherOfficials->isNotEmpty())
        <div class="official-group">
          <strong>KAGAWAD:</strong>
          @foreach($kagawadsList->isNotEmpty() ? $kagawadsList : $otherOfficials as $k)
            <div class="official-name">{{ $k->name }}</div>
          @endforeach
        </div>
        @endif

        @if($skOfficial)
        <div class="official-group">
          <strong>SK CHAIRMAN:</strong>
          <div class="official-name">{{ $skOfficial->name }}</div>
        </div>
        @endif

        @if($secOfficial)
        <div class="official-group">
          <strong>SECRETARY:</strong>
          <div class="official-name">{{ $secOfficial->name }}</div>
        </div>
        @endif

        @if($treasOfficial)
        <div class="official-group">
          <strong>TREASURER:</strong>
          <div class="official-name">{{ $treasOfficial->name }}</div>
        </div>
        @endif
      </div>
    </div>

    <!-- Main Content -->
    <div class="content">
      <div class="watermark">BARANGAY PILI</div>

      <!-- Certificate Title -->
      <div class="cert-title">
        <h1>BARANGAY CERTIFICATION</h1>
        <div style="font-size: 9px; margin-top: 2px; font-style: italic;">
          @if(str_contains($certLower, 'jobseeker'))
            (First Time Jobseekers Assistance Act- RA 11261)
          @elseif(str_contains($certLower, 'indigency'))
            (Certificate of Indigency)
          @else
            ({{ $certName }})
          @endif
        </div>
      </div>

      <!-- Body -->
      <div class="doc-body">
        <p><strong>To whom it may concern:</strong></p>

        @if(str_contains($certLower, 'jobseeker'))
          <p>This is to certify that <strong>{{ $fullName }}</strong>, {{ $age }} years old, a resident of Purok <strong>{{ $purok ?? '' }}</strong>, Barangay Pili, Madridejos, Cebu, is a qualified first time jobseeker under <strong>Republic Act No. 11261</strong>.</p>
          <p>The holder was informed of the rights, duties, and responsibilities under the First Time Jobseekers Assistance Act and has executed the required undertaking before this barangay office.</p>
          <p>This certification is issued upon request for <strong>{{ strtoupper($purpose) }}</strong> and for lawful purposes it may serve.</p>
        @else
          <p>{!! $bodyText !!}</p>
        @endif
      </div>

      <!-- Date and Signatures -->
      <div class="cert-footer">
        <div style="margin-top: 12px; font-size: 9px; line-height: 1.6;">
          <p>Signed this <strong>{{ date('jS') }} day of {{ date('F Y') }}</strong>, in the Madridejos, Cebu</p>
        </div>

        <div style="margin-top: 20px; text-align: center;">
          <div style="border: 1px solid #333; height: 40px; width: 120px; margin: 0 auto 6px;"></div>
          <p style="font-size: 9px; font-weight: 600;">First Time Jobseeker</p>
          <div style="margin-top: 12px; border-top: 1px solid #333; padding-top: 2px;">
            <p style="font-size: 9px;"><strong>{{ date('M d, Y') }}</strong></p>
            <p style="font-size: 8px;">Date</p>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 16px; font-size: 9px;">
          <div>
            <p style="margin-bottom: 14px; font-size: 8.5px;"><strong>Witnessed by:</strong></p>
            <div style="border-top: 1px solid #333; height: 28px; margin-bottom: 2px;"></div>
            <p style="font-size: 8px;">Kagawad</p>
            <div style="margin-top: 8px; border-top: 1px solid #333; padding-top: 2px;"></div>
            <p style="font-size: 8px;">Date</p>
          </div>
          <div style="text-align: center;">
            <p style="margin-bottom: 14px; font-size: 8.5px; text-align: left;"><strong>Approved by:</strong></p>
            <div style="border-top: 1px solid #333; margin-top: 28px; margin-bottom: 2px;"></div>
            <p style="font-size: 8.5px; font-weight: bold;">{{ $captainName ?? ($captainOfficial ? $captainOfficial->name : 'PUNONG BARANGAY') }}</p>
            <p style="font-size: 8px;">Punong Barangay</p>
          </div>
        </div>

        <div style="margin-top: 16px; font-size: 8px; text-align: center; padding-top: 8px; border-top: 1px solid #ddd;">
          <p><strong>This certification is valid only until {{ date('M d, Y', strtotime('+1 year')) }} one (1) year from the issuance.</strong></p>
        </div>

        <div class="doc-stamp" style="margin-top: 12px; font-size: 8px; border: none; padding: 0;">
          NOT VALID WITHOUT OFFICIAL DRY SEAL
        </div>
      </div>
    </div>
  </div>

</body>

</html>
