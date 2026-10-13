@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
  /* ── Viewport Lock: Completely non-scrollable dashboard ── */
  html, body {
    overflow: hidden !important;
    height: 100% !important;
    max-height: 100vh !important;
  }
  .wrapper {
    height: 100vh !important;
    max-height: 100vh !important;
    overflow: hidden !important;
  }
  .sidebar {
    height: 100vh !important;
    max-height: 100vh !important;
    overflow-y: auto !important;
  }
  .main-content {
    height: 100vh !important;
    max-height: 100vh !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
  }
  .topbar {
    flex-shrink: 0 !important;
    min-height: 48px !important;
    height: 48px !important;
    padding: 3px 16px !important;
  }
  #flash-message {
    margin: 4px 10px 0 !important;
    flex-shrink: 0 !important;
  }
  #flash-message .alert {
    padding: 5px 12px !important;
    font-size: 11.5px !important;
    margin-bottom: 0 !important;
  }

  /* ── Override page-content for dashboard only ── */
  .page-content {
    padding: 6px 10px !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 6px !important;
    box-sizing: border-box !important;
    flex: 1 !important;
    height: calc(100vh - 48px) !important;
    max-height: calc(100vh - 48px) !important;
  }

  /* ── Row 1: KPI Cards ── */
  .db-kpi-row {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
  }
  .db-kpi-card {
    flex: 1;
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 6px 10px;
    display: flex;
    flex-direction: column;
    gap: 1px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    min-width: 0;
  }
  .db-kpi-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 2.5px;
    background: var(--kc, #e5e7eb);
  }
  .db-kpi-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1px; }
  .db-kpi-icon {
    width: 26px; height: 26px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px;
    background: var(--ki, #f1f5f9);
    color: var(--kc, #64748b);
  }
  .db-kpi-badge { font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 20px; white-space: nowrap; }
  .db-kpi-badge.up     { background:#f0fdf4; color:#15803d; }
  .db-kpi-badge.down   { background:#fef2f2; color:#b91c1c; }
  .db-kpi-badge.neutral{ background:#f1f5f9; color:#64748b; }
  .db-kpi-val  { font-size: 17px; font-weight: 800; color: #111; line-height: 1.1; }
  .db-kpi-lbl  { font-size: 9px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing:.3px; }

  /* ── Row 2: Demographics Strip ── */
  .db-demo-row {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
  }
  .db-demo-card {
    flex: 1;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-left: 3px solid var(--dc, #e5e7eb);
    border-radius: 7px;
    padding: 5px 9px;
    display: flex;
    align-items: center;
    gap: 7px;
    text-decoration: none;
    transition: box-shadow .15s, transform .15s;
    min-width: 0;
  }
  .db-demo-card:hover { box-shadow: 0 3px 8px rgba(0,0,0,.06); transform: translateY(-1px); }
  .db-demo-icon {
    width: 26px; height: 26px; border-radius: 6px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px;
    background: var(--di, #f1f5f9);
    color: var(--dc, #64748b);
  }
  .db-demo-val { font-size: 15px; font-weight: 800; line-height: 1; }
  .db-demo-lbl { font-size: 9px; color: #6b7280; font-weight: 600; margin-top: 1px; }
  .db-demo-pct { font-size: 8.5px; font-weight: 700; padding: 1px 4px; border-radius: 8px; margin-top: 1px; display: inline-block; }

  /* ── Row 3: Main Grid ── */
  .db-main {
    flex: 1;
    display: grid;
    grid-template-columns: 1.1fr 1fr 0.9fr;
    gap: 6px;
    min-height: 0;
    overflow: hidden;
  }

  /* Columns */
  .db-col {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-height: 0;
    overflow: hidden;
  }

  /* Cards */
  .db-card {
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,.03);
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
    flex: 1;
  }
  .db-card-hd {
    padding: 5px 9px;
    border-bottom: 1px solid #f3f4f6;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: space-between;
  }
  .db-card-hd h5 { font-size: 11px; font-weight: 700; color: #1f2937; margin: 0; white-space: nowrap; }
  .db-card-hd span { font-size: 9px; color: #9ca3af; }
  .db-card-bd {
    padding: 5px 8px;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
  }

  /* Interactive Clickable Chart Cards */
  .db-chart-card {
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
  }
  .db-chart-card:hover {
    border-color: #93c5fd;
    box-shadow: 0 4px 12px rgba(37,99,235,0.08);
    transform: translateY(-1px);
  }
  .db-expand-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 9px;
    font-weight: 600;
    color: #64748b;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 1px 5px;
    transition: all .15s;
    user-select: none;
  }
  .db-chart-card:hover .db-expand-badge {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
  }

  /* Chart canvas wrappers */
  .db-chart {
    flex: 1;
    position: relative;
    min-height: 0;
    width: 100%;
  }
  .db-chart canvas { position: absolute; inset: 0; width: 100% !important; height: 100% !important; }

  /* Doughnut layout */
  .db-doughnut-wrap { display: flex; flex-direction: column; flex: 1; min-height: 0; gap: 3px; }
  .db-doughnut-wrap .db-chart { flex: 1; }
  .db-legend { display: flex; flex-wrap: wrap; gap: 2px 6px; flex-shrink: 0; }
  .db-legend-item { display: flex; align-items: center; gap: 3px; font-size: 9px; color: #374151; font-weight: 600; }
  .db-legend-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

  /* Table */
  .db-tbl-wrap { overflow-y: auto; flex: 1; min-height: 0; }
  .db-tbl { width: 100%; border-collapse: collapse; }
  .db-tbl th { background:#f9fafb; font-size:9px; font-weight:700; color:#6b7280; text-transform:uppercase; padding:4px 7px; border-bottom:1px solid #e5e7eb; position:sticky; top:0; z-index:1; }
  .db-tbl td { padding:4px 7px; font-size:10.5px; color:#1f2937; border-bottom:1px solid #f3f4f6; }
  .db-tbl tr:hover td { background:#f9fafb; cursor:pointer; }

  /* Activity */
  .db-activity { list-style:none; margin:0; padding:0; overflow-y:auto; flex:1; min-height:0; }
  .db-activity li { display:flex; gap:6px; align-items:flex-start; padding:3px 0; border-bottom:1px solid #f3f4f6; }
  .db-activity li:last-child { border-bottom:none; }
  .db-act-dot { width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:8.5px; flex-shrink:0; background:#eff6ff; color:#1d4ed8; }
  .db-act-txt { font-size:10px; font-weight:600; color:#1e293b; line-height:1.25; }
  .db-act-meta { font-size:8.5px; color:#9ca3af; margin-top:1px; }

  /* Status badges */
  .db-badge { display:inline-flex; align-items:center; font-size:9px; font-weight:700; padding:1px 5px; border-radius:20px; white-space:nowrap; }
  .db-badge.pending    { background:#fffbeb; color:#92400e; }
  .db-badge.processing { background:#eff6ff; color:#1d4ed8; }
  .db-badge.approved   { background:#f0fdf4; color:#15803d; }
  .db-badge.released   { background:#f0f9ff; color:#0369a1; }
  .db-badge.rejected   { background:#fef2f2; color:#b91c1c; }

  /* ── Chart Detail Modal Styles ── */
  .db-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 2500;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }
  .db-modal-overlay.show {
    display: flex;
  }
  .db-modal-box {
    background: #fff;
    border-radius: 14px;
    width: 100%;
    max-width: 860px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.35);
    animation: dbModalIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }
  @keyframes dbModalIn {
    from { opacity: 0; transform: scale(0.96) translateY(-8px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
  }
  .db-modal-hd {
    padding: 12px 18px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    flex-shrink: 0;
  }
  .db-modal-bd {
    padding: 14px 18px;
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #f8fafc;
  }
  .db-modal-ft {
    padding: 10px 18px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    flex-shrink: 0;
  }
  .db-modal-pill {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
  }
  .db-modal-pill-lbl {
    font-size: 10px;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
  }
  .db-modal-pill-val {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
    margin-top: 2px;
  }

  /* Responsive fallback for smaller screens / tablets */
  @media (max-width: 900px) {
    html, body, .wrapper, .main-content, .page-content {
      overflow-y: auto !important;
      height: auto !important;
      max-height: none !important;
    }
    .db-kpi-row, .db-demo-row { flex-wrap: wrap; }
    .db-main { grid-template-columns: 1fr; height: auto; }
  }
</style>
@endsection

@section('content')

@php
  $totalRes   = max($stats['residents'] ?? 1, 1);
  $pwdPct     = round(($demographics['pwd'] ?? 0) / $totalRes * 100, 1);
  $seniorPct  = round(($demographics['senior'] ?? 0) / $totalRes * 100, 1);
  $spPct      = round(($demographics['single_parent'] ?? 0) / $totalRes * 100, 1);
  $maleCount  = $demographics['gender']['Male']   ?? 0;
  $femaleCount= $demographics['gender']['Female'] ?? 0;
  $malePct    = round($maleCount   / $totalRes * 100, 1);
  $femalePct  = round($femaleCount / $totalRes * 100, 1);
@endphp

{{-- ── ROW 1: KPI Cards ── --}}
<div class="db-kpi-row">

  <div class="db-kpi-card" style="--kc:#1d4ed8;--ki:#eff6ff;">
    <div class="db-kpi-top">
      <div class="db-kpi-icon"><i class="fas fa-users"></i></div>
      <span class="db-kpi-badge neutral">Total</span>
    </div>
    <div class="db-kpi-val">{{ number_format($stats['residents'] ?? 0) }}</div>
    <div class="db-kpi-lbl">Active Residents</div>
  </div>

  <div class="db-kpi-card" style="--kc:#d97706;--ki:#fffbeb;">
    <div class="db-kpi-top">
      <div class="db-kpi-icon"><i class="fas fa-clock"></i></div>
      <span class="db-kpi-badge {{ ($stats['pending'] ?? 0) > 10 ? 'down' : 'neutral' }}">{{ ($stats['pending'] ?? 0) > 0 ? 'Action needed' : 'Clear' }}</span>
    </div>
    <div class="db-kpi-val">{{ number_format($stats['pending'] ?? 0) }}</div>
    <div class="db-kpi-lbl">Pending Requests</div>
  </div>

  <div class="db-kpi-card" style="--kc:#0d9488;--ki:#f0fdfa;">
    <div class="db-kpi-top">
      <div class="db-kpi-icon"><i class="fas fa-check-circle"></i></div>
      <span class="db-kpi-badge neutral">{{ $stats['completion_rate'] ?? 0 }}%</span>
    </div>
    <div class="db-kpi-val">{{ number_format($stats['released'] ?? 0) }}</div>
    <div class="db-kpi-lbl">Released Docs</div>
  </div>

  <div class="db-kpi-card" style="--kc:#7c3aed;--ki:#f5f3ff;">
    <div class="db-kpi-top">
      <div class="db-kpi-icon"><i class="fas fa-peso-sign"></i></div>
      @if(($stats['revenue_trend'] ?? 0) >= 0)
        <span class="db-kpi-badge up"><i class="fas fa-arrow-up"></i> {{ $stats['revenue_trend'] ?? 0 }}%</span>
      @else
        <span class="db-kpi-badge down"><i class="fas fa-arrow-down"></i> {{ abs($stats['revenue_trend'] ?? 0) }}%</span>
      @endif
    </div>
    <div class="db-kpi-val" style="font-size:15px;">₱{{ number_format($stats['month_revenue'] ?? $stats['revenue'] ?? 0, 0) }}</div>
    <div class="db-kpi-lbl">Revenue (Month)</div>
  </div>

  <div class="db-kpi-card" style="--kc:#b91c1c;--ki:#fef2f2;">
    <div class="db-kpi-top">
      <div class="db-kpi-icon"><i class="fas fa-file-alt"></i></div>
      <span class="db-kpi-badge neutral">{{ now()->format('M') }}</span>
    </div>
    <div class="db-kpi-val">{{ number_format($stats['month_requests'] ?? $stats['total_req'] ?? 0) }}</div>
    <div class="db-kpi-lbl">New Requests</div>
  </div>

</div>

{{-- ── ROW 2: Demographics Strip ── --}}
<div class="db-demo-row">

  <a href="{{ route('admin.residents', ['classification' => 'pwd']) }}" class="db-demo-card" style="--dc:#7c3aed;--di:#f5f3ff;">
    <div class="db-demo-icon"><i class="fas fa-wheelchair"></i></div>
    <div style="min-width:0;">
      <div class="db-demo-val" style="color:#7c3aed;">{{ number_format($demographics['pwd'] ?? 0) }}</div>
      <div class="db-demo-lbl">PWD Residents</div>
      <span class="db-demo-pct" style="background:#f5f3ff;color:#7c3aed;">{{ $pwdPct }}%</span>
    </div>
  </a>

  <a href="{{ route('admin.residents', ['classification' => 'senior']) }}" class="db-demo-card" style="--dc:#d97706;--di:#fffbeb;">
    <div class="db-demo-icon"><i class="fas fa-user-clock"></i></div>
    <div style="min-width:0;">
      <div class="db-demo-val" style="color:#d97706;">{{ number_format($demographics['senior'] ?? 0) }}</div>
      <div class="db-demo-lbl">Senior Citizens</div>
      <span class="db-demo-pct" style="background:#fffbeb;color:#d97706;">{{ $seniorPct }}%</span>
    </div>
  </a>

  <a href="{{ route('admin.residents', ['classification' => 'single_parent']) }}" class="db-demo-card" style="--dc:#0d9488;--di:#f0fdfa;">
    <div class="db-demo-icon"><i class="fas fa-child"></i></div>
    <div style="min-width:0;">
      <div class="db-demo-val" style="color:#0d9488;">{{ number_format($demographics['single_parent'] ?? 0) }}</div>
      <div class="db-demo-lbl">Single Parents</div>
      <span class="db-demo-pct" style="background:#f0fdfa;color:#0d9488;">{{ $spPct }}%</span>
    </div>
  </a>

  <div class="db-demo-card" style="--dc:#1d4ed8;--di:#eff6ff; cursor:default;">
    <div class="db-demo-icon"><i class="fas fa-venus-mars"></i></div>
    <div style="min-width:0;flex:1;">
      <div class="db-demo-lbl" style="margin-bottom:2px;">Gender Breakdown</div>
      <div style="display:flex;gap:7px;font-size:9.5px;font-weight:700;">
        <span style="color:#1d4ed8;"><i class="fas fa-male"></i> {{ $maleCount }} <span style="color:#9ca3af;font-weight:500;">({{ $malePct }}%)</span></span>
        <span style="color:#db2777;"><i class="fas fa-female"></i> {{ $femaleCount }} <span style="color:#9ca3af;font-weight:500;">({{ $femalePct }}%)</span></span>
      </div>
      <div style="display:flex;height:3.5px;border-radius:3px;overflow:hidden;margin-top:3px;gap:1px;">
        <div style="width:{{ $malePct }}%;background:#1d4ed8;"></div>
        <div style="width:{{ $femalePct }}%;background:#db2777;"></div>
      </div>
    </div>
  </div>

  <a href="{{ route('admin.reports') }}" class="db-demo-card" style="--dc:#059669;--di:#f0fdf4;">
    <div class="db-demo-icon"><i class="fas fa-coins"></i></div>
    <div style="min-width:0;">
      <div class="db-demo-val" style="color:#059669;font-size:14px;">₱{{ number_format($stats['revenue'] ?? 0, 0) }}</div>
      <div class="db-demo-lbl">Total Collections</div>
      <span class="db-demo-pct" style="background:#f0fdf4;color:#059669;">All time</span>
    </div>
  </a>

</div>

{{-- ── ROW 3: Main Charts Grid ── --}}
<div class="db-main">

  {{-- Column A: Bar + Line --}}
  <div class="db-col">
    <!-- Chart 1: Monthly Requests vs Released -->
    <div class="db-card db-chart-card" data-chart="monthly" title="Click to view enlarged chart & breakdown">
      <div class="db-card-hd">
        <h5><i class="fas fa-chart-bar" style="color:#b91c1c;margin-right:5px;"></i>Monthly Requests vs Released</h5>
        <span class="db-expand-badge"><i class="fas fa-up-right-and-down-left-from-center"></i> Expand</span>
      </div>
      <div class="db-card-bd">
        <div class="db-chart"><canvas id="monthlyChart"></canvas></div>
      </div>
    </div>

    <!-- Chart 2: Monthly Collections -->
    <div class="db-card db-chart-card" data-chart="revenue" title="Click to view enlarged chart & breakdown">
      <div class="db-card-hd">
        <h5><i class="fas fa-chart-line" style="color:#7c3aed;margin-right:5px;"></i>Monthly Collections (PHP)</h5>
        <span class="db-expand-badge"><i class="fas fa-up-right-and-down-left-from-center"></i> Expand</span>
      </div>
      <div class="db-card-bd">
        <div class="db-chart"><canvas id="revenueChart"></canvas></div>
      </div>
    </div>
  </div>

  {{-- Column B: Doughnut + Age Group --}}
  <div class="db-col">
    <!-- Chart 3: Requests by Type -->
    <div class="db-card db-chart-card" data-chart="type" title="Click to view enlarged chart & breakdown">
      <div class="db-card-hd">
        <h5><i class="fas fa-chart-pie" style="color:#0d9488;margin-right:5px;"></i>Requests by Type</h5>
        <span class="db-expand-badge"><i class="fas fa-up-right-and-down-left-from-center"></i> Expand</span>
      </div>
      <div class="db-card-bd">
        <div class="db-doughnut-wrap">
          <div class="db-chart"><canvas id="typeChart"></canvas></div>
          <div class="db-legend" id="typeLegend"></div>
        </div>
      </div>
    </div>

    <!-- Chart 4: Age Group Distribution -->
    <div class="db-card db-chart-card" data-chart="age" title="Click to view enlarged chart & breakdown">
      <div class="db-card-hd">
        <h5><i class="fas fa-users" style="color:#d97706;margin-right:5px;"></i>Age Group Distribution</h5>
        <span class="db-expand-badge"><i class="fas fa-up-right-and-down-left-from-center"></i> Expand</span>
      </div>
      <div class="db-card-bd">
        <div class="db-chart"><canvas id="ageChart"></canvas></div>
      </div>
    </div>
  </div>

  {{-- Column C: Recent Requests + Activity --}}
  <div class="db-col">
    <div class="db-card" style="flex:1.35;">
      <div class="db-card-hd">
        <h5><i class="fas fa-list" style="color:#b91c1c;margin-right:5px;"></i>Recent Requests</h5>
        <a href="{{ route('admin.requests') }}" style="font-size:9.5px;color:#1d4ed8;font-weight:600;text-decoration:none;">View All →</a>
      </div>
      <div class="db-tbl-wrap">
        <table class="db-tbl">
          <thead>
            <tr><th>#</th><th>Resident</th><th>Document</th><th>Status</th></tr>
          </thead>
          <tbody>
            @forelse ($recentRequests->take(7) as $req)
              <tr onclick="window.location='{{ route('admin.requests', ['view' => $req->tracking_number]) }}'">
                <td><code style="font-size:9px;">{{ substr($req->tracking_number,-6) }}</code></td>
                <td style="font-weight:600;max-width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $req->resident?->full_name }}</td>
                <td style="color:#6b7280;max-width:70px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $req->certificate?->name }}</td>
                <td><span class="db-badge {{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
              </tr>
            @empty
              <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:12px;">No requests yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="db-card" style="flex:1;">
      <div class="db-card-hd">
        <h5><i class="fas fa-history" style="color:#1d4ed8;margin-right:5px;"></i>Recent Activity</h5>
      </div>
      <div class="db-card-bd">
        <ul class="db-activity">
          @forelse($recentActivity ?? [] as $log)
            <li>
              <div class="db-act-dot"><i class="fas fa-bolt"></i></div>
              <div>
                <div class="db-act-txt">{{ Str::limit($log->description ?: $log->action, 46) }}</div>
                <div class="db-act-meta">{{ explode('@', $log->user?->email)[0] ?? 'System' }} · {{ $log->created_at->diffForHumans(null, true, true) }}</div>
              </div>
            </li>
          @empty
            <li><div class="db-act-meta" style="padding:4px 0;">No recent activity.</div></li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>

</div>

{{-- ── Enlarged Chart Modal ── --}}
<div class="db-modal-overlay" id="chartModal">
  <div class="db-modal-box">
    <!-- Modal Header -->
    <div class="db-modal-hd">
      <div style="display:flex; align-items:center; gap:10px;">
        <div id="chartModalIcon" style="width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:15px; background:#eff6ff; color:#1d4ed8;">
          <i class="fas fa-chart-bar"></i>
        </div>
        <div>
          <h5 id="chartModalTitle" style="font-size:14.5px; font-weight:800; color:#111827; margin:0;">Chart Title</h5>
          <span id="chartModalSubtitle" style="font-size:11px; color:#6b7280; font-weight:500;">Chart Subtitle</span>
        </div>
      </div>
      <button type="button" onclick="closeChartModal()" style="background:#f1f5f9; border:none; width:28px; height:28px; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#64748b; font-size:15px; cursor:pointer; transition:all .15s;" title="Close modal">&times;</button>
    </div>

    <!-- Modal Body -->
    <div class="db-modal-bd">
      <!-- Dynamic Key Metrics Strip -->
      <div id="chartModalStats" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:8px;"></div>

      <!-- High-res Canvas Container -->
      <div style="background:#fff; border-radius:10px; border:1px solid #e5e7eb; padding:16px; position:relative; height:330px; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
        <canvas id="modalChartCanvas"></canvas>
      </div>

      <!-- Detailed Breakdown Table -->
      <div id="chartModalBreakdown" style="background:#fff; border-radius:10px; border:1px solid #e5e7eb; padding:12px; overflow-x:auto;"></div>
    </div>

    <!-- Modal Footer -->
    <div class="db-modal-ft">
      <a href="{{ route('admin.reports') }}" class="btn btn-outline-secondary btn-sm" style="font-size:11.5px; display:inline-flex; align-items:center; gap:6px; padding:5px 12px; text-decoration:none;">
        <i class="fas fa-file-invoice"></i> View Official Reports & Export
      </a>
      <button type="button" class="btn btn-secondary btn-sm" onclick="closeChartModal()" style="padding:5px 16px; font-size:11.5px; font-weight:600;">
        Close
      </button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // ── Raw Data Sources ──
  const monthlyLabels = @json($monthly['labels']   ?? []);
  const monthlyData   = @json($monthly['data']     ?? []);
  const monthlyRel    = @json($monthly['released'] ?? []);
  const typeLabels    = @json($byType['labels']    ?? []);
  const typeData      = @json($byType['data']      ?? []);
  const revLabels     = @json($revenueChart['labels'] ?? []);
  const revData       = @json($revenueChart['data']   ?? []);
  const ageLabels     = @json(array_keys($demographics['age_groups']   ?? []));
  const ageData       = @json(array_values($demographics['age_groups'] ?? []));

  Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
  Chart.defaults.font.size   = 10;
  Chart.defaults.color       = '#6b7280';

  const palette = ['#b91c1c','#0d9488','#7c3aed','#d97706','#0ea5e9','#db2777','#059669','#4f46e5'];

  // ── 1. Bar: Monthly Requests vs Released ──
  new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
      labels: monthlyLabels.length ? monthlyLabels : ['No Data'],
      datasets: [
        { label:'Received', data: monthlyData.length ? monthlyData : [0], backgroundColor:'rgba(185,28,28,0.85)', borderRadius:3, barPercentage:.65 },
        { label:'Released', data: monthlyRel.length  ? monthlyRel  : [0], backgroundColor:'rgba(13,148,136,0.85)',  borderRadius:3, barPercentage:.65 }
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ position:'top', labels:{ boxWidth:8, font:{size:9.5,weight:'bold'}, padding:5 } }, tooltip:{ padding:7 } },
      scales:{
        y:{ beginAtZero:true, grid:{color:'#f3f4f6'}, ticks:{stepSize:1,font:{size:9}} },
        x:{ grid:{display:false}, ticks:{font:{size:9,weight:'600'}} }
      }
    }
  });

  // ── 2. Line: Monthly Collections ──
  new Chart(document.getElementById('revenueChart'), {
    type:'line',
    data:{
      labels: revLabels.length ? revLabels : ['No Data'],
      datasets:[{ label:'Collections', data: revData.length ? revData : [0],
        borderColor:'#7c3aed', backgroundColor:'rgba(124,58,237,.1)',
        fill:true, tension:0.4, pointRadius:3.5, pointHoverRadius:5, borderWidth:2.2 }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:false}, tooltip:{ padding:7, callbacks:{ label:ctx=>'₱'+Number(ctx.parsed.y).toLocaleString() } } },
      scales:{
        y:{ beginAtZero:true, grid:{color:'#f3f4f6'}, ticks:{font:{size:9}, callback:v=>'₱'+Number(v).toLocaleString()} },
        x:{ grid:{display:false}, ticks:{font:{size:9,weight:'600'}} }
      }
    }
  });

  // ── 3. Doughnut: Requests by Type ──
  const typeChart = new Chart(document.getElementById('typeChart'), {
    type:'doughnut',
    data:{
      labels: typeLabels.length ? typeLabels : ['No Data'],
      datasets:[{ data: typeData.length ? typeData : [1], backgroundColor:palette, borderWidth:2, borderColor:'#fff' }]
    },
    options:{
      responsive:true, maintainAspectRatio:false, cutout:'60%',
      plugins:{ legend:{display:false}, tooltip:{padding:7} }
    }
  });
  // Custom legend
  const legend = document.getElementById('typeLegend');
  if (typeLabels.length && legend) {
    typeLabels.forEach((lbl,i) => {
      const el = document.createElement('div');
      el.className = 'db-legend-item';
      el.innerHTML = `<span class="db-legend-dot" style="background:${palette[i%palette.length]}"></span>${lbl} (${typeData[i]??0})`;
      legend.appendChild(el);
    });
  }

  // ── 4. Horizontal Bar: Age Groups ──
  new Chart(document.getElementById('ageChart'), {
    type:'bar',
    data:{
      labels: ageLabels.length ? ageLabels : ['No Data'],
      datasets:[{ label:'Residents', data: ageData.length ? ageData : [0],
        backgroundColor:['#60a5fa','#34d399','#f59e0b','#f87171','#c084fc'],
        borderRadius:3, barPercentage:0.7 }]
    },
    options:{
      responsive:true, maintainAspectRatio:false, indexAxis:'y',
      plugins:{ legend:{display:false}, tooltip:{padding:7} },
      scales:{
        x:{ beginAtZero:true, grid:{color:'#f3f4f6'}, ticks:{font:{size:9}} },
        y:{ grid:{display:false}, ticks:{font:{size:9,weight:'600'}} }
      }
    }
  });

  // ── CHART MODAL ENLARGEMENT SYSTEM ──
  let modalChartInstance = null;

  function openChartModal(chartKey) {
    const modal = document.getElementById('chartModal');
    const iconEl = document.getElementById('chartModalIcon');
    const titleEl = document.getElementById('chartModalTitle');
    const subtitleEl = document.getElementById('chartModalSubtitle');
    const statsEl = document.getElementById('chartModalStats');
    const breakdownEl = document.getElementById('chartModalBreakdown');
    const canvas = document.getElementById('modalChartCanvas');

    if (!modal || !canvas) return;

    // Clean up previous modal chart instance
    if (modalChartInstance) {
      modalChartInstance.destroy();
      modalChartInstance = null;
    }

    if (chartKey === 'monthly') {
      iconEl.innerHTML = '<i class="fas fa-chart-bar"></i>';
      iconEl.style.background = '#fef2f2';
      iconEl.style.color = '#b91c1c';
      titleEl.textContent = 'Monthly Requests vs Released Documents';
      subtitleEl.textContent = 'Comparative volume of incoming resident requests vs released documents over the last 6 months';

      const totalRec = monthlyData.reduce((a, b) => a + Number(b), 0);
      const totalRel = monthlyRel.reduce((a, b) => a + Number(b), 0);
      const rate = totalRec > 0 ? Math.round((totalRel / totalRec) * 100) : 0;
      const maxRec = monthlyData.length ? Math.max(...monthlyData) : 0;
      const peakIdx = monthlyData.indexOf(maxRec);
      const peakMonth = peakIdx >= 0 && monthlyLabels[peakIdx] ? monthlyLabels[peakIdx] : 'N/A';

      statsEl.innerHTML = `
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Total Received</div>
          <div class="db-modal-pill-val" style="color:#b91c1c;">${totalRec.toLocaleString()}</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Total Released</div>
          <div class="db-modal-pill-val" style="color:#0d9488;">${totalRel.toLocaleString()}</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Overall Issuance Rate</div>
          <div class="db-modal-pill-val" style="color:#1d4ed8;">${rate}%</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Peak Volume Month</div>
          <div class="db-modal-pill-val" style="color:#d97706;">${peakMonth}</div>
        </div>
      `;

      let tableRows = '';
      monthlyLabels.forEach((m, i) => {
        const rec = monthlyData[i] || 0;
        const rel = monthlyRel[i] || 0;
        const pct = rec > 0 ? Math.round((rel / rec) * 100) : 0;
        tableRows += `
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:6px 10px; font-weight:700; color:#1e293b;">${m}</td>
            <td style="padding:6px 10px; font-weight:600; color:#b91c1c;">${rec.toLocaleString()}</td>
            <td style="padding:6px 10px; font-weight:600; color:#0d9488;">${rel.toLocaleString()}</td>
            <td style="padding:6px 10px;">
              <div style="display:flex; align-items:center; gap:6px;">
                <div style="flex:1; max-width:90px; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden;">
                  <div style="width:${pct}%; height:100%; background:#0d9488;"></div>
                </div>
                <span style="font-size:11px; font-weight:700; color:#475569;">${pct}%</span>
              </div>
            </td>
          </tr>
        `;
      });

      breakdownEl.innerHTML = `
        <h6 style="font-size:11.5px; font-weight:800; color:#334155; margin:0 0 8px;">Monthly Breakdown</h6>
        <table style="width:100%; border-collapse:collapse; font-size:11px;">
          <thead>
            <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; text-align:left; color:#64748b; font-size:10px; text-transform:uppercase;">
              <th style="padding:6px 10px;">Month</th>
              <th style="padding:6px 10px;">Requests Received</th>
              <th style="padding:6px 10px;">Documents Released</th>
              <th style="padding:6px 10px;">Fulfillment Rate</th>
            </tr>
          </thead>
          <tbody>${tableRows}</tbody>
        </table>
      `;

      modalChartInstance = new Chart(canvas, {
        type: 'bar',
        data: {
          labels: monthlyLabels,
          datasets: [
            { label:'Requests Received', data: monthlyData, backgroundColor:'rgba(185,28,28,0.85)', borderRadius:4, barPercentage:.6 },
            { label:'Documents Released', data: monthlyRel,  backgroundColor:'rgba(13,148,136,0.85)',  borderRadius:4, barPercentage:.6 }
          ]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: {
            legend: { position:'top', labels:{ font:{size:11.5,weight:'bold'}, padding:12 } },
            tooltip: { padding:10, titleFont:{size:12}, bodyFont:{size:12} }
          },
          scales: {
            y: { beginAtZero:true, grid:{color:'#f1f5f9'}, ticks:{font:{size:11}} },
            x: { grid:{display:false}, ticks:{font:{size:11,weight:'600'}} }
          }
        }
      });

    } else if (chartKey === 'revenue') {
      iconEl.innerHTML = '<i class="fas fa-chart-line"></i>';
      iconEl.style.background = '#f5f3ff';
      iconEl.style.color = '#7c3aed';
      titleEl.textContent = 'Monthly Collections & Revenue Trend';
      subtitleEl.textContent = 'Recorded payment revenue from barangay clearances and document issuances (PHP)';

      const totalRev = revData.reduce((a, b) => a + Number(b), 0);
      const avgRev = revData.length > 0 ? Math.round(totalRev / revData.length) : 0;
      const maxRev = revData.length ? Math.max(...revData) : 0;
      const peakIdx = revData.indexOf(maxRev);
      const peakMonth = peakIdx >= 0 && revLabels[peakIdx] ? revLabels[peakIdx] : 'N/A';

      statsEl.innerHTML = `
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">6-Month Total</div>
          <div class="db-modal-pill-val" style="color:#7c3aed;">₱${totalRev.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Monthly Average</div>
          <div class="db-modal-pill-val" style="color:#1d4ed8;">₱${avgRev.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Peak Revenue Month</div>
          <div class="db-modal-pill-val" style="color:#059669;">${peakMonth} (₱${maxRev.toLocaleString()})</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Active Months Tracked</div>
          <div class="db-modal-pill-val" style="color:#d97706;">${revLabels.length} Months</div>
        </div>
      `;

      let tableRows = '';
      revLabels.forEach((m, i) => {
        const amt = Number(revData[i] || 0);
        const share = totalRev > 0 ? ((amt / totalRev) * 100).toFixed(1) : 0;
        tableRows += `
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:6px 10px; font-weight:700; color:#1e293b;">${m}</td>
            <td style="padding:6px 10px; font-weight:700; color:#7c3aed;">₱${amt.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
            <td style="padding:6px 10px;">
              <div style="display:flex; align-items:center; gap:6px;">
                <div style="flex:1; max-width:90px; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden;">
                  <div style="width:${share}%; height:100%; background:#7c3aed;"></div>
                </div>
                <span style="font-size:11px; font-weight:700; color:#475569;">${share}%</span>
              </div>
            </td>
          </tr>
        `;
      });

      breakdownEl.innerHTML = `
        <h6 style="font-size:11.5px; font-weight:800; color:#334155; margin:0 0 8px;">Revenue Details by Month</h6>
        <table style="width:100%; border-collapse:collapse; font-size:11px;">
          <thead>
            <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; text-align:left; color:#64748b; font-size:10px; text-transform:uppercase;">
              <th style="padding:6px 10px;">Month</th>
              <th style="padding:6px 10px;">Collections (PHP)</th>
              <th style="padding:6px 10px;">Share of Total</th>
            </tr>
          </thead>
          <tbody>${tableRows}</tbody>
        </table>
      `;

      modalChartInstance = new Chart(canvas, {
        type: 'line',
        data: {
          labels: revLabels,
          datasets: [{
            label: 'Monthly Collections (PHP)',
            data: revData,
            borderColor: '#7c3aed',
            backgroundColor: 'rgba(124, 58, 237, 0.12)',
            fill: true,
            tension: 0.38,
            pointRadius: 5,
            pointHoverRadius: 8,
            borderWidth: 3
          }]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: {
            legend: { display:false },
            tooltip: {
              padding: 10,
              callbacks: { label: ctx => '₱' + Number(ctx.parsed.y).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) }
            }
          },
          scales: {
            y: { beginAtZero:true, grid:{color:'#f1f5f9'}, ticks:{font:{size:11}, callback:v=>'₱'+Number(v).toLocaleString()} },
            x: { grid:{display:false}, ticks:{font:{size:11,weight:'600'}} }
          }
        }
      });

    } else if (chartKey === 'type') {
      iconEl.innerHTML = '<i class="fas fa-chart-pie"></i>';
      iconEl.style.background = '#f0fdfa';
      iconEl.style.color = '#0d9488';
      titleEl.textContent = 'Document Requests by Certificate Type';
      subtitleEl.textContent = 'Distribution and resident demand across all clearance and certification categories';

      const totalReq = typeData.reduce((a, b) => a + Number(b), 0);
      const maxCount = typeData.length ? Math.max(...typeData) : 0;
      const topIdx = typeData.indexOf(maxCount);
      const topName = topIdx >= 0 && typeLabels[topIdx] ? typeLabels[topIdx] : 'None';
      const topShare = totalReq > 0 ? Math.round((maxCount / totalReq) * 100) : 0;

      statsEl.innerHTML = `
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Total Requests Filed</div>
          <div class="db-modal-pill-val" style="color:#0d9488;">${totalReq.toLocaleString()}</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Document Types</div>
          <div class="db-modal-pill-val" style="color:#1d4ed8;">${typeLabels.length} Types</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Most Requested Document</div>
          <div class="db-modal-pill-val" style="color:#b91c1c; font-size:14px;">${topName}</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Top Document Share</div>
          <div class="db-modal-pill-val" style="color:#7c3aed;">${topShare}%</div>
        </div>
      `;

      let tableRows = '';
      typeLabels.forEach((name, i) => {
        const count = Number(typeData[i] || 0);
        const share = totalReq > 0 ? ((count / totalReq) * 100).toFixed(1) : 0;
        const color = palette[i % palette.length];
        tableRows += `
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:6px 10px; font-weight:700; color:#1e293b;">
              <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:${color}; margin-right:6px;"></span>
              ${name}
            </td>
            <td style="padding:6px 10px; font-weight:700; color:#0f172a;">${count.toLocaleString()}</td>
            <td style="padding:6px 10px;">
              <div style="display:flex; align-items:center; gap:6px;">
                <div style="flex:1; max-width:90px; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden;">
                  <div style="width:${share}%; height:100%; background:${color};"></div>
                </div>
                <span style="font-size:11px; font-weight:700; color:#475569;">${share}%</span>
              </div>
            </td>
          </tr>
        `;
      });

      breakdownEl.innerHTML = `
        <h6 style="font-size:11.5px; font-weight:800; color:#334155; margin:0 0 8px;">Breakdown by Document Category</h6>
        <table style="width:100%; border-collapse:collapse; font-size:11px;">
          <thead>
            <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; text-align:left; color:#64748b; font-size:10px; text-transform:uppercase;">
              <th style="padding:6px 10px;">Certificate / Clearance Name</th>
              <th style="padding:6px 10px;">Request Volume</th>
              <th style="padding:6px 10px;">Proportion</th>
            </tr>
          </thead>
          <tbody>${tableRows}</tbody>
        </table>
      `;

      modalChartInstance = new Chart(canvas, {
        type: 'doughnut',
        data: {
          labels: typeLabels,
          datasets: [{ data: typeData, backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
          responsive: true, maintainAspectRatio: false, cutout: '55%',
          plugins: {
            legend: { position:'right', labels:{ boxWidth:11, font:{size:11,weight:'600'}, padding:10 } },
            tooltip: { padding:10, callbacks:{ label: ctx => `${ctx.label}: ${ctx.raw} requests` } }
          }
        }
      });

    } else if (chartKey === 'age') {
      iconEl.innerHTML = '<i class="fas fa-users"></i>';
      iconEl.style.background = '#fffbeb';
      iconEl.style.color = '#d97706';
      titleEl.textContent = 'Resident Age Group Demographics';
      subtitleEl.textContent = 'Distribution of active registered residents across age brackets in Barangay Pili';

      const totalPop = ageData.reduce((a, b) => a + Number(b), 0);
      const maxPop = ageData.length ? Math.max(...ageData) : 0;
      const topIdx = ageData.indexOf(maxPop);
      const topGroup = topIdx >= 0 && ageLabels[topIdx] ? ageLabels[topIdx] : 'N/A';
      const seniorPop = ageLabels.includes('Senior (60+)') ? (ageData[ageLabels.indexOf('Senior (60+)')] || 0) : 0;
      const seniorPct = totalPop > 0 ? ((seniorPop / totalPop) * 100).toFixed(1) : 0;

      statsEl.innerHTML = `
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Total Population</div>
          <div class="db-modal-pill-val" style="color:#0f172a;">${totalPop.toLocaleString()}</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Largest Age Bracket</div>
          <div class="db-modal-pill-val" style="color:#d97706; font-size:14px;">${topGroup}</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Senior Citizens (60+)</div>
          <div class="db-modal-pill-val" style="color:#b91c1c;">${seniorPop.toLocaleString()} (${seniorPct}%)</div>
        </div>
        <div class="db-modal-pill">
          <div class="db-modal-pill-lbl">Age Groups Tracked</div>
          <div class="db-modal-pill-val" style="color:#7c3aed;">${ageLabels.length} Brackets</div>
        </div>
      `;

      let tableRows = '';
      const ageColors = ['#60a5fa','#34d399','#f59e0b','#f87171','#c084fc'];
      ageLabels.forEach((group, i) => {
        const count = Number(ageData[i] || 0);
        const share = totalPop > 0 ? ((count / totalPop) * 100).toFixed(1) : 0;
        const color = ageColors[i % ageColors.length];
        tableRows += `
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:6px 10px; font-weight:700; color:#1e293b;">
              <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:${color}; margin-right:6px;"></span>
              ${group}
            </td>
            <td style="padding:6px 10px; font-weight:700; color:#0f172a;">${count.toLocaleString()}</td>
            <td style="padding:6px 10px;">
              <div style="display:flex; align-items:center; gap:6px;">
                <div style="flex:1; max-width:90px; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden;">
                  <div style="width:${share}%; height:100%; background:${color};"></div>
                </div>
                <span style="font-size:11px; font-weight:700; color:#475569;">${share}%</span>
              </div>
            </td>
          </tr>
        `;
      });

      breakdownEl.innerHTML = `
        <h6 style="font-size:11.5px; font-weight:800; color:#334155; margin:0 0 8px;">Age Bracket Breakdown</h6>
        <table style="width:100%; border-collapse:collapse; font-size:11px;">
          <thead>
            <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; text-align:left; color:#64748b; font-size:10px; text-transform:uppercase;">
              <th style="padding:6px 10px;">Age Group</th>
              <th style="padding:6px 10px;">Registered Residents</th>
              <th style="padding:6px 10px;">Percentage</th>
            </tr>
          </thead>
          <tbody>${tableRows}</tbody>
        </table>
      `;

      modalChartInstance = new Chart(canvas, {
        type: 'bar',
        data: {
          labels: ageLabels,
          datasets: [{
            label: 'Registered Residents',
            data: ageData,
            backgroundColor: ageColors,
            borderRadius: 5,
            barPercentage: 0.65
          }]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: {
            legend: { display:false },
            tooltip: { padding:10 }
          },
          scales: {
            y: { beginAtZero:true, grid:{color:'#f1f5f9'}, ticks:{font:{size:11}} },
            x: { grid:{display:false}, ticks:{font:{size:11,weight:'600'}} }
          }
        }
      });
    }

    // Show modal
    modal.classList.add('show');
  }

  function closeChartModal() {
    const modal = document.getElementById('chartModal');
    if (modal) modal.classList.remove('show');
    if (modalChartInstance) {
      modalChartInstance.destroy();
      modalChartInstance = null;
    }
  }

  // Attach click listener to all chart cards
  document.querySelectorAll('.db-chart-card').forEach(card => {
    card.addEventListener('click', function(e) {
      // Don't trigger if clicked on an actual link
      if (e.target.closest('a')) return;
      const chartKey = this.dataset.chart;
      if (chartKey) openChartModal(chartKey);
    });
  });

  // Close modal when clicking on overlay background
  const chartModalOverlay = document.getElementById('chartModal');
  if (chartModalOverlay) {
    chartModalOverlay.addEventListener('click', function(e) {
      if (e.target === this) closeChartModal();
    });
  }

  // Close on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeChartModal();
  });
</script>
@endsection
