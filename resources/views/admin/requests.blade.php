@extends('layouts.app')

@section('title', 'Manage Requests')

@section('content')
<!-- Filters -->
<div class="card" style="margin-bottom:20px;">
  <div class="card-body" style="padding:16px 20px;">
    <form method="GET" action="{{ route('admin.requests') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
      <div style="flex:1;min-width:200px;">
        <label class="form-label">Search</label>
        <input type="text" name="search" class="form-control" placeholder="Tracking #, name, document…"
          value="{{ $search }}">
      </div>
      <div style="min-width:160px;">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
          @foreach (['pending', 'processing', 'approved', 'rejected', 'released'] as $s)
            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
      <a href="{{ route('admin.requests') }}" class="btn btn-secondary">Reset</a>
    </form>
  </div>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-file-alt" style="color:var(--primary);margin-right:8px;"></i>
      All Requests <span style="font-size:13px;font-weight:400;color:#6b7280;">({{ $requests->total() }})</span></h5>
  </div>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Tracking #</th>
          <th>Resident</th>
          <th>Document</th>
          <th>Fee</th>
          <th>Payment</th>
          <th>Date Filed</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @if ($requests->isEmpty())
          <tr>
            <td colspan="8" class="text-center text-muted" style="padding:40px;">
              {{ $status !== 'all' ? 'No ' . ucfirst($status) . ' Requests Found.' : 'No requests found.' }}
            </td>
          </tr>
        @else
          @foreach ($requests as $r)
            <tr>
              <td><code style="font-size:11px;">{{ $r->tracking_number }}</code></td>
              <td><span style="font-weight:600;">{{ $r->resident->full_name }}</span>
                <br><small class="text-muted">{{ $r->resident->contact_number }}</small>
              </td>
              <td>{{ $r->certificate->name }}</td>
              <td>₱{{ number_format($r->certificate->fee, 2) }}</td>
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
              </td>
              <td>
                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                  <a href="{{ route('admin.requests', ['view' => $r->tracking_number, 'status' => $status, 'search' => $search]) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye"></i>
                  </a>
                  @if (in_array($r->status, ['approved', 'released']))
                    <a href="{{ route('print.certificate', ['id' => $r->id]) }}" target="_blank"
                      class="btn btn-outline-primary btn-sm" title="Preview Certificate">
                      <i class="fas fa-up-right-from-square"></i>
                    </a>
                    <a href="{{ route('print.certificate', ['id' => $r->id, 'print' => 1]) }}" target="_blank"
                      class="btn btn-success btn-sm" title="Print Certificate">
                      <i class="fas fa-print"></i>
                    </a>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
  <!-- Pagination -->
  @if ($requests->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $requests->appends(['status' => $status, 'search' => $search])->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>

<!-- View Modal -->
@if ($viewedRequest)
  <div class="modal-overlay show" id="viewModal">
    <div class="modal-box" style="max-width:660px;">
      <div class="modal-header">
        <h5><i class="fas fa-file-alt" style="color:var(--primary);margin-right:8px;"></i>Request Details</h5>
        <a class="modal-close" style="text-decoration:none; color:inherit; font-size:24px;" href="{{ route('admin.requests', ['status' => $status, 'search' => $search]) }}">&times;</a>
      </div>
      <div class="modal-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Tracking #</div>
            <div style="font-weight:700;font-family:monospace;">{{ $viewedRequest->tracking_number }}</div>
          </div>
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Status</div>
            <div>
              <span class="badge bg-{{ $viewedRequest->status === 'pending' ? 'warning' : ($viewedRequest->status === 'processing' ? 'info' : ($viewedRequest->status === 'approved' ? 'success' : ($viewedRequest->status === 'rejected' ? 'danger' : 'primary'))) }}">
                  {{ ucfirst($viewedRequest->status) }}
              </span>
            </div>
          </div>
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Resident</div>
            <div style="font-weight:600;">{{ $viewedRequest->resident->full_name }}</div>
          </div>
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Contact</div>
            <div>{{ $viewedRequest->resident->contact_number ?? '—' }}</div>
          </div>
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Document</div>
            <div style="font-weight:600;">{{ $viewedRequest->certificate->name }}</div>
          </div>
          @if ($viewedRequest->expected_release_date)
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Release Date</div>
            <div style="font-weight:600; color:#15803d;"><i class="fas fa-calendar-check"></i> {{ \Carbon\Carbon::parse($viewedRequest->expected_release_date)->format('M d, Y h:i A') }}</div>
          </div>
          @else
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Release Date</div>
            <div style="color:#6b7280;">Not set</div>
          </div>
          @endif
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Fee & Payment</div>
            <div style="font-weight:700;color:var(--primary);display:flex;align-items:center;gap:6px;">
              ₱{{ number_format($viewedRequest->certificate->fee, 2) }}
              <span class="badge bg-{{ ($viewedRequest->payment->payment_status ?? 'unpaid') === 'paid' ? 'success' : (($viewedRequest->payment->payment_status ?? 'unpaid') === 'waived' ? 'secondary' : 'danger') }}">
                  {{ ucfirst($viewedRequest->payment->payment_status ?? 'unpaid') }}
              </span>
              <span class="badge" style="font-size:10px;background:#e0f2fe;color:#0369a1;">Walk-in</span>
            </div>
          </div>
          <div style="grid-column:span 2;">
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Purpose</div>
            <div>{{ $viewedRequest->purpose }}</div>
          </div>
          @if ($viewedRequest->remarks)
            <div style="grid-column:span 2;">
              <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Remarks</div>
              <div>{{ $viewedRequest->remarks }}</div>
            </div>
          @endif
          @if (!empty($viewedRequest->payment->proof_of_payment))
            <div style="grid-column:span 2;margin-top:10px;">
              <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Proof of Payment</div>
              @php
                $proof_ext = strtolower(pathinfo($viewedRequest->payment->proof_of_payment, PATHINFO_EXTENSION));
                $proof_url = asset('assets/uploads/' . $viewedRequest->payment->proof_of_payment);
              @endphp
              @if (in_array($proof_ext, ['jpg', 'jpeg', 'png', 'gif']))
                <a href="{{ $proof_url }}" target="_blank" title="Click to view full size">
                  <img src="{{ $proof_url }}" alt="Proof of Payment"
                    style="max-width:100%;max-height:280px;border-radius:8px;border:1.5px solid var(--gray-light);box-shadow:var(--shadow);display:block;">
                </a>
              @else
                <a href="{{ $proof_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                  <i class="fas fa-file-pdf"></i> View Proof Document (PDF)
                </a>
              @endif
            </div>
          @endif
        </div>

        <!-- Action Form -->
        @if (!in_array($viewedRequest->status, ['rejected', 'released']))
          <hr style="margin-bottom:16px;">
          <form method="POST" action="{{ route('admin.requests.update_status') }}" id="actionForm">
            @csrf
            <input type="hidden" name="request_id" value="{{ $viewedRequest->id }}">
            <div class="form-group">
              <label class="form-label">Remarks / Notes</label>
              <textarea name="remarks" class="form-control" rows="2" placeholder="Optional remarks…">{{ old('remarks') }}</textarea>
            </div>
            @if (in_array($viewedRequest->status, ['pending', 'processing']))
              <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label">Expected Release Date (Required for Approval) *</label>
                <input type="datetime-local" name="expected_release_date" class="form-control" 
                       value="{{ old('expected_release_date', \Carbon\Carbon::now()->addHours(24)->format('Y-m-d\TH:i')) }}">
              </div>
            @endif
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              @if ($viewedRequest->status === 'pending' || $viewedRequest->status === 'processing')
                <button type="submit" name="action" value="approve" class="btn btn-success">
                  <i class="fas fa-check"></i> Approve & Ready for Release
                </button>
                <button type="submit" name="action" value="reject" class="btn btn-danger"
                  onclick="return confirm('Reject this request?')">
                  <i class="fas fa-times"></i> Reject
                </button>
              @elseif ($viewedRequest->status === 'approved')
                <a href="{{ route('print.certificate', ['id' => $viewedRequest->id]) }}" target="_blank"
                  class="btn btn-outline-primary">
                  <i class="fas fa-up-right-from-square"></i> Preview Certificate
                </a>
                <a href="{{ route('print.certificate', ['id' => $viewedRequest->id, 'print' => 1]) }}" target="_blank"
                  class="btn btn-primary">
                  <i class="fas fa-print"></i> Print Document
                </a>
                <button type="submit" name="action" value="release" class="btn btn-success">
                  <i class="fas fa-box-open"></i> Mark Released
                </button>
              @endif
              <button type="submit" name="action" value="archive" class="btn btn-secondary"
                onclick="return confirm('Archive this request?')">
                <i class="fas fa-box-archive"></i> Archive
              </button>
            </div>
          </form>
        @else
          @if (in_array($viewedRequest->status, ['approved', 'released']))
            <a href="{{ route('print.certificate', ['id' => $viewedRequest->id]) }}" target="_blank"
              class="btn btn-outline-primary">
              <i class="fas fa-up-right-from-square"></i> Preview Certificate
            </a>
            <a href="{{ route('print.certificate', ['id' => $viewedRequest->id, 'print' => 1]) }}" target="_blank"
              class="btn btn-primary">
              <i class="fas fa-print"></i> Print Document
            </a>
          @endif
        @endif

        <!-- Manual Payment Collection Form (if fee > 0 and unpaid/waived status can be changed) -->
        @if ($viewedRequest->certificate->fee > 0 && ($viewedRequest->payment->payment_status ?? 'unpaid') !== 'paid')
          <hr style="margin-top:20px; margin-bottom:16px;">
          <form method="POST" action="{{ route('admin.requests.payment') }}">
            @csrf
            <input type="hidden" name="request_id" value="{{ $viewedRequest->id }}">
            <div style="font-size:13px;font-weight:700;color:#374151;margin-bottom:10px;">
              <i class="fas fa-peso-sign" style="color:var(--primary);margin-right:6px;"></i>Receive Payment
            </div>
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label">Amount Collected (₱) *</label>
                <input type="number" name="amount" step="0.01" class="form-control" required value="{{ old('amount', $viewedRequest->payment->amount ?? $viewedRequest->certificate->fee) }}">
              </div>

              <div class="form-group">
                <label class="form-label">Payment Method *</label>
                <select name="payment_method" class="form-select" required>
                  <option value="cash" selected>Walk-in Payment (Cash)</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Payment Status *</label>
                <select name="payment_status" class="form-select" required>
                  <option value="paid" {{ (old('payment_status') ?? ($viewedRequest->payment->payment_status ?? '')) === 'paid' ? 'selected' : '' }}>Paid</option>
                  <option value="unpaid" {{ (old('payment_status') ?? ($viewedRequest->payment->payment_status ?? '')) === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                  <option value="waived" {{ (old('payment_status') ?? ($viewedRequest->payment->payment_status ?? '')) === 'waived' ? 'selected' : '' }}>Waived / Free</option>
                </select>
              </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100" style="margin-top:8px;">
              <i class="fas fa-save"></i> Save Payment Details
            </button>
          </form>
        @endif
      </div>
    </div>
  </div>
@endif
@endsection
