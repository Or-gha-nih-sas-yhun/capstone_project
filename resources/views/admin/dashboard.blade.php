@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
  /* Force the page-content to take exactly the screen height minus topbar */
  html, body {
    overflow: hidden !important;
  }
  .page-content {
    padding: 10px 14px;
    height: calc(100vh - 60px); /* Adjust based on topbar height */
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  /* ── Hero Banner ───────────────────────────────────── */
  .dash-hero {
    background: linear-gradient(130deg, #b91c1c 0%, #7f1d1d 100%);
    border-radius: 8px;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #fff;
    flex-shrink: 0;
  }
  .dash-hero h1 { font-size: 16px; margin: 0; font-weight: 700; }
  .dash-hero p  { margin: 2px 0 0; font-size: 11px; opacity: .85; }
  .dash-hero .hero-meta { display: flex; gap: 12px; }
  .dash-hero .hero-meta-item { font-size: 11px; opacity: .85; display: flex; align-items: center; gap: 4px; }
  .dash-hero .btn { padding: 4px 10px; font-size: 11px; }

  /* ── Stat Cards Row ─────────────────────────────────── */
  .stats-primary {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    flex-shrink: 0;
  }
  .kpi-card {
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    position: relative;
    overflow: hidden;
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
    width: 28px; height: 28px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; flex-shrink: 0;
    background: var(--kpi-bg, #f1f5f9);
    color: var(--kpi-color, #64748b);
  }
  .kpi-badge {
    font-size: 9px; font-weight: 700;
    padding: 2px 6px; border-radius: 20px;
  }
  .kpi-badge.up   { background: #f0fdf4; color: #15803d; }
  .kpi-badge.down { background: #fef2f2; color: #b91c1c; }
  .kpi-badge.neutral { background: #f1f5f9; color: #64748b; }
  .kpi-value { font-size: 18px; font-weight: 800; color: #111; line-height: 1; }
  .kpi-label { font-size: 10px; color: #6b7280; font-weight: 600; text-transform: uppercase; }

  /* ── Main Grid ─────────────────────────────────── */
  .dash-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 10px;
    flex: 1; /* Takes up remaining height */
    min-height: 0; /* Important for flex child */
  }

  .left-col, .right-col {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-height: 0;
  }

  .card {
    display: flex;
    flex-direction: column;
    margin: 0;
    border-radius: 8px;
    min-height: 0; /* Let it shrink */
  }
  .card-header {
    padding: 8px 12px;
    flex-shrink: 0;
  }
  .card-header h5 {
    font-size: 12px;
    margin: 0;
  }
  .card-body {
    padding: 8px 12px;
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    position: relative;
  }
  
  .chart-box {
    position: relative;
    width: 100%;
    flex: 1;
    min-height: 0;
  }

  .table-wrapper {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
  }
  .table { margin: 0; }
  .table th, .table td { padding: 6px 10px; font-size: 11px; }

  .activity-list { list-style: none; margin: 0; padding: 0; overflow-y: auto; flex: 1; }
  .activity-list li {
    display: flex; gap: 8px; align-items: flex-start;
    padding: 6px 0; border-bottom: 1px solid #f1f5f9;
  }
  .activity-list li:last-child { border-bottom: none; }
  .activity-dot {
    width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; flex-shrink: 0;
    background: #eff6ff; color: #1d4ed8;
  }
  .activity-text { font-size: 11px; font-weight: 600; color: #1e293b; line-height: 1.2; }
  .activity-meta { font-size: 9px; color: #6b7280; margin-top: 2px; }

  /* Classification row */
  .classif-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    flex-shrink: 0;
  }
  .classif-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 6px 8px;
    display: flex; align-items: center; gap: 8px; text-decoration: none;
  }
  .classif-card .ci { width: 24px; height: 24px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; }
  .classif-card .cv { font-size: 13px; font-weight: 800; line-height: 1; }
  .classif-card .cl { font-size: 9px; color: #6b7280; font-weight: 600; white-space: nowrap; }

  /* Completion Bar */
  .completion-wrapper {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 8px 12px;
    display: flex; align-items: center; gap: 16px; flex-shrink: 0;
  }
  .progress-bar-wrap { background: #f1f5f9; border-radius: 99px; height: 6px; flex: 1; overflow: hidden; margin-top: 4px; }
  .progress-bar-fill { height: 100%; border-radius: 99px; }

</style>
@endsection

@section('content')

{{-- ── Hero ──────────────────────────────────────────────── --}}
<div class="dash-hero">
  <div>
    <h1><i class="fas fa-tachometer-alt"></i> Administrative Dashboard</h1>
    <p>Monitor requests, collections, and activity in real time.</p>
  </div>
  <div class="hero-meta">
    <div class="hero-meta-item"><i class="fas fa-calendar-day"></i> {{ now()->format('M j, Y') }}</div>
    <div class="hero-meta-item"><i class="fas fa-inbox"></i> {{ $stats['today_requests'] }} requests today</div>
  </div>
</div>

{{-- ── Primary KPI Cards ─────────────────────────────────── --}}
<div class="stats-primary">
  <div class="kpi-card" style="--kpi-color:#1d4ed8; --kpi-bg:#eff6ff;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-users"></i></div>
      <span class="kpi-badge neutral">All Time</span>
    </div>
    <div><div class="kpi-value">{{ number_format($stats['residents']) }}</div><div class="kpi-label">Residents</div></div>
  </div>
  <div class="kpi-card" style="--kpi-color:#d97706; --kpi-bg:#fffbeb;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-clock"></i></div>
      <span class="kpi-badge {{ $stats['pending'] > 10 ? 'down' : 'neutral' }}">{{ $stats['pending'] > 0 ? 'Action needed' : 'Clear' }}</span>
    </div>
    <div><div class="kpi-value">{{ number_format($stats['pending']) }}</div><div class="kpi-label">Pending Requests</div></div>
  </div>
  <div class="kpi-card" style="--kpi-color:#0d9488; --kpi-bg:#f0fdfa;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
      <span class="kpi-badge neutral">{{ $stats['completion_rate'] }}%</span>
    </div>
    <div><div class="kpi-value">{{ number_format($stats['released']) }}</div><div class="kpi-label">Released</div></div>
  </div>
  <div class="kpi-card" style="--kpi-color:#7c3aed; --kpi-bg:#f5f3ff;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-peso-sign"></i></div>
      @if($stats['revenue_trend'] >= 0)
        <span class="kpi-badge up"><i class="fas fa-arrow-up"></i> {{ $stats['revenue_trend'] }}%</span>
      @else
        <span class="kpi-badge down"><i class="fas fa-arrow-down"></i> {{ abs($stats['revenue_trend']) }}%</span>
      @endif
    </div>
    <div><div class="kpi-value">₱{{ number_format($stats['month_revenue'], 0) }}</div><div class="kpi-label">Revenue This Month</div></div>
  </div>
  <div class="kpi-card" style="--kpi-color:#b91c1c; --kpi-bg:#fef2f2;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-file-alt"></i></div>
      <span class="kpi-badge neutral">This month</span>
    </div>
    <div><div class="kpi-value">{{ number_format($stats['month_requests']) }}</div><div class="kpi-label">New Requests</div></div>
  </div>
</div>

{{-- ── Classification Quick Stats ────────────────────────── --}}
<div class="classif-row">
  <a href="{{ route('admin.residents', ['classification' => 'pwd']) }}" class="classif-card">
    <div class="ci" style="background:#f5f3ff; color:#7c3aed;"><i class="fas fa-wheelchair"></i></div>
    <div><div class="cv" style="color:#7c3aed;">{{ $stats['pwd'] }}</div><div class="cl">PWD</div></div>
  </a>
  <a href="{{ route('admin.residents', ['classification' => 'senior']) }}" class="classif-card">
    <div class="ci" style="background:#fffbeb; color:#d97706;"><i class="fas fa-user-clock"></i></div>
    <div><div class="cv" style="color:#d97706;">{{ $stats['senior'] }}</div><div class="cl">Senior Citizens</div></div>
  </a>
  <a href="{{ route('admin.residents', ['classification' => 'single_parent']) }}" class="classif-card">
    <div class="ci" style="background:#f0fdfa; color:#0d9488;"><i class="fas fa-child"></i></div>
    <div><div class="cv" style="color:#0d9488;">{{ $stats['single_parent'] }}</div><div class="cl">Single Parents</div></div>
  </a>
  <a href="{{ route('admin.reports') }}" class="classif-card">
    <div class="ci" style="background:#eff6ff; color:#1d4ed8;"><i class="fas fa-chart-pie"></i></div>
    <div><div class="cv" style="color:#1d4ed8;">₱{{ number_format($stats['revenue'], 0) }}</div><div class="cl">Total Revenue</div></div>
  </a>
  <a href="{{ route('admin.residents', ['status' => 'pending']) }}" class="classif-card">
    <div class="ci" style="background:#fef2f2; color:#b91c1c;"><i class="fas fa-user-plus"></i></div>
    <div><div class="cv" style="color:#b91c1c;">{{ $stats['pending_residents'] }}</div><div class="cl">Pending Auth</div></div>
  </a>
</div>

{{-- ── Main Grid ────────────────────────────────── --}}
<div class="dash-grid">

  {{-- Left: Charts --}}
  <div class="left-col">
    <div class="card" style="flex:1;">
      <div class="card-header">
        <h5><i class="fas fa-chart-bar" style="color:var(--primary);margin-right:6px;"></i>Monthly Requests vs Released</h5>
      </div>
      <div class="card-body">
        <div class="chart-box"><canvas id="monthlyChart"></canvas></div>
      </div>
    </div>
    <div class="card" style="flex:1;">
      <div class="card-header">
        <h5><i class="fas fa-peso-sign" style="color:var(--primary);margin-right:6px;"></i>Monthly Collections</h5>
      </div>
      <div class="card-body">
        <div class="chart-box"><canvas id="revenueChart"></canvas></div>
      </div>
    </div>
  </div>

  {{-- Right: Activity & Requests --}}
  <div class="right-col">
    <div style="display:flex; gap:10px; flex:1; min-height:0;">
      <div class="card" style="flex:1;">
        <div class="card-header">
          <h5><i class="fas fa-chart-pie" style="color:var(--primary);margin-right:6px;"></i>Request Types</h5>
        </div>
        <div class="card-body">
          <div class="chart-box"><canvas id="typeChart"></canvas></div>
        </div>
      </div>
      <div class="card" style="flex:1;">
        <div class="card-header">
          <h5><i class="fas fa-history" style="color:var(--primary);margin-right:6px;"></i>Recent Activity</h5>
        </div>
        <div class="card-body">
          <ul class="activity-list">
            @forelse($recentActivity as $log)
              <li>
                <div class="activity-dot"><i class="fas fa-bolt"></i></div>
                <div>
                  <div class="activity-text">{{ Str::limit($log->description ?: $log->action, 35) }}</div>
                  <div class="activity-meta">
                    {{ explode('@', $log->user?->email)[0] ?? 'System' }} &middot; {{ $log->created_at->diffForHumans(null, true, true) }}
                  </div>
                </div>
              </li>
            @empty
              <li><div class="activity-meta">No recent activity.</div></li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    <div class="card" style="flex:1;">
      <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h5><i class="fas fa-list" style="color:var(--primary);margin-right:6px;"></i>Recent Requests</h5>
        <a href="{{ route('admin.requests') }}" class="btn btn-outline-primary" style="padding:2px 8px; font-size:10px;">View All</a>
      </div>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Tracking #</th>
              <th>Resident</th>
              <th>Document</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentRequests->take(5) as $req)
              <tr style="cursor:pointer;" onclick="window.location='{{ route('admin.requests', ['view' => $req->tracking_number]) }}'">
                <td><code>{{ substr($req->tracking_number, -6) }}</code></td>
                <td style="font-weight:600;">{{ $req->resident?->full_name }}</td>
                <td>{{ $req->certificate?->name }}</td>
                <td>
                  <span class="badge bg-{{ $req->status === 'pending' ? 'warning' : ($req->status === 'processing' ? 'info' : ($req->status === 'approved' ? 'success' : ($req->status === 'rejected' ? 'danger' : 'primary'))) }}">
                    {{ ucfirst($req->status) }}
                  </span>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted">No requests yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const monthlyLabels  = @json($monthly['labels']);
  const monthlyData    = @json($monthly['data']);
  const monthlyRel     = @json($monthly['released']);
  const typeLabels     = @json($byType['labels']);
  const typeData       = @json($byType['data']);
  const revLabels      = @json($revenueChart['labels']);
  const revData        = @json($revenueChart['data']);

  Chart.defaults.font.family = 'Inter, sans-serif';
  Chart.defaults.font.size = 10;

  // Monthly Requests vs Released
  if (document.getElementById('monthlyChart')) {
    new Chart(document.getElementById('monthlyChart'), {
      type: 'bar',
      data: {
        labels: monthlyLabels,
        datasets: [
          { label: 'Total', data: monthlyData, backgroundColor: '#b91c1c', borderRadius: 3, barPercentage: 0.6 },
          { label: 'Released', data: monthlyRel, backgroundColor: '#0d9488', borderRadius: 3, barPercentage: 0.6 }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 8, font: { size: 9 } } } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1 } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  // Monthly Revenue Line
  if (document.getElementById('revenueChart')) {
    new Chart(document.getElementById('revenueChart'), {
      type: 'line',
      data: {
        labels: revLabels,
        datasets: [{
          label: 'Revenue', data: revData, borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,0.08)',
          fill: true, tension: 0.4, pointRadius: 3
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { callback: v => '₱' + v } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  // Doughnut – Requests by Type
  if (document.getElementById('typeChart')) {
    new Chart(document.getElementById('typeChart'), {
      type: 'doughnut',
      data: {
        labels: typeLabels,
        datasets: [{ data: typeData, backgroundColor: ['#b91c1c','#0d9488','#7c3aed','#d97706','#0ea5e9','#db2777'], borderWidth: 1 }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '60%',
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, font: { size: 9 } } } }
      }
    });
  }
</script>
@endsection
