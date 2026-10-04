@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="profile-grid">

  <!-- Profile Card -->
  <div class="card" style="grid-column:span 2;">
    <div style="padding:28px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
      <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));
                  border:4px solid #fff;display:flex;align-items:center;justify-content:center;
                  font-size:32px;font-weight:800;color:#fff;box-shadow: 0 4px 6px rgba(0,0,0,0.15); overflow:hidden; flex-shrink: 0;">
        @if ($resident->photo)
          <img src="{{ asset('assets/uploads/' . $resident->photo) }}" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;">
        @else
          {{ strtoupper(substr($resident->first_name, 0, 1)) }}
        @endif
      </div>
      <div>
        <div style="font-size:22px;font-weight:800;">{{ $resident->full_name }}</div>
        <div style="color:#6b7280;font-size:14px;margin-bottom:8px;">Username: {{ Auth::user()->username }}</div>
        <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:13px;color:#374151;">
          <span><i class="fas fa-map-marker-alt" style="color:var(--primary);margin-right:4px;"></i>{{ $resident->address }}</span>
          <span><i class="fas fa-phone" style="color:var(--primary);margin-right:4px;"></i>{{ $resident->contact_number ?? '—' }}</span>
          <span><i class="fas fa-birthday-cake" style="color:var(--primary);margin-right:4px;"></i>{{ \Carbon\Carbon::parse($resident->birthdate)->format('F d, Y') }}</span>
          <span><i class="fas fa-user" style="color:var(--primary);margin-right:4px;"></i>{{ $resident->age }} yrs old</span>
        </div>
      </div>
    </div>
  </div>

  <div class="card" style="grid-column:span 2;">
    <div class="card-header"><h5><i class="fas fa-camera" style="color:var(--primary);margin-right:8px;"></i>Profile Photo</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('resident.profile') }}" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
        @csrf
        <input type="hidden" name="action" value="update_photo">
        <div style="flex:1;min-width:220px;">
          <label class="form-label">Upload New Photo</label>
          <input type="file" name="photo" class="form-control" accept="image/*" required>
        </div>
        <button class="btn btn-primary"><i class="fas fa-upload"></i> Update Photo</button>
      </form>
      @if ($resident->photo)
      <form method="POST" action="{{ route('resident.profile') }}" id="remove-photo-form" style="margin-top:12px;">
        @csrf
        <input type="hidden" name="action" value="remove_photo">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('removePhotoModal').style.display='flex'">
          <i class="fas fa-trash"></i> Remove Photo
        </button>
      </form>
      @endif
    </div>
  </div>

  <!-- Update Info Form -->
  <div class="card">
    <div class="card-header"><h5><i class="fas fa-user-edit" style="color:var(--primary);margin-right:8px;"></i>Edit Profile</h5></div>
    <div class="card-body">
      <form method="POST" action="{{ route('resident.profile') }}">
        @csrf
        <input type="hidden" name="action" value="update_profile">
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required value="{{ old('first_name', $resident->first_name) }}" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
          </div>
          <div class="form-group">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" required value="{{ old('last_name', $resident->last_name) }}" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Middle Name</label>
          <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $resident->middle_name) }}" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
        </div>
        <div class="form-group">
          <label class="form-label">Contact Number</label>
          <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $resident->contact_number) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <input type="text" name="address" class="form-control" required value="{{ old('address', $resident->address) }}">
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Purok</label>
            <input type="text" name="purok" class="form-control" value="{{ old('purok', $resident->purok) }}">
          </div>
          <div class="form-group">
            <label class="form-label">Occupation</label>
            <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $resident->occupation) }}">
          </div>
          <div class="form-group">
            <label class="form-label">Civil Status</label>
            <select name="civil_status" class="form-select">
              @foreach (['Single','Married','Widowed','Separated'] as $cs)
              <option {{ old('civil_status', $resident->civil_status) === $cs ? 'selected' : '' }}>{{ $cs }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Voter Status</label>
            <select name="voter_status" class="form-select">
              @foreach (['Not Registered','Registered'] as $vs)
              <option {{ old('voter_status', $resident->voter_status) === $vs ? 'selected' : '' }}>{{ $vs }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Update Profile</button>
      </form>
    </div>
  </div>

  <!-- Change Password -->
  <div class="card">
    <div class="card-header"><h5><i class="fas fa-lock" style="color:var(--primary);margin-right:8px;"></i>Change Password</h5></div>
    <div class="card-body">
      @if (session('password_change_pending'))
        <!-- Verification OTP form -->
        <div style="background-color: #fffbeb; border-left: 4px solid #d97706; padding: 12px; margin-bottom: 16px; border-radius: 4px; font-size: 14px; color: #b45309;">
          <i class="fas fa-paper-plane" style="margin-right: 6px;"></i> A confirmation code has been sent to your email.
        </div>
        <form method="POST" action="{{ route('resident.profile') }}" id="password-otp-form">
          @csrf
          <input type="hidden" name="action" value="verify_password_otp">
          <div class="form-group">
            <label class="form-label" style="text-align: center; display: block; margin-bottom: 8px;">Enter 6-Digit Confirmation Code</label>
            <div style="display: flex; justify-content: center; gap: 8px; margin: 16px 0;" id="password-otp-inputs">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="form-control otp-box-field" style="width: 42px; height: 48px; text-align: center; font-size: 20px; font-weight: bold; border: 1px solid #ced4da; border-radius: .25rem;">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="form-control otp-box-field" style="width: 42px; height: 48px; text-align: center; font-size: 20px; font-weight: bold; border: 1px solid #ced4da; border-radius: .25rem;">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="form-control otp-box-field" style="width: 42px; height: 48px; text-align: center; font-size: 20px; font-weight: bold; border: 1px solid #ced4da; border-radius: .25rem;">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="form-control otp-box-field" style="width: 42px; height: 48px; text-align: center; font-size: 20px; font-weight: bold; border: 1px solid #ced4da; border-radius: .25rem;">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="form-control otp-box-field" style="width: 42px; height: 48px; text-align: center; font-size: 20px; font-weight: bold; border: 1px solid #ced4da; border-radius: .25rem;">
              <input type="text" maxlength="1" pattern="[0-9]" required autocomplete="off" class="form-control otp-box-field" style="width: 42px; height: 48px; text-align: center; font-size: 20px; font-weight: bold; border: 1px solid #ced4da; border-radius: .25rem;">
            </div>
            <input type="hidden" name="otp_code" id="password_otp_code_val" required>
          </div>
          <button type="submit" class="btn btn-warning w-100"><i class="fas fa-shield-halved"></i> Confirm Password Change</button>
        </form>

        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.otp-box-field');
            const hiddenInput = document.getElementById('password_otp_code_val');
            const form = document.getElementById('password-otp-form');
            
            inputs.forEach((input, index) => {
              input.addEventListener('input', () => {
                input.value = input.value.replace(/[^0-9]/g, '');
                if (input.value && index < inputs.length - 1) {
                  inputs[index + 1].focus();
                }
                updateHiddenValue();
              });

              input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                  inputs[index - 1].focus();
                }
              });
            });

            function updateHiddenValue() {
              let code = '';
              inputs.forEach(input => code += input.value);
              hiddenInput.value = code;
            }

            form.addEventListener('submit', function(e) {
              updateHiddenValue();
              if (hiddenInput.value.length !== 6) {
                e.preventDefault();
                let err = document.getElementById('otp-error-msg');
                if (!err) {
                  err = document.createElement('div');
                  err.id = 'otp-error-msg';
                  err.style.color = '#dc2626';
                  err.style.fontSize = '12px';
                  err.style.fontWeight = '600';
                  err.style.textAlign = 'center';
                  err.style.marginTop = '6px';
                  document.getElementById('password-otp-inputs').parentNode.appendChild(err);
                }
                err.textContent = 'Please enter all 6 digits of the confirmation code.';
              }
            });
          });
        </script>
      @else
        <!-- Standard Change Password Form -->
        <form method="POST" action="{{ route('resident.profile') }}">
          @csrf
          <input type="hidden" name="action" value="change_password">
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required minlength="6">
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="6">
          </div>
          <button type="submit" class="btn btn-warning w-100" style="height:44px; font-weight:700; border-radius:10px;"><i class="fas fa-key"></i> Request Password Change</button>
        </form>
      @endif
    </div>
  </div>

