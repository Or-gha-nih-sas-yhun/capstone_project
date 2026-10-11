@extends('layouts.app')

@section('title', 'Manage Residents')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <form method="GET" action="{{ route('admin.residents') }}" style="display:flex;gap:8px;flex:1;max-width:400px;">
    <input type="hidden" name="status" value="{{ $status }}">
    <input type="text" name="search" class="form-control" placeholder="Search by name, email, contact…"
           value="{{ $search }}">
    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
    @if ($search)
      <a href="{{ route('admin.residents', ['status' => $status]) }}" class="btn btn-secondary">Reset</a>
    @endif
  </form>
  <button class="btn btn-primary" onclick="openModal('residentModal')">
    <i class="fas fa-plus"></i> Add Resident
  </button>
</div>

<<<<<<< HEAD
{{-- Classification Summary Cards --}}
<div class="grid-4" style="margin-bottom:20px;">
  <a href="{{ route('admin.residents', ['status' => 'active']) }}" class="stat-card" style="text-decoration:none; border-left: 4px solid #1d4ed8;">
    <div class="stat-icon" style="background:#eff6ff; color:#1d4ed8;"><i class="fas fa-users"></i></div>
    <div><div class="stat-number" style="color:#1d4ed8;">{{ $classificationCounts['total'] }}</div><div class="stat-label">Total Residents</div></div>
  </a>
  <a href="{{ route('admin.residents', ['status' => 'active', 'classification' => 'pwd']) }}" class="stat-card" style="text-decoration:none; border-left: 4px solid #7c3aed;">
    <div class="stat-icon" style="background:#f5f3ff; color:#7c3aed;"><i class="fas fa-wheelchair"></i></div>
    <div><div class="stat-number" style="color:#7c3aed;">{{ $classificationCounts['pwd'] }}</div><div class="stat-label">PWD</div></div>
  </a>
  <a href="{{ route('admin.residents', ['status' => 'active', 'classification' => 'senior']) }}" class="stat-card" style="text-decoration:none; border-left: 4px solid #d97706;">
    <div class="stat-icon" style="background:#fffbeb; color:#d97706;"><i class="fas fa-user-clock"></i></div>
    <div><div class="stat-number" style="color:#d97706;">{{ $classificationCounts['senior'] }}</div><div class="stat-label">Senior Citizens</div></div>
  </a>
  <a href="{{ route('admin.residents', ['status' => 'active', 'classification' => 'single_parent']) }}" class="stat-card" style="text-decoration:none; border-left: 4px solid #0d9488;">
    <div class="stat-icon" style="background:#f0fdfa; color:#0d9488;"><i class="fas fa-child"></i></div>
    <div><div class="stat-number" style="color:#0d9488;">{{ $classificationCounts['single_parent'] }}</div><div class="stat-label">Single Parents</div></div>
  </a>
</div>
<div class="grid-4" style="margin-bottom:24px;">
  <a href="{{ route('admin.residents', ['status' => 'active', 'classification' => 'widowed']) }}" class="stat-card" style="text-decoration:none; border-left: 4px solid #64748b;">
    <div class="stat-icon" style="background:#f8fafc; color:#64748b;"><i class="fas fa-ribbon"></i></div>
    <div><div class="stat-number" style="color:#64748b;">{{ $classificationCounts['widowed'] }}</div><div class="stat-label">Widowed</div></div>
  </a>
  <a href="{{ route('admin.residents', ['status' => 'active', 'classification' => 'married']) }}" class="stat-card" style="text-decoration:none; border-left: 4px solid #db2777;">
    <div class="stat-icon" style="background:#fdf2f8; color:#db2777;"><i class="fas fa-heart"></i></div>
    <div><div class="stat-number" style="color:#db2777;">{{ $classificationCounts['married'] }}</div><div class="stat-label">Married</div></div>
  </a>
  <a href="{{ route('admin.residents', ['status' => 'active', 'classification' => 'single']) }}" class="stat-card" style="text-decoration:none; border-left: 4px solid #0ea5e9;">
    <div class="stat-icon" style="background:#f0f9ff; color:#0ea5e9;"><i class="fas fa-user"></i></div>
    <div><div class="stat-number" style="color:#0ea5e9;">{{ $classificationCounts['single'] }}</div><div class="stat-label">Single</div></div>
  </a>
  <div class="stat-card" style="border-left: 4px solid #e5e7eb; background:#f9fafb;">
    <div class="stat-icon" style="background:#f1f5f9; color:#94a3b8;"><i class="fas fa-chart-pie"></i></div>
    <div><div class="stat-number" style="color:#94a3b8;">{{ $classificationCounts['pwd'] + $classificationCounts['senior'] }}</div><div class="stat-label">PWD + Senior</div></div>
  </div>
