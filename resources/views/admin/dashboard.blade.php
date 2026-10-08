@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
  .page-content {
    padding: 18px 20px;
  }

  /* ── Hero Banner ───────────────────────────────────── */
  .dash-hero {
    background: linear-gradient(130deg, #b91c1c 0%, #7f1d1d 100%);
    border-radius: 14px;
    padding: 22px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    color: #fff;
  }
  .dash-hero h1 { font-size: 20px; margin: 0; font-weight: 800; }
  .dash-hero p  { margin: 4px 0 0; font-size: 13px; opacity: .85; }
  .dash-hero .hero-meta { display: flex; gap: 20px; flex-wrap: wrap; }
  .dash-hero .hero-meta-item { font-size: 12px; opacity: .85; display: flex; align-items: center; gap: 6px; }

  /* ── Stat Cards Row ─────────────────────────────────── */
  .stats-primary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
  }

  .kpi-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    transition: box-shadow .2s ease;
    position: relative;
    overflow: hidden;
  }
  .kpi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
  .kpi-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--kpi-color, #e5e7eb);
    border-radius: 0 0 12px 12px;
  }
  .kpi-top { display: flex; justify-content: space-between; align-items: flex-start; }
  .kpi-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
    background: var(--kpi-bg, #f1f5f9);
    color: var(--kpi-color, #64748b);
  }
  .kpi-badge {
    font-size: 11px; font-weight: 700;
    padding: 3px 8px; border-radius: 20px;
    display: flex; align-items: center; gap: 4px;
  }
  .kpi-badge.up   { background: #f0fdf4; color: #15803d; }
  .kpi-badge.down { background: #fef2f2; color: #b91c1c; }
  .kpi-badge.neutral { background: #f1f5f9; color: #64748b; }
  .kpi-value { font-size: 26px; font-weight: 800; color: #111; line-height: 1; }
  .kpi-label { font-size: 12px; color: #6b7280; font-weight: 600; }

  /* ── Classification Mini-cards ──────────────────────── */
  .classif-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
  }
  .classif-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    transition: all .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.03);
  }
  .classif-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); transform: translateY(-2px); }
  .classif-card .ci { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
  .classif-card .cv { font-size: 20px; font-weight: 800; line-height: 1; }
  .classif-card .cl { font-size: 11px; color: #6b7280; font-weight: 600; }

  /* ── Dashboard Grid ─────────────────────────────────── */
  .dash-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(0, .8fr);
    gap: 16px;
    margin-bottom: 20px;
  }
  .dash-grid-3 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
  }

  .chart-box { position: relative; width: 100%; height: 220px; }

  .activity-list { list-style: none; margin: 0; padding: 0; }
  .activity-list li {
    display: flex; gap: 12px; align-items: flex-start;
    padding: 10px 0; border-bottom: 1px solid #f1f5f9;
  }
  .activity-list li:last-child { border-bottom: none; }
  .activity-dot {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; flex-shrink: 0;
    background: #eff6ff; color: #1d4ed8;
  }
  .activity-text { font-size: 13px; font-weight: 600; color: #1e293b; }
  .activity-meta { font-size: 11px; color: #6b7280; margin-top: 1px; }

  .progress-bar-wrap { background: #f1f5f9; border-radius: 99px; height: 8px; overflow: hidden; }
  .progress-bar-fill { height: 100%; border-radius: 99px; transition: width .8s ease; }

  @media (max-width: 900px) {
    .dash-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 600px) {
    .stats-primary { grid-template-columns: repeat(2, 1fr); }
    .dash-hero { padding: 16px; }
    .dash-hero h1 { font-size: 17px; }
  }
</style>
@endsection

@section('content')

{{-- ── Hero ──────────────────────────────────────────────── --}}
<div class="dash-hero">
  <div>
    <div style="font-size:12px; opacity:.7; text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px;">
      Barangay Pili, Madridejos, Cebu
    </div>
    <h1><i class="fas fa-tachometer-alt" style="margin-right:8px; opacity:.9;"></i>Administrative Dashboard</h1>
    <p>Monitor residents, certificate requests, collections, and monthly activity in real time.</p>
  </div>
  <div class="hero-meta">
    <div class="hero-meta-item"><i class="fas fa-calendar-day"></i> {{ now()->format('l, F j, Y') }}</div>
    <div class="hero-meta-item"><i class="fas fa-inbox"></i> {{ $stats['today_requests'] }} request(s) today</div>
    <div class="hero-meta-item"><i class="fas fa-box-open"></i> {{ $stats['today_released'] }} released today</div>
  </div>
  <a href="{{ route('admin.requests') }}" class="btn" style="background:rgba(255,255,255,.15); color:#fff; border:1.5px solid rgba(255,255,255,.4); backdrop-filter:blur(4px);">
    <i class="fas fa-file-alt"></i> Manage Requests
  </a>
</div>

{{-- ── Pending Residents Alert ────────────────────────────── --}}
@if ($stats['pending_residents'] > 0)
<div class="alert alert-warning" style="margin-bottom:20px; justify-content:space-between; flex-wrap:wrap; gap:10px;">
  <div style="display:flex; align-items:center; gap:12px;">
    <i class="fas fa-user-clock" style="font-size:20px; color:var(--warning);"></i>
    <div>
      <div style="font-weight:700; color:#92400e;">{{ $stats['pending_residents'] }} Resident Registration(s) Pending Approval</div>
      <div style="font-size:12px; color:#b45309;">New registrations are waiting for your verification.</div>
    </div>
  </div>
  <a href="{{ route('admin.residents', ['status' => 'pending']) }}" class="btn btn-warning btn-sm">
    <i class="fas fa-shield-halved"></i> Review Now
  </a>
</div>
@endif

{{-- ── Primary KPI Cards ─────────────────────────────────── --}}
<div class="stats-primary">

  <div class="kpi-card" style="--kpi-color:#1d4ed8; --kpi-bg:#eff6ff;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-users"></i></div>
      <span class="kpi-badge neutral"><i class="fas fa-database"></i> All Time</span>
    </div>
    <div>
      <div class="kpi-value">{{ number_format($stats['residents']) }}</div>
      <div class="kpi-label">Active Residents</div>
    </div>
  </div>

  <div class="kpi-card" style="--kpi-color:#d97706; --kpi-bg:#fffbeb;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-clock"></i></div>
      <span class="kpi-badge {{ $stats['pending'] > 10 ? 'down' : 'neutral' }}">
        <i class="fas fa-circle" style="font-size:6px;"></i> {{ $stats['pending'] > 0 ? 'Action needed' : 'Clear' }}
      </span>
    </div>
    <div>
      <div class="kpi-value">{{ number_format($stats['pending']) }}</div>
      <div class="kpi-label">Pending Requests</div>
    </div>
  </div>

  <div class="kpi-card" style="--kpi-color:#0d9488; --kpi-bg:#f0fdfa;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
      <span class="kpi-badge neutral">{{ $stats['completion_rate'] }}%</span>
    </div>
    <div>
      <div class="kpi-value">{{ number_format($stats['released']) }}</div>
      <div class="kpi-label">Released Documents</div>
    </div>
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
    <div>
      <div class="kpi-value" style="font-size:20px;">₱{{ number_format($stats['month_revenue'], 0) }}</div>
      <div class="kpi-label">Revenue This Month</div>
    </div>
  </div>

  <div class="kpi-card" style="--kpi-color:#b91c1c; --kpi-bg:#fef2f2;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-file-alt"></i></div>
      <span class="kpi-badge neutral">{{ $stats['month_requests'] }} this month</span>
    </div>
    <div>
      <div class="kpi-value">{{ number_format($stats['total_req']) }}</div>
      <div class="kpi-label">Total Requests</div>
    </div>
  </div>

</div>

{{-- ── Completion Rate Progress Bar ──────────────────────── --}}
<div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:14px 20px; margin-bottom:20px; display:flex; align-items:center; gap:20px; flex-wrap:wrap; box-shadow:0 1px 4px rgba(0,0,0,.04);">
  <div style="flex:1; min-width:200px;">
    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
      <span style="font-size:13px; font-weight:700; color:#374151;"><i class="fas fa-tasks" style="color:var(--primary); margin-right:6px;"></i>Request Completion Rate</span>
      <span style="font-size:13px; font-weight:800; color:#0d9488;">{{ $stats['completion_rate'] }}%</span>
    </div>
    <div class="progress-bar-wrap">
      <div class="progress-bar-fill" style="width:{{ $stats['completion_rate'] }}%; background: linear-gradient(90deg, #0d9488, #10b981);"></div>
    </div>
  </div>
  <div style="display:flex; gap:20px; flex-wrap:wrap;">
    <div style="text-align:center;">
      <div style="font-size:18px; font-weight:800; color:#0d9488;">{{ $stats['released'] }}</div>
      <div style="font-size:11px; color:#6b7280;">Released</div>
    </div>
    <div style="text-align:center;">
      <div style="font-size:18px; font-weight:800; color:#d97706;">{{ $stats['approved'] }}</div>
      <div style="font-size:11px; color:#6b7280;">Approved</div>
    </div>
    <div style="text-align:center;">
      <div style="font-size:18px; font-weight:800; color:#b91c1c;">{{ $stats['rejected'] }}</div>
      <div style="font-size:11px; color:#6b7280;">Rejected</div>
    </div>
    <div style="text-align:center;">
      <div style="font-size:18px; font-weight:800; color:#1d4ed8;">{{ $stats['total_req'] }}</div>
      <div style="font-size:11px; color:#6b7280;">Total</div>
    </div>
    @if($topCert)
    <div style="text-align:center; border-left:1px solid #e5e7eb; padding-left:20px;">
      <div style="font-size:12px; font-weight:700; color:#7c3aed;"><i class="fas fa-trophy" style="margin-right:4px;"></i>{{ $topCert->name }}</div>
      <div style="font-size:11px; color:#6b7280;">Most Requested ({{ $topCert->requests_count }})</div>
    </div>
    @endif
  </div>
</div>

{{-- ── Classification Quick Stats ────────────────────────── --}}
<div class="classif-row">
  <a href="{{ route('admin.residents', ['classification' => 'pwd']) }}" class="classif-card">
    <div class="ci" style="background:#f5f3ff; color:#7c3aed;"><i class="fas fa-wheelchair"></i></div>
    <div><div class="cv" style="color:#7c3aed;">{{ $stats['pwd'] }}</div><div class="cl">PWD Residents</div></div>
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
    <div><div class="cv" style="color:#1d4ed8;">₱{{ number_format($stats['revenue'], 0) }}</div><div class="cl">Total Collections</div></div>
  </a>
  <a href="{{ route('admin.residents', ['status' => 'pending']) }}" class="classif-card">
    <div class="ci" style="background:#fef2f2; color:#b91c1c;"><i class="fas fa-user-plus"></i></div>
    <div><div class="cv" style="color:#b91c1c;">{{ $stats['pending_residents'] }}</div><div class="cl">Awaiting Approval</div></div>
  </a>
</div>

{{-- ── Charts + Table Row ────────────────────────────────── --}}
<div class="dash-grid">

  {{-- Left: Charts stacked --}}
  <div style="display:flex; flex-direction:column; gap:16px;">

    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-chart-bar" style="color:var(--primary);margin-right:8px;"></i>Monthly Requests vs Released <span style="font-size:12px; font-weight:400; color:#6b7280;">(last 6 months)</span></h5>
      </div>
      <div class="card-body">
        <div class="chart-box"><canvas id="monthlyChart"></canvas></div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-peso-sign" style="color:var(--primary);margin-right:8px;"></i>Monthly Collections <span style="font-size:12px; font-weight:400; color:#6b7280;">(last 6 months)</span></h5>
      </div>
      <div class="card-body">
        <div class="chart-box"><canvas id="revenueChart"></canvas></div>
      </div>
    </div>

  </div>

  {{-- Right: Recent requests + activity --}}
  <div style="display:flex; flex-direction:column; gap:16px;">

    <div class="card" style="flex:1;">
      <div class="card-header">
        <h5><i class="fas fa-list" style="color:var(--primary);margin-right:8px;"></i>Recent Requests</h5>
        <a href="{{ route('admin.requests') }}" class="btn btn-outline-primary btn-sm">View All</a>
      </div>
      <div class="table-wrapper">
        <table class="table" style="font-size:13px;">
          <thead>
            <tr>
              <th>Tracking #</th>
              <th>Resident</th>
              <th>Document</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentRequests as $req)
              <tr style="cursor:pointer;" onclick="window.location='{{ route('admin.requests', ['view' => $req->tracking_number]) }}'" title="Click to view">
                <td><code style="font-size:11px;">{{ substr($req->tracking_number, -8) }}</code></td>
                <td style="font-weight:600; max-width:130px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $req->resident?->full_name }}</td>
                <td style="max-width:110px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $req->certificate?->name }}</td>
                <td>
                  <span class="badge bg-{{ $req->status === 'pending' ? 'warning' : ($req->status === 'processing' ? 'info' : ($req->status === 'approved' ? 'success' : ($req->status === 'rejected' ? 'danger' : 'primary'))) }}">
                    {{ ucfirst($req->status) }}
                  </span>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted" style="padding:24px; font-size:13px;">No requests yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-history" style="color:var(--primary);margin-right:8px;"></i>Recent Activity</h5>
        <a href="{{ route('admin.activity') }}" class="btn btn-outline-primary btn-sm">View All</a>
      </div>
      <div class="card-body">
        <ul class="activity-list">
          @forelse($recentActivity as $log)
            <li>
              <div class="activity-dot"><i class="fas fa-bolt"></i></div>
              <div>
                <div class="activity-text">{{ $log->description ?: $log->action }}</div>
                <div class="activity-meta">
                  {{ $log->user?->email ?? 'System' }} &middot;
                  <span title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</span>
                </div>
              </div>
            </li>
          @empty
            <li><div class="activity-meta" style="padding:12px 0;">No recent activity.</div></li>
          @endforelse
        </ul>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-chart-pie" style="color:var(--primary);margin-right:8px;"></i>Requests by Type</h5>
      </div>
      <div class="card-body">
        @if(empty($byType['data']))
          <p class="text-muted text-center" style="font-size:12px; margin:0;">No data yet.</p>
        @else
          <div class="chart-box" style="height:180px;"><canvas id="typeChart"></canvas></div>
        @endif
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

  // Monthly Requests vs Released
  if (document.getElementById('monthlyChart')) {
    new Chart(document.getElementById('monthlyChart'), {
      type: 'bar',
      data: {
        labels: monthlyLabels.length ? monthlyLabels : ['No Data'],
        datasets: [
          {
            label: 'Total Requests',
            data: monthlyData.length ? monthlyData : [0],
            backgroundColor: '#b91c1c',
            borderRadius: 5,
            barPercentage: 0.6,
          },
          {
            label: 'Released',
            data: monthlyRel.length ? monthlyRel : [0],
            backgroundColor: '#0d9488',
            borderRadius: 5,
            barPercentage: 0.6,
          }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { size: 10 } } },
          x: { grid: { display: false }, ticks: { font: { size: 10 } } }
        }
      }
    });
  }

  // Monthly Revenue Line
  if (document.getElementById('revenueChart')) {
    new Chart(document.getElementById('revenueChart'), {
      type: 'line',
      data: {
        labels: revLabels.length ? revLabels : ['No Data'],
        datasets: [{
          label: 'Collections (PHP)',
          data: revData.length ? revData : [0],
          borderColor: '#7c3aed',
          backgroundColor: 'rgba(124,58,237,0.08)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#7c3aed',
          pointRadius: 5,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 }, callback: v => '₱' + v.toLocaleString() } },
          x: { grid: { display: false }, ticks: { font: { size: 10 } } }
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
        datasets: [{
          data: typeData,
          backgroundColor: ['#b91c1c','#0d9488','#7c3aed','#d97706','#0ea5e9','#db2777'],
          borderWidth: 2, borderColor: '#fff'
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '65%',
        plugins: {
          legend: { position: 'right', labels: { boxWidth: 10, padding: 8, font: { size: 10 } } }
        }
      }
    });
  }
</script>
@endsection
