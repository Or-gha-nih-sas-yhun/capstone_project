@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
<<<<<<< HEAD
  html, body { overflow: hidden !important; }

  .page-content {
    padding: 12px 16px;
    height: calc(100vh - var(--topbar-h));
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 10px;
    box-sizing: border-box;
  }

  /* ── KPI Row ── */
  .stats-primary {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    flex-shrink: 0;
  }
  .kpi-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
  }
  .kpi-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--kpi-color, #e5e7eb);
  }
  .kpi-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
  .kpi-icon {
    width: 34px; height: 34px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    background: var(--kpi-bg, #f1f5f9);
    color: var(--kpi-color, #64748b);
  }
  .kpi-badge { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; }
  .kpi-badge.up     { background: #f0fdf4; color: #15803d; }
  .kpi-badge.down   { background: #fef2f2; color: #b91c1c; }
  .kpi-badge.neutral{ background: #f1f5f9; color: #64748b; }
  .kpi-value { font-size: 24px; font-weight: 800; color: #111; line-height: 1; }
  .kpi-label { font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }

  /* ── Classification Strip ── */
  .classif-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    flex-shrink: 0;
  }
  .classif-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 14px;
    display: flex; align-items: center; gap: 10px; text-decoration: none;
    transition: box-shadow .2s, transform .2s;
  }
  .classif-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); transform: translateY(-1px); }
  .classif-card .ci { width: 32px; height: 32px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
  .classif-card .cv { font-size: 18px; font-weight: 800; line-height: 1; }
  .classif-card .cl { font-size: 11px; color: #4b5563; font-weight: 600; margin-top: 2px; }

  /* ── Main Grid ── */
  .dash-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 10px;
    flex: 1;
    min-height: 0;
  }
  .left-col, .right-col {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-height: 0;
    overflow: hidden;
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
  }
  .card-header {
    padding: 10px 14px;
    border-bottom: 1px solid #f3f4f6;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: space-between;
  }
  .card-header h5 {
    font-size: 13px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
  }
  .card-body {
    padding: 10px 14px;
    flex: 1;
=======
  html,
  body {
    overflow: hidden;
  }

  .wrapper,
  .main-content {
    height: 100vh;
    overflow: hidden;
  }

  .page-content {
    height: calc(100vh - var(--topbar-h));
    overflow: hidden;
    padding: 12px 16px;
  }

  .dashboard-shell {
    display: grid;
    grid-template-rows: auto auto 1fr;
    gap: 10px;
    height: 100%;
    min-height: 0;
  }

  .dashboard-hero {
    background: #fff;
    border: 1px solid var(--gray-light);
    border-left: 6px solid var(--primary);
    padding: 10px 14px;
    box-shadow: var(--shadow);
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: center;
  }

  .dashboard-hero h1 {
    font-size: 18px;
    margin: 0;
    color: #161616;
  }

  .dashboard-hero p {
    margin-top: 2px;
    font-size: 12px;
    color: var(--gray);
  }

  .dashboard-stats {
    display: grid;
    grid-template-columns: repeat(5, minmax(150px, 1fr));
    gap: 10px;
  }

  .dashboard-stats .stat-card {
    border-top: 3px solid var(--primary);
    min-height: 74px;
    padding: 10px 12px;
    gap: 10px;
  }

  .dashboard-stats .stat-icon {
    width: 40px;
    height: 40px;
    font-size: 16px;
    border-radius: 8px;
  }

  .dashboard-stats .stat-value {
    font-size: 20px;
  }

  .dashboard-stats .stat-label {
    font-size: 11px;
  }

  .dashboard-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(360px, .85fr);
    gap: 12px;
    align-items: stretch;
    min-height: 0;
    overflow: hidden;
  }

  .chart-panel {
    display: grid;
    grid-template-rows: 1fr 1fr;
    gap: 12px;
    min-height: 0;
  }

  .dashboard-grid .card,
  .chart-panel .card {
>>>>>>> origin/master
    min-height: 0;
    display: flex;
    flex-direction: column;
  }

<<<<<<< HEAD
  /* Chart containers – fully fluid */
  .chart-box {
    position: relative;
    flex: 1;
    min-height: 0;
    width: 100%;
  }
  .chart-box canvas { display: block; }

  /* Right pane inner layout */
  .right-top { display: flex; gap: 10px; flex: 1.2; min-height: 0; }
  .right-top .card { flex: 1; }

  /* Table */
  .table-wrapper { overflow-y: auto; flex: 1; min-height: 0; }
  .table { width: 100%; border-collapse: collapse; }
  .table th {
    background: #f9fafb; font-size: 11px; font-weight: 700; color: #6b7280;
    text-transform: uppercase; padding: 8px 12px; border-bottom: 1px solid #e5e7eb;
    position: sticky; top: 0;
  }
  .table td { padding: 8px 12px; font-size: 13px; color: #1f2937; border-bottom: 1px solid #f3f4f6; }

  /* Activity */
  .activity-list { list-style: none; margin: 0; padding: 0; overflow-y: auto; flex: 1; }
  .activity-list li { display: flex; gap: 10px; align-items: flex-start; padding: 7px 0; border-bottom: 1px solid #f3f4f6; }
  .activity-list li:last-child { border-bottom: none; }
  .activity-dot { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; background: #eff6ff; color: #1d4ed8; }
  .activity-text { font-size: 12px; font-weight: 600; color: #1e293b; line-height: 1.3; }
  .activity-meta { font-size: 10px; color: #9ca3af; margin-top: 2px; }
=======
  .dashboard-grid .card-header {
    padding: 9px 14px;
    flex-shrink: 0;
  }

  .dashboard-grid .card-body {
    padding: 10px 14px;
    flex: 1;
    min-height: 0;
  }

  .chart-box {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 115px;
  }

  .dashboard-table th,
  .dashboard-table td {
    padding: 8px 10px;
    font-size: 12px;
  }

  .dashboard-grid .table-wrapper {
    flex: 1;
    min-height: 0;
    overflow: hidden;
  }

  .dashboard-grid .table {
    table-layout: fixed;
  }

  .dashboard-grid .table td,
  .dashboard-grid .table th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .dashboard-grid .badge {
    font-size: 10px;
    padding: 3px 7px;
  }

  .dashboard-shell .alert {
    margin-bottom: 0;
    padding: 8px 12px;
  }

  @media (max-width: 1100px) {
    .dashboard-stats { grid-template-columns: repeat(2, 1fr); }
    .dashboard-grid { grid-template-columns: 1fr; }
  }

  @media (max-width: 576px) {
    .dashboard-stats { grid-template-columns: 1fr; }
    .dashboard-hero { align-items: flex-start; flex-direction: column; }
  }
>>>>>>> origin/master
</style>
@endsection

@section('content')
<<<<<<< HEAD

{{-- ── KPI Cards ── --}}
<div class="stats-primary">
  <div class="kpi-card" style="--kpi-color:#1d4ed8;--kpi-bg:#eff6ff;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-users"></i></div>
      <span class="kpi-badge neutral">Total</span>
    </div>
    <div class="kpi-value">{{ number_format($stats['residents']) }}</div>
    <div class="kpi-label">Active Residents</div>
  </div>

  <div class="kpi-card" style="--kpi-color:#d97706;--kpi-bg:#fffbeb;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-clock"></i></div>
      <span class="kpi-badge {{ $stats['pending'] > 10 ? 'down' : 'neutral' }}">{{ $stats['pending'] > 0 ? 'Needs action' : 'Clear' }}</span>
    </div>
    <div class="kpi-value">{{ number_format($stats['pending']) }}</div>
    <div class="kpi-label">Pending Requests</div>
  </div>

  <div class="kpi-card" style="--kpi-color:#0d9488;--kpi-bg:#f0fdfa;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
      <span class="kpi-badge neutral">{{ $stats['completion_rate'] }}%</span>
    </div>
    <div class="kpi-value">{{ number_format($stats['released']) }}</div>
    <div class="kpi-label">Released Docs</div>
  </div>

  <div class="kpi-card" style="--kpi-color:#7c3aed;--kpi-bg:#f5f3ff;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-peso-sign"></i></div>
      @if($stats['revenue_trend'] >= 0)
        <span class="kpi-badge up"><i class="fas fa-arrow-up"></i> {{ $stats['revenue_trend'] }}%</span>
      @else
        <span class="kpi-badge down"><i class="fas fa-arrow-down"></i> {{ abs($stats['revenue_trend']) }}%</span>
      @endif
    </div>
    <div class="kpi-value" style="font-size:18px;">₱{{ number_format($stats['month_revenue'],0) }}</div>
    <div class="kpi-label">Revenue (Month)</div>
  </div>

  <div class="kpi-card" style="--kpi-color:#b91c1c;--kpi-bg:#fef2f2;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-file-alt"></i></div>
      <span class="kpi-badge neutral">{{ now()->format('M') }}</span>
    </div>
    <div class="kpi-value">{{ number_format($stats['month_requests']) }}</div>
    <div class="kpi-label">New Requests</div>
  </div>
</div>

{{-- ── Classification Strip ── --}}
<div class="classif-row">
  <a href="{{ route('admin.residents', ['classification' => 'pwd']) }}" class="classif-card">
    <div class="ci" style="background:#f5f3ff;color:#7c3aed;"><i class="fas fa-wheelchair"></i></div>
    <div><div class="cv" style="color:#7c3aed;">{{ $stats['pwd'] }}</div><div class="cl">PWD Residents</div></div>
  </a>
  <a href="{{ route('admin.residents', ['classification' => 'senior']) }}" class="classif-card">
    <div class="ci" style="background:#fffbeb;color:#d97706;"><i class="fas fa-user-clock"></i></div>
    <div><div class="cv" style="color:#d97706;">{{ $stats['senior'] }}</div><div class="cl">Senior Citizens</div></div>
  </a>
  <a href="{{ route('admin.residents', ['classification' => 'single_parent']) }}" class="classif-card">
    <div class="ci" style="background:#f0fdfa;color:#0d9488;"><i class="fas fa-child"></i></div>
    <div><div class="cv" style="color:#0d9488;">{{ $stats['single_parent'] }}</div><div class="cl">Single Parents</div></div>
  </a>
  <a href="{{ route('admin.reports') }}" class="classif-card">
    <div class="ci" style="background:#eff6ff;color:#1d4ed8;"><i class="fas fa-coins"></i></div>
    <div><div class="cv" style="color:#1d4ed8;">₱{{ number_format($stats['revenue'],0) }}</div><div class="cl">Total Collections</div></div>
  </a>
</div>

{{-- ── Main Grid ── --}}
<div class="dash-grid">

  {{-- Left: Two Charts stacked --}}
  <div class="left-col">
    <div class="card" style="flex:1;">
      <div class="card-header">
        <h5><i class="fas fa-chart-bar" style="color:#b91c1c;margin-right:6px;"></i>Monthly Requests vs Released</h5>
      </div>
      <div class="card-body">
        <div class="chart-box"><canvas id="monthlyChart"></canvas></div>
      </div>
    </div>
    <div class="card" style="flex:1;">
      <div class="card-header">
        <h5><i class="fas fa-chart-line" style="color:#7c3aed;margin-right:6px;"></i>Monthly Collections (PHP)</h5>
      </div>
      <div class="card-body">
        <div class="chart-box"><canvas id="revenueChart"></canvas></div>
      </div>
    </div>
  </div>

  {{-- Right: Pie top row + Table + Activity --}}
  <div class="right-col">
    {{-- Top: Pie + Activity side by side --}}
    <div class="right-top">
      <div class="card">
        <div class="card-header"><h5><i class="fas fa-chart-pie" style="color:#0d9488;margin-right:6px;"></i>Requests by Type</h5></div>
        <div class="card-body"><div class="chart-box"><canvas id="typeChart"></canvas></div></div>
      </div>
      <div class="card">
        <div class="card-header"><h5><i class="fas fa-history" style="color:#1d4ed8;margin-right:6px;"></i>Recent Activity</h5></div>
        <div class="card-body">
          <ul class="activity-list">
            @forelse($recentActivity as $log)
              <li>
                <div class="activity-dot"><i class="fas fa-bolt"></i></div>
                <div>
                  <div class="activity-text">{{ Str::limit($log->description ?: $log->action, 40) }}</div>
                  <div class="activity-meta">{{ explode('@', $log->user?->email)[0] ?? 'System' }} · {{ $log->created_at->diffForHumans(null, true, true) }}</div>
                </div>
              </li>
            @empty
              <li><div class="activity-meta">No recent activity.</div></li>
            @endforelse
          </ul>
=======
<div class="dashboard-shell">
<div class="dashboard-hero">
  <div>
    <div class="eyebrow">Barangay Management System</div>
    <h1>Administrative Dashboard</h1>
    <p>Monitor residents, certificate requests, approvals, releases, and monthly activity.</p>
  </div>
  <a href="{{ route('admin.requests') }}" class="btn btn-primary"><i class="fas fa-file-alt"></i> Manage Requests</a>
</div>

@if ($stats['pending_residents'] > 0)
  <div class="alert alert-warning"
    style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
    <div style="display:flex; align-items:center; gap:12px;">
      <i class="fas fa-user-clock" style="font-size:20px; color:var(--warning);"></i>
      <div>
        <h5 style="margin:0 0 2px 0; color:#92400e; font-size:13px; font-weight:700;">Resident Accounts Pending Approval</h5>
        <p style="margin:0; font-size:12px; color:#b45309;">There are {{ $stats['pending_residents'] }} new resident registration(s) awaiting verification.</p>
      </div>
    </div>
    <a href="{{ route('admin.residents', ['status' => 'pending']) }}" class="btn btn-warning btn-sm"
      style="background:var(--warning); color:#fff; font-weight:600; padding:4px 10px;">
      <i class="fas fa-shield-halved"></i> Review
    </a>
  </div>
@endif

<div class="dashboard-stats">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
    <div>
      <div class="stat-value">{{ number_format($stats['residents']) }}</div>
      <div class="stat-label">Total Residents</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon teal"><i class="fas fa-file-alt"></i></div>
    <div>
      <div class="stat-value">{{ number_format($stats['total_req']) }}</div>
      <div class="stat-label">Total Requests</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
    <div>
      <div class="stat-value">{{ number_format($stats['pending']) }}</div>
      <div class="stat-label">Pending Requests</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
    <div>
      <div class="stat-value">{{ number_format($stats['approved']) }}</div>
      <div class="stat-label">Approved</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fas fa-box-open"></i></div>
    <div>
      <div class="stat-value">{{ number_format($stats['released']) }}</div>
      <div class="stat-label">Released</div>
    </div>
  </div>
</div>

<div class="dashboard-grid">
  <div class="chart-panel">
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-chart-bar" style="color:var(--primary);margin-right:8px;"></i>Monthly Requests</h5>
      </div>
      <div class="card-body">
        <div class="chart-box">
          <canvas id="monthlyChart"></canvas>
>>>>>>> origin/master
        </div>
      </div>
    </div>

<<<<<<< HEAD
    {{-- Recent Requests Table --}}
    <div class="card" style="flex:0 0 auto;">
      <div class="card-header">
        <h5><i class="fas fa-list" style="color:#b91c1c;margin-right:6px;"></i>Recent Requests</h5>
        <a href="{{ route('admin.requests') }}" class="btn btn-outline-primary btn-sm" style="font-size:11px;padding:3px 10px;">View All</a>
      </div>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr><th>Tracking #</th><th>Resident</th><th>Document</th><th>Status</th></tr>
          </thead>
          <tbody>
            @forelse ($recentRequests->take(5) as $req)
              <tr style="cursor:pointer;" onclick="window.location='{{ route('admin.requests', ['view' => $req->tracking_number]) }}'">
                <td><code style="font-size:12px;">{{ substr($req->tracking_number,-6) }}</code></td>
                <td style="font-weight:600;">{{ $req->resident?->full_name }}</td>
                <td style="color:#6b7280;">{{ $req->certificate?->name }}</td>
                <td>
                  <span class="badge bg-{{ $req->status==='pending'?'warning':($req->status==='processing'?'info':($req->status==='approved'?'success':($req->status==='rejected'?'danger':'primary'))) }}" style="font-size:11px;padding:4px 8px;">
                    {{ ucfirst($req->status) }}
                  </span>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted" style="padding:16px;">No requests yet.</td></tr>
            @endforelse
          </tbody>
        </table>
=======
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-chart-pie" style="color:var(--primary);margin-right:8px;"></i>Requests by Type</h5>
      </div>
      <div class="card-body">
        @if (empty($byType['data']))
          <p class="text-muted text-center" style="font-size:11px; margin:0;">No data yet.</p>
        @else
          <div class="chart-box">
            <canvas id="typeChart"></canvas>
          </div>
        @endif
>>>>>>> origin/master
      </div>
    </div>
  </div>

<<<<<<< HEAD
=======
  <!-- Right Side: Recent Requests Table -->
  <div class="card">
    <div class="card-header">
      <h5><i class="fas fa-list" style="color:var(--primary);margin-right:8px;"></i>Recent Requests</h5>
      <a href="{{ route('admin.requests') }}" class="btn btn-outline-primary btn-sm">View All</a>
    </div>
    <div class="table-wrapper">
      <table class="table dashboard-table">
        <thead>
          <tr>
            <th>Tracking #</th>
            <th>Resident</th>
            <th>Document</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @if ($recentRequests->isEmpty())
            <tr>
              <td colspan="4" class="text-center text-muted" style="padding:20px; font-size:12px;">No requests yet.</td>
            </tr>
          @else
            @foreach ($recentRequests as $req)
              <tr style="cursor:pointer;" onclick="window.location='{{ route('admin.requests', ['view' => $req->tracking_number]) }}'" title="Click to view details">
                <td><code style="font-size:10.5px;">{{ substr($req->tracking_number, -6) }}</code></td>
                <td style="font-weight:600;">{{ $req->resident->full_name }}</td>
                <td>{{ $req->certificate->name }}</td>
                <td>
                  <span class="badge bg-{{ $req->status === 'pending' ? 'warning' : ($req->status === 'processing' ? 'info' : ($req->status === 'approved' ? 'success' : ($req->status === 'rejected' ? 'danger' : 'primary'))) }}">
                      {{ ucfirst($req->status) }}
                  </span>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>

</div>
>>>>>>> origin/master
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const monthlyLabels = @json($monthly['labels']);
<<<<<<< HEAD
  const monthlyData   = @json($monthly['data']);
  const monthlyRel    = @json($monthly['released']);
  const typeLabels    = @json($byType['labels']);
  const typeData      = @json($byType['data']);
  const revLabels     = @json($revenueChart['labels']);
  const revData       = @json($revenueChart['data']);

  Chart.defaults.font.family = 'Inter, sans-serif';
  Chart.defaults.font.size   = 12;
  Chart.defaults.color       = '#6b7280';

  // Bar Chart
  new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
      labels: monthlyLabels,
      datasets: [
        { label: 'Total Received', data: monthlyData, backgroundColor: '#b91c1c', borderRadius: 4, barPercentage: .65 },
        { label: 'Released',       data: monthlyRel,  backgroundColor: '#0d9488', borderRadius: 4, barPercentage: .65 }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 12, weight: 'bold' }, padding: 10 } },
                 tooltip: { bodyFont: { size: 13 }, padding: 10 } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { size: 11 } } },
        x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } }
      }
    }
  });

  // Line Chart
  new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
      labels: revLabels,
      datasets: [{
        label: 'Collections', data: revData, borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,.1)',
        fill: true, tension: 0.4, pointRadius: 5, pointHoverRadius: 7, borderWidth: 2.5
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { bodyFont: { size: 13 }, padding: 10,
        callbacks: { label: ctx => '₱' + ctx.parsed.y.toLocaleString() } } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, callback: v => '₱' + v } },
        x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } }
      }
    }
  });

  // Doughnut Chart
  new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
      labels: typeLabels,
      datasets: [{ data: typeData,
        backgroundColor: ['#b91c1c','#0d9488','#7c3aed','#d97706','#0ea5e9','#db2777'],
        borderWidth: 2, borderColor: '#fff' }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '58%',
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11, weight: '600' }, padding: 8 } },
        tooltip: { bodyFont: { size: 13 }, padding: 10 }
      }
    }
  });
