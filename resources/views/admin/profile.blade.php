@extends('layouts.app')

@section('title', 'Admin Profile')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:900px;margin:0 auto;">

  <!-- Profile Card -->
  <div class="card" style="grid-column:span 2;">
    <div style="padding:28px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
      <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));
                  border:4px solid #fff;display:flex;align-items:center;justify-content:center;
                  font-size:32px;font-weight:800;color:#fff;box-shadow: 0 4px 6px rgba(0,0,0,0.15); overflow:hidden; flex-shrink: 0;">
        @if ($user->photo)
          <img src="{{ asset('assets/uploads/' . $user->photo) }}" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;">
        @else
          {{ strtoupper(substr($user->username, 0, 1)) }}
        @endif
      </div>
      <div>
        <div style="font-size:22px;font-weight:800;">@{{ $user->username }}</div>
        <div style="color:#6b7280;font-size:14px;margin-bottom:8px;">{{ $user->email }}</div>
        <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:13px;color:#374151;">
          <span><i class="fas fa-shield-alt" style="color:var(--primary);margin-right:4px;"></i>System {{ ucfirst($user->role) }} Account</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Update Profile Form -->
  <div class="card" style="grid-column: span 2;">
    <div class="card-header"><h5><i class="fas fa-user-edit" style="color:var(--primary);margin-right:8px;"></i>Account Settings</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.profile') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" required value="{{ old('email', $user->email) }}">
          </div>
          <div class="form-group">
            <label class="form-label">Upload Profile Photo</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
          </div>
        </div>
        
        <div class="grid-2" style="margin-top: 10px;">
          <div class="form-group">
            <label class="form-label">New Password (leave empty to keep current)</label>
            <input type="password" name="password" class="form-control" placeholder="Min 6 characters">
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password">
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:14px;"><i class="fas fa-save"></i> Save Profile Settings</button>
      </form>
    </div>
  </div>

</div>
@endsection