</div>

{{-- Remove Photo Confirmation Modal (WebView-safe) --}}
<div id="removePhotoModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div style="background:#fff; border-radius:16px; max-width:320px; width:100%; padding:24px 20px; box-shadow:0 20px 50px rgba(0,0,0,0.25); text-align:center;">
    <div style="width:50px; height:50px; background:#fef2f2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
      <i class="fas fa-trash-alt" style="font-size:20px; color:#b91c1c;"></i>
    </div>
    <h5 style="margin:0 0 6px; font-size:16.5px; font-weight:700; color:#111;">Remove Photo?</h5>
    <p style="margin:0 0 20px; font-size:13px; color:#6b7280;">Are you sure you want to delete your profile photo?</p>
    <div style="display:flex; gap:10px;">
      <button onclick="document.getElementById('removePhotoModal').style.display='none'"
        style="flex:1; padding:10px; border-radius:8px; border:1.5px solid #e5e7eb; background:#f9fafb; font-size:13.5px; font-weight:600; cursor:pointer; color:#374151;">
        Cancel
      </button>
      <button onclick="document.getElementById('remove-photo-form').submit()"
        style="flex:1; padding:10px; border-radius:8px; border:none; background:#b91c1c; font-size:13.5px; font-weight:700; cursor:pointer; color:#fff;">
        Remove
      </button>
    </div>
  </div>
</div>
@endsection
