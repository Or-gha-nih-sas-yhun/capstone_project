@extends('layouts.app')

@section('title', 'Payments Monitoring')

@section('content')
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
  <!-- Cash Card -->
  <div class="card" style="margin:0; padding:20px; display:flex; align-items:center; gap:16px;">
    <div style="background:#eff6ff; color:#2563eb; width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
      <i class="fas fa-money-bill-wave"></i>
    </div>
    <div>
      <div style="font-size:12px; color:var(--gray);">Walk-in Revenue (Cash)</div>
      <h3 style="margin:0; font-size:20px; font-weight:700;">₱{{ number_format($stats['total_cash'], 2) }}</h3>
    </div>
  </div>

  <!-- Total Revenue Card -->
  <div class="card" style="margin:0; padding:20px; display:flex; align-items:center; gap:16px; background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color:#fff;">
    <div style="background:rgba(255,255,255,0.2); color:#fff; width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
      <i class="fas fa-hand-holding-usd"></i>
    </div>
    <div>
      <div style="font-size:12px; opacity:0.8;">Total Revenue</div>
      <h3 style="margin:0; font-size:20px; font-weight:700;">₱{{ number_format($stats['total_paid'], 2) }}</h3>
    </div>
  </div>
  <!-- Pending Card -->
  <div class="card" style="margin:0; padding:20px; display:flex; align-items:center; gap:16px;">
    <div style="background:#fff7ed; color:#ea580c; width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
      <i class="fas fa-clock"></i>
    </div>
    <div>
      <div style="font-size:12px; color:var(--gray);">Pending / Unpaid</div>
      <h3 style="margin:0; font-size:20px; font-weight:700;">₱{{ number_format($stats['total_pending'], 2) }}</h3>
    </div>
  </div>
</div>

