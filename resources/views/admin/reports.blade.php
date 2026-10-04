@extends('layouts.app')

@section('title', 'Reports')

@section('styles')
<style>
  @media print {
    .no-print { display: none !important; }
    .sidebar { display: none !important; }
    .topbar { display: none !important; }
    .page-content { padding: 0 !important; margin: 0 !important; }
    .wrapper { display: block !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    .official-report { box-shadow: none !important; padding: 0 !important; border: none !important; }
  }
</style>
@endsection

@section('content')
<div class="gov-page-heading no-print">
  <div>
    <div class="eyebrow">Official Reporting</div>
    <h1>Reports</h1>
    <p>Transactions report, certificate type breakdown, and printable monthly reports.</p>
  </div>
</div>

<div class="card no-print" style="margin-bottom:20px;">
  <div class="card-body" style="padding:16px 20px;">
    <form method="GET" action="{{ route('admin.reports') }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
      <div>
        <label class="form-label">Reporting Month</label>
        <input type="month" name="month" class="form-control" value="{{ $month }}">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Generate</button>
      <button type="button" class="btn btn-success" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
      <button type="submit" name="export" value="csv" class="btn btn-secondary"><i class="fas fa-file-csv"></i> Export CSV</button>
    </form>
  </div>
</div>

<div id="printReport" class="official-report" style="background:#fff; padding:30px; border:1px solid #e5e7eb; border-radius:8px;">
  <div class="report-letterhead" style="display:flex; justify-content:space-between; align-items:center; border-bottom:3px solid #333; padding-bottom:12px; margin-bottom:20px;">
    <div class="logo">
      <img src="{{ asset('assets/images/pili_logo.png') }}" alt="Barangay Logo" style="width: 72px; height: 72px; object-fit: contain;">
    </div>
    <div style="text-align:center;">
      <div class="rep" style="font-size:14px; text-transform:uppercase; letter-spacing:0.5px;">Republic of the Philippines</div>
      <div style="font-size:14px;">Province of Cebu</div>
      <div style="font-size:14px;">Municipality of Madridejos</div>
      <strong style="font-size:18px; display:block; margin-top:2px;">BARANGAY PILI</strong>
      <h2 style="font-size:18px; margin-top:8px; font-weight:800; color:#111;">MONTHLY TRANSACTIONS REPORT</h2>
      <p style="font-size:12.5px; color:#4b5563; margin-top:2px;">{{ \Carbon\Carbon::parse($start)->format('F 1, Y') }} to {{ \Carbon\Carbon::parse($start)->format('F t, Y') }}</p>
    </div>
    <div class="logo">
      <img src="{{ asset('assets/images/municipality_logo.png') }}" alt="Municipality Logo" style="width: 72px; height: 72px; object-fit: contain;">
    </div>
  </div>

  <div class="report-summary" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin-bottom:24px;">
    <div style="background:#f9fafb; padding:14px; border-radius:8px; border:1px solid #e5e7eb;">
      <span style="font-size:11px; color:#6b7280; text-transform:uppercase; font-weight:700; display:block; margin-bottom:4px;">Total Transactions</span>
      <strong style="font-size:20px; color:#111827;">{{ count($transactions) }}</strong>
    </div>
    <div style="background:#f9fafb; padding:14px; border-radius:8px; border:1px solid #e5e7eb;">
      <span style="font-size:11px; color:#6b7280; text-transform:uppercase; font-weight:700; display:block; margin-bottom:4px;">Total Collections</span>
      <strong style="font-size:20px; color:#b91c1c;">PHP {{ number_format($totalAmount, 2) }}</strong>
    </div>
    <div style="background:#f9fafb; padding:14px; border-radius:8px; border:1px solid #e5e7eb;">
      <span style="font-size:11px; color:#6b7280; text-transform:uppercase; font-weight:700; display:block; margin-bottom:4px;">Report Generated</span>
      <strong style="font-size:14px; color:#111827; display:block; margin-top:6px;">{{ date('M d, Y') }}</strong>
    </div>
  </div>

  <section class="report-section" style="margin-bottom:24px;">
    <h3 style="font-size:14px; font-weight:800; border-bottom:1px solid #e5e7eb; padding-bottom:6px; margin-bottom:10px; color:#374151;">Transactions Report</h3>
    <table class="report-table" style="width:100%; border-collapse:collapse; font-size:12.5px;">
      <thead>
        <tr style="border-bottom:2px solid #e5e7eb; text-align:left; background:#f9fafb;">
          <th style="padding:8px;">Tracking No.</th>
          <th style="padding:8px;">Resident</th>
          <th style="padding:8px;">Certificate</th>
          <th style="padding:8px;">Date Filed</th>
          <th style="padding:8px;">Status</th>
          <th style="padding:8px;">Payment</th>
          <th style="padding:8px;">Amount</th>
        </tr>
      </thead>
      <tbody>
        @if ($transactions->isEmpty())
          <tr><td colspan="7" class="text-center" style="padding:20px; color:#6b7280;">No transactions for this period.</td></tr>
        @else
          @foreach ($transactions as $row)
            <tr style="border-bottom:1px solid #e5e7eb;">
              <td style="padding:8px; font-family:monospace;">{{ $row->tracking_number }}</td>
              <td style="padding:8px;">{{ $row->resident->full_name }}</td>
              <td style="padding:8px;">{{ $row->certificate->name }}</td>
              <td style="padding:8px;">{{ \Carbon\Carbon::parse($row->requested_at)->format('M d, Y') }}</td>
              <td style="padding:8px;">{{ ucfirst($row->status) }}</td>
              <td style="padding:8px;">{{ ucfirst($row->payment->payment_status ?? 'unpaid') }}</td>
              <td style="padding:8px; font-weight:600;">PHP {{ number_format($row->payment ? $row->payment->amount : $row->certificate->fee, 2) }}</td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </section>

  <section class="report-section" style="margin-bottom:24px;">
    <h3 style="font-size:14px; font-weight:800; border-bottom:1px solid #e5e7eb; padding-bottom:6px; margin-bottom:10px; color:#374151;">Breakdown by Certificate Types</h3>
    <table class="report-table" style="width:100%; border-collapse:collapse; font-size:12.5px;">
      <thead>
        <tr style="border-bottom:2px solid #e5e7eb; text-align:left; background:#f9fafb;">
          <th style="padding:8px;">Certificate Type</th>
          <th style="padding:8px;">Group</th>
          <th style="padding:8px;">Total</th>
          <th style="padding:8px;">Approved</th>
          <th style="padding:8px;">Released</th>
          <th style="padding:8px;">Collections</th>
        </tr>
      </thead>
      <tbody>
        @if ($byCert->isEmpty())
          <tr><td colspan="6" class="text-center" style="padding:20px; color:#6b7280;">No certificate activity for this period.</td></tr>
        @else
          @foreach ($byCert as $row)
            <tr style="border-bottom:1px solid #e5e7eb;">
              <td style="padding:8px; font-weight:600;">{{ $row->name }}</td>
              <td style="padding:8px;">{{ $row->category ?? 'General' }}</td>
              <td style="padding:8px;">{{ $row->total }}</td>
              <td style="padding:8px;">{{ $row->approved }}</td>
              <td style="padding:8px;">{{ $row->released }}</td>
              <td style="padding:8px; font-weight:600; color:var(--primary);">PHP {{ number_format($row->collections, 2) }}</td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </section>

  <section class="report-section" style="margin-top:40px;">
    <div class="report-signatures" style="display:flex; justify-content:space-between; align-items:flex-start;">
      <div style="text-align:left;">
        <span style="font-size:11px; color:#6b7280; display:block; margin-bottom:30px;">Prepared by:</span>
        <strong style="font-size:13px; border-bottom:1px solid #333; padding-bottom:2px; display:inline-block;">{{ Auth::user()->username }}</strong>
        <small style="display:block; font-size:11px; color:#6b7280; margin-top:4px;">Barangay System Administrator</small>
      </div>
      <div style="text-align:right;">
        <span style="font-size:11px; color:#6b7280; display:block; margin-bottom:30px;">Certified Correct:</span>
        <strong style="font-size:13px; border-bottom:1px solid #333; padding-bottom:2px; display:inline-block;">{{ $captainName ?? 'PUNONG BARANGAY' }}</strong>
        <small style="display:block; font-size:11px; color:#6b7280; margin-top:4px;">Punong Barangay</small>
      </div>
    </div>
  </section>
</div>
@endsection
