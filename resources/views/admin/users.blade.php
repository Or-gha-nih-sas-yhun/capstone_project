@extends('layouts.app')

@section('title', 'Manage Admin Users')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <form method="GET" action="{{ route('admin.users') }}" style="display:flex;gap:8px;flex:1;max-width:400px;">
    <input type="text" name="search" class="form-control" placeholder="Search by username or email..."
           value="{{ $search }}">
    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
    @if ($search)
      <a href="{{ route('admin.users') }}" class="btn btn-secondary">Reset</a>
    @endif
  </form>
  <button class="btn btn-primary" onclick="openModal('userModal')">
    <i class="fas fa-plus"></i> Add Account
  </button>
</div>

<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-user-shield" style="color:var(--primary);margin-right:8px;"></i>
      Admin & Staff Accounts <span style="font-size:13px;font-weight:400;color:#6b7280;">({{ $users->total() }})</span></h5>
  </div>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @if ($users->isEmpty())
          <tr><td colspan="5" class="text-center text-muted" style="padding:40px;">No accounts found.</td></tr>
        @else
          @foreach ($users as $u)
            <tr>
              <td style="font-weight:600;">
                <div>{{ $u->username }}</div>
              </td>
              <td>{{ $u->email }}</td>
              <td><span class="badge bg-secondary">{{ ucfirst($u->role) }}</span></td>
              <td>
                <span class="badge bg-{{ $u->status === 'active' ? 'success' : ($u->status === 'inactive' ? 'secondary' : 'danger') }}">
                  {{ ucfirst($u->status) }}
                </span>
              </td>
              <td>
                <div style="display:flex;gap:4px;">
                  <a href="{{ route('admin.users', ['edit' => $u->id, 'search' => $search]) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                  @if ($u->id !== Auth::id())
                    <a href="{{ route('admin.users.delete', ['id' => $u->id]) }}" class="btn btn-danger btn-sm"
                       onclick="return confirm('Archive this user account?')"><i class="fas fa-trash"></i></a>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
  @if ($users->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $users->appends(['search' => $search])->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>

<!-- Add / Edit Modal -->
<div class="modal-overlay {{ ($editUser || $errors->any()) ? 'show' : '' }}" id="userModal">
  <div class="modal-box" style="max-width:500px;">
    <div class="modal-header">
      <h5>{{ $editUser ? 'Edit Account' : 'Add Admin/Staff Account' }}</h5>
      <a class="modal-close" style="text-decoration:none; color:inherit; font-size:24px;" href="{{ route('admin.users', ['search' => $search]) }}">&times;</a>
    </div>
    <form method="POST" action="{{ route('admin.users.store') }}">
      @csrf
      <div class="modal-body">
        <input type="hidden" name="user_id" value="{{ $editUser ? $editUser->id : 0 }}">
        <div class="form-group">
          <label class="form-label">Username *</label>
          <input type="text" name="username" class="form-control" required value="{{ old('username', $editUser ? $editUser->username : '') }}">
        </div>
        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input type="email" name="email" class="form-control" required value="{{ old('email', $editUser ? $editUser->email : '') }}">
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Role *</label>
            <select name="role" class="form-select" required>
              <option value="staff" {{ (old('role') ?? ($editUser ? $editUser->role : '')) === 'staff' ? 'selected' : '' }}>Staff</option>
              <option value="admin" {{ (old('role') ?? ($editUser ? $editUser->role : '')) === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Status *</label>
            <select name="status" class="form-select" required>
              <option value="active" {{ (old('status') ?? ($editUser ? $editUser->status : '')) === 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ (old('status') ?? ($editUser ? $editUser->status : '')) === 'inactive' ? 'selected' : '' }}>Inactive</option>
              <option value="suspended" {{ (old('status') ?? ($editUser ? $editUser->status : '')) === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Password {{ $editUser ? '(Leave blank to keep current)' : '*' }}</label>
          <input type="password" name="password" class="form-control" {{ $editUser ? '' : 'required' }} minlength="6">
        </div>
      </div>
      <div class="modal-footer">
        <a class="btn btn-secondary" style="text-decoration:none;" href="{{ route('admin.users', ['search' => $search]) }}">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> {{ $editUser ? 'Update' : 'Save' }} Account
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
