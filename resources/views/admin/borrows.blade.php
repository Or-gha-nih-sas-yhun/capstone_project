@extends('layouts.app')

@section('title', 'Borrow Requests Management')

@section('content')
<div class="card">
  <div class="card-header" style="flex-wrap:wrap; gap:12px;">
    <h5><i class="fas fa-hand-holding" style="color:var(--primary);margin-right:8px;"></i>Equipment Borrow Requests ({{ $borrows->total() }})</h5>
    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
      <form method="GET" action="{{ route('admin.borrows') }}" style="display:flex; gap:8px; align-items:center;">
        <select name="status" class="form-select" style="width:145px;" onchange="this.form.submit()">
          <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
          <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
          <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
          <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
          <option value="returned" {{ $status === 'returned' ? 'selected' : '' }}>Returned</option>
        </select>
        <input type="text" name="search" class="form-control" placeholder="Search resident..." value="{{ $search }}" style="width:200px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        @if($search || $status !== 'all')
          <a href="{{ route('admin.borrows') }}" class="btn btn-outline-secondary"><i class="fas fa-sync"></i></a>
        @endif
      </form>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Resident</th>
          <th>Items Borrowed</th>
          <th>Borrow Date</th>
          <th>Return Date</th>
          <th>Purpose</th>
          <th>Verification Doc</th>
          <th>Status</th>
          <th>Remarks</th>
          <th>Processed By</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @if ($borrows->isEmpty())
          <tr>
            <td colspan="10" class="text-center" style="padding:40px;">No borrow requests filed yet.</td>
          </tr>
        @else
          @foreach ($borrows as $b)
            <tr>
              <td>
                <div style="font-weight:600;">{{ $b->resident->full_name }}</div>
                <div style="font-size:11px; color:var(--gray);">{{ $b->resident->contact_number ?? 'No Contact' }}</div>
              </td>
              <td>
                <div style="font-size:12px;">
                  @if($b->tent_quantity > 0)
                    <span style="display:inline-block; margin-right:8px;"><i class="fas fa-campground" style="color:#d97706;"></i> <strong>{{ $b->tent_quantity }}</strong> Tents</span>
                  @endif
                  @if($b->chair_quantity > 0)
                    <span style="display:inline-block; margin-right:8px;"><i class="fas fa-chair" style="color:#2563eb;"></i> <strong>{{ $b->chair_quantity }}</strong> Chairs</span>
                  @endif
                  @if($b->table_quantity > 0)
                    <span style="display:inline-block; margin-right:8px;"><i class="fas fa-table" style="color:#10b981;"></i> <strong>{{ $b->table_quantity }}</strong> Tables</span>
                  @endif
                </div>
              </td>
              <td><small>{{ \Carbon\Carbon::parse($b->borrow_date)->format('M d, Y') }}</small></td>
              <td><small>{{ \Carbon\Carbon::parse($b->return_date)->format('M d, Y') }}</small></td>
              <td style="max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $b->purpose }}">
                {{ $b->purpose }}
              </td>
              <td>
                @if($b->verification_document)
                  <a href="{{ asset('assets/uploads/borrow_documents/' . $b->verification_document) }}" target="_blank" class="btn btn-outline-secondary btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
                    <i class="fas fa-file-pdf" style="color:var(--primary);"></i> View Doc
                  </a>
                @else
                  <span class="text-muted">No Document</span>
                @endif
              </td>
              <td>
                <span class="badge bg-{{ $b->status === 'returned' ? 'success' : ($b->status === 'approved' ? 'info' : ($b->status === 'rejected' ? 'danger' : 'warning')) }}">
                  {{ ucfirst($b->status) }}
                </span>
              </td>
              <td style="max-width:140px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $b->remarks }}">
                {{ $b->remarks ?? '--' }}
              </td>
              <td>
                @if($b->approver)
                  <small>{{ $b->approver->username }}</small>
                @else
                  <span class="text-muted">--</span>
                @endif
              </td>
              <td>
                @if($b->status === 'pending' || $b->status === 'approved')
                  <button type="button" class="btn btn-outline-primary btn-sm" onclick="openBorrowModal({{ json_encode($b) }})" title="Update Request">
                    <i class="fas fa-edit"></i>
                  </button>
                @else
                  <button type="button" class="btn btn-outline-secondary btn-sm" disabled style="opacity:0.5;">
                    <i class="fas fa-check"></i> Done
                  </button>
                @endif
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
  @if ($borrows->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $borrows->appends(request()->query())->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>

<!-- Edit Borrow Status Modal -->
<div class="modal" id="borrowModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div class="modal-content" style="background:#fff; border-radius:16px; max-width:480px; width:100%; overflow:hidden; box-shadow:var(--shadow-md);">
    <div class="modal-header" style="padding:16px 24px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:between; align-items:center;">
      <h5 style="margin:0; font-weight:600;"><i class="fas fa-edit" style="color:var(--primary); margin-right:8px;"></i>Process Borrow Request</h5>
      <button type="button" class="btn-close" onclick="closeBorrowModal()" style="background:none; border:none; cursor:pointer; font-size:18px; color:var(--gray);">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.borrows.update_status') }}">
      @csrf
      <input type="hidden" name="borrow_id" id="modal_borrow_id">
      
      <div style="padding:24px; display:flex; flex-direction:column; gap:16px;">
        <div class="form-group" style="margin:0;">
          <label class="form-label">Resident Borrower</label>
          <input type="text" id="modal_borrower_name" class="form-control" readonly style="background:#f9fafb;">
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Borrowing Items</label>
          <input type="text" id="modal_items" class="form-control" readonly style="background:#f9fafb;">
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Update Status *</label>
          <select name="status" id="modal_status" class="form-select" required>
            <option value="approved">Approve &amp; Prepare Equipment</option>
            <option value="rejected">Reject Request</option>
            <option value="returned">Mark as Returned (Completed)</option>
          </select>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Remarks / Feedback</label>
          <textarea name="remarks" id="modal_remarks" class="form-control" rows="3" placeholder="Provide reason if rejecting, or notes for picking up items..."></textarea>
        </div>
      </div>

      <div style="padding:16px 24px; border-top:1px solid #f3f4f6; background:#f9fafb; display:flex; justify-content:flex-end; gap:8px;">
        <button type="button" class="btn btn-outline-secondary" onclick="closeBorrowModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openBorrowModal(borrow) {
    document.getElementById('modal_borrow_id').value = borrow.id;
    document.getElementById('modal_borrower_name').value = borrow.resident ? borrow.resident.full_name : 'N/A';
    
    let itemsList = [];
    if(borrow.tent_quantity > 0) itemsList.push(`${borrow.tent_quantity} Tents`);
    if(borrow.chair_quantity > 0) itemsList.push(`${borrow.chair_quantity} Chairs`);
    if(borrow.table_quantity > 0) itemsList.push(`${borrow.table_quantity} Tables`);
    document.getElementById('modal_items').value = itemsList.join(', ');

    document.getElementById('modal_status').value = borrow.status === 'pending' ? 'approved' : borrow.status;
    document.getElementById('modal_remarks').value = borrow.remarks || '';

    document.getElementById('borrowModal').style.display = 'flex';
  }

  function closeBorrowModal() {
    document.getElementById('borrowModal').style.display = 'none';
  }
</script>
@endsection
