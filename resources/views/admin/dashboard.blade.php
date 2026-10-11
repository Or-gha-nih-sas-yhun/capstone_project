@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
  /* ── Zoom & Responsive Adaptations ── */
  html, body {
    overflow-x: hidden;
  }

  .page-content {
    padding: 12px 16px;
    min-height: calc(100vh - var(--topbar-h));
    display: flex;
    flex-direction: column;
    gap: 12px;
    box-sizing: border-box;
    overflow-y: auto;
    overflow-x: hidden;
  }

  /* ── KPI Row ── */
  .stats-primary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    flex-shrink: 0;
  }
  .kpi-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
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
  .kpi-top { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 2px; 
  }
  .kpi-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    background: var(--kpi-bg, #f1f5f9);
    color: var(--kpi-color, #64748b);
    flex-shrink: 0;
  }
  .kpi-badge { 
    font-size: 11px; 
    font-weight: 700; 
    padding: 2px 8px; 
    border-radius: 20px; 
    white-space: nowrap; 
  }
  .kpi-badge.up     { background: #f0fdf4; color: #15803d; }
  .kpi-badge.down   { background: #fef2f2; color: #b91c1c; }
  .kpi-badge.neutral{ background: #f1f5f9; color: #64748b; }
  .kpi-value { 
    font-size: 24px; 
    font-weight: 800; 
    color: #111; 
    line-height: 1.1; 
    word-break: break-all; 
  }
  .kpi-label { 
    font-size: 11px; 
    color: #6b7280; 
    font-weight: 600; 
    text-transform: uppercase; 
    letter-spacing: .4px; 
  }

  /* ── Classification Strip ── */
  .classif-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 12px;
    flex-shrink: 0;
  }
  .classif-card {
    background: #fff; 
    border: 1px solid #e5e7eb; 
    border-radius: 8px; 
    padding: 10px 14px;
    display: flex; 
    align-items: center; 
    gap: 10px; 
    text-decoration: none;
    transition: box-shadow .2s, transform .2s; 
    min-width: 0;
  }
  .classif-card:hover { 
    box-shadow: 0 4px 12px rgba(0,0,0,.08); 
    transform: translateY(-1px); 
  }
  .classif-card .ci { 
    width: 34px; height: 34px; border-radius: 7px; 
    display: flex; align-items: center; justify-content: center; 
    font-size: 14px; flex-shrink: 0; 
  }
  .classif-card .cv { font-size: 18px; font-weight: 800; line-height: 1; }
  .classif-card .cl { font-size: 11px; color: #4b5563; font-weight: 600; margin-top: 2px; }

  /* ── Main Grid ── */
  .dash-grid {
    display: grid;
    grid-template-columns: minmax(320px, 1fr) minmax(360px, 1.35fr);
    gap: 12px;
    flex: 1;
    min-height: 0;
  }
  @media (max-width: 1024px) {
    .dash-grid {
      grid-template-columns: 1fr;
    }
  }
  .left-col, .right-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
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
  }
  .card-header {
    padding: 10px 14px;
    border-bottom: 1px solid #f3f4f6;
    flex-shrink: 0;
    display: flex; 
    align-items: center; 
    justify-content: space-between;
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
    display: flex;
    flex-direction: column;
    min-height: 0;
  }

  /* Chart containers – fluid and scalable with zoom */
  .chart-box {
    position: relative;
    flex: 1;
    min-height: 190px;
    width: 100%;
  }
  .chart-box canvas { 
    display: block; 
    width: 100% !important; 
    height: 100% !important; 
  }

  /* Right pane inner layout */
  .right-top { 
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 12px; 
    flex: 1; 
    min-height: 0; 
  }
  .right-top .card { flex: 1; }

  /* Table */
  .table-wrapper { 
    overflow-y: auto; 
    max-height: 250px;
    flex: 1; 
    min-height: 0; 
  }
  .table { width: 100%; border-collapse: collapse; }
  .table th {
    background: #f9fafb; font-size: 11px; font-weight: 700; color: #6b7280;
    text-transform: uppercase; padding: 8px 12px; border-bottom: 1px solid #e5e7eb;
    position: sticky; top: 0; z-index: 1;
  }
  .table td { padding: 8px 12px; font-size: 12.5px; color: #1f2937; border-bottom: 1px solid #f3f4f6; }

  /* Activity */
  .activity-list { 
    list-style: none; 
    margin: 0; 
    padding: 0; 
    overflow-y: auto; 
    max-height: 200px;
    flex: 1; 
  }
  .activity-list li { display: flex; gap: 10px; align-items: flex-start; padding: 7px 0; border-bottom: 1px solid #f3f4f6; }
  .activity-list li:last-child { border-bottom: none; }
  .activity-dot { 
    width: 28px; height: 28px; border-radius: 50%; 
    display: flex; align-items: center; justify-content: center; 
    font-size: 11px; flex-shrink: 0; background: #eff6ff; color: #1d4ed8; 
  }
  .activity-text { font-size: 12px; font-weight: 600; color: #1e293b; line-height: 1.3; }
  .activity-meta { font-size: 10px; color: #9ca3af; margin-top: 2px; }
</style>
@endsection

@section('content')

{{-- ── KPI Cards ── --}}
<div class="stats-primary">
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
    <div class="kpi-value" style="font-size:18px;">₱{{ number_format($stats['month_revenue'] ?? $stats['revenue'] ?? 0, 0) }}</div>
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

{{-- ── Classification Strip ── --}}
<div class="classif-row">
  <a href="{{ route('admin.residents', ['classification' => 'pwd']) }}" class="classif-card">
    <div class="ci" style="background:#f5f3ff;color:#7c3aed;"><i class="fas fa-wheelchair"></i></div>
    <div><div class="cv" style="color:#7c3aed;">{{ $stats['pwd'] ?? 0 }}</div><div class="cl">PWD Residents</div></div>
  </a>
  <a href="{{ route('admin.residents', ['classification' => 'senior']) }}" class="classif-card">
    <div class="ci" style="background:#fffbeb;color:#d97706;"><i class="fas fa-user-clock"></i></div>
    <div><div class="cv" style="color:#d97706;">{{ $stats['senior'] ?? 0 }}</div><div class="cl">Senior Citizens</div></div>
  </a>
  <a href="{{ route('admin.residents', ['classification' => 'single_parent']) }}" class="classif-card">
    <div class="ci" style="background:#f0fdfa;color:#0d9488;"><i class="fas fa-child"></i></div>
    <div><div class="cv" style="color:#0d9488;">{{ $stats['single_parent'] ?? 0 }}</div><div class="cl">Single Parents</div></div>
  </a>
  <a href="{{ route('admin.reports') }}" class="classif-card">
    <div class="ci" style="background:#eff6ff;color:#1d4ed8;"><i class="fas fa-coins"></i></div>
    <div><div class="cv" style="color:#1d4ed8;">₱{{ number_format($stats['revenue'] ?? 0, 0) }}</div><div class="cl">Total Collections</div></div>
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
            @forelse($recentActivity ?? [] as $log)
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
        </div>
      </div>
    </div>

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

  Chart.defaults.font.family = 'Inter, sans-serif';
  Chart.defaults.font.size   = 12;
  Chart.defaults.color       = '#6b7280';

  // Bar Chart
  if (document.getElementById('monthlyChart')) {
    new Chart(document.getElementById('monthlyChart'), {
      type: 'bar',
      data: {
        labels: monthlyLabels.length ? monthlyLabels : ['No Data'],
        datasets: [
          { label: 'Total Received', data: monthlyData.length ? monthlyData : [0], backgroundColor: '#b91c1c', borderRadius: 4, barPercentage: .65 },
          { label: 'Released',       data: monthlyRel.length ? monthlyRel : [0],  backgroundColor: '#0d9488', borderRadius: 4, barPercentage: .65 }
        ]
      },
      options: {
        responsive: true, 
        maintainAspectRatio: false,
        plugins: { 
          legend: { position: 'top', labels: { boxWidth: 12, font: { size: 12, weight: 'bold' }, padding: 10 } },
          tooltip: { bodyFont: { size: 13 }, padding: 10 } 
        },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { size: 11 } } },
          x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } }
        }
      }
    });
  }

  // Line Chart
  if (document.getElementById('revenueChart')) {
    new Chart(document.getElementById('revenueChart'), {
      type: 'line',
      data: {
        labels: revLabels.length ? revLabels : ['No Data'],
        datasets: [{
          label: 'Collections', 
          data: revData.length ? revData : [0], 
          borderColor: '#7c3aed', 
          backgroundColor: 'rgba(124,58,237,.1)',
          fill: true, 
          tension: 0.4, 
          pointRadius: 5, 
          pointHoverRadius: 7, 
          borderWidth: 2.5
        }]
      },
      options: {
        responsive: true, 
        maintainAspectRatio: false,
        plugins: { 
          legend: { display: false }, 
          tooltip: { 
            bodyFont: { size: 13 }, 
            padding: 10,
            callbacks: { label: ctx => '₱' + Number(ctx.parsed.y).toLocaleString() } 
          } 
        },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, callback: v => '₱' + Number(v).toLocaleString() } },
          x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } }
        }
      }
    });
  }

  // Doughnut Chart
  if (document.getElementById('typeChart')) {
    new Chart(document.getElementById('typeChart'), {
      type: 'doughnut',
      data: {
        labels: typeLabels.length ? typeLabels : ['No Data'],
        datasets: [{ 
          data: typeData.length ? typeData : [1],
          backgroundColor: ['#b91c1c','#0d9488','#7c3aed','#d97706','#0ea5e9','#db2777'],
          borderWidth: 2, 
          borderColor: '#fff' 
        }]
      },
      options: {
        responsive: true, 
        maintainAspectRatio: false, 
        cutout: '58%',
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11, weight: '600' }, padding: 8 } },
          tooltip: { bodyFont: { size: 13 }, padding: 10 }
        }
      }
    });
  }
</script>
@endsection