<!-- Document Inventory / Revenue by Type -->
<div class="card" style="margin-bottom:24px; padding:0; overflow:hidden;">
  <div class="card-header" style="background:linear-gradient(135deg,#1e3a5f 0%,#0f172a 100%); border:none;">
    <h5 style="color:#fff; margin:0;">
      <i class="fas fa-chart-bar" style="margin-right:8px; color:#60a5fa;"></i>
      Document Revenue Inventory
    </h5>
    <span style="font-size:12px; color:rgba(255,255,255,0.6);">Breakdown of requests and collections per document type</span>
  </div>
  <div style="overflow-x:auto;">
    <table class="table" style="margin:0;">
      <thead>
        <tr style="background:#f8fafc;">
          <th style="font-size:12px; text-transform:uppercase; color:#64748b; letter-spacing:.05em;">#</th>
          <th style="font-size:12px; text-transform:uppercase; color:#64748b; letter-spacing:.05em;">Document Type</th>
          <th style="font-size:12px; text-transform:uppercase; color:#64748b; letter-spacing:.05em; text-align:center;">Unit Fee</th>
          <th style="font-size:12px; text-transform:uppercase; color:#64748b; letter-spacing:.05em; text-align:center;">Total Requests</th>
          <th style="font-size:12px; text-transform:uppercase; color:#64748b; letter-spacing:.05em; text-align:center;">Paid</th>
          <th style="font-size:12px; text-transform:uppercase; color:#64748b; letter-spacing:.05em; text-align:center;">Unpaid</th>
          <th style="font-size:12px; text-transform:uppercase; color:#64748b; letter-spacing:.05em; text-align:center;">Waived</th>
          <th style="font-size:12px; text-transform:uppercase; color:#64748b; letter-spacing:.05em; text-align:right;">Revenue Collected</th>
          <th style="font-size:12px; text-transform:uppercase; color:#64748b; letter-spacing:.05em; text-align:center;">Collection Rate</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($inventory as $i => $item)
          @php
            $rate = $item->total_requests > 0 ? round(($item->total_paid_count / $item->total_requests) * 100) : 0;
            $rateColor = $rate >= 75 ? '#16a34a' : ($rate >= 40 ? '#d97706' : '#dc2626');
          @endphp
          <tr>
            <td style="color:#94a3b8; font-size:13px;">{{ $i + 1 }}</td>
            <td>
              <div style="font-weight:600; font-size:14px; color:#1e293b;">{{ $item->cert_name }}</div>
            </td>
            <td style="text-align:center;">
              @if ($item->unit_fee > 0)
                <span style="font-size:13px; font-weight:600; color:#0369a1;">₱{{ number_format($item->unit_fee, 2) }}</span>
              @else
                <span class="badge" style="background:#dcfce7; color:#15803d; font-size:11px;">FREE</span>
              @endif
            </td>
            <td style="text-align:center;">
              <span style="font-size:15px; font-weight:700; color:#1e293b;">{{ $item->total_requests }}</span>
            </td>
            <td style="text-align:center;">
              <span class="badge" style="background:#dcfce7; color:#15803d; font-size:12px; padding:4px 10px;">{{ $item->total_paid_count }}</span>
            </td>
            <td style="text-align:center;">
              <span class="badge" style="background:#fee2e2; color:#dc2626; font-size:12px; padding:4px 10px;">{{ $item->total_unpaid_count }}</span>
            </td>
            <td style="text-align:center;">
              <span class="badge" style="background:#f3f4f6; color:#6b7280; font-size:12px; padding:4px 10px;">{{ $item->total_waived_count }}</span>
            </td>
            <td style="text-align:right; font-weight:700; font-size:15px; color:var(--primary);">
              ₱{{ number_format($item->total_revenue, 2) }}
            </td>
            <td style="text-align:center;">
              <div style="display:flex; align-items:center; gap:8px; justify-content:center;">
                <div style="width:60px; height:6px; background:#e5e7eb; border-radius:9999px; overflow:hidden;">
                  <div style="width:{{ $rate }}%; height:100%; background:{{ $rateColor }}; border-radius:9999px;"></div>
                </div>
                <span style="font-size:12px; font-weight:700; color:{{ $rateColor }};">{{ $rate }}%</span>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" style="text-align:center; padding:32px; color:#94a3b8;">
              <i class="fas fa-inbox" style="font-size:24px; display:block; margin-bottom:8px;"></i>
              No payment data available yet.
            </td>
          </tr>
        @endforelse
      </tbody>
      @if ($inventory->isNotEmpty())
      <tfoot>
        <tr style="background:#f8fafc; font-weight:700;">
          <td colspan="3" style="font-size:13px; color:#374151;">Totals</td>
          <td style="text-align:center; font-size:15px;">{{ $inventory->sum('total_requests') }}</td>
          <td style="text-align:center;"><span class="badge" style="background:#dcfce7; color:#15803d; font-size:12px; padding:4px 10px;">{{ $inventory->sum('total_paid_count') }}</span></td>
          <td style="text-align:center;"><span class="badge" style="background:#fee2e2; color:#dc2626; font-size:12px; padding:4px 10px;">{{ $inventory->sum('total_unpaid_count') }}</span></td>
          <td style="text-align:center;"><span class="badge" style="background:#f3f4f6; color:#6b7280; font-size:12px; padding:4px 10px;">{{ $inventory->sum('total_waived_count') }}</span></td>
          <td style="text-align:right; font-size:16px; color:var(--primary);">₱{{ number_format($inventory->sum('total_revenue'), 2) }}</td>
          <td></td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>

