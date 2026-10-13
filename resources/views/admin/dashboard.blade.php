@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
  .page-content {
    min-height: calc(100vh - var(--topbar-h));
    height: auto;
    overflow: visible;
    padding: 24px clamp(18px, 2.4vw, 36px);
  }

  .dashboard-shell {
    display: flex;
    flex-direction: column;
    gap: 18px;
    min-height: calc(100vh - var(--topbar-h) - 48px);
    max-width: 1760px;
    margin: 0 auto;
  }

  .dashboard-hero {
    background: linear-gradient(115deg, #fff 0%, #fff 72%, #fff7f7 100%);
    border: 1px solid #e8ebf0;
    border-left: 5px solid var(--primary);
    border-radius: 12px;
    padding: 18px 22px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, .045);
    display: flex;
    justify-content: space-between;
    gap: 24px;
    align-items: center;
  }

  .dashboard-hero h1 {
    font-size: clamp(20px, 2vw, 26px);
    margin: 0;
    color: #161616;
    letter-spacing: -.4px;
  }

  .dashboard-hero p {
    margin: 5px 0 0;
    font-size: 13px;
    color: var(--gray);
  }

  .dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
  }

  .dashboard-stats .stat-card {
    position: relative;
    min-width: 0;
    min-height: 98px;
    padding: 16px 18px;
    gap: 14px;
    border: 1px solid #e8ebf0;
    border-top: 3px solid var(--primary);
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(15, 23, 42, .04);
  }

  .dashboard-stats .stat-icon {
    width: 46px;
    height: 46px;
    font-size: 18px;
    border-radius: 11px;
  }

  .dashboard-stats .stat-value {
    font-size: 24px;
    line-height: 1.15;
  }

  .dashboard-stats .stat-label {
    margin-top: 4px;
    font-size: 12px;
  }

  .dashboard-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(390px, 1fr);
    gap: 18px;
    align-items: stretch;
    min-height: 0;
  }

  .chart-panel {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    grid-template-rows: minmax(300px, 1.15fr) minmax(250px, .9fr);
    gap: 18px;
    min-width: 0;
  }

  .chart-panel .chart-primary {
    grid-column: 1 / -1;
  }

  .dashboard-grid .card,
  .chart-panel .card {
    min-height: 0;
    min-width: 0;
    display: flex;
    flex-direction: column;
    border: 1px solid #e8ebf0;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, .045);
  }

  .dashboard-grid .card-header {
    min-height: 56px;
    padding: 12px 18px;
    flex-shrink: 0;
    border-bottom-color: #edf0f4;
  }

  .dashboard-grid .card-body {
    padding: 16px 18px;
    flex: 1;
    min-height: 0;
  }

  .chart-box {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 190px;
  }

  .dashboard-table th,
  .dashboard-table td {
    padding: 12px 14px;
    font-size: 12.5px;
  }

  .dashboard-grid .table-wrapper {
    flex: 1;
    min-height: 0;
    overflow: auto;
  }

  .dashboard-grid .table {
    table-layout: fixed;
    min-width: 560px;
  }

  .dashboard-grid .table td,
  .dashboard-grid .table th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .dashboard-grid .badge {
    font-size: 11px;
    padding: 5px 9px;
  }

  .dashboard-shell .alert {
    margin: 0;
    padding: 13px 16px;
    border-radius: 10px;
  }

  @media (max-width: 1100px) {
    .dashboard-grid {
      grid-template-columns: minmax(0, 1fr);
    }

    .dashboard-grid > .card {
      min-height: 420px;
    }
  }

  @media (max-width: 576px) {
    .page-content {
      padding: 16px 12px;
    }

    .dashboard-shell {
      gap: 14px;
    }

    .dashboard-hero {
      align-items: flex-start;
      flex-direction: column;
      padding: 16px;
    }

    .dashboard-hero .btn {
      width: 100%;
      justify-content: center;
    }

    .dashboard-stats {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
    }

    .dashboard-stats .stat-card {
      min-height: 84px;
      padding: 12px;
      gap: 10px;
    }

    .dashboard-stats .stat-icon {
      width: 38px;
      height: 38px;
      font-size: 15px;
    }

    .dashboard-stats .stat-value {
      font-size: 20px;
    }

    .dashboard-stats .stat-label {
      font-size: 10px;
    }

    .chart-panel {
      grid-template-columns: minmax(0, 1fr);
      grid-template-rows: repeat(3, minmax(250px, auto));
      gap: 14px;
    }

    .chart-panel .chart-primary {
      grid-column: auto;
    }

    .dashboard-grid {
      gap: 14px;
    }

    .dashboard-grid > .card {
      min-height: 360px;
    }

    .dashboard-grid .card-header,
    .dashboard-grid .card-body {
      padding-left: 14px;
      padding-right: 14px;
    }
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
    <div class="card chart-primary">
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

    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-venus-mars" style="color:var(--primary);margin-right:8px;"></i>Residents by Gender</h5>
      </div>
      <div class="card-body">
        <div class="chart-box">
          <canvas id="genderChart"></canvas>
        </div>
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
  const genderLabels = @json($byGender['labels']);
  const genderData = @json($byGender['data']);

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

  if (document.getElementById('genderChart')) {
    new Chart(document.getElementById('genderChart'), {
      type: 'doughnut',
      data: {
        labels: genderLabels,
        datasets: [{
          data: genderData,
          backgroundColor: ['#2563eb', '#db2777', '#6b7280'],
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
