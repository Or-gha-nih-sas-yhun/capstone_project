@extends('layouts.app')

@section('title', 'My Requests')

@section('styles')
<style>
/* ── My Requests Mobile Cards ─────────────────────────── */
.requests-table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Mobile card layout */
@media (max-width: 640px) {
  .requests-table-wrap { display: none; }

  .request-cards { display: flex; flex-direction: column; gap: 12px; padding: 12px; }

  .request-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 2px 6px rgba(0,0,0,.03);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }

  .request-card:active {
    transform: scale(0.99);
  }

  .request-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 12px;
  }

  .request-card-title {
    font-size: 15.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
  }

  .request-card-track-row {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 6px;
  }

  .request-card-track {
    font-size: 11.5px;
    font-weight: 600;
    color: #475569;
    font-family: ui-monospace, SFMono-Regular, monospace;
    letter-spacing: 0.02em;
  }

  .btn-copy-code {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 11px;
    padding: 2px 4px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
  }
  .btn-copy-code:hover, .btn-copy-code:active {
    color: var(--primary);
  }

  .request-card-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 12px;
    margin-bottom: 12px;
    background: #f8fafc;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #f1f5f9;
  }

  .request-card-meta-item label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #64748b;
    margin-bottom: 2px;
  }

  .request-card-meta-item span {
    font-size: 13px;
    color: #1e293b;
    font-weight: 600;
  }

  .request-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
    gap: 8px;
  }

  .request-card-date {
    font-size: 11.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 4px;
  }
}

@media (min-width: 641px) {
  .request-cards { display: none; }
}

