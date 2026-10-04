@extends('layouts.app')

@section('title', 'My Summons / Blotters')

@section('styles')
<style>
@media (max-width: 640px) {
  .summons-table-wrap { display: none !important; }
  .summon-cards { display: flex !important; flex-direction: column; gap: 12px; padding: 12px; }
}
@media (min-width: 641px) {
  .summon-cards { display: none !important; }
}
.native-mobile-app .summons-table-wrap { display: none !important; }
.native-mobile-app .summon-cards { display: flex !important; flex-direction: column; gap: 12px; padding: 12px; }
</style>
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-gavel" style="color:var(--primary);margin-right:8px;"></i>My Registered Case Summons &amp; Blotters</h5>
  </div>

  {{-- Desktop Table --}}
  <div class="summons-table-wrap table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Case Number</th>
          <th>Type</th>
          <th>Role</th>
          <th>Opposing Party</th>
          <th>Incident Details</th>
          <th>Next Hearing Schedule</th>
          <th>Status</th>
          <th>Hearing History / Remarks</th>
        </tr>
      </thead>
      <tbody>
        @if ($summons->isEmpty())
          <tr>
            <td colspan="8" class="text-center" style="padding:40px;">
              <div style="font-size:40px;margin-bottom:10px;">🕊️</div>
              <p class="text-muted">You do not have any registered summons or blotters active.</p>
            </td>
          </tr>
        @else
          @foreach ($summons as $s)
            @php
              $isComplainant = (Auth::user()->resident->id == $s->complainant_resident_id);
            @endphp
            <tr>
              <td><strong style="color:var(--primary); font-size:12px;">{{ $s->case_number }}</strong></td>
              <td>
                <span class="badge bg-{{ $s->case_type === 'summon' ? 'primary' : 'secondary' }}" style="font-size:10px;">
                  {{ ucfirst($s->case_type) }}
                </span>
              </td>
              <td>
                <span class="badge bg-{{ $isComplainant ? 'primary' : 'danger' }}">
                  {{ $isComplainant ? 'Complainant (You)' : 'Respondent' }}
                </span>
              </td>
              <td>
                <strong>{{ $isComplainant ? $s->respondent_name : $s->complainant_name }}</strong>
                <div style="font-size:11px; color:var(--gray);">
                  Contact: {{ $isComplainant ? ($s->respondent_contact ?? 'N/A') : ($s->complainant_contact ?? 'N/A') }}
                </div>
              </td>
              <td>
                <div style="font-weight:600; font-size:12px;">{{ $s->nature_of_complaint ?? 'Dispute / Incident' }}</div>
                @if($s->incident_location)
                  <div style="font-size:11px; color:var(--gray);"><i class="fas fa-map-marker-alt" style="margin-right:4px;"></i> {{ $s->incident_location }}</div>
                @endif
                @if($s->incident_date)
                  <div style="font-size:11px; color:var(--gray);"><i class="fas fa-calendar-alt" style="margin-right:4px;"></i> {{ \Carbon\Carbon::parse($s->incident_date)->format('M d, Y') }}</div>
                @endif
              </td>
              <td>
                @if($s->case_type === 'blotter')
                  <span class="text-muted" style="font-style:italic; font-size:11px;">Record Only (No Hearing)</span>
                @elseif($s->schedule_date)
                  <div style="font-weight:600;">{{ \Carbon\Carbon::parse($s->schedule_date)->format('M d, Y') }}</div>
                  <div style="font-size:11px; color:var(--gray);">{{ \Carbon\Carbon::parse($s->schedule_date)->format('h:i A') }}</div>
                @else
                  <span class="text-muted" style="font-style:italic;">Not Scheduled</span>
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
                <span class="badge bg-{{ $badgeColor }}" style="font-size: 10px;">
                  {{ ucwords(str_replace('_', ' ', $s->status)) }}
                </span>
              </td>
              <td>
                <div style="font-size:12px; max-width:250px;">
                  @if($s->case_type === 'summon' && $s->hearings->count() > 0)
                    <div style="font-weight:600; color:var(--primary); margin-bottom:4px;">Hearings Timeline ({{ $s->hearings->count() }})</div>
                    <ul style="padding-left:14px; margin:0; line-height:1.4; font-size:11px; color:#555;">
                      @foreach($s->hearings as $h)
                        <li>
                          <strong>Session #{{ $h->hearing_number }}</strong> ({{ $h->schedule_date->format('M d, Y') }}):
                          <span class="text-muted">{{ $h->remarks ?? 'No notes' }}</span>
                        </li>
                      @endforeach
                    </ul>
                  @else
                    @if($s->hearing_remarks)
                      <strong>Remarks:</strong> {{ $s->hearing_remarks }}
                    @else
                      <span class="text-muted" style="font-style:italic;">No remarks yet</span>
                    @endif
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
  <div class="summon-cards mobile-card-list">
    @if ($summons->isEmpty())
      <div style="text-align:center;padding:40px 16px;">
        <div style="font-size:40px;margin-bottom:10px;">🕊️</div>
        <p class="text-muted">You do not have any registered summons or blotters active.</p>
      </div>
    @else
      @foreach ($summons as $s)
        @php
          $isComplainant = (Auth::user()->resident->id == $s->complainant_resident_id);
          $badgeColor = 'warning';
          if ($s->status === 'amicably_settled') $badgeColor = 'success';
          elseif ($s->status === 'certified_to_file_action') $badgeColor = 'danger';
          elseif ($s->status === 'scheduled') $badgeColor = 'info';
          elseif ($s->status === 'dismissed' || $s->status === 'cancelled') $badgeColor = 'secondary';
        @endphp
        <div class="data-mobile-card">
          <div class="data-mobile-card-header">
            <div>
              <div class="data-mobile-card-title">{{ $s->case_number }}</div>
              <div class="data-mobile-card-subtitle">
                <span class="badge bg-{{ $s->case_type === 'summon' ? 'primary' : 'secondary' }}" style="font-size:10px;">
                  {{ ucfirst($s->case_type) }}
                </span>
                <span class="badge bg-{{ $isComplainant ? 'primary' : 'danger' }}" style="margin-left:4px;">
                  {{ $isComplainant ? 'Complainant (You)' : 'Respondent' }}
                </span>
              </div>
            </div>
            <span class="badge bg-{{ $badgeColor }}">
              {{ ucwords(str_replace('_', ' ', $s->status)) }}
            </span>
          </div>

          <div class="data-mobile-card-grid">
            <div class="data-mobile-card-item">
              <label>Opposing Party</label>
              <div style="font-weight:600;">{{ $isComplainant ? $s->respondent_name : $s->complainant_name }}</div>
              <small class="text-muted">{{ $isComplainant ? ($s->respondent_contact ?? 'No contact') : ($s->complainant_contact ?? 'No contact') }}</small>
            </div>
            <div class="data-mobile-card-item">
              <label>Next Hearing</label>
              <div>
                @if($s->case_type === 'blotter')
                  <span class="text-muted">Record Only</span>
                @elseif($s->schedule_date)
                  <strong>{{ \Carbon\Carbon::parse($s->schedule_date)->format('M d, Y') }}</strong>
                  <small class="text-muted" style="display:block;">{{ \Carbon\Carbon::parse($s->schedule_date)->format('h:i A') }}</small>
                @else
                  <span class="text-muted">Not Scheduled</span>
                @endif
              </div>
            </div>
            <div class="data-mobile-card-item" style="grid-column:span 2;">
              <label>Incident Details</label>
              <div style="font-size:12.5px;">{{ $s->nature_of_complaint ?? 'Dispute / Incident' }}</div>
              @if($s->incident_location)
                <small class="text-muted"><i class="fas fa-map-marker-alt"></i> {{ $s->incident_location }}</small>
              @endif
            </div>
            @if($s->case_type === 'summon' && $s->hearings->count() > 0)
              <div class="data-mobile-card-item" style="grid-column:span 2;">
                <label>Hearing History ({{ $s->hearings->count() }})</label>
                <div style="font-size:12px; color:#475569;">
                  Latest: Session #{{ $s->hearings->last()->hearing_number }} ({{ $s->hearings->last()->schedule_date->format('M d, Y') }})
                </div>
              </div>
            @elseif($s->hearing_remarks)
              <div class="data-mobile-card-item" style="grid-column:span 2;">
                <label>Remarks</label>
                <div style="font-size:12px; color:#475569;">{{ $s->hearing_remarks }}</div>
              </div>
            @endif
          </div>
        </div>
      @endforeach
    @endif
  </div>
  @if ($summons->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $summons->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>
@endsection
