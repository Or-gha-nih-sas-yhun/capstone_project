<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Track Request — Barangay Pili</title>
<meta name="description" content="Track your barangay clearance or certificate request status.">
<link rel="icon" type="image/png" href="{{ asset('assets/images/pili_logo.png') }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  .track-page { min-height:100vh; background:linear-gradient(135deg,#111827,#1f2937,#1e3a5f); display:flex; flex-direction:column; align-items:center; padding:40px 20px; }
  .track-header { text-align:center; color:#fff; margin-bottom:32px; }
  .track-header h1 { font-size:28px; font-weight:800; }
  .track-header p { opacity:.7; margin-top:6px; }
  .track-box { background:#fff; border-radius:16px; box-shadow:0 20px 50px rgba(0,0,0,.3); width:100%; max-width:680px; overflow:hidden; }
  .track-search { padding:28px 32px; border-bottom:1px solid #e5e7eb; }
  .track-result { padding:28px 32px; }
  .step-row { display:flex; align-items:flex-start; gap:16px; margin-bottom:18px; }
  .step-icon { width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0; }
  .step-icon.done   { background:#f0fdf4;color:#0d9488; }
  .step-icon.active { background:#eff6ff;color:#1a56db; box-shadow:0 0 0 4px rgba(26,86,219,.15); }
  .step-icon.idle   { background:#f3f4f6;color:#9ca3af; }
  .step-info h6 { font-size:14px;font-weight:700;margin-bottom:2px; }
  .step-info small { font-size:12px;color:#6b7280; }
  .step-connector { width:2px;height:18px;background:#e5e7eb;margin-left:22px; }
</style>
</head>
<body>
<div class="track-page">
  <div class="track-header">
    <div style="font-size:48px;margin-bottom:12px;">🔍</div>
    <h1>Track Your Request</h1>
    <p>Enter your tracking number to view the status of your request.</p>
  </div>

  <div class="track-box">
    <div class="track-search">
      <form method="POST" action="{{ route('track') }}">
        @csrf
        <label class="form-label" style="font-weight:700;font-size:14px;">Tracking Number</label>
        <div style="display:flex;gap:10px;margin-top:6px;">
          <input type="text" name="tracking" class="form-control" placeholder="e.g. PILI-20250514-ABCDEF"
                 value="{{ $tracking }}" required
                 style="font-family:monospace;font-size:15px;letter-spacing:.5px;">
          <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
            <i class="fas fa-search"></i> Track
          </button>
        </div>
      </form>
    </div>

    @if ($error)
    <div class="track-result">
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
    </div>
    @elseif ($result)
    <div class="track-result">
      <!-- Header info -->
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
        <div>
          <div style="font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Tracking Number</div>
          <div style="font-family:monospace;font-size:17px;font-weight:800;color:#111827;">{{ $result->tracking_number }}</div>
        </div>
        <span class="badge bg-{{ $result->status === 'pending' ? 'warning' : ($result->status === 'processing' ? 'info' : ($result->status === 'approved' ? 'success' : ($result->status === 'rejected' ? 'danger' : 'primary'))) }}">
            {{ ucfirst($result->status) }}
        </span>
      </div>

      <!-- Details grid -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px;padding:16px;background:#f9fafb;border-radius:10px;">
        <div><div style="font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase;">Resident</div>
             <div style="font-weight:600;">{{ $result->resident->full_name }}</div></div>
        <div><div style="font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase;">Document</div>
             <div style="font-weight:600;">{{ $result->certificate->name }}</div></div>
        <div><div style="font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase;">Purpose</div>
             <div>{{ $result->purpose }}</div></div>
        <div><div style="font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase;">Date Filed</div>
             <div>{{ \Carbon\Carbon::parse($result->requested_at)->format('M d, Y h:i A') }}</div></div>
        <div><div style="font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase;">Fee</div>
             <div style="font-weight:700;color:#1a56db;">₱{{ number_format($result->certificate->fee, 2) }}</div></div>
        <div><div style="font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase;">Payment</div>
             <div>
               <span class="badge bg-{{ ($result->payment->payment_status ?? 'unpaid') === 'paid' ? 'success' : (($result->payment->payment_status ?? 'unpaid') === 'waived' ? 'secondary' : 'danger') }}">
                   {{ ucfirst($result->payment->payment_status ?? 'unpaid') }}
               </span>
               @if (($result->payment->payment_method ?? '') === 'gcash')
                 <span class="badge bg-info" style="font-size:10px;text-transform:uppercase;margin-left:4px;">GCash</span>
               @endif
             </div></div>
      </div>

      <!-- Timeline -->
      <div style="font-size:13px;font-weight:700;color:#374151;margin-bottom:14px;">Request Progress</div>
      @php
        $status_steps = ['pending','approved','released'];
        $status_icons = ['fas fa-clock','fas fa-check-circle','fas fa-box-open'];
        $status_labels= ['Request Submitted','Approved','Released'];
        $current_idx = array_search($result->status, $status_steps);
        if ($result->status === 'rejected') $current_idx = -1;
      @endphp
      @foreach ($status_steps as $i => $step)
        @php
          $state = ($i < $current_idx) ? 'done' : ($i === $current_idx ? 'active' : 'idle');
        @endphp
        <div class="step-row">
          <div class="step-icon {{ $state }}">
            <i class="{{ $status_icons[$i] }}"></i>
          </div>
          <div class="step-info">
            <h6 style="color:{{ $state==='idle'?'#9ca3af':'#111827' }}">{{ $status_labels[$i] }}</h6>
            <small>{{ $state==='done'?'Completed':($state==='active'?'Current step':'Pending') }}</small>
          </div>
        </div>
        @if ($i < count($status_steps)-1)
          <div class="step-connector"></div>
        @endif
      @endforeach

      @if ($result->status === 'rejected')
      <div class="alert alert-danger" style="margin-top:16px;">
        <i class="fas fa-times-circle"></i>
        Request was <strong>rejected</strong>.
        @if ($result->remarks)Remarks: {{ $result->remarks }}@endif
      </div>
      @elseif ($result->expected_release_date && $result->status === 'approved')
      <div class="alert alert-success" style="margin-top:16px;">
        <i class="fas fa-calendar-check"></i>
        Expected Release Date: <strong>{{ \Carbon\Carbon::parse($result->expected_release_date)->format('M d, Y h:i A') }}</strong>
      </div>
      @endif
      
      @if ($result->remarks)
      <div style="margin-top:20px;padding:16px;background:#ebf8fa;border-radius:10px;border-left:4px solid #0891b2;">
        <div style="font-size:11px;color:#0891b2;font-weight:700;text-transform:uppercase;margin-bottom:4px;"><i class="fas fa-envelope" style="margin-right:6px;"></i>Message from Admin / Remarks</div>
        <div style="font-size:14px;color:#164e63;line-height:1.5;">{!! nl2br(e($result->remarks)) !!}</div>
      </div>
      @endif
    </div>
    @elseif ($searched)
    <div class="track-result"><div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Please enter a tracking number.</div></div>
    @endif
  </div>

  <div style="margin-top:24px;color:rgba(255,255,255,.5);font-size:13px;text-align:center;">
    <a href="{{ route('login') }}" style="color:rgba(255,255,255,.6);">← Back to Login</a>
    &nbsp;·&nbsp; Barangay Pili Digital Services
  </div>
</div>
<script src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>
