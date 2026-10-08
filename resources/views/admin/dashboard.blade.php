@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
  /* User-Friendly Spaced Layout */
  .page-content {
    padding: 24px 30px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    /* Allow scrolling naturally */
    overflow-y: auto;
  }

  /* ── Stat Cards Row ─────────────────────────────────── */
  .stats-primary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
  }
  .kpi-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
  }
  .kpi-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 4px;
    background: var(--kpi-color, #e5e7eb);
  }
  .kpi-top { display: flex; justify-content: space-between; align-items: flex-start; }
  .kpi-icon {
    width: 48px; height: 48px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
    background: var(--kpi-bg, #f1f5f9);
    color: var(--kpi-color, #64748b);
  }
  .kpi-badge {
    font-size: 13px; font-weight: 700;
    padding: 4px 10px; border-radius: 20px;
  }
  .kpi-badge.up   { background: #f0fdf4; color: #15803d; }
  .kpi-badge.down { background: #fef2f2; color: #b91c1c; }
  .kpi-badge.neutral { background: #f1f5f9; color: #64748b; }
  .kpi-value { font-size: 32px; font-weight: 800; color: #111; line-height: 1; }
  .kpi-label { font-size: 15px; color: #4b5563; font-weight: 600; margin-top: 4px; }

  /* Classification row */
  .classif-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
  }
  .classif-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px 20px;
    display: flex; align-items: center; gap: 16px; text-decoration: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
  }
  .classif-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.06); }
  .classif-card .ci { width: 42px; height: 42px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;}
  .classif-card .cv { font-size: 22px; font-weight: 800; line-height: 1; margin-bottom: 4px;}
  .classif-card .cl { font-size: 14px; color: #4b5563; font-weight: 600; }

  /* ── Main Grid ─────────────────────────────────── */
  .dash-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr);
    gap: 24px;
  }

  .card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    margin-bottom: 24px;
  }
  .card:last-child { margin-bottom: 0; }
  
  .card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #f3f4f6;
    display: flex; justify-content: space-between; align-items: center;
  }
  .card-header h5 {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
  }
  .card-body {
    padding: 24px;
  }
  
  /* Make charts large and visible */
  .chart-box {
    position: relative;
    width: 100%;
    height: 320px; /* Fixed high height for proper rendering */
  }

  .table-wrapper {
    overflow-x: auto;
  }
  .table { width: 100%; min-width: 500px; border-collapse: collapse; }
  .table th { background: #f9fafb; font-weight: 700; color: #374151; text-transform: uppercase; font-size: 12px; padding: 14px 16px; border-bottom: 2px solid #e5e7eb; }
  .table td { padding: 16px; font-size: 15px; color: #1f2937; border-bottom: 1px solid #f3f4f6; }

  .activity-list { list-style: none; margin: 0; padding: 0; }
  .activity-list li {
    display: flex; gap: 16px; align-items: flex-start;
    padding: 16px 0; border-bottom: 1px solid #f1f5f9;
  }
  .activity-list li:last-child { border-bottom: none; }
  .activity-dot {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
    background: #eff6ff; color: #1d4ed8;
  }
  .activity-text { font-size: 15px; font-weight: 600; color: #1e293b; line-height: 1.4; }
  .activity-meta { font-size: 13px; color: #6b7280; margin-top: 4px; }

  @media (max-width: 1024px) {
    .dash-grid { grid-template-columns: 1fr; }
  }
</style>
@endsection

@section('content')

{{-- ── Primary KPI Cards ─────────────────────────────────── --}}
<div class="stats-primary">
  <div class="kpi-card" style="--kpi-color:#1d4ed8; --kpi-bg:#eff6ff;">
    <div class="kpi-top">
      <div class="kpi-icon"><i class="fas fa-users"></i></div>
      <span class="kpi-badge neutral">All Time</span>
    </div>
    <div><div class="kpi-value">{{ number_format($stats['residents']) }}</div><div class="kpi-label">Total Residents</div></div>
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
    <div><div class="kpi-value">{{ number_format($stats['released']) }}</div><div class="kpi-label">Released Documents</div></div>
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
    <div><div class="cv" style="color:#1d4ed8;">₱{{ number_format($stats['revenue'], 0) }}</div><div class="cl">Total Revenue</div></div>
  </a>
</div>

{{-- ── Main Grid ────────────────────────────────── --}}
<div class="dash-grid">

  {{-- Left: Charts --}}
  <div>
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-chart-bar" style="color:var(--primary);margin-right:10px;"></i>Monthly Requests vs Released</h5>
      </div>
      <div class="card-body">
        <div class="chart-box"><canvas id="monthlyChart"></canvas></div>
      </div>
    </div>
    
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-peso-sign" style="color:var(--primary);margin-right:10px;"></i>Monthly Collections</h5>
      </div>
      <div class="card-body">
        <div class="chart-box"><canvas id="revenueChart"></canvas></div>
      </div>
    </div>
  </div>

  {{-- Right: Activity & Requests --}}
  <div>
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-chart-pie" style="color:var(--primary);margin-right:10px;"></i>Requests by Document Type</h5>
      </div>
      <div class="card-body">
        <div class="chart-box" style="height:280px;"><canvas id="typeChart"></canvas></div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-list" style="color:var(--primary);margin-right:10px;"></i>Recent Requests</h5>
        <a href="{{ route('admin.requests') }}" class="btn btn-outline-primary" style="font-size: 14px; padding: 6px 16px;">View All</a>
      </div>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Tracking #</th>
              <th>Resident</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentRequests->take(5) as $req)
              <tr style="cursor:pointer;" onclick="window.location='{{ route('admin.requests', ['view' => $req->tracking_number]) }}'">
                <td><code style="font-size:14px;">{{ substr($req->tracking_number, -6) }}</code></td>
                <td style="font-weight:600;">{{ $req->resident?->full_name }}</td>
                <td>
                  <span class="badge bg-{{ $req->status === 'pending' ? 'warning' : ($req->status === 'processing' ? 'info' : ($req->status === 'approved' ? 'success' : ($req->status === 'rejected' ? 'danger' : 'primary'))) }}" style="font-size:13px; padding: 6px 12px;">
                    {{ ucfirst($req->status) }}
                  </span>
                </td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted" style="padding: 24px;">No requests yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-history" style="color:var(--primary);margin-right:10px;"></i>Recent System Activity</h5>
      </div>
      <div class="card-body">
        <ul class="activity-list">
          @forelse($recentActivity as $log)
            <li>
              <div class="activity-dot"><i class="fas fa-bolt"></i></div>
              <div>
                <div class="activity-text">{{ $log->description ?: $log->action }}</div>
                <div class="activity-meta">
                  {{ explode('@', $log->user?->email)[0] ?? 'System' }} &middot; {{ $log->created_at->diffForHumans(null, true, true) }} ago
                </div>
              </div>
            </li>
          @empty
            <li><div class="activity-meta" style="padding: 16px;">No recent activity.</div></li>
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
  const monthlyLabels  = @json($monthly['labels']);
  const monthlyData    = @json($monthly['data']);
  const monthlyRel     = @json($monthly['released']);
  const typeLabels     = @json($byType['labels']);
  const typeData       = @json($byType['data']);
  const revLabels      = @json($revenueChart['labels']);
  const revData        = @json($revenueChart['data']);

  // Increase Global Chart Fonts for Readability
  Chart.defaults.font.family = 'Inter, sans-serif';
  Chart.defaults.font.size = 14; 
  Chart.defaults.color = '#4b5563';

  // Monthly Requests vs Released Bar Chart
  if (document.getElementById('monthlyChart')) {
    new Chart(document.getElementById('monthlyChart'), {
      type: 'bar',
      data: {
        labels: monthlyLabels,
        datasets: [
          { label: 'Total Received', data: monthlyData, backgroundColor: '#b91c1c', borderRadius: 4, barPercentage: 0.7 },
          { label: 'Total Released', data: monthlyRel, backgroundColor: '#0d9488', borderRadius: 4, barPercentage: 0.7 }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { 
          legend: { position: 'top', labels: { boxWidth: 16, font: { size: 15, weight: 'bold' }, padding: 20 } },
          tooltip: { titleFont: { size: 16 }, bodyFont: { size: 15 }, padding: 12 }
        },
        scales: {
          y: { beginAtZero: true, grid: { color: '#e5e7eb' }, ticks: { stepSize: 1, font: { size: 14 } } },
          x: { grid: { display: false }, ticks: { font: { size: 14, weight: '600' } } }
        }
      }
    });
  }

  // Monthly Revenue Line Chart
  if (document.getElementById('revenueChart')) {
    new Chart(document.getElementById('revenueChart'), {
      type: 'line',
      data: {
        labels: revLabels,
        datasets: [{
          label: 'Total Revenue', data: revData, borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,0.1)',
          fill: true, tension: 0.4, pointRadius: 6, pointHoverRadius: 8, borderWidth: 3
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { 
          legend: { display: false },
          tooltip: { titleFont: { size: 16 }, bodyFont: { size: 15 }, padding: 12, callbacks: { label: function(context) { return '₱' + context.parsed.y.toLocaleString(); } } }
        },
        scales: {
          y: { beginAtZero: true, grid: { color: '#e5e7eb' }, ticks: { font: { size: 14 }, callback: v => '₱' + v.toLocaleString() } },
          x: { grid: { display: false }, ticks: { font: { size: 14, weight: '600' } } }
        }
      }
    });
  }

  // Doughnut – Requests by Type Pie Chart
  if (document.getElementById('typeChart')) {
    new Chart(document.getElementById('typeChart'), {
      type: 'doughnut',
      data: {
        labels: typeLabels,
        datasets: [{ data: typeData, backgroundColor: ['#b91c1c','#0d9488','#7c3aed','#d97706','#0ea5e9','#db2777'], borderWidth: 2, borderColor: '#fff' }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '55%',
        plugins: { 
          legend: { position: 'right', labels: { boxWidth: 16, font: { size: 14, weight: '600' }, padding: 16 } },
          tooltip: { titleFont: { size: 16 }, bodyFont: { size: 15 }, padding: 12 }
        }
      }
    });
  }
</script>
@endsection
