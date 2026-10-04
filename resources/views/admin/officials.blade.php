@extends('layouts.app')

@section('title', 'Manage Barangay Officials')

@section('content')
<div style="display:flex;justify-content:flex-end;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <button class="btn btn-primary" onclick="openModal('officialModal')">
    <i class="fas fa-plus"></i> Add Official
  </button>
</div>

<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-user-tie" style="color:var(--primary);margin-right:8px;"></i>
      Barangay Officials <span style="font-size:13px;font-weight:400;color:#6b7280;">({{ count($officials) }})</span></h5>
  </div>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Photo</th>
          <th>Name</th>
          <th>Position</th>
          <th>Term Start</th>
          <th>Term End</th>
          <th>Sort Order</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @if ($officials->isEmpty())
          <tr><td colspan="8" class="text-center text-muted" style="padding:40px;">No officials found.</td></tr>
        @else
          @foreach ($officials as $o)
            <tr>
              <td>
                <div style="width:40px;height:40px;border-radius:50%;background:#e5e7eb;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                  @if ($o->photo)
                    <img src="{{ asset('assets/uploads/' . $o->photo) }}" alt="{{ $o->name }}" style="width:100%;height:100%;object-fit:cover;">
                  @else
                    <i class="fas fa-user" style="color:#9ca3af;"></i>
                  @endif
                </div>
              </td>
              <td style="font-weight:600;">{{ $o->name }}</td>
              <td><span class="badge bg-secondary">{{ $o->position }}</span></td>
              <td>{{ $o->term_start ? \Carbon\Carbon::parse($o->term_start)->format('M d, Y') : '—' }}</td>
              <td>{{ $o->term_end ? \Carbon\Carbon::parse($o->term_end)->format('M d, Y') : '—' }}</td>
              <td><code>{{ $o->sort_order }}</code></td>
              <td>
                <span class="badge bg-{{ $o->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($o->status) }}</span>
              </td>
              <td>
                <div style="display:flex;gap:4px;">
                  <a href="{{ route('admin.officials', ['edit' => $o->id]) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                  <a href="{{ route('admin.officials.delete', ['id' => $o->id]) }}" class="btn btn-danger btn-sm"
                     onclick="return confirm('Delete this official permanently?')"><i class="fas fa-trash"></i></a>
                </div>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal-overlay {{ ($editOfficial || $errors->any()) ? 'show' : '' }}" id="officialModal">
  <div class="modal-box" style="max-width:560px;">
    <div class="modal-header">
      <h5>{{ $editOfficial ? 'Edit Official' : 'Add Barangay Official' }}</h5>
      <a class="modal-close" style="text-decoration:none; color:inherit; font-size:24px;" href="{{ route('admin.officials') }}">&times;</a>
    </div>
    <form method="POST" action="{{ route('admin.officials.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-body">
        <input type="hidden" name="official_id" value="{{ $editOfficial ? $editOfficial->id : 0 }}">
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" name="name" class="form-control" required value="{{ old('name', $editOfficial ? $editOfficial->name : '') }}" placeholder="e.g. HON. JUAN DELA CRUZ">
        </div>
        <div class="form-group">
          <label class="form-label">Position / Role *</label>
          <input type="text" name="position" class="form-control" required value="{{ old('position', $editOfficial ? $editOfficial->position : '') }}" placeholder="e.g. Barangay Kagawad">
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Term Start</label>
            <input type="date" name="term_start" class="form-control" value="{{ old('term_start', $editOfficial ? $editOfficial->term_start : '') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Term End</label>
            <input type="date" name="term_end" class="form-control" value="{{ old('term_end', $editOfficial ? $editOfficial->term_end : '') }}">
          </div>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Sort Order *</label>
            <input type="number" name="sort_order" class="form-control" required value="{{ old('sort_order', $editOfficial ? $editOfficial->sort_order : 0) }}">
            <small class="form-text">Lower number displays higher in list templates.</small>
          </div>
          <div class="form-group">
            <label class="form-label">Status *</label>
            <select name="status" class="form-select" required>
              <option value="active" {{ (old('status') ?? ($editOfficial ? $editOfficial->status : '')) === 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ (old('status') ?? ($editOfficial ? $editOfficial->status : '')) === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Photo</label>
          <input type="file" name="photo" class="form-control" accept="image/*">
        </div>
      </div>
      <div class="modal-footer">
        @if ($editOfficial)
          <button type="button" class="btn btn-danger" style="margin-right:auto;" onclick="openModal('archiveOfficialModal')">
            <i class="fas fa-archive"></i> Archive
          </button>
        @endif
        <a class="btn btn-secondary" style="text-decoration:none;" href="{{ route('admin.officials') }}">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> {{ $editOfficial ? 'Update' : 'Save' }}
        </button>
      </div>
    </form>
  </div>
</div>

@if ($editOfficial)
<!-- Archive Confirmation Modal -->
<div class="modal-overlay" id="archiveOfficialModal">
  <div class="modal-box" style="max-width:440px;">
    <div class="modal-header">
      <h5><i class="fas fa-archive" style="color:var(--danger);margin-right:8px;"></i>Archive Official</h5>
      <button type="button" class="modal-close" onclick="closeModal('archiveOfficialModal')">&times;</button>
    </div>
    <div class="modal-body">
      <p style="margin-bottom:12px;">Are you sure you want to archive <strong>{{ $editOfficial->name }}</strong> ({{ $editOfficial->position }})?</p>
      <p class="text-muted" style="font-size:13px;margin-bottom:12px;">
        <i class="fas fa-file-signature"></i> They will no longer appear on printed certificates or the officials list.
      </p>
      <p class="text-muted" style="font-size:13px;margin:0;">
        <i class="fas fa-info-circle"></i> You can restore this record anytime from the Archive page.
      </p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('archiveOfficialModal')">Cancel</button>
      <a href="{{ route('admin.officials.archive', ['id' => $editOfficial->id]) }}" class="btn btn-danger" style="text-decoration:none;">
        <i class="fas fa-archive"></i> Archive
      </a>
    </div>
  </div>
</div>
@endif
@endsection
