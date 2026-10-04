@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
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
    min-height: 0;
    display: flex;
    flex-direction: column;
  }

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
</style>
@endsection

@section('content')
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
        </div>
      </div>
    </div>

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
      </div>
    </div>
  </div>

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
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const monthlyLabels = @json($monthly['labels']);
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
</script>
@endsection
