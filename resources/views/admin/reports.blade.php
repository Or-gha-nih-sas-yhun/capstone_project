@extends('layouts.app')

@section('title', 'Issuance & Transactions Reports')

@section('styles')
<style>
  /* ── Screen UI Styles ── */
  .reports-shell {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .filter-panel {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.03);
  }

  .period-nav {
    display: flex;
    gap: 8px;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 12px;
    margin-bottom: 14px;
    flex-wrap: wrap;
  }

  .period-btn {
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid #e5e7eb;
    color: #4b5563;
    background: #f9fafb;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .period-btn:hover {
    background: #f3f4f6;
    color: #111827;
  }

  .period-btn.active {
    background: var(--primary, #b91c1c);
    color: #fff;
    border-color: var(--primary, #b91c1c);
  }

  .filter-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 12px;
    align-items: flex-end;
  }

  .filter-form-grid .form-group {
    margin-bottom: 0;
  }

  .filter-form-grid .form-label {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
  }

  .filter-form-grid .form-control,
  .filter-form-grid .form-select {
    padding: 7px 10px;
    font-size: 13px;
    height: 38px;
  }

  .filter-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
  }

  /* ── Official Report Document ── */
  .official-report {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 36px 40px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
  }

  .report-letterhead {
    display: grid;
    grid-template-columns: 80px 1fr 80px;
    align-items: center;
    gap: 16px;
    border-bottom: 3px double #1f2937;
    padding-bottom: 16px;
    margin-bottom: 24px;
  }

  .letterhead-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
  }

  .letterhead-text {
    text-align: center;
  }

  .letterhead-text .rep {
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #4b5563;
    font-weight: 600;
  }

  .letterhead-text .loc {
    font-size: 12.5px;
    color: #1f2937;
    line-height: 1.35;
  }

  .letterhead-text .brgy {
    font-size: 18px;
    font-weight: 800;
    color: #111827;
    letter-spacing: 0.5px;
    margin-top: 2px;
  }

  .report-headline {
    text-align: center;
    margin-top: 10px;
  }

  .report-headline h2 {
    font-size: 17px;
    font-weight: 800;
    color: #b91c1c;
    margin: 0;
    letter-spacing: 0.5px;
  }

  .report-headline p {
    font-size: 12.5px;
    color: #4b5563;
    font-weight: 600;
    margin: 3px 0 0;
  }

  /* Metric KPI Cards in Report */
  .kpi-report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin-bottom: 24px;
  }

  .kpi-report-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    border-left: 4px solid #94a3b8;
  }

  .kpi-report-card.issued {
    border-left-color: #059669;
    background: #f0fdf4;
  }

  .kpi-report-card.collections {
    border-left-color: #7c3aed;
    background: #f5f3ff;
  }

  .kpi-report-card.pending {
    border-left-color: #d97706;
    background: #fffbeb;
  }

  .kpi-report-card .k-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.3px;
    display: block;
    margin-bottom: 2px;
  }

  .kpi-report-card .k-val {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
  }

  .kpi-report-card .k-sub {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
  }

  /* Tables */
  .report-section {
    margin-bottom: 28px;
  }

  .section-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 6px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .section-title span.badge {
    font-size: 11px;
    font-weight: 600;
  }

  .report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
  }

  .report-table th {
    background: #f1f5f9;
    color: #334155;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.3px;
    padding: 8px 10px;
    border: 1px solid #cbd5e1;
    text-align: left;
  }

  .report-table td {
    padding: 7px 10px;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    vertical-align: middle;
  }

  .report-table tfoot td {
    background: #f8fafc;
    font-weight: 700;
    border-top: 2px solid #cbd5e1;
  }

  /* Signatures */
  .signatures-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-top: 40px;
    page-break-inside: avoid;
  }

  .signature-block {
    text-align: center;
  }

  .signature-line {
    width: 220px;
    margin: 45px auto 4px;
    border-bottom: 1.5px solid #1f2937;
  }

  .signature-name {
    font-size: 13.5px;
    font-weight: 700;
    color: #111827;
  }

  .signature-role {
    font-size: 11.5px;
    color: #4b5563;
  }

  /* ── PRINT OPTIMIZATIONS ── */
  @media print {
    .no-print,
    .sidebar,
    .topbar,
    .zoom-controls,
    .mobile-bottom-nav,
    #sidebar,
    #sidebar-overlay,
    .gov-page-heading {
      display: none !important;
    }

    body {
      background: #fff !important;
      color: #000 !important;
      font-size: 11pt !important;
      zoom: 1 !important;
    }

    .wrapper,
    .main-content,
    .page-content {
      margin: 0 !important;
      padding: 0 !important;
      height: auto !important;
      min-height: auto !important;
      overflow: visible !important;
      width: 100% !important;
    }

    .official-report {
      box-shadow: none !important;
      border: none !important;
      padding: 0 !important;
      margin: 0 !important;
      width: 100% !important;
    }

    .report-table th {
      background: #e2e8f0 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .kpi-report-card {
      border: 1px solid #cbd5e1 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .kpi-report-card.issued { background: #e6fcf5 !important; }
    .kpi-report-card.collections { background: #f3e8ff !important; }

    .report-section,
    .signatures-grid {
      page-break-inside: avoid;
    }

    @page {
      size: auto;
      margin: 12mm 15mm;
    }
  }
</style>
@endsection

@section('content')
<div class="reports-shell">

  {{-- Page Heading (Screen Only) --}}
  <div class="gov-page-heading no-print" style="margin-bottom:0;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
      <div>
        <div class="eyebrow" style="color:var(--primary); font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:0.5px;">
          Barangay Records & Issuance Office
        </div>
        <h1 style="font-size:22px; font-weight:800; margin:2px 0 0; color:#111827;">Issuance & Transactions Reports</h1>
        <p style="font-size:13px; color:#4b5563; margin-top:2px;">
          Filter daily, weekly, or monthly issuance reports, analyze certificate type breakdowns, and print official documentation.
        </p>
      </div>
      <div style="display:flex; gap:8px;">
        <button type="button" class="btn btn-success" onclick="window.print()" style="font-weight:600;">
          <i class="fas fa-print"></i> Print Official Report
        </button>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-secondary" style="font-weight:600; background:#fff;">
          <i class="fas fa-file-csv" style="color:#059669;"></i> Export to CSV
        </a>
      </div>
    </div>
  </div>

  {{-- Interactive Filter Toolbar (Screen Only) --}}
  <div class="filter-panel no-print">
    {{-- Quick Period Pills --}}
    <div class="period-nav">
      <span style="font-size:12px; font-weight:700; color:#6b7280; align-self:center; margin-right:4px;">TIME PERIOD:</span>
      <a href="{{ route('admin.reports', array_merge(request()->except(['period', 'export']), ['period' => 'daily', 'date' => now()->toDateString()])) }}"
        class="period-btn {{ $period === 'daily' ? 'active' : '' }}">
        <i class="fas fa-calendar-day"></i> Daily (Today)
      </a>
      <a href="{{ route('admin.reports', array_merge(request()->except(['period', 'export']), ['period' => 'weekly', 'week_date' => now()->toDateString()])) }}"
        class="period-btn {{ $period === 'weekly' ? 'active' : '' }}">
        <i class="fas fa-calendar-week"></i> Weekly (This Week)
      </a>
      <a href="{{ route('admin.reports', array_merge(request()->except(['period', 'export']), ['period' => 'monthly', 'month' => now()->format('Y-m')])) }}"
        class="period-btn {{ $period === 'monthly' ? 'active' : '' }}">
        <i class="fas fa-calendar-alt"></i> Monthly ({{ now()->format('M Y') }})
      </a>
      <a href="{{ route('admin.reports', array_merge(request()->except(['period', 'export']), ['period' => 'custom', 'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString()])) }}"
        class="period-btn {{ $period === 'custom' ? 'active' : '' }}">
        <i class="fas fa-sliders-h"></i> Custom Date Range
      </a>
    </div>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('admin.reports') }}">
      <input type="hidden" name="period" value="{{ $period }}">

      <div class="filter-form-grid">
        {{-- Date selection based on period --}}
        @if($period === 'daily')
          <div class="form-group">
            <label class="form-label"><i class="fas fa-calendar-day" style="color:var(--primary);"></i> Specific Date</label>
            <input type="date" name="date" class="form-control" value="{{ $dateParam }}">
          </div>
        @elseif($period === 'weekly')
          <div class="form-group">
            <label class="form-label"><i class="fas fa-calendar-week" style="color:var(--primary);"></i> Week Containing Date</label>
            <input type="date" name="week_date" class="form-control" value="{{ $weekDateParam }}">
          </div>
        @elseif($period === 'custom')
          <div class="form-group">
            <label class="form-label">From Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ $startDateParam }}">
          </div>
          <div class="form-group">
            <label class="form-label">To Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ $endDateParam }}">
          </div>
        @else
          <div class="form-group">
            <label class="form-label"><i class="fas fa-calendar-alt" style="color:var(--primary);"></i> Target Month</label>
            <input type="month" name="month" class="form-control" value="{{ $monthParam }}">
          </div>
        @endif

        {{-- Status Filter --}}
        <div class="form-group">
          <label class="form-label"><i class="fas fa-filter"></i> Document Status</label>
          <select name="status" class="form-select">
            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
            <option value="released" {{ $statusFilter === 'released' ? 'selected' : '' }}>Released (Issued Only)</option>
            <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved (Pending Release)</option>
            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending Review</option>
            <option value="processing" {{ $statusFilter === 'processing' ? 'selected' : '' }}>In Processing</option>
            <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
          </select>
        </div>

        {{-- Certificate Type Filter --}}
        <div class="form-group">
          <label class="form-label"><i class="fas fa-certificate"></i> Certificate Type</label>
          <select name="certificate_id" class="form-select">
            <option value="all">All Certificates</option>
            @foreach($allCertificates as $cert)
              <option value="{{ $cert->id }}" {{ (string)$certificateFilter === (string)$cert->id ? 'selected' : '' }}>
                {{ $cert->name }} (₱{{ number_format($cert->fee, 0) }})
              </option>
            @endforeach
          </select>
        </div>

        {{-- Purok Filter --}}
        <div class="form-group">
          <label class="form-label"><i class="fas fa-map-marker-alt"></i> Resident Purok</label>
          <select name="purok" class="form-select">
            <option value="">All Puroks</option>
            @foreach($puroks as $purok)
              <option value="{{ $purok }}" {{ $purokFilter === $purok ? 'selected' : '' }}>{{ $purok }}</option>
            @endforeach
          </select>
        </div>

        {{-- Payment Status Filter --}}
        <div class="form-group">
          <label class="form-label"><i class="fas fa-coins"></i> Payment Status</label>
          <select name="payment_status" class="form-select">
            <option value="all" {{ $paymentStatusFilter === 'all' ? 'selected' : '' }}>All Payments</option>
            <option value="paid" {{ $paymentStatusFilter === 'paid' ? 'selected' : '' }}>Paid Only</option>
            <option value="unpaid" {{ $paymentStatusFilter === 'unpaid' ? 'selected' : '' }}>Unpaid Only</option>
          </select>
        </div>

        {{-- Submit & Reset Buttons --}}
        <div class="filter-actions">
          <button type="submit" class="btn btn-primary" style="height:38px;">
            <i class="fas fa-search"></i> Generate Report
          </button>
          <a href="{{ route('admin.reports') }}" class="btn btn-outline-secondary" style="height:38px; display:inline-flex; align-items:center;" title="Reset Filters">
            <i class="fas fa-undo"></i> Reset
          </a>
        </div>
      </div>
    </form>
  </div>

  {{-- Official Printable Document Canvas --}}
  <div id="printReport" class="official-report">

    {{-- Official Government Letterhead --}}
    <div class="report-letterhead">
      <div style="text-align:center;">
        <img src="{{ setting_image('brand.logo') }}" alt="Barangay Logo" class="letterhead-logo">
      </div>
      <div class="letterhead-text">
        <div class="rep">Republic of the Philippines</div>
        <div class="loc">{{ barangay_province_line() }}</div>
        <div class="loc">{{ barangay_municipality_line() }}</div>
        <div class="brgy">{{ strtoupper(barangay_label()) }}</div>
        <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#4b5563; margin-top:2px; font-weight:600;">
          Office of the Barangay Administrator & Records Custodian
        </div>
        <div class="report-headline">
          <h2>{{ $reportTitle }}</h2>
          <p>{{ $reportSubtitle }}</p>
        </div>
      </div>
      <div style="text-align:center;">
        <img src="{{ setting_image('brand.municipality_logo', 'assets/images/municipality_logo.png') }}" alt="Municipality Logo" class="letterhead-logo">
      </div>
    </div>

    {{-- Executive Summary KPI Cards --}}
    <div class="kpi-report-grid">
      <div class="kpi-report-card issued">
        <span class="k-label"><i class="fas fa-stamp" style="color:#059669;"></i> Total Issued (Released)</span>
        <div class="k-val" style="color:#059669;">{{ number_format($summary['total_issued']) }}</div>
        <div class="k-sub">Completion rate: <strong>{{ $summary['issuance_rate'] }}%</strong></div>
      </div>

      <div class="kpi-report-card">
        <span class="k-label"><i class="fas fa-file-invoice"></i> Total Requests Filed</span>
        <div class="k-val">{{ number_format($summary['total_requests']) }}</div>
        <div class="k-sub">Received in period</div>
      </div>

      <div class="kpi-report-card">
        <span class="k-label"><i class="fas fa-check-circle" style="color:#2563eb;"></i> Approved (Ready)</span>
        <div class="k-val" style="color:#2563eb;">{{ number_format($summary['total_approved']) }}</div>
        <div class="k-sub">Awaiting resident pickup</div>
      </div>

      <div class="kpi-report-card pending">
        <span class="k-label"><i class="fas fa-clock" style="color:#d97706;"></i> Pending & Processing</span>
        <div class="k-val" style="color:#d97706;">{{ number_format($summary['total_pending'] + $summary['total_processing']) }}</div>
        <div class="k-sub">Under staff review</div>
      </div>

      <div class="kpi-report-card collections">
        <span class="k-label"><i class="fas fa-peso-sign" style="color:#7c3aed;"></i> Total Collections</span>
        <div class="k-val" style="color:#7c3aed;">₱{{ number_format($summary['total_collections'], 2) }}</div>
        <div class="k-sub">Paid certification fees</div>
      </div>
    </div>

    {{-- Section 1: Certificate & Clearance Type Breakdown --}}
    <section class="report-section">
      <div class="section-title">
        <span><i class="fas fa-chart-pie" style="color:#0d9488; margin-right:6px;"></i> Certificate & Clearance Type Breakdown</span>
        <span class="badge" style="background:#f1f5f9; color:#475569; padding:4px 8px; border-radius:6px; font-weight:600;">
          {{ $byCert->count() }} Active Document Categories
        </span>
      </div>

      <div style="overflow-x:auto;">
        <table class="report-table">
          <thead>
            <tr>
              <th style="width:28%;">Certificate / Document Type</th>
              <th style="width:14%;">Category</th>
              <th style="text-align:right; width:10%;">Fee</th>
              <th style="text-align:center; width:10%;">Requests</th>
              <th style="text-align:center; width:10%;">Approved</th>
              <th style="text-align:center; width:12%; background:#ecfdf5; color:#065f46;">Issued (Released)</th>
              <th style="text-align:center; width:8%;">Share</th>
              <th style="text-align:right; width:14%;">Collections</th>
            </tr>
          </thead>
          <tbody>
            @forelse($byCert as $cert)
              <tr>
                <td style="font-weight:700;">
                  {{ $cert->name }}
                </td>
                <td style="color:#64748b;">
                  <span style="display:inline-block; padding:2px 6px; border-radius:4px; background:#f1f5f9; font-size:10.5px; font-weight:600;">
                    {{ $cert->category ?: 'General' }}
                  </span>
                </td>
                <td style="text-align:right; font-family:monospace;">
                  ₱{{ number_format($cert->fee, 2) }}
                </td>
                <td style="text-align:center; font-weight:600;">
                  {{ number_format($cert->total_requests) }}
                </td>
                <td style="text-align:center; color:#2563eb; font-weight:600;">
                  {{ number_format($cert->approved_count) }}
                </td>
                <td style="text-align:center; font-weight:800; background:#f0fdf4; color:#047857;">
                  {{ number_format($cert->issued_count) }}
                </td>
                <td style="text-align:center; font-weight:600; color:#4b5563;">
                  {{ $cert->share_percentage }}%
                </td>
                <td style="text-align:right; font-weight:700; color:#111827;">
                  ₱{{ number_format($cert->collections, 2) }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" style="text-align:center; padding:18px; color:#6b7280;">
                  No certificate issuance activity recorded for this period.
                </td>
              </tr>
            @endforelse
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="text-transform:uppercase;">TOTAL SUMMARY</td>
              <td style="text-align:center;">{{ number_format($byCert->sum('total_requests')) }}</td>
              <td style="text-align:center; color:#2563eb;">{{ number_format($byCert->sum('approved_count')) }}</td>
              <td style="text-align:center; color:#047857; font-weight:800;">{{ number_format($byCert->sum('issued_count')) }}</td>
              <td style="text-align:center;">100%</td>
              <td style="text-align:right; color:#7c3aed;">₱{{ number_format($byCert->sum('collections'), 2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </section>

    {{-- Section 2: Detailed Document Issuance Register --}}
    <section class="report-section">
      <div class="section-title">
        <span><i class="fas fa-list-check" style="color:var(--primary); margin-right:6px;"></i> Detailed Issuance & Transactions Register</span>
        <span class="badge" style="background:#eff6ff; color:#1d4ed8; padding:4px 8px; border-radius:6px; font-weight:600;">
          Showing {{ $transactions->count() }} Filtered Records
        </span>
      </div>

      <div style="overflow-x:auto;">
        <table class="report-table">
          <thead>
            <tr>
              <th style="width:11%;">Tracking No.</th>
              <th style="width:17%;">Resident Name & Purok</th>
              <th style="width:8%;">Gender</th>
              <th style="width:10%;">Civil Status</th>
              <th style="width:17%;">Certificate / Clearance</th>
              <th style="width:11%;">Date Filed</th>
              <th style="width:11%;">Date Issued</th>
              <th style="width:8%; text-align:center;">Status</th>
              <th style="width:7%; text-align:right;">Amount (PHP)</th>
            </tr>
          </thead>
          <tbody>
            @forelse($transactions as $row)
              <tr>
                <td>
                  <code style="font-size:11px; font-weight:700; background:#f1f5f9; padding:2px 5px; border-radius:4px; color:#1e293b;">
                    {{ $row->tracking_number }}
                  </code>
                </td>
                <td>
                  <div style="font-weight:700; color:#111827;">{{ $row->resident ? $row->resident->full_name : 'N/A' }}</div>
                  @if($row->resident && $row->resident->purok)
                    <div style="font-size:10.5px; color:#6b7280;"><i class="fas fa-location-dot" style="font-size:10px;"></i> {{ $row->resident->purok }}</div>
                  @endif
                </td>
                <td>{{ $row->resident->gender ?? 'N/A' }}</td>
                <td>{{ $row->resident->civil_status ?? 'N/A' }}</td>
                <td>
                  <div style="font-weight:600;">{{ $row->certificate ? $row->certificate->name : 'N/A' }}</div>
                  @if($row->purpose)
                    <div style="font-size:10.5px; color:#6b7280; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:220px;" title="{{ $row->purpose }}">
                      Purpose: {{ $row->purpose }}
                    </div>
                  @endif
                </td>
                <td>
                  {{ $row->requested_at ? \Carbon\Carbon::parse($row->requested_at)->format('M d, Y') : '—' }}
                  <div style="font-size:10px; color:#9ca3af;">{{ $row->requested_at ? \Carbon\Carbon::parse($row->requested_at)->format('h:i A') : '' }}</div>
                </td>
                <td>
                  @if($row->released_at)
                    <span style="font-weight:600; color:#047857;">
                      {{ \Carbon\Carbon::parse($row->released_at)->format('M d, Y') }}
                    </span>
                    <div style="font-size:10px; color:#9ca3af;">{{ \Carbon\Carbon::parse($row->released_at)->format('h:i A') }}</div>
                  @else
                    <span style="color:#9ca3af; font-style:italic;">Not Released</span>
                  @endif
                </td>
                <td style="text-align:center;">
                  @php
                    $statusStyles = [
                      'released'   => 'background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;',
                      'approved'   => 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;',
                      'processing' => 'background:#f0fdfa; color:#0d9488; border:1px solid #99f6e4;',
                      'pending'    => 'background:#fffbeb; color:#b45309; border:1px solid #fde68a;',
                      'rejected'   => 'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;',
                    ];
                  @endphp
                  <span style="display:inline-block; padding:3px 7px; border-radius:12px; font-size:10.5px; font-weight:700; text-transform:uppercase; {{ $statusStyles[$row->status] ?? 'background:#f1f5f9; color:#475569;' }}">
                    {{ ucfirst($row->status) }}
                  </span>
                </td>
                <td style="text-align:right;">
                  @php
                    $isPaid = $row->payment && $row->payment->payment_status === 'paid';
                    $amount = $row->payment ? $row->payment->amount : ($row->certificate->fee ?? 0);
                  @endphp
                  <div style="font-weight:700; font-family:monospace; color:{{ $isPaid ? '#111827' : '#9ca3af' }};">
                    ₱{{ number_format($amount, 2) }}
                  </div>
                  <span style="font-size:10px; font-weight:600; color:{{ $isPaid ? '#047857' : '#dc2626' }};">
                    {{ $isPaid ? 'PAID (' . strtoupper($row->payment->payment_method ?? 'CASH') . ')' : 'UNPAID' }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" style="text-align:center; padding:24px; color:#6b7280;">
                  No matching transaction or issuance records found for the selected filter parameters.
                </td>
              </tr>
            @endforelse
          </tbody>
          @if($transactions->isNotEmpty())
            <tfoot>
              <tr>
                <td colspan="8" style="text-transform:uppercase;">
                  TOTAL FILTERED TRANSACTIONS COLLECTION (PAID)
                </td>
                <td style="text-align:right; color:#7c3aed; font-size:13px; font-weight:800;">
                  ₱{{ number_format($filteredCollections, 2) }}
                </td>
              </tr>
            </tfoot>
          @endif
        </table>
      </div>
    </section>

    {{-- Section 3: Resident Demographics Summary --}}
    <section class="report-section">
      <div class="section-title">
        <span><i class="fas fa-users" style="color:#4f46e5; margin-right:6px;"></i> Community Registry & Special Demographics</span>
        @if($purokFilter)
          <span class="badge" style="background:#fef3c7; color:#92400e; padding:4px 8px; border-radius:6px; font-weight:600;">
            Filtered by: {{ $purokFilter }}
          </span>
        @endif
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
        {{-- Special Classifications --}}
        <div>
          <table class="report-table">
            <thead>
              <tr>
                <th>Classification</th>
                <th style="text-align:right;">Count</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="font-weight:600;">Total Registered Residents</td>
                <td style="text-align:right; font-weight:700;">{{ number_format($demographics['total_registered']) }}</td>
              </tr>
              <tr>
                <td>PWD (Persons with Disabilities)</td>
                <td style="text-align:right; font-weight:600; color:#7c3aed;">{{ number_format($demographics['total_pwd']) }}</td>
              </tr>
              <tr>
                <td>Senior Citizens (60+ yrs)</td>
                <td style="text-align:right; font-weight:600; color:#d97706;">{{ number_format($demographics['total_senior']) }}</td>
              </tr>
              <tr>
                <td>Solo / Single Parents</td>
                <td style="text-align:right; font-weight:600; color:#0d9488;">{{ number_format($demographics['total_single_parent']) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        {{-- By Gender --}}
        <div>
          <table class="report-table">
            <thead>
              <tr>
                <th>Gender Distribution</th>
                <th style="text-align:right;">Count</th>
              </tr>
            </thead>
            <tbody>
              @foreach($demographics['gender'] as $gender => $cnt)
                <tr>
                  <td>{{ ucfirst($gender) }}</td>
                  <td style="text-align:right; font-weight:600;">{{ number_format($cnt) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- By Civil Status --}}
        <div>
          <table class="report-table">
            <thead>
              <tr>
                <th>Civil Status</th>
                <th style="text-align:right;">Count</th>
              </tr>
            </thead>
            <tbody>
              @foreach($demographics['civil_status'] as $cstatus => $cnt)
                <tr>
                  <td>{{ ucfirst($cstatus) }}</td>
                  <td style="text-align:right; font-weight:600;">{{ number_format($cnt) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </section>

    {{-- Official Signatories --}}
    <section class="signatures-grid">
      <div class="signature-block">
        <span style="font-size:11px; text-transform:uppercase; color:#6b7280; font-weight:600; letter-spacing:0.4px;">
          Prepared and Certified By:
        </span>
        <div class="signature-line"></div>
        <div class="signature-name">{{ strtoupper(Auth::user()->resident ? Auth::user()->resident->full_name : Auth::user()->username) }}</div>
        <div class="signature-role">Barangay Records Officer / Administrator</div>
        <div style="font-size:10.5px; color:#9ca3af; margin-top:2px;">Date: {{ now()->format('F d, Y') }}</div>
      </div>

      <div class="signature-block">
        <span style="font-size:11px; text-transform:uppercase; color:#6b7280; font-weight:600; letter-spacing:0.4px;">
          Attested and Approved By:
        </span>
        <div class="signature-line"></div>
        <div class="signature-name">{{ strtoupper($captainName ?? 'PUNONG BARANGAY') }}</div>
        <div class="signature-role">Punong Barangay</div>
        <div style="font-size:10.5px; color:#9ca3af; margin-top:2px;">{{ barangay_label() }}</div>
      </div>
    </section>

  </div>

</div>
@endsection
