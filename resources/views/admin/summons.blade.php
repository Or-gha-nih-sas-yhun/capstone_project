@extends('layouts.app')

@section('title', 'Summon & Blotter Management')

@section('content')
<div class="card">
  <div class="card-header" style="flex-wrap:wrap; gap:12px;">
    <h5><i class="fas fa-gavel" style="color:var(--primary);margin-right:8px;"></i>Barangay Summons &amp; Blotters ({{ $summons->total() }})</h5>
    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
      <form method="GET" action="{{ route('admin.summons') }}" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <select name="case_type" class="form-select" style="width:140px;" onchange="this.form.submit()">
          <option value="all" {{ ($type ?? 'all') === 'all' ? 'selected' : '' }}>All Case Types</option>
          <option value="summon" {{ ($type ?? 'all') === 'summon' ? 'selected' : '' }}>Summons</option>
          <option value="blotter" {{ ($type ?? 'all') === 'blotter' ? 'selected' : '' }}>Blotters Only</option>
        </select>
        
        <select name="status" class="form-select" style="width:130px;" onchange="this.form.submit()">
          <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
          <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
          <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
          <option value="amicably_settled" {{ $status === 'amicably_settled' ? 'selected' : '' }}>Amicably Settled</option>
          <option value="certified_to_file_action" {{ $status === 'certified_to_file_action' ? 'selected' : '' }}>Certified for Court</option>
          <option value="dismissed" {{ $status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
          <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        
        <input type="text" name="search" class="form-control" placeholder="Search case / names / details..." value="{{ $search }}" style="width:220px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        @if($search || $status !== 'all' || ($type ?? 'all') !== 'all')
          <a href="{{ route('admin.summons') }}" class="btn btn-outline-secondary"><i class="fas fa-sync"></i></a>
        @endif
      </form>
      <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal()">
        <i class="fas fa-plus"></i> File New Case
      </button>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Case Number</th>
          <th>Type</th>
          <th>Complainant (Declarant)</th>
          <th>Respondent (Accused)</th>
          <th>Incident Details</th>
          <th>Hearing Schedule</th>
          <th>Status</th>
          <th>Print KP Forms</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @if ($summons->isEmpty())
          <tr>
            <td colspan="9" class="text-center" style="padding:40px;">No cases registered yet matching current filters.</td>
          </tr>
        @else
          @foreach ($summons as $s)
            <tr>
              <td><strong style="color:var(--primary); font-size:12px;">{{ $s->case_number }}</strong></td>
              <td>
                <span class="badge bg-{{ $s->case_type === 'summon' ? 'primary' : 'secondary' }}" style="font-size:10px;">
                  {{ ucfirst($s->case_type) }}
                </span>
              </td>
              <td>
                <div><strong>{{ $s->complainant_name }}</strong></div>
                @if($s->complainant_resident_id)
                  <span class="badge bg-success" style="font-size:9px;">Resident</span>
                @endif
                <div style="font-size:11px; color:var(--gray);">{{ $s->complainant_contact ?? 'No Contact' }}</div>
              </td>
              <td>
                <div><strong>{{ $s->respondent_name }}</strong></div>
                @if($s->respondent_resident_id)
                  <span class="badge bg-success" style="font-size:9px;">Resident</span>
                @endif
                <div style="font-size:11px; color:var(--gray);">{{ $s->respondent_contact ?? 'No Contact' }}</div>
              </td>
              <td>
                <div style="font-weight:600; font-size:12px;" title="{{ $s->nature_of_complaint ?? 'N/A' }}">
                  {{ Str::limit($s->nature_of_complaint ?? 'Dispute / Incident', 25) }}
                </div>
                <div style="font-size:11px; color:var(--gray);" title="Location">
                  <i class="fas fa-map-marker-alt" style="margin-right:4px;"></i> {{ $s->incident_location ?? 'Barangay Pili' }}
                </div>
                <div style="font-size:11px; color:var(--gray);">
                  <i class="fas fa-calendar-alt" style="margin-right:4px;"></i> {{ $s->incident_date ? \Carbon\Carbon::parse($s->incident_date)->format('M d, Y') : 'Date Unspecified' }}
                </div>
              </td>
              <td>
                @if($s->case_type === 'blotter')
                  <span class="text-muted" style="font-size:11px; font-style:italic;">Record Only (No Hearing)</span>
                @elseif($s->schedule_date)
                  <div style="font-weight:600;">{{ \Carbon\Carbon::parse($s->schedule_date)->format('M d, Y') }}</div>
                  <div style="font-size:11px; color:var(--gray);">{{ \Carbon\Carbon::parse($s->schedule_date)->format('h:i A') }}</div>
                  @if($s->hearings->count() > 1)
                    <span class="badge bg-dark" style="font-size:9px;">Hearing #{{ $s->hearings->count() }}</span>
                  @endif
                @else
                  <span class="text-danger" style="font-size:11px; font-weight:600;">Not Scheduled</span>
                @endif
              </td>
              <td>
                @php
                  $badgeColor = 'warning';
                  if ($s->status === 'amicably_settled') $badgeColor = 'success';
                  elseif ($s->status === 'certified_to_file_action') $badgeColor = 'danger';
                  elseif ($s->status === 'scheduled') $badgeColor = 'info';
                  elseif ($s->status === 'dismissed' || $s->status === 'cancelled') $badgeColor = 'secondary';
                @endphp
                <span class="badge bg-{{ $badgeColor }}" style="font-size: 11px;">
                  {{ ucwords(str_replace('_', ' ', $s->status)) }}
                </span>
              </td>
              <td>
                <div class="dropdown-print" style="position:relative; display:inline-block;">
                  <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle-btn" onclick="togglePrintDropdown(this)">
                    <i class="fas fa-print"></i> Forms
                  </button>
                  <div class="dropdown-print-content" style="display:none; position:absolute; right:0; background-color:#f9f9f9; min-width:160px; box-shadow:0px 8px 16px 0px rgba(0,0,0,0.2); z-index:1; border-radius:4px; border:1px solid #e5e7eb;">
                    <a href="{{ route('print.summon', ['id' => $s->id, 'form_type' => 'kp7']) }}" target="_blank" style="color:black; padding:8px 12px; text-decoration:none; display:block; font-size:12px;"><i class="fas fa-file-invoice" style="margin-right:6px; color:#4b5563;"></i>KP 7: Complaint</a>
                    @if($s->case_type === 'summon')
                      <a href="{{ route('print.summon', ['id' => $s->id, 'form_type' => 'kp8']) }}" target="_blank" style="color:black; padding:8px 12px; text-decoration:none; display:block; font-size:12px;"><i class="fas fa-envelope-open-text" style="margin-right:6px; color:#2563eb;"></i>KP 8: Complainant Notice</a>
                      <a href="{{ route('print.summon', ['id' => $s->id, 'form_type' => 'kp9']) }}" target="_blank" style="color:black; padding:8px 12px; text-decoration:none; display:block; font-size:12px;"><i class="fas fa-gavel" style="margin-right:6px; color:#dc2626;"></i>KP 9: Respondent Summon</a>
                      <a href="{{ route('print.summon', ['id' => $s->id, 'form_type' => 'kp20']) }}" target="_blank" style="color:black; padding:8px 12px; text-decoration:none; display:block; font-size:12px;"><i class="fas fa-file-signature" style="margin-right:6px; color:#16a34a;"></i>KP 20: Court Certificate</a>
                    @endif
                  </div>
                </div>
              </td>
              <td>
                <div style="display:flex; gap:4px;">
                  <button type="button" class="btn btn-outline-primary btn-sm" onclick="openEditModal({{ json_encode($s->load('hearings')) }})" title="Edit / Update Case">
                    <i class="fas fa-edit"></i>
                  </button>
                  <a href="{{ route('admin.summons.delete', $s->id) }}" class="btn btn-outline-danger btn-sm" onclick="return confirm('Archive this case? It can be restored from the System Archive page.')" title="Archive Case">
                    <i class="fas fa-archive"></i>
                  </a>
                </div>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
  @if ($summons->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $summons->appends(request()->query())->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>

<!-- Create Summon Modal -->
<div class="modal" id="createModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px; overflow-y:auto;">
  <div class="modal-content" style="background:#fff; border-radius:16px; max-width:650px; width:100%; overflow:hidden; box-shadow:var(--shadow-md); margin-top: 40px; margin-bottom: 40px;">
    <div class="modal-header" style="padding:16px 24px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:between; align-items:center;">
      <h5 style="margin:0; font-weight:600;"><i class="fas fa-plus" style="color:var(--primary); margin-right:8px;"></i>File New Blotter or Summon Case</h5>
      <button type="button" class="btn-close" onclick="closeCreateModal()" style="background:none; border:none; cursor:pointer; font-size:18px; color:var(--gray);">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.summons.store') }}">
      @csrf
      
      <div style="padding:24px; display:flex; flex-direction:column; gap:16px; max-height:calc(100vh - 200px); overflow-y:auto;">
        
        <!-- Case Type Select -->
        <div class="form-group" style="margin:0;">
          <label class="form-label" style="font-weight:700;">Case Category / Classification *</label>
          <select name="case_type" id="create_case_type" class="form-select" required onchange="toggleScheduleRequirement(this.value)" style="border: 2px solid var(--primary);">
            <option value="summon">Summon (Requires Conciliation/Hearing Schedule)</option>
            <option value="blotter">Blotter (For Official Record Only - No Hearing)</option>
          </select>
        </div>

        <!-- Complainant -->
        <h6 style="margin:10px 0 0 0; font-weight:700; color:var(--primary); border-bottom:1px solid #f3f4f6; padding-bottom:6px;">Complainant Details (Declarant)</h6>
        <div class="form-group" style="margin:0;">
          <label class="form-label">Link Registered Resident (Optional)</label>
          <select name="complainant_resident_id" id="complainant_resident_select" class="form-select" onchange="autoFillComplainant(this)">
            <option value="">-- Choose Resident --</option>
            @foreach($residents as $res)
              <option value="{{ $res->id }}" data-name="{{ $res->full_name }}" data-contact="{{ $res->contact_number }}">{{ $res->full_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="grid-2" style="margin:0;">
          <div class="form-group" style="margin:0;">
            <label class="form-label">Complainant Full Name *</label>
            <input type="text" name="complainant_name" id="complainant_name" class="form-control" required placeholder="Full Name">
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Contact Number</label>
            <input type="text" name="complainant_contact" id="complainant_contact" class="form-control" placeholder="Contact number">
          </div>
        </div>

        <!-- Respondent -->
        <h6 style="margin:10px 0 0 0; font-weight:700; color:var(--primary); border-bottom:1px solid #f3f4f6; padding-bottom:6px;">Respondent Details (Accused)</h6>
        <div class="form-group" style="margin:0;">
          <label class="form-label">Link Registered Resident (Optional)</label>
          <select name="respondent_resident_id" id="respondent_resident_select" class="form-select" onchange="autoFillRespondent(this)">
            <option value="">-- Choose Resident --</option>
            @foreach($residents as $res)
              <option value="{{ $res->id }}" data-name="{{ $res->full_name }}" data-contact="{{ $res->contact_number }}">{{ $res->full_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="grid-2" style="margin:0;">
          <div class="form-group" style="margin:0;">
            <label class="form-label">Respondent Full Name *</label>
            <input type="text" name="respondent_name" id="respondent_name" class="form-control" required placeholder="Full Name">
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Contact Number</label>
            <input type="text" name="respondent_contact" id="respondent_contact" class="form-control" placeholder="Contact number">
          </div>
        </div>

        <!-- Incident Metadata -->
        <h6 style="margin:10px 0 0 0; font-weight:700; color:var(--primary); border-bottom:1px solid #f3f4f6; padding-bottom:6px;">Incident Meta-data</h6>
        <div class="form-group" style="margin:0;">
          <label class="form-label">Nature of Dispute / Offense *</label>
          <input type="text" name="nature_of_complaint" class="form-control" required placeholder="e.g. Slander, Boundary Dispute, Physical Assault, Noise Complaint">
        </div>
        <div class="grid-2" style="margin:0;">
          <div class="form-group" style="margin:0;">
            <label class="form-label">Incident Date &amp; Time</label>
            <input type="datetime-local" name="incident_date" class="form-control">
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Incident Location (Purok/Place)</label>
            <input type="text" name="incident_location" class="form-control" placeholder="e.g. Purok 3, Barangay Pili">
          </div>
        </div>

        <!-- Hearing details -->
        <div id="hearing_schedule_container">
          <h6 style="margin:10px 0 0 0; font-weight:700; color:var(--primary); border-bottom:1px solid #f3f4f6; padding-bottom:6px;">Hearing &amp; Complaint Details</h6>
          <div class="form-group" style="margin-top:10px;">
            <label class="form-label">Hearing Schedule Date &amp; Time *</label>
            <input type="datetime-local" name="schedule_date" id="create_schedule_date" class="form-control" min="{{ date('Y-m-d\TH:i') }}">
          </div>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Complaint Narrative / Case Details *</label>
          <textarea name="complain_details" class="form-control" rows="4" required placeholder="Specify narrative/details of the complaint / incident..."></textarea>
        </div>
      </div>

      <div style="padding:16px 24px; border-top:1px solid #f3f4f6; background:#f9fafb; display:flex; justify-content:flex-end; gap:8px;">
        <button type="button" class="btn btn-outline-secondary" onclick="closeCreateModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> File Case</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Summon Modal -->
<div class="modal" id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px; overflow-y:auto;">
  <div class="modal-content" style="background:#fff; border-radius:16px; max-width:650px; width:100%; overflow:hidden; box-shadow:var(--shadow-md); margin-top: 40px; margin-bottom: 40px;">
    <div class="modal-header" style="padding:16px 24px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:between; align-items:center;">
      <h5 style="margin:0; font-weight:600;"><i class="fas fa-edit" style="color:var(--primary); margin-right:8px;"></i>Update Summon/Blotter Case &amp; Hearings</h5>
      <button type="button" class="btn-close" onclick="closeEditModal()" style="background:none; border:none; cursor:pointer; font-size:18px; color:var(--gray);">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.summons.update') }}">
      @csrf
      <input type="hidden" name="summon_id" id="modal_summon_id">
      
      <div style="padding:24px; display:flex; flex-direction:column; gap:16px; max-height:calc(100vh - 200px); overflow-y:auto;">
        <div class="grid-2" style="margin:0;">
          <div class="form-group" style="margin:0;">
            <label class="form-label">Case Number</label>
            <input type="text" id="modal_case_number" class="form-control" readonly style="background:#f9fafb;">
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Case Status *</label>
            <select name="status" id="modal_status" class="form-select" required>
              <option value="pending">Pending</option>
              <option value="scheduled">Scheduled</option>
              <option value="amicably_settled">Amicably Settled (Resolved)</option>
              <option value="certified_to_file_action">Certified to File Action (Court Referral)</option>
              <option value="dismissed">Dismissed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>

        <!-- Editable Incident Data -->
        <h6 style="margin:5px 0 0 0; font-weight:700; color:var(--primary); border-bottom:1px solid #f3f4f6; padding-bottom:6px;">Update Incident Meta-data</h6>
        <div class="form-group" style="margin:0;">
          <label class="form-label">Nature of Dispute / Offense</label>
          <input type="text" name="nature_of_complaint" id="modal_nature_of_complaint" class="form-control">
        </div>
        <div class="grid-2" style="margin:0;">
          <div class="form-group" style="margin:0;">
            <label class="form-label">Incident Date &amp; Time</label>
            <input type="datetime-local" name="incident_date" id="modal_incident_date" class="form-control">
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Incident Location</label>
            <input type="text" name="incident_location" id="modal_incident_location" class="form-control">
          </div>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Resolution Summary / Hearing Remarks (Latest Session)</label>
          <textarea name="hearing_remarks" id="modal_remarks" class="form-control" rows="3" placeholder="Hearing resolution details, notes, amicable settlement stipulations, etc."></textarea>
        </div>

        <!-- Hearing logs timeline -->
        <div id="modal_hearings_timeline_container" style="background:#f8fafc; border-radius:8px; padding:16px; border:1px solid #e2e8f0;">
          <h6 style="margin:0 0 10px 0; font-weight:700; color:#1e293b;"><i class="fas fa-history" style="margin-right:6px;"></i>Hearing History Logs</h6>
          <ul id="modal_hearings_list" style="padding-left:18px; margin:0; line-height:1.6; font-size:12px; color:#475569;">
            <!-- Rendered dynamically by javascript -->
          </ul>
        </div>

        <!-- Add New Hearing Section (Only relevant for summons) -->
        <div id="modal_new_hearing_section" style="border: 1px solid #bdf0ff; background:#ecfdf5; border-radius:8px; padding:16px;">
          <h6 style="margin:0 0 10px 0; font-weight:700; color:#065f46;"><i class="fas fa-calendar-plus" style="margin-right:6px;"></i>Schedule Subsequent Hearing Session</h6>
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
            <div class="form-group" style="margin:0;">
              <label class="form-label">Next Session Date &amp; Time</label>
              <input type="datetime-local" name="new_schedule_date" class="form-control" min="{{ date('Y-m-d\TH:i') }}">
            </div>
            <div class="form-group" style="margin:0;">
              <label class="form-label">Conducted By</label>
              <input type="text" name="new_conducted_by" class="form-control" placeholder="e.g. Punong Barangay" value="Punong Barangay">
            </div>
          </div>
          <div class="form-group" style="margin-top:10px;">
            <label class="form-label">Preparation Remarks for Next Session</label>
            <input type="text" name="new_hearing_remarks" class="form-control" placeholder="e.g. Schedule for second amicable mediation attempt">
          </div>
        </div>

      </div>

      <div style="padding:16px 24px; border-top:1px solid #f3f4f6; background:#f9fafb; display:flex; justify-content:flex-end; gap:8px;">
        <button type="button" class="btn btn-outline-secondary" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

@section('styles')
<style>
  .grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
  @media(max-width:480px){
    .grid-2 { grid-template-columns: 1fr; }
  }
  .dropdown-print {
    position: relative;
    display: inline-block;
  }
  .dropdown-print-content a:hover {
    background-color: #f1f5f9;
  }
</style>
@endsection

<script>
  // Close print dropdowns when clicking outside
  window.addEventListener('click', function(e) {
    if (!e.target.matches('.dropdown-toggle-btn') && !e.target.closest('.dropdown-print')) {
      const dropdowns = document.getElementsByClassName('dropdown-print-content');
      for (let i = 0; i < dropdowns.length; i++) {
        dropdowns[i].style.display = 'none';
      }
    }
  });

  function togglePrintDropdown(btn) {
    const content = btn.nextElementSibling;
    const isShowing = content.style.display === 'block';
    // Close other dropdowns first
    const dropdowns = document.getElementsByClassName('dropdown-print-content');
    for (let i = 0; i < dropdowns.length; i++) {
      dropdowns[i].style.display = 'none';
    }
    content.style.display = isShowing ? 'none' : 'block';
  }

  function autoFillComplainant(select) {
    const opt = select.options[select.selectedIndex];
    if (opt.value) {
      document.getElementById('complainant_name').value = opt.getAttribute('data-name');
      document.getElementById('complainant_contact').value = opt.getAttribute('data-contact') || '';
    } else {
      document.getElementById('complainant_name').value = '';
      document.getElementById('complainant_contact').value = '';
    }
  }

  function autoFillRespondent(select) {
    const opt = select.options[select.selectedIndex];
    if (opt.value) {
      document.getElementById('respondent_name').value = opt.getAttribute('data-name');
      document.getElementById('respondent_contact').value = opt.getAttribute('data-contact') || '';
    } else {
      document.getElementById('respondent_name').value = '';
      document.getElementById('respondent_contact').value = '';
    }
  }

  function toggleScheduleRequirement(val) {
    const container = document.getElementById('hearing_schedule_container');
    const input = document.getElementById('create_schedule_date');
    if (val === 'blotter') {
      container.style.display = 'none';
      input.removeAttribute('required');
    } else {
      container.style.display = 'block';
      input.setAttribute('required', 'required');
    }
  }

  function openCreateModal() {
    document.getElementById('createModal').style.display = 'flex';
    toggleScheduleRequirement(document.getElementById('create_case_type').value);
  }
  function closeCreateModal() {
    document.getElementById('createModal').style.display = 'none';
  }

  function openEditModal(summon) {
    document.getElementById('modal_summon_id').value = summon.id;
    document.getElementById('modal_case_number').value = summon.case_number;
    document.getElementById('modal_status').value = summon.status;
    document.getElementById('modal_remarks').value = summon.hearing_remarks || '';
    
    document.getElementById('modal_nature_of_complaint').value = summon.nature_of_complaint || '';
    
    // Format incident_date (YYYY-MM-DDTHH:MM)
    if(summon.incident_date) {
      const d = new Date(summon.incident_date);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      const hours = String(d.getHours()).padStart(2, '0');
      const minutes = String(d.getMinutes()).padStart(2, '0');
      document.getElementById('modal_incident_date').value = `${year}-${month}-${day}T${hours}:${minutes}`;
    } else {
      document.getElementById('modal_incident_date').value = '';
    }

    document.getElementById('modal_incident_location').value = summon.incident_location || '';

    // Handle schedule block and history
    const isSummon = (summon.case_type === 'summon');
    const newHearingSection = document.getElementById('modal_new_hearing_section');
    const timelineContainer = document.getElementById('modal_hearings_timeline_container');
    const hearingsList = document.getElementById('modal_hearings_list');
    
    hearingsList.innerHTML = '';

    if (isSummon) {
      newHearingSection.style.display = 'block';
      
      if (summon.hearings && summon.hearings.length > 0) {
        timelineContainer.style.display = 'block';
        summon.hearings.forEach(h => {
          const d = new Date(h.schedule_date);
          const formattedDate = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
          const li = document.createElement('li');
          li.style.marginBottom = '8px';
          li.innerHTML = `<strong>Session #${h.hearing_number}</strong> Scheduled: <code>${formattedDate}</code> <br> <span style="color:#64748b;">Conducted by: ${h.conducted_by || 'Punong Barangay'} - Remarks: ${h.remarks || 'No notes'}</span>`;
          hearingsList.appendChild(li);
        });
      } else {
        timelineContainer.style.display = 'none';
      }
    } else {
      newHearingSection.style.display = 'none';
      timelineContainer.style.display = 'none';
    }

    document.getElementById('editModal').style.display = 'flex';
  }

  function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
  }
</script>
@endsection
