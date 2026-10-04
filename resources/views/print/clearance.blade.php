<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Barangay Clearance — Barangay Pili</title>
  <link href="https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #dcdcdc; font-family: Georgia, serif; }
    .toolbar { background: #b91c1c; padding: 15px; text-align: center; color: #fff; }
    .toolbar button { padding: 10px 20px; border: none; border-radius: 5px; background: #fff; cursor: pointer; margin-left: 10px; font-weight: 700; color: #b91c1c; }
    .page { width: 210mm; min-height: 297mm; background: #fff; margin: 20px auto; padding: 15mm 18mm; position: relative; overflow: hidden; }
    
    /* HEADER */
    .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
    .logo img { width: 85px; height: 85px; object-fit: contain; }
    .header-text { flex: 1; text-align: center; }
    .header-text div { line-height: 1.35; }
    .province, .municipality { font-size: 18px; font-family: Georgia, serif; }
    .barangay { font-size: 30px; font-weight: bold; font-family: Georgia, serif; letter-spacing: 1px; }
    .office-title { text-align: center; font-size: 38px; font-family: 'UnifrakturMaguntia', cursive; font-weight: 700; margin-top: 10px; color: #111; }
    .header-line { border: none; border-top: 3px solid #333; margin-top: 8px; margin-bottom: 18px; }

    /* MAIN */
    .main-content { display: flex; gap: 20px; }

    /* SIDEBAR */
    .sidebar { width: 180px; background: #ebd2b6; position: relative; padding: 25px 10px; text-align: center; flex-shrink: 0; border: 1px solid #d5af84; }
    .sidebar::before { content: ''; position: absolute; top: -12px; left: 0; width: 105%; height: 22px; background: #dfb98f; border-radius: 4px; border: 1px solid #cf9e69; }
    .sidebar::after { content: ''; position: absolute; bottom: -12px; right: 0; width: 105%; height: 22px; background: #dfb98f; border-radius: 4px; border: 1px solid #cf9e69; }
    .sidebar-inner { position: relative; z-index: 1; }
    .scroll-dot-top { position: absolute; top: -6px; left: 8px; width: 10px; height: 10px; background: #333; border-radius: 50%; z-index: 2; }
    .scroll-dot-bottom { position: absolute; bottom: -6px; left: -8px; width: 10px; height: 10px; background: #333; border-radius: 50%; z-index: 2; }
    .sidebar h3 { font-size: 15px; font-weight: bold; margin-bottom: 20px; color: #7b5836; line-height: 1.3; letter-spacing: 0.5px; }
    .official-group { margin-bottom: 10px; }
    .official-label { display: block; font-size: 13px; font-style: italic; font-weight: bold; color: #7b5836; margin-bottom: 4px; }
    .official-name { font-size: 13px; color: #333; line-height: 1.5; font-weight: bold; }
    .punong-name { font-size: 14px; font-weight: bold; color: #333; }
    .punong-title { font-size: 18px; font-family: 'Dancing Script', cursive; color: #333; margin-top: -2px; margin-bottom: 10px; font-weight: normal; }

    /* CONTENT */
    .content { flex: 1; position: relative; z-index: 2; }
    .cert-title { text-align: center; margin-bottom: 16px; }
    .cert-title h1 { font-size: 22px; font-weight: bold; color: #222; letter-spacing: 1px; }
    .doc-body { font-size: 14px; line-height: 1.7; color: #222; }
    .doc-body p { margin-bottom: 12px; }
    .indent-para { text-indent: 40px; }
    .to-whom { font-size: 16px; font-weight: bold; margin-bottom: 8px; }
    .underline-value { display: inline-block; border-bottom: 1px solid #000; font-weight: 700; padding: 0 4px; }

    /* INFO with picture box */
    .info-with-picture { display: flex; gap: 15px; margin-top: 12px; margin-bottom: 18px; }
    .info-fields { flex: 1; }
    .info-fields div { margin-bottom: 5px; font-size: 14px; line-height: 1.5; }
    .info-fields div span.label { font-weight: bold; }
    .picture-box { width: 95px; height: 115px; border: 2px solid #4a73b5; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #555; font-weight: bold; letter-spacing: 1px; }

    /* THUMBMARK & SIGNATURE */
    .thumb-sig-section { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 20px; margin-bottom: 20px; }
    .thumb-boxes { display: flex; gap: 20px; }
    .thumb-box { text-align: center; }
    .thumbprint-box { width: 95px; height: 80px; border: 2px solid #4a73b5; }
    .thumb-label { font-size: 12px; margin-top: 4px; color: #555; }
    .sig-label { font-size: 12px; font-weight: bold; letter-spacing: 1px; }

    /* SIGNATURE BLOCKS */
    .sig-approved-label { font-size: 13px; }
    .sig-name { font-size: 13px; font-weight: bold; }
    .sig-title { font-size: 12px; }
    .footer-and-right-sig { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 25px; }
    .footer-info { font-size: 12px; line-height: 1.6; }

    /* WATERMARK */
    .watermark { position: absolute; top: 50%; left: 55%; transform: translate(-50%, -50%); width: 480px; height: 480px; z-index: 0; opacity: 0.12; pointer-events: none; }
    .doc-stamp { margin-top: 25px; font-size: 12px; color: #333; }

    @media print {
      .toolbar { display: none; }
      body { background: #fff; }
      .page { margin: 0; border: none; }
    }
  </style>
</head>
<body {!! $autoPrint ? 'onload="window.print()"' : '' !!}>

  <div class="toolbar">
    BARANGAY CLEARANCE PRINT PREVIEW
    <button onclick="window.print();">Print</button>
    <button onclick="window.close();" style="background:#fee2e2; color:#b91c1c;">Close</button>
  </div>

  <div class="page">
    <div class="watermark">
      <img src="{{ asset('assets/images/pili_logo.png') }}" style="width: 100%; height: 100%; object-fit: contain;" alt="Watermark">
    </div>

    <!-- HEADER -->
    <div class="top-header">
      <div class="logo">
        <img src="{{ asset('assets/images/pili_logo.png') }}" alt="Pili Barangay Logo">
      </div>
      <div class="header-text">
        <div class="province">Province of Cebu</div>
        <div class="municipality">Municipality of Madridejos</div>
        <div class="barangay">BARANGAY PILI</div>
      </div>
      <div class="logo">
        <img src="{{ asset('assets/images/municipality_logo.png') }}" alt="Municipality Logo">
      </div>
    </div>

    <div class="office-title">Office of the Punong Barangay</div>
    <div class="header-line"></div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
      <!-- SIDEBAR -->
      <div class="sidebar">
        <div class="scroll-dot-top"></div>
        <div class="scroll-dot-bottom"></div>
        <div class="sidebar-inner">
          <h3>SANGGUNIANG<br>BARANGAY</h3>

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
            <div class="punong-name">{{ $captainOfficial ? $captainOfficial->name : ($captainName ?? 'PUNONG BARANGAY') }}</div>
            <div class="punong-title">Punong Barangay</div>
          </div>

          @if($kagawadsList->isNotEmpty() || $otherOfficials->isNotEmpty())
          <div class="official-group" style="margin-top:10px;">
            <span class="official-label">KAGAWAD:</span>
            @foreach($kagawadsList->isNotEmpty() ? $kagawadsList : $otherOfficials as $k)
              <div class="official-name">{{ $k->name }}</div>
            @endforeach
          </div>
          @endif

          @if($skOfficial)
          <div class="official-group" style="margin-top:10px;">
            <span class="official-label">SK CHAIRMAN:</span>
            <div class="official-name">{{ $skOfficial->name }}</div>
          </div>
          @endif

          @if($secOfficial)
          <div class="official-group" style="margin-top:10px;">
            <span class="official-label">SECRETARY:</span>
            <div class="official-name">{{ $secOfficial->name }}</div>
          </div>
          @endif

          @if($treasOfficial)
          <div class="official-group" style="margin-top:10px;">
            <span class="official-label">TREASURER:</span>
            <div class="official-name">{{ $treasOfficial->name }}</div>
          </div>
          @endif
        </div>
      </div>

      <!-- CONTENT -->
      <div class="content">
        <div class="cert-title">
          <h1>BARANGAY CLEARANCE</h1>
        </div>

        <div class="doc-body">
          <p class="to-whom">To whom it may concern:</p>

          <p class="indent-para">
            This is to certify that the person whose name, picture, thumb mark and signature appear hereon has requested a CLEARANCE from this office.
          </p>

          <!-- INFO + PICTURE -->
          <div class="info-with-picture">
            <div class="info-fields">
              <div><span class="label">NAME:</span> {{ strtoupper($certReq->resident->full_name) }}</div>
              <div><span class="label">DATE OF BIRTH:</span> {{ \Carbon\Carbon::parse($certReq->resident->birthdate)->format('F d, Y') }}</div>
              <div><span class="label">CIVIL STATUS:</span> {{ $certReq->resident->civil_status }}</div>
              <div><span class="label">ADDRESS:</span> {{ $certReq->resident->address }}</div>
              <div><span class="label">GENDER:</span> {{ $certReq->resident->gender }}</div>
              <div><span class="label">PLACE OF BIRTH:</span> {{ $certReq->resident->place_of_birth ?? 'PILI, MADRIDEJOS, CEBU' }}</div>
              <div><span class="label">PURPOSE:</span> {{ $certReq->purpose }}</div>
            </div>

            <div class="picture-box">
              @if ($certReq->resident->photo)
                <img src="{{ asset('assets/uploads/' . $certReq->resident->photo) }}" alt="Resident photo" style="width:100%;height:100%;object-fit:cover;">
              @else
                PICTURE
              @endif
            </div>
          </div>

          <!-- THUMBMARKS + SIGNATURE -->
          <div class="thumb-sig-section">
            <div class="thumb-boxes">
              <div class="thumb-box">
                <div class="thumbprint-box"></div>
                <div class="thumb-label">Left Thumbmark</div>
              </div>
              <div class="thumb-box">
                <div class="thumbprint-box"></div>
                <div class="thumb-label">Right Thumbmark</div>
              </div>
            </div>

            <div style="text-align: center; width: 180px; margin-bottom: 4px;">
              <div style="border-top: 1.5px solid #333; margin-bottom: 4px;"></div>
              <div class="sig-label">SIGNATURE</div>
            </div>
          </div>

          <p class="indent-para">
            This is to certify further that he/she has no pending case derogatory record in this office and is found to be of good moral character, law-abiding and a peace-loving citizen.
          </p>

          <p class="indent-para">
            Issued this <u>{{ date('j') }}<sup>{{ date('S') }}</sup></u> day of
            {{ date('F Y') }}, at Madridejos, Cebu.
          </p>

          <!-- LEFT SIGNATURE -->
          <div style="margin-top: 20px;">
            <div class="sig-approved-label">Approved by:</div>
            <div style="margin-top: 15px; margin-left: 50px;">
              <div class="sig-name"><u style="text-underline-offset: 2px;">{{ $secOfficial ? $secOfficial->name : ($secName ?? 'BARANGAY SECRETARY') }}</u></div>
              <div class="sig-title">Barangay Secretary</div>
            </div>
          </div>

          <!-- FOOTER AND RIGHT SIGNATURE -->
          <div class="footer-and-right-sig">
            <div class="footer-info">
              <div>Paid under O.R. No. <span style="display:inline-block; border-bottom: 1px solid #333; width: 60px; text-align:center;">{{ $certReq->payment->receipt_number ?? '' }}</span></div>
              <div>Certification fee &nbsp; <span style="display:inline-block; border-bottom: 1px solid #333; width: 60px; text-align:center;">{{ $certReq->payment ? 'P' . number_format($certReq->payment->amount, 2) : '' }}</span></div>
              <div>Documentary Stamp</div>
              <div>Amount paid &nbsp;&nbsp;&nbsp;&nbsp; P=<span style="display:inline-block; border-bottom: 1px solid #333; width: 60px; text-align:center;">{{ $certReq->payment ? number_format($certReq->payment->amount, 2) : '' }}</span></div>
              <div>Issued on {{ date('m/d/Y') }}</div>
              <div>Issued at Pili, Madridejos, Cebu</div>
            </div>

            <div style="width: 250px; text-align: center;">
              <div class="sig-approved-label" style="text-align: left; margin-bottom: 25px;">Approved by:</div>
              <div style="border-top: 1.5px solid #333; margin-bottom: 4px;"></div>
              <div class="sig-name">{{ $captainName ?? ($captainOfficial ? $captainOfficial->name : 'PUNONG BARANGAY') }}</div>
              <div class="sig-title">PUNONG BARANGAY</div>
            </div>
          </div>

          <div class="doc-stamp">
            Only until December 31, {{ date('Y') }} and not valid without Official Dry Seal and Left &amp; Right thumb.
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