.native-mobile-app .requests-table-wrap { display: none !important; }
.native-mobile-app .request-cards { display: flex !important; flex-direction: column; gap: 12px; padding: 12px; }
</style>
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-list" style="color:var(--primary);margin-right:8px;"></i>My Requests ({{ $requests->total() }})</h5>
    <a href="{{ route('resident.request') }}" class="btn btn-primary btn-sm">
      <i class="fas fa-plus"></i> New Request
    </a>
  </div>

  {{-- ── Desktop table ──────────────────────────────────── --}}
  <div class="requests-table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Tracking #</th>
          <th>Document</th>
          <th>Purpose</th>
          <th>Fee</th>
          <th>Payment</th>
          <th>Filed</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @if ($requests->isEmpty())
          <tr>
            <td colspan="8" class="text-center" style="padding:40px;">
              <div style="font-size:40px;margin-bottom:10px;">📭</div>
              <p class="text-muted">No requests yet.</p>
              <a href="{{ route('resident.request') }}" class="btn btn-primary" style="margin-top:8px;">
                <i class="fas fa-plus"></i> Make a Request
              </a>
            </td>
          </tr>
        @else
          @foreach ($requests as $r)
            <tr>
              <td><code style="font-size:11px;">{{ $r->tracking_number }}</code></td>
              <td style="font-weight:600;">{{ $r->certificate->name }}</td>
              <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                title="{{ $r->purpose }}">{{ $r->purpose }}</td>
              <td>
                @if ($r->certificate->fee > 0)
                  ₱{{ number_format($r->certificate->fee, 2) }}
                @else
                  <span class="badge bg-success">FREE</span>
                @endif
              </td>
              <td>
                <span class="badge bg-{{ ($r->payment->payment_status ?? 'unpaid') === 'paid' ? 'success' : (($r->payment->payment_status ?? 'unpaid') === 'waived' ? 'secondary' : 'danger') }}">
                    {{ ucfirst($r->payment->payment_status ?? 'unpaid') }}
                </span>
              </td>
              <td><small>{{ \Carbon\Carbon::parse($r->requested_at)->format('M d, Y h:i A') }}</small></td>
              <td>
                <span class="badge bg-{{ $r->status === 'pending' ? 'warning' : ($r->status === 'processing' ? 'info' : ($r->status === 'approved' ? 'success' : ($r->status === 'rejected' ? 'danger' : 'primary'))) }}">
                    {{ ucfirst($r->status) }}
                </span>
                @if ($r->status === 'approved' && $r->expected_release_date)
                  <div style="font-size:10px; margin-top:4px; color:#15803d; font-weight:600;">
                    <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($r->expected_release_date)->format('M d, g:i A') }}
                  </div>
                @endif
                @if ($r->remarks)
                  <div style="font-size:10px; margin-top:2px; color:#0369a1; font-weight:600;" title="{{ $r->remarks }}">
                    <i class="fas fa-envelope"></i> See remarks
                  </div>
                @endif
              </td>
              <td>
                <div style="display:flex;gap:4px;">
                  <a href="{{ route('track', ['tracking' => $r->tracking_number]) }}"
                    class="btn btn-outline-secondary btn-sm" target="_blank" title="Track">
                    <i class="fas fa-search-location"></i>
                  </a>
                </div>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>

  {{-- ── Mobile cards ───────────────────────────────────── --}}
  <div class="request-cards" style="padding:12px;">
    @if ($requests->isEmpty())
      <div style="text-align:center;padding:40px 16px;">
        <div style="font-size:48px;margin-bottom:12px;">📭</div>
        <p class="text-muted" style="margin-bottom:16px;">No requests yet.</p>
        <a href="{{ route('resident.request') }}" class="btn btn-primary">
          <i class="fas fa-plus"></i> Make a Request
        </a>
      </div>
    @else
      @foreach ($requests as $r)
        <div class="request-card">
          <div class="request-card-header">
            <div>
              <div class="request-card-title">{{ $r->certificate->name }}</div>
              <div class="request-card-track-row">
                <span class="request-card-track">{{ $r->tracking_number }}</span>
                <button type="button" class="btn-copy-code" onclick="copyTrackingCode('{{ $r->tracking_number }}', this)" title="Copy tracking number">
                  <i class="fas fa-copy"></i>
                </button>
              </div>
            </div>
            <div style="display:flex; flex-direction:column; align-items:flex-end;">
              <span class="badge bg-{{ $r->status === 'pending' ? 'warning' : ($r->status === 'processing' ? 'info' : ($r->status === 'approved' ? 'success' : ($r->status === 'rejected' ? 'danger' : 'primary'))) }}" style="flex-shrink:0;">
                {{ ucfirst($r->status) }}
              </span>
              @if ($r->status === 'approved' && $r->expected_release_date)
                <div style="font-size:10px; margin-top:4px; color:#15803d; font-weight:600;">
                  <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($r->expected_release_date)->format('M d') }}
                </div>
              @endif
              @if ($r->remarks)
                <div style="font-size:10px; margin-top:2px; color:#0369a1; font-weight:600;" title="{{ $r->remarks }}">
                  <i class="fas fa-envelope"></i> Remarks
                </div>
              @endif
            </div>
          </div>

          <div class="request-card-meta">
            <div class="request-card-meta-item">
              <label>Purpose</label>
              <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;max-width:140px;" title="{{ $r->purpose }}">{{ $r->purpose }}</span>
            </div>
            <div class="request-card-meta-item">
              <label>Fee</label>
              <span>
                @if ($r->certificate->fee > 0)
                  ₱{{ number_format($r->certificate->fee, 2) }}
                @else
                  <span class="badge bg-success" style="font-size:10px;">FREE</span>
                @endif
              </span>
            </div>
            <div class="request-card-meta-item">
              <label>Payment</label>
              <span class="badge bg-{{ ($r->payment->payment_status ?? 'unpaid') === 'paid' ? 'success' : (($r->payment->payment_status ?? 'unpaid') === 'waived' ? 'secondary' : 'danger') }}" style="font-size:10px;">
                {{ ucfirst($r->payment->payment_status ?? 'unpaid') }}
              </span>
            </div>
            <div class="request-card-meta-item">
              <label>Filed</label>
              <span style="font-size:12px;">{{ \Carbon\Carbon::parse($r->requested_at)->format('M d, Y') }}</span>
            </div>
          </div>

          <div class="request-card-footer">
            <span class="request-card-date">
              <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($r->requested_at)->format('h:i A') }}
            </span>
            <a href="{{ route('track', ['tracking' => $r->tracking_number]) }}"
              class="btn btn-outline-secondary btn-sm" style="display:inline-flex; align-items:center; gap:5px;">
              <i class="fas fa-search-location" style="color:var(--primary);"></i> Track Status
            </a>
          </div>
        </div>
      @endforeach
    @endif
  </div>

  @if ($requests->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $requests->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>
@endsection

@section('scripts')
<script>
function copyTrackingCode(text, btn) {
  navigator.clipboard.writeText(text).then(() => {
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check" style="color:#16a34a;"></i>';
    setTimeout(() => { btn.innerHTML = originalHtml; }, 1800);
  }).catch(() => {
    // Fallback
    const input = document.createElement('input');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check" style="color:#16a34a;"></i>';
    setTimeout(() => { btn.innerHTML = originalHtml; }, 1800);
  });
}
</script>
@endsection
