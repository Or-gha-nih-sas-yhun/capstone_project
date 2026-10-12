@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
  /* ── Reset & Base ── */
  html, body { overflow: hidden; }

  .page-content {
    padding: 10px 14px;
    height: calc(100vh - var(--topbar-h, 56px));
    display: grid;
    grid-template-rows: auto auto 1fr;
    gap: 10px;
    box-sizing: border-box;
    overflow: hidden;
  }

  /* ── Row 1: KPI Cards ── */
  .kpi-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
  }
  .kpi-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    min-width: 0;
  }
  .kpi-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--kpi-color, #e5e7eb);
  }
  .kpi-top { display: flex; justify-content: space-between; align-items: center; }
  .kpi-icon {
    width: 32px; height: 32px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    background: var(--kpi-bg, #f1f5f9);
    color: var(--kpi-color, #64748b);
    flex-shrink: 0;
  }
  .kpi-badge { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; white-space: nowrap; }
  .kpi-badge.up     { background: #f0fdf4; color: #15803d; }
  .kpi-badge.down   { background: #fef2f2; color: #b91c1c; }
  .kpi-badge.neutral{ background: #f1f5f9; color: #64748b; }
  .kpi-value { font-size: 22px; font-weight: 800; color: #111; line-height: 1.1; word-break: break-all; }
  .kpi-label { font-size: 10px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }

  /* ── Row 2: Demographics Strip ── */
  .demo-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
  }
  .demo-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    transition: box-shadow .2s, transform .15s;
    min-width: 0;
    position: relative;
    overflow: hidden;
  }
  .demo-card::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    background: var(--dc, #e5e7eb);
    border-radius: 4px 0 0 4px;
  }
  .demo-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.1); transform: translateY(-1px); }
  .demo-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
    background: var(--di, #f1f5f9);
    color: var(--dc, #64748b);
  }
  .demo-info { min-width: 0; }
  .demo-val { font-size: 20px; font-weight: 800; line-height: 1; }
  .demo-lbl { font-size: 10.5px; color: #6b7280; font-weight: 600; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .demo-pct { font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 20px; margin-top: 3px; display: inline-block; }

  /* ── Row 3: Main Grid (charts + table) ── */
  .main-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr 0.95fr;
    gap: 10px;
    min-height: 0;
    overflow: hidden;
  }

  /* Column A: Left */
  .col-a {
    display: grid;
    grid-template-rows: 1fr 1fr;
    gap: 10px;
    min-height: 0;
  }

  /* Column B: Middle */
  .col-b {
    display: grid;
    grid-template-rows: 1fr 1fr;
    gap: 10px;
    min-height: 0;
  }

  /* Column C: Right */
  .col-c {
    display: grid;
    grid-template-rows: auto 1fr;
    gap: 10px;
    min-height: 0;
  }

  /* Cards */
  .card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 4px rgba(0,0,0,.03);
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
  }
  .card-header {
    padding: 8px 12px;
    border-bottom: 1px solid #f3f4f6;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .card-header h5 {
    font-size: 12px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .card-body {
    padding: 8px 10px;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
  }

  /* Chart containers */
  .chart-box {
    position: relative;
    flex: 1;
    min-height: 0;
    width: 100%;
  }
  .chart-box canvas {
    display: block;
    width: 100% !important;
    height: 100% !important;
  }

  /* Doughnut legend */
  .doughnut-wrap {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
  }
  .doughnut-chart { flex: 1; min-height: 0; position: relative; }
  .doughnut-chart canvas { width: 100% !important; height: 100% !important; display: block; }
  .doughnut-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 10px;
    padding: 4px 2px 2px;
    flex-shrink: 0;
  }
  .legend-item { display: flex; align-items: center; gap: 4px; font-size: 10px; color: #374151; font-weight: 600; white-space: nowrap; }
  .legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

  /* Table */
  .table-wrapper { overflow-y: auto; flex: 1; min-height: 0; }
  .dash-table { width: 100%; border-collapse: collapse; }
  .dash-table th {
    background: #f9fafb; font-size: 10px; font-weight: 700; color: #6b7280;
    text-transform: uppercase; padding: 6px 10px; border-bottom: 1px solid #e5e7eb;
    position: sticky; top: 0; z-index: 1;
  }
  .dash-table td { padding: 6px 10px; font-size: 11.5px; color: #1f2937; border-bottom: 1px solid #f3f4f6; }
  .dash-table tr:hover td { background: #f9fafb; cursor: pointer; }

  /* Activity */
  .activity-list { list-style: none; margin: 0; padding: 0; overflow-y: auto; flex: 1; min-height: 0; }
  .activity-list li { display: flex; gap: 8px; align-items: flex-start; padding: 5px 0; border-bottom: 1px solid #f3f4f6; }
  .activity-list li:last-child { border-bottom: none; }
  .activity-dot {
    width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; flex-shrink: 0; background: #eff6ff; color: #1d4ed8;
  }
  .activity-text { font-size: 11px; font-weight: 600; color: #1e293b; line-height: 1.3; }
  .activity-meta { font-size: 9.5px; color: #9ca3af; margin-top: 1px; }

  /* Demographic mini-bars */
  .demo-bar-list { display: flex; flex-direction: column; gap: 7px; padding: 4px 0; flex: 1; }
  .demo-bar-item { display: flex; flex-direction: column; gap: 2px; }
  .demo-bar-label { display: flex; justify-content: space-between; font-size: 10.5px; font-weight: 600; color: #374151; }
  .demo-bar-track { height: 6px; background: #f1f5f9; border-radius: 4px; overflow: hidden; }
  .demo-bar-fill { height: 100%; border-radius: 4px; transition: width .8s ease; }

  /* Status badge */
  .status-badge {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; white-space: nowrap;
  }
  .status-badge.pending    { background: #fffbeb; color: #92400e; }
  .status-badge.processing { background: #eff6ff; color: #1d4ed8; }
  .status-badge.approved   { background: #f0fdf4; color: #15803d; }
  .status-badge.released   { background: #f0f9ff; color: #0369a1; }
  .status-badge.rejected   { background: #fef2f2; color: #b91c1c; }
</style>
@endsection

@section('content')

{{-- ── Row 1: KPI Cards ── --}}
<div class="kpi-row">
  <div class="kpi-card" style="--kpi-color:#1d4ed8;--kpi-bg:#eff6ff;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-users"></i></div>
      <span class="kpi-badge neutral">Total</span>
    </div>
    <div class="kpi-value">{{ number_format($stats['residents'] ?? 0) }}</div>
    <div class="kpi-label">Active Residents</div>
  </div>

  <div class="kpi-card" style="--kpi-color:#d97706;--kpi-bg:#fffbeb;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-clock"></i></div>
      <span class="kpi-badge {{ ($stats['pending'] ?? 0) > 10 ? 'down' : 'neutral' }}">{{ ($stats['pending'] ?? 0) > 0 ? 'Needs action' : 'Clear' }}</span>
    </div>
    <div class="kpi-value">{{ number_format($stats['pending'] ?? 0) }}</div>
    <div class="kpi-label">Pending Requests</div>
  </div>

  <div class="kpi-card" style="--kpi-color:#0d9488;--kpi-bg:#f0fdfa;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
      <span class="kpi-badge neutral">{{ $stats['completion_rate'] ?? 0 }}%</span>
    </div>
    <div class="kpi-value">{{ number_format($stats['released'] ?? 0) }}</div>
    <div class="kpi-label">Released Docs</div>
  </div>

  <div class="kpi-card" style="--kpi-color:#7c3aed;--kpi-bg:#f5f3ff;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-peso-sign"></i></div>
      @if(($stats['revenue_trend'] ?? 0) >= 0)
        <span class="kpi-badge up"><i class="fas fa-arrow-up"></i> {{ $stats['revenue_trend'] ?? 0 }}%</span>
      @else
        <span class="kpi-badge down"><i class="fas fa-arrow-down"></i> {{ abs($stats['revenue_trend'] ?? 0) }}%</span>
      @endif
    </div>
    <div class="kpi-value" style="font-size:16px;">₱{{ number_format($stats['month_revenue'] ?? $stats['revenue'] ?? 0, 0) }}</div>
    <div class="kpi-label">Revenue (Month)</div>
  </div>

  <div class="kpi-card" style="--kpi-color:#b91c1c;--kpi-bg:#fef2f2;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-file-alt"></i></div>
      <span class="kpi-badge neutral">{{ now()->format('M') }}</span>
    </div>
    <div class="kpi-value">{{ number_format($stats['month_requests'] ?? $stats['total_req'] ?? 0) }}</div>
    <div class="kpi-label">New Requests</div>
  </div>
</div>

{{-- ── Row 2: Demographics Strip ── --}}
@php
  $totalRes = max($stats['residents'] ?? 1, 1);
  $pwdPct     = round(($demographics['pwd'] ?? 0) / $totalRes * 100, 1);
  $seniorPct  = round(($demographics['senior'] ?? 0) / $totalRes * 100, 1);
  $spPct      = round(($demographics['single_parent'] ?? 0) / $totalRes * 100, 1);
  $maleCount  = $demographics['gender']['Male'] ?? 0;
  $femaleCount= $demographics['gender']['Female'] ?? 0;
  $otherCount = $demographics['gender']['Other'] ?? 0;
  $malePct    = round($maleCount / $totalRes * 100, 1);
  $femalePct  = round($femaleCount / $totalRes * 100, 1);
@endphp
<div class="demo-row">
  <a href="{{ route('admin.residents', ['classification' => 'pwd']) }}" class="demo-card" style="--dc:#7c3aed;--di:#f5f3ff;">
    <div class="demo-icon"><i class="fas fa-wheelchair"></i></div>
    <div class="demo-info">
      <div class="demo-val" style="color:#7c3aed;">{{ number_format($demographics['pwd'] ?? 0) }}</div>
      <div class="demo-lbl">PWD Residents</div>
      <span class="demo-pct" style="background:#f5f3ff;color:#7c3aed;">{{ $pwdPct }}% of total</span>
    </div>
  </a>

  <a href="{{ route('admin.residents', ['classification' => 'senior']) }}" class="demo-card" style="--dc:#d97706;--di:#fffbeb;">
    <div class="demo-icon"><i class="fas fa-user-clock"></i></div>
    <div class="demo-info">
      <div class="demo-val" style="color:#d97706;">{{ number_format($demographics['senior'] ?? 0) }}</div>
      <div class="demo-lbl">Senior Citizens</div>
      <span class="demo-pct" style="background:#fffbeb;color:#d97706;">{{ $seniorPct }}% of total</span>
    </div>
  </a>

  <a href="{{ route('admin.residents', ['classification' => 'single_parent']) }}" class="demo-card" style="--dc:#0d9488;--di:#f0fdfa;">
    <div class="demo-icon"><i class="fas fa-child"></i></div>
    <div class="demo-info">
      <div class="demo-val" style="color:#0d9488;">{{ number_format($demographics['single_parent'] ?? 0) }}</div>
      <div class="demo-lbl">Single Parents</div>
      <span class="demo-pct" style="background:#f0fdfa;color:#0d9488;">{{ $spPct }}% of total</span>
    </div>
  </a>

  <a href="{{ route('admin.reports') }}" class="demo-card" style="--dc:#1d4ed8;--di:#eff6ff;">
    <div class="demo-icon"><i class="fas fa-venus-mars"></i></div>
    <div class="demo-info" style="flex:1;min-width:0;">
      <div class="demo-lbl" style="margin-bottom:4px;">Gender Breakdown</div>
      <div style="display:flex;gap:6px;font-size:10px;font-weight:700;">
        <span style="color:#1d4ed8;"><i class="fas fa-male"></i> {{ $maleCount }} <span style="color:#9ca3af;font-weight:500;">({{ $malePct }}%)</span></span>
        <span style="color:#db2777;"><i class="fas fa-female"></i> {{ $femaleCount }} <span style="color:#9ca3af;font-weight:500;">({{ $femalePct }}%)</span></span>
        @if($otherCount > 0)
        <span style="color:#64748b;"><i class="fas fa-genderless"></i> {{ $otherCount }}</span>
        @endif
      </div>
      <div style="display:flex;height:5px;border-radius:4px;overflow:hidden;margin-top:5px;gap:1px;">
        <div style="width:{{ $malePct }}%;background:#1d4ed8;"></div>
        <div style="width:{{ $femalePct }}%;background:#db2777;"></div>
        @if($otherCount > 0)<div style="flex:1;background:#94a3b8;"></div>@endif
      </div>
    </div>
  </a>
</div>

{{-- ── Row 3: Main Charts & Data Grid ── --}}
<div class="main-grid">

  {{-- Column A: Bar Chart + Line Chart --}}
  <div class="col-a">
    {{-- Bar: Monthly Requests vs Released --}}
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-chart-bar" style="color:#b91c1c;margin-right:5px;"></i>Monthly Requests vs Released</h5>
        <span style="font-size:10px;color:#9ca3af;">Last 6 months</span>
      </div>
      <div class="card-body">
        <div class="chart-box"><canvas id="monthlyChart"></canvas></div>
      </div>
    </div>

    {{-- Line: Monthly Collections --}}
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-chart-line" style="color:#7c3aed;margin-right:5px;"></i>Monthly Collections (PHP)</h5>
        <span style="font-size:10px;color:#9ca3af;">Last 6 months</span>
      </div>
      <div class="card-body">
        <div class="chart-box"><canvas id="revenueChart"></canvas></div>
      </div>
    </div>
  </div>

  {{-- Column B: Pie Chart + Age Group Bar Chart --}}
  <div class="col-b">
    {{-- Doughnut: Requests by Type --}}
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-chart-pie" style="color:#0d9488;margin-right:5px;"></i>Requests by Type</h5>
      </div>
      <div class="card-body">
        <div class="doughnut-wrap">
          <div class="doughnut-chart"><canvas id="typeChart"></canvas></div>
          <div class="doughnut-legend" id="typeLegend"></div>
        </div>
      </div>
    </div>

    {{-- Bar: Age Group Distribution --}}
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-people-arrows" style="color:#d97706;margin-right:5px;"></i>Age Group Distribution</h5>
      </div>
      <div class="card-body">
        <div class="chart-box"><canvas id="ageChart"></canvas></div>
      </div>
    </div>
  </div>

  {{-- Column C: Recent Requests Table + Activity --}}
  <div class="col-c">
    {{-- Recent Requests Table --}}
    <div class="card" style="flex:1.2;">
      <div class="card-header">
        <h5><i class="fas fa-list" style="color:#b91c1c;margin-right:5px;"></i>Recent Requests</h5>
        <a href="{{ route('admin.requests') }}" class="btn btn-outline-primary btn-sm" style="font-size:10px;padding:2px 8px;">View All</a>
      </div>
      <div class="table-wrapper">
        <table class="dash-table">
          <thead>
            <tr><th>#</th><th>Resident</th><th>Document</th><th>Status</th></tr>
          </thead>
          <tbody>
            @forelse ($recentRequests->take(6) as $req)
              <tr onclick="window.location='{{ route('admin.requests', ['view' => $req->tracking_number]) }}'">
                <td><code style="font-size:10px;">{{ substr($req->tracking_number,-6) }}</code></td>
                <td style="font-weight:600;max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $req->resident?->full_name }}</td>
                <td style="color:#6b7280;max-width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;">{{ $req->certificate?->name }}</td>
                <td>
                  <span class="status-badge {{ $req->status }}">{{ ucfirst($req->status) }}</span>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted" style="padding:12px;">No requests yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Recent Activity --}}
    <div class="card" style="flex:1;">
      <div class="card-header">
        <h5><i class="fas fa-history" style="color:#1d4ed8;margin-right:5px;"></i>Recent Activity</h5>
      </div>
      <div class="card-body">
        <ul class="activity-list">
          @forelse($recentActivity ?? [] as $log)
            <li>
              <div class="activity-dot"><i class="fas fa-bolt"></i></div>
              <div>
                <div class="activity-text">{{ Str::limit($log->description ?: $log->action, 45) }}</div>
                <div class="activity-meta">{{ explode('@', $log->user?->email)[0] ?? 'System' }} · {{ $log->created_at->diffForHumans(null, true, true) }}</div>
              </div>
            </li>
          @empty
            <li><div class="activity-meta">No recent activity.</div></li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>

</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const monthlyLabels = @json($monthly['labels'] ?? []);
  const monthlyData   = @json($monthly['data'] ?? []);
  const monthlyRel    = @json($monthly['released'] ?? []);
  const typeLabels    = @json($byType['labels'] ?? []);
  const typeData      = @json($byType['data'] ?? []);
  const revLabels     = @json($revenueChart['labels'] ?? []);
  const revData       = @json($revenueChart['data'] ?? []);
  const ageLabels     = @json(array_keys($demographics['age_groups'] ?? []));
  const ageData       = @json(array_values($demographics['age_groups'] ?? []));

  Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
  Chart.defaults.font.size   = 11;
  Chart.defaults.color       = '#6b7280';

  const palette = ['#b91c1c','#0d9488','#7c3aed','#d97706','#0ea5e9','#db2777','#059669','#4f46e5'];

  // ── Bar: Monthly Requests vs Released ──
  if (document.getElementById('monthlyChart')) {
    new Chart(document.getElementById('monthlyChart'), {
      type: 'bar',
      data: {
        labels: monthlyLabels.length ? monthlyLabels : ['No Data'],
        datasets: [
          { label: 'Received', data: monthlyData.length ? monthlyData : [0], backgroundColor: 'rgba(185,28,28,0.85)', borderRadius: 4, barPercentage: .65 },
          { label: 'Released', data: monthlyRel.length ? monthlyRel : [0],  backgroundColor: 'rgba(13,148,136,0.85)', borderRadius: 4, barPercentage: .65 }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11, weight: 'bold' }, padding: 8 } },
          tooltip: { bodyFont: { size: 12 }, padding: 8 }
        },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { size: 10 } } },
          x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } }
        }
      }
    });
  }

  // ── Line: Monthly Collections ──
  if (document.getElementById('revenueChart')) {
    new Chart(document.getElementById('revenueChart'), {
      type: 'line',
      data: {
        labels: revLabels.length ? revLabels : ['No Data'],
        datasets: [{
          label: 'Collections (₱)',
          data: revData.length ? revData : [0],
          borderColor: '#7c3aed',
          backgroundColor: 'rgba(124,58,237,.12)',
          fill: true, tension: 0.4,
          pointRadius: 4, pointHoverRadius: 6,
          borderWidth: 2.5
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            bodyFont: { size: 12 }, padding: 8,
            callbacks: { label: ctx => '₱' + Number(ctx.parsed.y).toLocaleString() }
          }
        },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 }, callback: v => '₱' + Number(v).toLocaleString() } },
          x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } }
        }
      }
    });
  }

  // ── Doughnut: Requests by Type ──
  if (document.getElementById('typeChart')) {
    const typeChart = new Chart(document.getElementById('typeChart'), {
      type: 'doughnut',
      data: {
        labels: typeLabels.length ? typeLabels : ['No Data'],
        datasets: [{
          data: typeData.length ? typeData : [1],
          backgroundColor: palette,
          borderWidth: 2, borderColor: '#fff'
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '60%',
        plugins: {
          legend: { display: false },
          tooltip: { bodyFont: { size: 12 }, padding: 8 }
        }
      }
    });

    // Custom legend
    const legend = document.getElementById('typeLegend');
    if (legend && typeLabels.length) {
      typeLabels.forEach((lbl, i) => {
        const item = document.createElement('div');
        item.className = 'legend-item';
        item.innerHTML = `<span class="legend-dot" style="background:${palette[i % palette.length]}"></span>${lbl} (${typeData[i] ?? 0})`;
        legend.appendChild(item);
      });
    }
  }

  // ── Bar: Age Group Distribution ──
  if (document.getElementById('ageChart')) {
    new Chart(document.getElementById('ageChart'), {
      type: 'bar',
      data: {
        labels: ageLabels.length ? ageLabels : ['No Data'],
        datasets: [{
          label: 'Residents',
          data: ageData.length ? ageData : [0],
          backgroundColor: ['#60a5fa','#34d399','#f59e0b','#f87171','#c084fc'],
          borderRadius: 5,
          barPercentage: 0.7
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
          legend: { display: false },
          tooltip: { bodyFont: { size: 12 }, padding: 8 }
        },
        scales: {
          x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 } } },
          y: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } }
        }
      }
    });
  }
</script>
@endsection
