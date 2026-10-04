@extends('layouts.app')

@section('title', 'New Request')

@section('styles')
<style>
.cert-card {
  position: relative;
  border: 2px solid var(--gray-light);
  border-radius: 12px;
  padding: 16px 12px;
  transition: all .2s ease;
  text-align: center;
  background: #fff;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}
.cert-card:active {
  transform: scale(0.98);
}
.cert-card .cert-check-indicator {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  color: #fff;
  transition: all 0.2s ease;
}
.cert-card.selected {
  border-color: var(--primary) !important;
  background: #fef2f2 !important;
  box-shadow: 0 4px 12px rgba(185, 28, 28, 0.12);
}
.cert-card.selected .cert-check-indicator {
  background: var(--primary);
  color: #fff;
}
@media (max-width: 640px) {
  .cert-selection-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 10px !important;
  }
  .cert-card {
    padding: 14px 8px;
  }
}
@media (max-width: 380px) {
  .cert-selection-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>
@endsection

@section('content')
<div style="max-width:680px;margin:0 auto;">
  <div class="card" style="border-radius:14px; overflow:hidden;">
    <div class="card-header" style="display:flex; align-items:center; justify-content:space-between;">
      <h5><i class="fas fa-plus-circle" style="color:var(--primary);margin-right:8px;"></i>New Clearance / Certificate Request</h5>
    </div>
    <div class="card-body" style="padding:20px;">

      <!-- Resident Info (read-only) -->
      <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; margin-bottom:20px;">
        <div style="font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#64748b; margin-bottom:8px;">Filing As</div>
        <div style="display:flex; align-items:center; gap:12px;">
          <div style="width:44px; height:44px; border-radius:50%; background:linear-gradient(135deg,var(--primary),#dc2626);
                      display:flex; align-items:center; justify-content:center; font-size:17px; font-weight:800; color:#fff; flex-shrink:0;">
            @if(Auth::user()->resident && Auth::user()->resident->photo)
              <img src="{{ asset('assets/uploads/' . Auth::user()->resident->photo) }}" alt="Photo" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
            @else
              {{ strtoupper(substr($resident->first_name, 0, 1)) }}
            @endif
          </div>
          <div style="min-width:0;">
            <div style="font-weight:700; font-size:15px; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              {{ $resident->full_name }}
            </div>
            <div style="font-size:12px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              {{ $resident->address }}{{ $resident->purok ? ' · ' . $resident->purok : '' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Notice -->
      <div class="alert alert-info" style="margin-bottom:16px; border-left:4px solid #2563eb; border-radius:8px; font-size:13px;">
        <i class="fas fa-university" style="margin-right:8px;"></i>
        <div>
          <strong>Payment & Claiming Information</strong><br>
          <span>Processing fee will be settled at <strong>Barangay Pili Hall</strong> upon document pickup.</span>
        </div>
      </div>

      <form method="POST" action="{{ route('resident.request') }}" id="requestForm">
        @csrf
        <!-- Certificate type cards -->
        <div class="form-group" style="margin-bottom:18px;">
          <label class="form-label" style="font-weight:700; margin-bottom:10px; display:block;">Select Document to Request *</label>
          <div class="cert-selection-grid">
            @foreach ($certs as $c)
            <label style="cursor:pointer; display:block; margin:0;">
              <input type="radio" name="certificate_id" value="{{ $c->id }}"
                     class="cert-radio" style="display:none;" required
                     {{ old('certificate_id') == $c->id ? 'checked' : '' }}
                     onchange="updateFee({{ $c->fee }}, '{{ htmlspecialchars($c->requirements ?? '', ENT_QUOTES) }}')">
              <div class="cert-card {{ old('certificate_id') == $c->id ? 'selected' : '' }}" id="cert-{{ $c->id }}">
                <span class="cert-check-indicator"><i class="fas fa-check"></i></span>
                <div style="font-size:26px; margin-bottom:6px;">📄</div>
                <div style="font-size:13px; font-weight:700; color:#1e293b; margin-bottom:4px; line-height:1.3;">
                  {{ $c->name }}
                </div>
                <div style="font-size:12.5px; color:var(--primary); font-weight:700;">
                  {{ $c->fee > 0 ? '₱' . number_format($c->fee, 2) : 'FREE' }}
                </div>
                <div style="font-size:11px; color:#94a3b8; margin-top:4px;">
                  ~{{ $c->processing_days }} day{{ $c->processing_days > 1 ? 's' : '' }}
                </div>
              </div>
            </label>
            @endforeach
          </div>
        </div>

        <!-- Requirements notice -->
        <div id="req-notice" style="display:none; margin-bottom:16px;">
          <div class="alert alert-info" style="margin:0; border-radius:8px; font-size:13px;">
            <i class="fas fa-clipboard-list"></i>
            <div>
              <strong>Requirements:</strong>
              <span id="req-text"></span>
            </div>
          </div>
        </div>

        <!-- Fee display -->
        <div id="fee-display" style="display:none; margin-bottom:16px;">
          <div class="alert alert-warning" style="margin:0; border-radius:8px; font-size:13px;">
            <i class="fas fa-peso-sign"></i>
            <div>Processing fee: <strong id="fee-amount"></strong>. <em>Payable at the Barangay Pili Office.</em></div>
          </div>
        </div>

        <div class="form-group" style="margin-bottom:20px;">
          <label class="form-label" for="purpose" style="font-weight:700;">Purpose / Reason for Request *</label>
          <textarea id="purpose" name="purpose" class="form-control" rows="3"
                    placeholder="e.g. For employment, scholarship, bank requirement, ID application…" required style="font-size:14px; border-radius:8px;">{{ old('purpose') }}</textarea>
          <div class="form-text" style="font-size:11.5px; color:#64748b; margin-top:4px;">
            State the exact purpose so the barangay staff can prepare the correct document.
          </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:10px;">
          <a href="{{ route('resident.my_requests') }}" class="btn btn-secondary" style="height:46px; display:inline-flex; align-items:center; justify-content:center; padding:0 18px; border-radius:10px; font-weight:600;">
            <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Back
          </a>
          <button type="submit" class="btn btn-primary" style="flex:1; height:46px; font-size:15px; font-weight:700; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
            <i class="fas fa-paper-plane"></i> Submit Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function updateFee(fee, reqs) {
  // Highlight selected card
  document.querySelectorAll('.cert-card').forEach(c => {
    c.classList.remove('selected');
  });
  const radio = document.querySelector('.cert-radio:checked');
  if (radio) {
    const card = radio.nextElementSibling;
    card.classList.add('selected');
  }
  // Show fee display
  const fd = document.getElementById('fee-display');
  const fa = document.getElementById('fee-amount');
  if (fee > 0) {
    fd.style.display = 'block';
    fa.textContent   = '₱' + parseFloat(fee).toFixed(2);
  } else {
    fd.style.display = 'none';
  }
  // Show requirements
  const rn = document.getElementById('req-notice');
  const rt = document.getElementById('req-text');
  if (reqs && reqs.trim() !== '') {
    rn.style.display = 'block';
    rt.textContent   = ' ' + reqs;
  } else {
    rn.style.display = 'none';
  }
}

// Handle old input highlight on page load if validation errors occurred
document.addEventListener('DOMContentLoaded', () => {
  const radio = document.querySelector('.cert-radio:checked');
  if (radio) {
    radio.dispatchEvent(new Event('change'));
  }
});
</script>
@endsection
