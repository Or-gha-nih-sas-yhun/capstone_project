@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
  /* ── Override page-content for dashboard only ── */
  .page-content {
    padding: 8px 12px !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    box-sizing: border-box !important;
    /* Use flex instead of grid so height adapts naturally */
    height: calc(100vh - 56px) !important;
  }

  /* ── Row 1: KPI Cards ── */
  .db-kpi-row {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
  }
  .db-kpi-card {
    flex: 1;
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 3px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    min-width: 0;
  }
  .db-kpi-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--kc, #e5e7eb);
  }
  .db-kpi-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px; }
  .db-kpi-icon {
    width: 30px; height: 30px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    background: var(--ki, #f1f5f9);
    color: var(--kc, #64748b);
  }
  .db-kpi-badge { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; white-space: nowrap; }
  .db-kpi-badge.up     { background:#f0fdf4; color:#15803d; }
  .db-kpi-badge.down   { background:#fef2f2; color:#b91c1c; }
  .db-kpi-badge.neutral{ background:#f1f5f9; color:#64748b; }
  .db-kpi-val  { font-size: 20px; font-weight: 800; color: #111; line-height: 1.1; }
  .db-kpi-lbl  { font-size: 10px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing:.4px; }

  /* ── Row 2: Demographics Strip ── */
  .db-demo-row {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
  }
  .db-demo-card {
    flex: 1;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-left: 4px solid var(--dc, #e5e7eb);
    border-radius: 8px;
    padding: 8px 11px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: box-shadow .15s, transform .15s;
    min-width: 0;
  }
  .db-demo-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); transform: translateY(-1px); }
  .db-demo-icon {
    width: 32px; height: 32px; border-radius: 7px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    background: var(--di, #f1f5f9);
    color: var(--dc, #64748b);
  }
  .db-demo-val { font-size: 18px; font-weight: 800; line-height: 1; }
  .db-demo-lbl { font-size: 10px; color: #6b7280; font-weight: 600; margin-top: 1px; }
  .db-demo-pct { font-size: 9.5px; font-weight: 700; padding: 1px 5px; border-radius: 10px; margin-top: 2px; display: inline-block; }

  /* ── Row 3: Main Grid ── */
  .db-main {
    flex: 1;
    display: grid;
    grid-template-columns: 1.1fr 1fr 0.9fr;
    gap: 8px;
    min-height: 0;
    overflow: hidden;
  }

  /* Columns */
  .db-col {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: 0;
    overflow: hidden;
  }

  /* Cards */
  .db-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 4px rgba(0,0,0,.03);
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
    flex: 1;
  }
  .db-card-hd {
    padding: 7px 11px;
    border-bottom: 1px solid #f3f4f6;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: space-between;
  }
  .db-card-hd h5 { font-size: 11.5px; font-weight: 700; color: #1f2937; margin: 0; white-space: nowrap; }
  .db-card-hd span { font-size: 9.5px; color: #9ca3af; }
  .db-card-bd {
    padding: 8px 10px;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
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
  .db-doughnut-wrap { display: flex; flex-direction: column; flex: 1; min-height: 0; gap: 4px; }
  .db-doughnut-wrap .db-chart { flex: 1; }
  .db-legend { display: flex; flex-wrap: wrap; gap: 3px 8px; flex-shrink: 0; }
  .db-legend-item { display: flex; align-items: center; gap: 4px; font-size: 9.5px; color: #374151; font-weight: 600; }
  .db-legend-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

  /* Table */
  .db-tbl-wrap { overflow-y: auto; flex: 1; min-height: 0; }
  .db-tbl { width: 100%; border-collapse: collapse; }
  .db-tbl th { background:#f9fafb; font-size:9.5px; font-weight:700; color:#6b7280; text-transform:uppercase; padding:5px 8px; border-bottom:1px solid #e5e7eb; position:sticky; top:0; z-index:1; }
  .db-tbl td { padding:5px 8px; font-size:11px; color:#1f2937; border-bottom:1px solid #f3f4f6; }
  .db-tbl tr:hover td { background:#f9fafb; cursor:pointer; }

  /* Activity */
  .db-activity { list-style:none; margin:0; padding:0; overflow-y:auto; flex:1; min-height:0; }
  .db-activity li { display:flex; gap:7px; align-items:flex-start; padding:4px 0; border-bottom:1px solid #f3f4f6; }
  .db-activity li:last-child { border-bottom:none; }
  .db-act-dot { width:22px; height:22px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:9px; flex-shrink:0; background:#eff6ff; color:#1d4ed8; }
  .db-act-txt { font-size:10.5px; font-weight:600; color:#1e293b; line-height:1.3; }
  .db-act-meta { font-size:9px; color:#9ca3af; margin-top:1px; }

  /* Status badges */
  .db-badge { display:inline-flex; align-items:center; font-size:9.5px; font-weight:700; padding:2px 6px; border-radius:20px; white-space:nowrap; }
  .db-badge.pending    { background:#fffbeb; color:#92400e; }
  .db-badge.processing { background:#eff6ff; color:#1d4ed8; }
  .db-badge.approved   { background:#f0fdf4; color:#15803d; }
  .db-badge.released   { background:#f0f9ff; color:#0369a1; }
  .db-badge.rejected   { background:#fef2f2; color:#b91c1c; }
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
      <div class="db-demo-lbl" style="margin-bottom:3px;">Gender Breakdown</div>
      <div style="display:flex;gap:8px;font-size:10px;font-weight:700;">
        <span style="color:#1d4ed8;"><i class="fas fa-male"></i> {{ $maleCount }} <span style="color:#9ca3af;font-weight:500;">({{ $malePct }}%)</span></span>
        <span style="color:#db2777;"><i class="fas fa-female"></i> {{ $femaleCount }} <span style="color:#9ca3af;font-weight:500;">({{ $femalePct }}%)</span></span>
      </div>
      <div style="display:flex;height:4px;border-radius:3px;overflow:hidden;margin-top:4px;gap:1px;">
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
    <div class="db-card">
      <div class="db-card-hd">
        <h5><i class="fas fa-chart-bar" style="color:#b91c1c;margin-right:5px;"></i>Monthly Requests vs Released</h5>
        <span>Last 6 months</span>
      </div>
      <div class="db-card-bd">
        <div class="db-chart"><canvas id="monthlyChart"></canvas></div>
      </div>
    </div>
    <div class="db-card">
      <div class="db-card-hd">
        <h5><i class="fas fa-chart-line" style="color:#7c3aed;margin-right:5px;"></i>Monthly Collections (PHP)</h5>
        <span>Last 6 months</span>
      </div>
      <div class="db-card-bd">
        <div class="db-chart"><canvas id="revenueChart"></canvas></div>
      </div>
    </div>
  </div>

  {{-- Column B: Doughnut + Age Group --}}
  <div class="db-col">
    <div class="db-card">
      <div class="db-card-hd">
        <h5><i class="fas fa-chart-pie" style="color:#0d9488;margin-right:5px;"></i>Requests by Type</h5>
      </div>
      <div class="db-card-bd">
        <div class="db-doughnut-wrap">
          <div class="db-chart"><canvas id="typeChart"></canvas></div>
          <div class="db-legend" id="typeLegend"></div>
        </div>
      </div>
    </div>
    <div class="db-card">
      <div class="db-card-hd">
        <h5><i class="fas fa-users" style="color:#d97706;margin-right:5px;"></i>Age Group Distribution</h5>
      </div>
      <div class="db-card-bd">
        <div class="db-chart"><canvas id="ageChart"></canvas></div>
      </div>
    </div>
  </div>

  {{-- Column C: Recent Requests + Activity --}}
  <div class="db-col">
    <div class="db-card" style="flex:1.4;">
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
                <td><code style="font-size:9.5px;">{{ substr($req->tracking_number,-6) }}</code></td>
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
                <div class="db-act-txt">{{ Str::limit($log->description ?: $log->action, 48) }}</div>
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
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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

  // Bar: Monthly Requests vs Released
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
      plugins:{ legend:{ position:'top', labels:{ boxWidth:9, font:{size:10,weight:'bold'}, padding:6 } }, tooltip:{ padding:8 } },
      scales:{
        y:{ beginAtZero:true, grid:{color:'#f3f4f6'}, ticks:{stepSize:1,font:{size:9}} },
        x:{ grid:{display:false}, ticks:{font:{size:9,weight:'600'}} }
      }
    }
  });

  // Line: Monthly Collections
  new Chart(document.getElementById('revenueChart'), {
    type:'line',
    data:{
      labels: revLabels.length ? revLabels : ['No Data'],
      datasets:[{ label:'Collections', data: revData.length ? revData : [0],
        borderColor:'#7c3aed', backgroundColor:'rgba(124,58,237,.1)',
        fill:true, tension:0.4, pointRadius:4, pointHoverRadius:6, borderWidth:2.5 }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:false}, tooltip:{ padding:8, callbacks:{ label:ctx=>'₱'+Number(ctx.parsed.y).toLocaleString() } } },
      scales:{
        y:{ beginAtZero:true, grid:{color:'#f3f4f6'}, ticks:{font:{size:9}, callback:v=>'₱'+Number(v).toLocaleString()} },
        x:{ grid:{display:false}, ticks:{font:{size:9,weight:'600'}} }
      }
    }
  });

  // Doughnut: Requests by Type
  const typeChart = new Chart(document.getElementById('typeChart'), {
    type:'doughnut',
    data:{
      labels: typeLabels.length ? typeLabels : ['No Data'],
      datasets:[{ data: typeData.length ? typeData : [1], backgroundColor:palette, borderWidth:2, borderColor:'#fff' }]
    },
    options:{
      responsive:true, maintainAspectRatio:false, cutout:'60%',
      plugins:{ legend:{display:false}, tooltip:{padding:8} }
    }
  });
  // Custom legend
  const legend = document.getElementById('typeLegend');
  if (typeLabels.length) {
    typeLabels.forEach((lbl,i) => {
      const el = document.createElement('div');
      el.className = 'db-legend-item';
      el.innerHTML = `<span class="db-legend-dot" style="background:${palette[i%palette.length]}"></span>${lbl} (${typeData[i]??0})`;
      legend.appendChild(el);
    });
  }

  // Horizontal Bar: Age Groups
  new Chart(document.getElementById('ageChart'), {
    type:'bar',
    data:{
      labels: ageLabels.length ? ageLabels : ['No Data'],
      datasets:[{ label:'Residents', data: ageData.length ? ageData : [0],
        backgroundColor:['#60a5fa','#34d399','#f59e0b','#f87171','#c084fc'],
        borderRadius:4, barPercentage:0.7 }]
    },
    options:{
      responsive:true, maintainAspectRatio:false, indexAxis:'y',
      plugins:{ legend:{display:false}, tooltip:{padding:8} },
      scales:{
        x:{ beginAtZero:true, grid:{color:'#f3f4f6'}, ticks:{font:{size:9}} },
        y:{ grid:{display:false}, ticks:{font:{size:9,weight:'600'}} }
      }
    }
  });
</script>
@endsection