</div>

<div class="card">
  <div class="card-header" style="flex-direction:column;align-items:stretch;gap:16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
      <h5><i class="fas fa-users" style="color:var(--primary);margin-right:8px;"></i>
          Residents <span style="font-size:13px;font-weight:400;color:#6b7280;">({{ $residents->total() }})</span></h5>
      @if($classification)
        <a href="{{ route('admin.residents', ['status' => $status]) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i> Clear Filter</a>
      @endif
    </div>
    {{-- Status Tabs --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap;border-bottom:1px solid var(--gray-light);padding-bottom:8px;">
      <a href="{{ route('admin.residents', ['status' => 'active', 'search' => $search]) }}" class="btn btn-sm {{ $status === 'active' && !$classification ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius:20px; padding:6px 16px;">
        <i class="fas fa-user-check"></i> All Active
=======
<div class="card">
  <div class="card-header" style="flex-direction:column;align-items:stretch;gap:16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h5><i class="fas fa-users" style="color:var(--primary);margin-right:8px;"></i>
          Residents <span style="font-size:13px;font-weight:400;color:#6b7280;">({{ $residents->total() }})</span></h5>
    </div>
    <!-- Tabs/Filters -->
    <div style="display:flex;gap:8px;border-bottom:1px solid var(--gray-light);padding-bottom:8px;">
      <a href="{{ route('admin.residents', ['status' => 'active', 'search' => $search]) }}" class="btn btn-sm {{ $status === 'active' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius:20px; padding:6px 16px;">
        <i class="fas fa-user-check"></i> Active Residents
>>>>>>> origin/master
      </a>
      <a href="{{ route('admin.residents', ['status' => 'pending', 'search' => $search]) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius:20px; padding:6px 16px; position:relative;">
        <i class="fas fa-user-clock"></i> Pending Approval
        @if ($pendingCount > 0)
          <span class="badge bg-danger" style="font-size:9px; padding:2px 6px; margin-left:4px;">{{ $pendingCount }}</span>
        @endif
      </a>
<<<<<<< HEAD
      <a href="{{ route('admin.residents', ['status' => 'active', 'classification' => 'pwd']) }}" class="btn btn-sm {{ $classification === 'pwd' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius:20px; padding:6px 16px;">
        <i class="fas fa-wheelchair"></i> PWD
      </a>
      <a href="{{ route('admin.residents', ['status' => 'active', 'classification' => 'senior']) }}" class="btn btn-sm {{ $classification === 'senior' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius:20px; padding:6px 16px;">
        <i class="fas fa-user-clock"></i> Senior Citizens
      </a>
      <a href="{{ route('admin.residents', ['status' => 'active', 'classification' => 'single_parent']) }}" class="btn btn-sm {{ $classification === 'single_parent' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius:20px; padding:6px 16px;">
        <i class="fas fa-child"></i> Single Parents
      </a>
      <a href="{{ route('admin.residents', ['status' => 'active', 'classification' => 'widowed']) }}" class="btn btn-sm {{ $classification === 'widowed' ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius:20px; padding:6px 16px;">
        <i class="fas fa-ribbon"></i> Widowed
      </a>
=======
>>>>>>> origin/master
    </div>
  </div>
  <div class="table-wrapper">
    <table class="table datatable" id="residents-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Gender</th>
          <th>Age</th>
          <th>Civil Status</th>
<<<<<<< HEAD
          <th>PWD</th>
=======
<<<<<<< HEAD
          <th>Classifications</th>
=======
>>>>>>> origin/master
>>>>>>> 160554eb4e3cb39dae4d3c7f8e7f029ef71115bc
          <th>Contact</th>
          <th>Purok</th>
          <th>Voter</th>
          <th>Account</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @if ($residents->isEmpty())
<<<<<<< HEAD
          <tr><td colspan="11" class="text-center text-muted" style="padding:40px;">No residents found.</td></tr>
=======
<<<<<<< HEAD
          <tr><td colspan="11" class="text-center text-muted" style="padding:40px;">No residents found.</td></tr>
=======
          <tr><td colspan="10" class="text-center text-muted" style="padding:40px;">No residents found.</td></tr>
>>>>>>> origin/master
>>>>>>> 160554eb4e3cb39dae4d3c7f8e7f029ef71115bc
        @else
          @foreach ($residents as $i => $r)
            <tr>
              <td>{{ $residents->firstItem() + $i }}</td>
              <td>
                <div style="font-weight:600;">{{ $r->last_name }}, {{ $r->first_name }} {{ $r->middle_name }}</div>
                <small class="text-muted">{{ $r->email ?? '' }}</small>
              </td>
              <td>{{ $r->gender }}</td>
              <td>{{ $r->age }}</td>
              <td>{{ $r->civil_status }}</td>
<<<<<<< HEAD
              <td>{{ $r->is_pwd ? 'Yes' : 'No' }}</td>
=======
<<<<<<< HEAD
              <td>
                <div style="display:flex; flex-wrap:wrap; gap:4px;">
                  @if($r->is_pwd)
                    <span class="badge" style="background:#f5f3ff;color:#7c3aed;border-color:#ddd6fe;"><i class="fas fa-wheelchair"></i> PWD</span>
                  @endif
                  @if(\Carbon\Carbon::parse($r->birthdate)->age >= 60)
                    <span class="badge" style="background:#fffbeb;color:#d97706;border-color:#fde68a;"><i class="fas fa-user-clock"></i> Senior</span>
                  @endif
                  @if($r->is_single_parent)
                    <span class="badge" style="background:#f0fdfa;color:#0d9488;border-color:#a7f3d0;"><i class="fas fa-child"></i> Single Parent</span>
                  @endif
                  @if(!$r->is_pwd && !$r->is_single_parent && \Carbon\Carbon::parse($r->birthdate)->age < 60)
                    <span class="text-muted" style="font-size:12px;">—</span>
                  @endif
                </div>
              </td>
=======
>>>>>>> origin/master
>>>>>>> 160554eb4e3cb39dae4d3c7f8e7f029ef71115bc
              <td>{{ $r->contact_number ?? '—' }}</td>
              <td>{{ $r->purok ?? '—' }}</td>
              <td>
                @if ($r->voter_status === 'Registered')
                  <span class="badge bg-success">Registered</span>
                @else
                  <span class="badge bg-secondary">No</span>
                @endif
              </td>
              <td>
                @if ($r->user)
                  <span class="badge bg-primary"><i class="fas fa-check"></i> Yes</span>
                @else
                  <span class="badge bg-secondary">None</span>
                @endif
              </td>
              <td>
                <div style="display:flex;gap:4px;">
                  @if ($status === 'pending')
                    <a href="{{ route('admin.residents.approve', ['id' => $r->id]) }}" class="btn btn-success btn-sm"
                       onclick="return confirm('Approve this resident registration?')">
                      <i class="fas fa-check-circle"></i> Approve
                    </a>
                    <a href="{{ route('admin.residents.reject', ['id' => $r->id]) }}" class="btn btn-danger btn-sm"
                       onclick="return confirm('Reject and remove this resident registration?')">
                      <i class="fas fa-times-circle"></i> Reject
                    </a>
                  @else
                    <a href="{{ route('admin.residents', ['edit' => $r->id, 'status' => $status, 'search' => $search]) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                    <button type="button" class="btn btn-danger btn-sm" title="Archive"
                            data-url="{{ route('admin.residents.delete', ['id' => $r->id]) }}"
                            data-name="{{ $r->last_name }}, {{ $r->first_name }} {{ $r->middle_name }}"
                            data-has-account="{{ $r->user ? '1' : '0' }}"
                            onclick="openArchiveModal(this)"><i class="fas fa-trash"></i></button>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
  @if ($residents->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $residents->appends(['status' => $status, 'search' => $search])->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>

<!-- Add / Edit Modal -->
<div class="modal-overlay {{ ($editResident || $errors->any()) ? 'show' : '' }}" id="residentModal">
  <div class="modal-box" style="max-width:700px;">
    <div class="modal-header">
      <h5>{{ $editResident ? 'Edit Resident' : 'Add New Resident' }}</h5>
      <a class="modal-close" style="text-decoration:none; color:inherit; font-size:24px;" href="{{ route('admin.residents', ['status' => $status, 'search' => $search]) }}">&times;</a>
    </div>
    <form method="POST" action="{{ route('admin.residents.store') }}">
      @csrf
      <div class="modal-body">
        <input type="hidden" name="resident_id" value="{{ $editResident ? $editResident->id : 0 }}">
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">First Name *</label>
            <input type="text" name="first_name" class="form-control" required value="{{ old('first_name', $editResident ? $editResident->first_name : '') }}" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
          </div>
          <div class="form-group">
            <label class="form-label">Last Name *</label>
            <input type="text" name="last_name" class="form-control" required value="{{ old('last_name', $editResident ? $editResident->last_name : '') }}" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
          </div>
          <div class="form-group">
            <label class="form-label">Middle Name</label>
            <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $editResident ? $editResident->middle_name : '') }}" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
          </div>
          <div class="form-group">
            <label class="form-label">Birthdate *</label>
            <input type="date" name="birthdate" class="form-control" required value="{{ old('birthdate', $editResident ? $editResident->birthdate : '') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Gender *</label>
            <select name="gender" class="form-select" required>
              @foreach (['Male','Female','Other'] as $g)
                <option value="{{ $g }}" {{ (old('gender') ?? ($editResident ? $editResident->gender : '')) === $g ? 'selected' : '' }}>{{ $g }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Civil Status *</label>
            <select name="civil_status" class="form-select" required>
              @foreach (['Single','Married','Widowed','Separated'] as $cs)
                <option value="{{ $cs }}" {{ (old('civil_status') ?? ($editResident ? $editResident->civil_status : '')) === $cs ? 'selected' : '' }}>{{ $cs }}</option>
              @endforeach
            </select>
          </div>
<<<<<<< HEAD
          <div class="form-group" style="grid-column:span 2;">
            <label class="form-label">Special Classifications</label>
            <div style="display:flex; gap: 20px; align-items:center;">
              <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:14px; color:var(--dark-3);">
                <input type="hidden" name="is_pwd" value="0">
                <input type="checkbox" name="is_pwd" value="1" {{ (old('is_pwd') ?? ($editResident ? $editResident->is_pwd : false)) ? 'checked' : '' }}>
                PWD (Person with Disability)
              </label>
              <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:14px; color:var(--dark-3);">
                <input type="hidden" name="is_single_parent" value="0">
                <input type="checkbox" name="is_single_parent" value="1" {{ (old('is_single_parent') ?? ($editResident ? $editResident->is_single_parent : false)) ? 'checked' : '' }}>
                Single Parent
              </label>
            </div>
          </div>
=======
>>>>>>> origin/master
          <div class="form-group">
            <label class="form-label">Person with Disability (PWD) *</label>
            <select name="is_pwd" class="form-select" required>
              <option value="0" {{ (string) old('is_pwd', $editResident ? (int) $editResident->is_pwd : 0) === '0' ? 'selected' : '' }}>No</option>
              <option value="1" {{ (string) old('is_pwd', $editResident ? (int) $editResident->is_pwd : 0) === '1' ? 'selected' : '' }}>Yes</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $editResident ? $editResident->contact_number : '') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $editResident ? $editResident->email : '') }}">
          </div>
          <div class="form-group" style="grid-column:span 2;">
            <label class="form-label">Complete Address *</label>
