@extends('layouts.app')

@section('title', 'Manage Puroks')

@section('content')
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div style="color:#6b7280;font-size:13.5px;max-width:560px;">
      <i class="fas fa-circle-info" style="color:var(--primary);margin-right:5px;"></i>
      The puroks / sitios of {{ barangay_label() }}. Active entries appear as dropdown choices in resident
      registration and records.
    </div>
    <button class="btn btn-primary" onclick="openModal('purokModal')">
      <i class="fas fa-plus"></i> Add Purok
    </button>
  </div>

  @if (session('success'))
    <div class="alert alert-success" style="margin-bottom:16px;">
      <i class="fas fa-circle-check" style="margin-right:6px;"></i>{{ session('success') }}
    </div>
  @endif

  <div class="card">
    <div class="card-header">
      <h5><i class="fas fa-map-location-dot" style="color:var(--primary);margin-right:8px;"></i>
        Puroks <span style="font-size:13px;font-weight:400;color:#6b7280;">({{ count($puroks) }})</span></h5>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Sort Order</th>
            <th>Residents</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @if ($puroks->isEmpty())
            <tr>
              <td colspan="5" class="text-center text-muted" style="padding:40px;">
                No puroks defined yet. Add the puroks of {{ barangay_label() }} to turn the resident purok field
                into a dropdown.
              </td>
            </tr>
          @else
            @foreach ($puroks as $p)
              <tr>
                <td style="font-weight:600;">{{ $p->name }}</td>
                <td><code>{{ $p->sort_order }}</code></td>
                <td>{{ $p->residentCount() }}</td>
                <td>
                  <span class="badge bg-{{ $p->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($p->status) }}</span>
                </td>
                <td>
                  <div style="display:flex;gap:4px;">
                    <a href="{{ route('admin.puroks', ['edit' => $p->id]) }}" class="btn btn-warning btn-sm"><i
                        class="fas fa-edit"></i></a>
                    <a href="{{ route('admin.puroks.delete', ['id' => $p->id]) }}" class="btn btn-danger btn-sm"
                      onclick="return confirm('Remove this purok? Puroks still used by residents are set to inactive instead of deleted.')"><i
                        class="fas fa-trash"></i></a>
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
  <div class="modal-overlay {{ $editPurok || $errors->any() ? 'show' : '' }}" id="purokModal">
    <div class="modal-box" style="max-width:480px;">
      <div class="modal-header">
        <h5>{{ $editPurok ? 'Edit Purok' : 'Add Purok' }}</h5>
        <a class="modal-close" style="text-decoration:none;color:inherit;font-size:24px;"
          href="{{ route('admin.puroks') }}">&times;</a>
      </div>
      <form method="POST" action="{{ route('admin.puroks.store') }}">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="purok_id" value="{{ $editPurok ? $editPurok->id : 0 }}">

          @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom:12px;">
              <ul style="margin:0 0 0 18px;">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="form-group">
            <label class="form-label">Purok Name *</label>
            <input type="text" name="name" class="form-control" required
              value="{{ old('name', $editPurok ? $editPurok->name : '') }}" placeholder="e.g. Purok 1 or Purok Mangga">
            @if ($editPurok)
              <small class="form-text">Renaming also updates the
                {{ $editPurok->residentCount() }} resident record(s) that use this purok.</small>
            @endif
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Sort Order *</label>
              <input type="number" name="sort_order" class="form-control" required
                value="{{ old('sort_order', $editPurok ? $editPurok->sort_order : count($puroks)) }}">
              <small class="form-text">Lower numbers appear first.</small>
            </div>
            <div class="form-group">
              <label class="form-label">Status *</label>
              <select name="status" class="form-select" required>
                @php($cur = old('status') ?? ($editPurok ? $editPurok->status : 'active'))
                <option value="active" @selected($cur === 'active')>Active</option>
                <option value="inactive" @selected($cur === 'inactive')>Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <a class="btn btn-secondary" style="text-decoration:none;" href="{{ route('admin.puroks') }}">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ $editPurok ? 'Update' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