<div class="card">

  <div class="card-header" style="flex-wrap: wrap; gap:12px;">
    <h5><i class="fas fa-money-bill-wave" style="color:var(--primary);margin-right:8px;"></i>Transactions &amp; OR Log</h5>
    <form method="GET" action="{{ route('admin.payments') }}" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
      <select name="status" class="form-select" style="width:130px;" onchange="this.form.submit()">
        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
        <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
        <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
        <option value="waived" {{ $status === 'waived' ? 'selected' : '' }}>Waived</option>
      </select>
      <select name="method" class="form-select" style="width:130px;" onchange="this.form.submit()">
        <option value="all" {{ $method === 'all' ? 'selected' : '' }}>All Methods</option>
        <option value="cash" {{ $method === 'cash' ? 'selected' : '' }}>Walk-in (Cash)</option>
      </select>
      <input type="text" name="search" class="form-control" placeholder="Search by Tracking # or Resident" value="{{ $search }}" style="width:220px;">
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
      @if($search || $status !== 'all' || $method !== 'all')
        <a href="{{ route('admin.payments') }}" class="btn btn-outline-secondary"><i class="fas fa-sync"></i></a>
      @endif
    </form>
  </div>

  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>

          <th>Tracking #</th>
          <th>Resident</th>
          <th>Document Type</th>
          <th>Amount</th>
          <th>Payment Method</th>
          <th>Paid Date</th>
          <th>Status</th>
          <th>Processed By</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @if ($payments->isEmpty())
          <tr>
            <td colspan="10" class="text-center" style="padding:40px;">No transaction logs found.</td>
          </tr>
        @else
          @foreach ($payments as $p)
            <tr>
              <td><code style="font-size:11px;">{{ $p->request->tracking_number ?? 'N/A' }}</code></td>
              <td>{{ $p->request->resident->full_name ?? 'N/A' }}</td>
              <td>{{ $p->request->certificate->name ?? 'N/A' }}</td>
              <td style="font-weight:600;">₱{{ number_format($p->amount, 2) }}</td>
              <td>
                <span style="display:inline-flex; align-items:center; gap:6px;">
                  @if($p->payment_method === 'cash')
                    <i class="fas fa-money-bill-wave" style="color:#2563eb;"></i> Cash
                  @endif
                </span>
              </td>
              <td>
                <small>{{ $p->paid_at ? \Carbon\Carbon::parse($p->paid_at)->format('M d, Y h:i A') : '--' }}</small>
              </td>
              <td>
                <span class="badge bg-{{ $p->payment_status === 'paid' ? 'success' : ($p->payment_status === 'waived' ? 'secondary' : 'danger') }}">
                  {{ ucfirst($p->payment_status) }}
                </span>
              </td>
              <td>
                <small class="text-muted">{{ $p->receivedBy->username ?? 'System' }}</small>
              </td>
              <td>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="openPaymentModal({{ json_encode($p) }})" title="Edit Payment">
                  <i class="fas fa-edit"></i>
                </button>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
  @if ($payments->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $payments->appends(request()->query())->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>

<!-- Edit Payment Modal -->
<div class="modal" id="paymentModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div class="modal-content" style="background:#fff; border-radius:16px; max-width:480px; width:100%; overflow:hidden; box-shadow:var(--shadow-md);">
    <div class="modal-header" style="padding:16px 24px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:between; align-items:center;">
      <h5 style="margin:0; font-weight:600;"><i class="fas fa-edit" style="color:var(--primary); margin-right:8px;"></i>Update Payment details</h5>
      <button type="button" class="btn-close" onclick="closePaymentModal()" style="background:none; border:none; cursor:pointer; font-size:18px; color:var(--gray);">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.payments.update') }}">
      @csrf
      <input type="hidden" name="payment_id" id="modal_payment_id">
      
      <div style="padding:24px; display:flex; flex-direction:column; gap:16px;">
        <div class="form-group" style="margin:0;">
          <label class="form-label">Resident Name</label>
          <input type="text" id="modal_resident" class="form-control" readonly style="background:#f9fafb;">
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Tracking Number</label>
          <input type="text" id="modal_tracking" class="form-control" readonly style="background:#f9fafb;">
        </div>



        <div class="grid-2" style="margin:0;">
          <div class="form-group" style="margin:0;">
            <label class="form-label">Amount (₱) *</label>
            <input type="number" step="0.01" name="amount" id="modal_amount" class="form-control" required min="0">
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Payment Method *</label>
            <select name="payment_method" id="modal_method" class="form-select" required>
              <option value="cash">Cash</option>
            </select>
          </div>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Payment Status *</label>
          <select name="payment_status" id="modal_status" class="form-select" required>
            <option value="unpaid">Unpaid</option>
            <option value="paid">Paid</option>
            <option value="waived">Waived</option>
          </select>
        </div>
      </div>

      <div style="padding:16px 24px; border-top:1px solid #f3f4f6; background:#f9fafb; display:flex; justify-content:flex-end; gap:8px;">
        <button type="button" class="btn btn-outline-secondary" onclick="closePaymentModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

@section('styles')
<style>
  .grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
  @media(max-width:480px){
    .grid-2 { grid-template-columns: 1fr; }
  }
</style>
@endsection

<script>
  function openPaymentModal(payment) {
    document.getElementById('modal_payment_id').value = payment.id;
    document.getElementById('modal_resident').value = payment.request && payment.request.resident ? payment.request.resident.full_name : 'N/A';
    document.getElementById('modal_tracking').value = payment.request ? payment.request.tracking_number : 'N/A';

    document.getElementById('modal_amount').value = payment.amount;
    document.getElementById('modal_method').value = payment.payment_method;
    document.getElementById('modal_status').value = payment.payment_status;

    const modal = document.getElementById('paymentModal');
    modal.style.display = 'flex';
  }

  function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
  }
</script>
@endsection