<<<<<<< HEAD
            <input type="text" name="address" class="form-control" required value="{{ old('address', $editResident ? $editResident->address : 'Barangay Pili, Madridejos, Cebu') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Purok</label>
            <input type="text" name="purok" class="form-control" value="{{ old('purok', $editResident ? $editResident->purok : '') }}">
=======
            <input type="text" name="address" class="form-control" required value="{{ old('address', $editResident ? $editResident->address : barangay_location(true)) }}">
          </div>
          <div class="form-group">
            <label class="form-label">Purok</label>
            @include('components.purok-select', ['value' => old('purok', $editResident ? $editResident->purok : '')])
>>>>>>> origin/master
          </div>
          <div class="form-group">
            <label class="form-label">Occupation</label>
            <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $editResident ? $editResident->occupation : '') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Voter Status</label>
            <select name="voter_status" class="form-select">
              @foreach (['Not Registered','Registered'] as $vs)
                <option value="{{ $vs }}" {{ (old('voter_status') ?? ($editResident ? $editResident->voter_status : '')) === $vs ? 'selected' : '' }}>{{ $vs }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Years of Residency</label>
            <input type="number" name="years_of_residency" class="form-control" min="0" value="{{ old('years_of_residency', $editResident ? $editResident->years_of_residency : 0) }}">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a class="btn btn-secondary" style="text-decoration:none;" href="{{ route('admin.residents', ['status' => $status, 'search' => $search]) }}">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> {{ $editResident ? 'Update' : 'Save' }} Resident
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Archive Confirmation Modal -->
<div class="modal-overlay" id="archiveModal">
  <div class="modal-box" style="max-width:440px;">
    <div class="modal-header">
      <h5><i class="fas fa-archive" style="color:var(--danger);margin-right:8px;"></i>Archive Resident</h5>
      <button type="button" class="modal-close" onclick="closeModal('archiveModal')">&times;</button>
    </div>
    <div class="modal-body">
      <p style="margin-bottom:12px;">Are you sure you want to archive <strong id="archiveResidentName"></strong>?</p>
      <p id="archiveAccountNote" class="text-muted" style="font-size:13px;margin-bottom:12px;">
        <i class="fas fa-user-lock"></i> Their linked user account will also be deactivated and they will no longer be able to log in.
      </p>
      <p class="text-muted" style="font-size:13px;margin:0;">
        <i class="fas fa-info-circle"></i> You can restore this record anytime from the Archive page.
      </p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('archiveModal')">Cancel</button>
      <a href="#" id="archiveConfirmBtn" class="btn btn-danger" style="text-decoration:none;">
        <i class="fas fa-archive"></i> Archive
      </a>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  function openArchiveModal(btn) {
    document.getElementById('archiveResidentName').textContent = btn.dataset.name.trim();
    document.getElementById('archiveAccountNote').style.display = btn.dataset.hasAccount === '1' ? '' : 'none';
    document.getElementById('archiveConfirmBtn').href = btn.dataset.url;
    openModal('archiveModal');
  }
</script>
@endsection
