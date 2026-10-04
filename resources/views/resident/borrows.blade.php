@extends('layouts.app')

@section('title', 'Borrow Equipment')

@section('styles')
<style>
@media (max-width: 640px) {
  .borrows-table-wrap { display: none !important; }
  .borrow-cards { display: flex !important; flex-direction: column; gap: 12px; padding: 12px; }
  
  .modal-content-borrow {
    margin: auto 0 0 0 !important;
    border-radius: 20px 20px 0 0 !important;
    max-height: 88vh !important;
    width: 100% !important;
  }
}
@media (min-width: 641px) {
  .borrow-cards { display: none !important; }
}
.native-mobile-app .borrows-table-wrap { display: none !important; }
.native-mobile-app .borrow-cards { display: flex !important; flex-direction: column; gap: 12px; padding: 12px; }

.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
@media(max-width: 480px){
  .grid-2 { grid-template-columns: 1fr; gap: 12px; }
}
</style>
@endsection

@section('content')
<div class="card">
  <div class="card-header" style="flex-wrap:wrap; gap:12px;">
    <h5><i class="fas fa-hand-holding" style="color:var(--primary);margin-right:8px;"></i>My Borrow Requests ({{ $borrows->total() }})</h5>
    <button type="button" class="btn btn-primary btn-sm" onclick="openBorrowModal()">
      <i class="fas fa-plus"></i> Request Borrow Equipment
    </button>
  </div>

  @if($errors->any())
    <div style="padding:16px 24px 0;">
      <div class="alert alert-danger" style="margin:0;">
        <i class="fas fa-exclamation-circle"></i>
        <ul style="margin: 0; padding-left: 20px;">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  {{-- Desktop Table --}}
  <div class="borrows-table-wrap table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Items Requested</th>
          <th>Borrow Date</th>
          <th>Return Date</th>
          <th>Purpose</th>
          <th>Verification Document</th>
          <th>Status</th>
          <th>Remarks / Notes</th>
        </tr>
      </thead>
      <tbody>
        @if ($borrows->isEmpty())
          <tr>
            <td colspan="7" class="text-center" style="padding:40px;">
              <div style="font-size:40px;margin-bottom:10px;">⛺🪑</div>
              <p class="text-muted">You have no active borrow requests yet.</p>
              <button type="button" class="btn btn-primary" onclick="openBorrowModal()" style="margin-top:8px;">
                <i class="fas fa-plus"></i> Request Borrow Equipment
              </button>
            </td>
          </tr>
        @else
          @foreach ($borrows as $b)
            <tr>
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
              <td style="max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $b->purpose }}">
                {{ $b->purpose }}
              </td>
              <td>
                @if($b->verification_document)
                  <a href="{{ asset('assets/uploads/borrow_documents/' . $b->verification_document) }}" target="_blank" class="btn btn-outline-secondary btn-sm" style="display:inline-flex; align-items:center; gap:4px;">
                    <i class="fas fa-file-image" style="color:var(--primary);"></i> View Attachment
                  </a>
                @else
                  <span class="text-muted">--</span>
                @endif
              </td>
              <td>
                <span class="badge bg-{{ $b->status === 'returned' ? 'success' : ($b->status === 'approved' ? 'info' : ($b->status === 'rejected' ? 'danger' : 'warning')) }}">
                  {{ ucfirst($b->status) }}
                </span>
              </td>
              <td>
                <div style="font-size:12px; max-width:180px;">
                  @if($b->remarks)
                    {{ $b->remarks }}
                  @else
                    <span class="text-muted" style="font-style:italic;">No feedback</span>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>

  {{-- Mobile Cards for APK & Phones --}}
  <div class="borrow-cards mobile-card-list">
    @if ($borrows->isEmpty())
      <div style="text-align:center;padding:40px 16px;">
        <div style="font-size:40px;margin-bottom:10px;">⛺🪑</div>
        <p class="text-muted" style="margin-bottom:16px;">You have no active borrow requests yet.</p>
        <button type="button" class="btn btn-primary" onclick="openBorrowModal()">
          <i class="fas fa-plus"></i> Request Borrow Equipment
        </button>
      </div>
    @else
      @foreach ($borrows as $b)
        <div class="data-mobile-card">
          <div class="data-mobile-card-header">
            <div>
              <div class="data-mobile-card-title" style="display:flex; flex-wrap:wrap; gap:6px;">
                @if($b->tent_quantity > 0)
                  <span style="display:inline-flex; align-items:center; gap:4px; background:#fef3c7; color:#b45309; padding:2px 8px; border-radius:6px; font-size:11.5px; font-weight:700;">
                    <i class="fas fa-campground"></i> {{ $b->tent_quantity }} Tents
                  </span>
                @endif
                @if($b->chair_quantity > 0)
                  <span style="display:inline-flex; align-items:center; gap:4px; background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:6px; font-size:11.5px; font-weight:700;">
                    <i class="fas fa-chair"></i> {{ $b->chair_quantity }} Chairs
                  </span>
                @endif
                @if($b->table_quantity > 0)
                  <span style="display:inline-flex; align-items:center; gap:4px; background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:6px; font-size:11.5px; font-weight:700;">
                    <i class="fas fa-table"></i> {{ $b->table_quantity }} Tables
                  </span>
                @endif
              </div>
              <div class="data-mobile-card-subtitle" style="margin-top:6px;">
                <i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($b->borrow_date)->format('M d, Y') }} &rarr; {{ \Carbon\Carbon::parse($b->return_date)->format('M d, Y') }}
              </div>
            </div>
            <span class="badge bg-{{ $b->status === 'returned' ? 'success' : ($b->status === 'approved' ? 'info' : ($b->status === 'rejected' ? 'danger' : 'warning')) }}" style="flex-shrink:0;">
              {{ ucfirst($b->status) }}
            </span>
          </div>

          <div class="data-mobile-card-grid">
            <div class="data-mobile-card-item" style="grid-column:span 2;">
              <label>Purpose</label>
              <div>{{ $b->purpose }}</div>
            </div>
            @if($b->verification_document)
              <div class="data-mobile-card-item" style="grid-column:span 2;">
                <label>Verification Document</label>
                <div>
                  <a href="{{ asset('assets/uploads/borrow_documents/' . $b->verification_document) }}" target="_blank" class="btn btn-outline-secondary btn-sm" style="display:inline-flex; align-items:center; gap:4px; margin-top:2px;">
                    <i class="fas fa-file-image" style="color:var(--primary);"></i> View Attachment
                  </a>
                </div>
              </div>
            @endif
            @if($b->remarks)
              <div class="data-mobile-card-item" style="grid-column:span 2;">
                <label>Feedback / Notes</label>
                <div style="font-size:12.5px; color:#475569;">{{ $b->remarks }}</div>
              </div>
            @endif
          </div>
        </div>
      @endforeach
    @endif
  </div>
  @if ($borrows->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $borrows->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>

<!-- Borrow Equipment Modal -->
<div class="modal" id="borrowModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:16px; overflow-y:auto;">
  <div class="modal-content modal-content-borrow" style="background:#fff; border-radius:16px; max-width:520px; width:100%; overflow:hidden; box-shadow:var(--shadow-md); margin-top:20px; margin-bottom:20px;">
    <div class="modal-header" style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
      <h5 style="margin:0; font-weight:700;"><i class="fas fa-hand-holding" style="color:var(--primary); margin-right:8px;"></i>Request Borrow Equipment</h5>
      <button type="button" class="btn-close" onclick="closeBorrowModal()" style="background:none; border:none; cursor:pointer; font-size:22px; color:var(--gray);">&times;</button>
    </div>
    <form method="POST" action="{{ route('resident.borrows.store') }}" enctype="multipart/form-data">
      @csrf
      
      <div style="padding:20px; display:flex; flex-direction:column; gap:16px; max-height:calc(100vh - 180px); overflow-y:auto;">
        <div class="form-group" style="margin:0;">
          <label class="form-label" style="font-weight:700;">Select Equipment Type *</label>
          <select name="item_type" id="borrow_item_type" class="form-select" required onchange="toggleQuantityFields()">
            <option value="all">Multiple / All Items</option>
            <option value="tent">Tent Only</option>
            <option value="chair">Chair Only</option>
            <option value="table">Table Only</option>
            <option value="both">Both Tent and Chair</option>
          </select>
        </div>

        <div style="margin:0; display:grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap:12px;" id="quantities_grid">
          <div class="form-group" style="margin:0;" id="tent_group">
            <label class="form-label">Tent Qty (Max 5) *</label>
            <input type="number" name="tent_quantity" id="tent_quantity" class="form-control" value="0" min="0" max="5" required>
          </div>
          <div class="form-group" style="margin:0;" id="chair_group">
            <label class="form-label">Chair Qty (Max 50) *</label>
            <input type="number" name="chair_quantity" id="chair_quantity" class="form-control" value="0" min="0" max="50" required>
          </div>
          <div class="form-group" style="margin:0;" id="table_group">
            <label class="form-label">Table Qty (Max 25) *</label>
            <input type="number" name="table_quantity" id="table_quantity" class="form-control" value="0" min="0" max="25" required>
          </div>
        </div>

        <div class="grid-2" style="margin:0;">
          <div class="form-group" style="margin:0;">
            <label class="form-label">Borrow Date *</label>
            <input type="date" name="borrow_date" id="borrow_date" class="form-control" required min="{{ date('Y-m-d') }}">
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Return Date *</label>
            <input type="date" name="return_date" id="return_date" class="form-control" required min="{{ date('Y-m-d') }}">
          </div>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Purpose of Booking *</label>
          <textarea name="purpose" class="form-control" rows="3" required placeholder="Describe the occasion / event (e.g. Birthday Party, Wake, Community Assembly)..." value="{{ old('purpose') }}"></textarea>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Residency Verification Document *</label>
          <div style="font-size:12px; color:var(--gray); margin-bottom:6px;">Upload a copy of your Barangay ID, Proof of Residency, or Request Letter (PDF or Image, max 5MB).</div>
          <input type="file" name="verification_document" class="form-control" required accept="image/*,application/pdf">
        </div>
      </div>

      <div style="padding:14px 20px; border-top:1px solid #f3f4f6; background:#f9fafb; display:flex; justify-content:flex-end; gap:8px;">
        <button type="button" class="btn btn-outline-secondary" onclick="closeBorrowModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" style="font-weight:700;"><i class="fas fa-paper-plane"></i> Submit Request</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openBorrowModal() {
    document.getElementById('borrowModal').style.display = 'flex';
    toggleQuantityFields();
  }
  
  function closeBorrowModal() {
    document.getElementById('borrowModal').style.display = 'none';
  }

  function toggleQuantityFields() {
    const type = document.getElementById('borrow_item_type').value;
    const tentGroup = document.getElementById('tent_group');
    const chairGroup = document.getElementById('chair_group');
    const tableGroup = document.getElementById('table_group');
    const tentQty = document.getElementById('tent_quantity');
    const chairQty = document.getElementById('chair_quantity');
    const tableQty = document.getElementById('table_quantity');

    // Default display
    tentGroup.style.display = 'block';
    chairGroup.style.display = 'block';
    tableGroup.style.display = 'block';

    if (type === 'tent') {
      chairGroup.style.display = 'none';
      tableGroup.style.display = 'none';
      chairQty.value = 0;
      tableQty.value = 0;
    } else if (type === 'chair') {
      tentGroup.style.display = 'none';
      tableGroup.style.display = 'none';
      tentQty.value = 0;
      tableQty.value = 0;
    } else if (type === 'table') {
      tentGroup.style.display = 'none';
      chairGroup.style.display = 'none';
      tentQty.value = 0;
      chairQty.value = 0;
    } else if (type === 'both') {
      tableGroup.style.display = 'none';
      tableQty.value = 0;
    }
  }

  // Adjust return date min value to match borrow date selection
  document.getElementById('borrow_date').addEventListener('change', function() {
    document.getElementById('return_date').min = this.value;
  });
</script>
@endsection