=======
  const monthlyData = @json($monthly['data']);

  const typeLabels = @json($byType['labels']);
  const typeData = @json($byType['data']);

  // Bar Chart for Monthly Requests
  if (document.getElementById('monthlyChart')) {
    new Chart(document.getElementById('monthlyChart'), {
      type: 'bar',
      data: {
        labels: monthlyLabels.length ? monthlyLabels : ['No Data'],
        datasets: [{
          label: 'Requests',
          data: monthlyData.length ? monthlyData : [0],
          backgroundColor: '#b91c1c',
          borderRadius: 4,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { size: 9 } } },
          x: { grid: { display: false }, ticks: { font: { size: 9 } } }
        }
      }
    });
  }

  // Doughnut Chart for Requests by Type
  if (document.getElementById('typeChart')) {
    new Chart(document.getElementById('typeChart'), {
      type: 'doughnut',
      data: {
        labels: typeLabels,
        datasets: [{
          data: typeData,
          backgroundColor: ['#b91c1c', '#dc2626', '#f97316', '#10b981', '#6b7280', '#f59e0b'],
          borderWidth: 1.5,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: { position: 'right', labels: { boxWidth: 10, padding: 8, font: { family: 'Inter', size: 9.5 } } }
        }
      }
    });
  }
>>>>>>> origin/master
</script>
@endsection
