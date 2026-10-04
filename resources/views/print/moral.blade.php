<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Certificate of Good Moral — Barangay Pili</title>
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
    .republic, .province, .municipality { font-size: 18px; font-family: Georgia, serif; }
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
    CERTIFICATE OF GOOD MORAL PRINT PREVIEW
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
        <div class="republic">Republic of the Philippines</div>
        <div class="province">Province of Cebu</div>
        <div class="municipality">Municipality of Madridejos</div>
        <div class="barangay">BARANGAY PILI</div>
      </div>
      <div class="logo">
        <img src="{{ asset('assets/images/municipality_logo.png') }}" alt="Municipality Logo">
      </div>
    </div>

    <div class="office-title">Office of the Barangay Captain</div>
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
          <h1>CERTIFICATE OF GOOD MORAL</h1>
        </div>

        <div class="doc-body">
          <p class="to-whom">To whom it may concern:</p>

          <p class="indent-para">
            This is to certify that <span class="underline-value" style="min-width: 180px;">{{ strtoupper($certReq->resident->full_name) }}</span>, 
            a resident of {{ $certReq->resident->address }}, Barangay Pili, Madridejos, Cebu, is personally known to this office as a person of good moral character, law-abiding, peace-loving, and a socially responsible citizen of our community.
          </p>

          <p class="indent-para">
            Records available in this barangay further show that the above-named person has no derogatory or criminal record filed in this office as of this date.
          </p>

          <p class="indent-para">
            This certification is issued upon the request of the above-named person for 
            <strong><span class="underline-value" style="min-width: 120px;">{{ strtoupper($certReq->purpose) }}</span></strong> purpose and for whatever lawful purpose it may serve.
          </p>

          <p class="indent-para" style="font-weight: bold;">
            Issued this {{ date('jS') }} day of {{ date('F Y') }}, at Pili, Madridejos, Cebu.
          </p>

          <!-- SIGNATURES -->
          <div style="display: flex; justify-content: flex-end; margin-top: 50px;">
            <div style="width: 250px; text-align: center;">
              <div style="border-top: 2px solid #333; margin-top: 40px; margin-bottom: 8px;"></div>
              <strong>{{ $captainName ?? ($captainOfficial ? $captainOfficial->name : 'PUNONG BARANGAY') }}</strong><br>
              Punong Barangay
            </div>
          </div>

          <!-- FOOTER -->
          <div class="footer-info" style="margin-top: 30px;">
            <table style="font-size: 13px; line-height: 1.4; border-collapse: collapse;">
              <tr>
                <td>Paid under O.R. No.</td>
                <td style="padding-left: 5px;"><span style="display:inline-block; border-bottom: 1px solid #333; width: 80px; text-align:center;">{{ $certReq->payment->receipt_number ?? 'N/A' }}</span></td>
              </tr>
              <tr>
                <td>Certification Fee</td>
                <td style="padding-left: 5px;">: <span style="text-decoration: underline;">P</span> <span style="display:inline-block; border-bottom: 1px solid #333; width: 60px; text-align:center;">{{ $certReq->payment ? number_format($certReq->payment->amount, 2) : number_format($certReq->certificate->fee, 2) }}</span></td>
              </tr>
              <tr>
                <td>Documentary Stamp</td>
                <td style="padding-left: 5px;">: <span style="text-decoration: underline;">P</span> <span style="display:inline-block; border-bottom: 1px solid #333; width: 60px; text-align:center;">As applicable</span></td>
              </tr>
              <tr>
                <td>Amount Paid</td>
                <td style="padding-left: 5px;">: <span style="text-decoration: underline;">P</span> <span style="display:inline-block; border-bottom: 1px solid #333; width: 60px; text-align:center;">{{ $certReq->payment ? number_format($certReq->payment->amount, 2) : number_format($certReq->certificate->fee, 2) }}</span></td>
              </tr>
            </table>

            <div style="margin-top: 10px;">
              <div>Issued on {{ date('m/d/Y') }}</div>
              <div>Issued at Pili, Madridejos, Cebu</div>
            </div>
          </div>

          <div class="doc-stamp">
            VALID ONLY UNTIL DECEMBER 31, {{ date('Y') }} AND NOT VALID WITHOUT OFFICIAL DRY SEAL
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
