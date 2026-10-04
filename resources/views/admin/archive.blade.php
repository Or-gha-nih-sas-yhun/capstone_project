@extends('layouts.app')

@section('title', 'System Archive')

@section('content')
<div style="display:grid; grid-template-columns: 1fr; gap:24px;">

  <!-- Archived Residents -->
  <div class="card">
    <div class="card-header">
      <h5><i class="fas fa-users" style="color:var(--primary);margin-right:8px;"></i>Archived Residents ({{ count($residents) }})</h5>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Gender</th>
            <th>Contact</th>
            <th>Archived Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @if ($residents->isEmpty())
            <tr><td colspan="5" class="text-center text-muted" style="padding:20px;">No archived residents.</td></tr>
          @else
            @foreach ($residents as $r)
              <tr>
                <td style="font-weight:600;">{{ $r->last_name }}, {{ $r->first_name }}</td>
                <td>{{ $r->gender }}</td>
                <td>{{ $r->contact_number ?? '—' }}</td>
                <td><small>{{ \Carbon\Carbon::parse($r->archived_at)->format('M d, Y h:i A') }}</small></td>
                <td>
                  <a href="{{ route('admin.archive.restore', ['type' => 'resident', 'id' => $r->id]) }}" class="btn btn-success btn-sm" onclick="return confirm('Restore this resident?')">
                    <i class="fas fa-undo"></i> Restore
                  </a>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>

  <!-- Archived Certificates -->
  <div class="card">
    <div class="card-header">
      <h5><i class="fas fa-certificate" style="color:var(--primary);margin-right:8px;"></i>Archived Certificate Types ({{ count($certificates) }})</h5>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Certificate Name</th>
            <th>Category</th>
            <th>Fee</th>
            <th>Archived Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @if ($certificates->isEmpty())
            <tr><td colspan="5" class="text-center text-muted" style="padding:20px;">No archived certificate types.</td></tr>
          @else
            @foreach ($certificates as $c)
              <tr>
                <td style="font-weight:600;">{{ $c->name }}</td>
                <td><span class="badge bg-secondary">{{ $c->category }}</span></td>
                <td>₱{{ number_format($c->fee, 2) }}</td>
                <td><small>{{ \Carbon\Carbon::parse($c->archived_at)->format('M d, Y h:i A') }}</small></td>
                <td>
                  <a href="{{ route('admin.archive.restore', ['type' => 'certificate', 'id' => $c->id]) }}" class="btn btn-success btn-sm" onclick="return confirm('Restore this certificate type?')">
                    <i class="fas fa-undo"></i> Restore
                  </a>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>

  <!-- Archived Requests -->
  <div class="card">
    <div class="card-header">
      <h5><i class="fas fa-file-alt" style="color:var(--primary);margin-right:8px;"></i>Archived Requests ({{ count($requests) }})</h5>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Tracking #</th>
            <th>Resident</th>
            <th>Document</th>
            <th>Archived Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @if ($requests->isEmpty())
            <tr><td colspan="5" class="text-center text-muted" style="padding:20px;">No archived requests.</td></tr>
          @else
            @foreach ($requests as $r)
              <tr>
                <td><code>{{ $r->tracking_number }}</code></td>
                <td>{{ $r->resident->full_name }}</td>
                <td>{{ $r->certificate->name }}</td>
                <td><small>{{ \Carbon\Carbon::parse($r->archived_at)->format('M d, Y h:i A') }}</small></td>
                <td>
                  <a href="{{ route('admin.archive.restore', ['type' => 'request', 'id' => $r->id]) }}" class="btn btn-success btn-sm" onclick="return confirm('Restore this request?')">
                    <i class="fas fa-undo"></i> Restore
                  </a>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>

  <!-- Archived Blotters / Summons -->
  <div class="card">
    <div class="card-header">
      <h5><i class="fas fa-gavel" style="color:var(--primary);margin-right:8px;"></i>Archived Blotters / Summons ({{ count($summons) }})</h5>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Case Number</th>
            <th>Type</th>
            <th>Complainant</th>
            <th>Respondent</th>
            <th>Archived Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @if ($summons->isEmpty())
            <tr><td colspan="6" class="text-center text-muted" style="padding:20px;">No archived summons or blotters.</td></tr>
          @else
            @foreach ($summons as $s)
              <tr>
                <td><strong style="color:var(--primary);">{{ $s->case_number }}</strong></td>
                <td><span class="badge bg-info text-dark" style="font-size: 11px;">{{ ucfirst($s->case_type) }}</span></td>
                <td>{{ $s->complainant_name }}</td>
                <td>{{ $s->respondent_name }}</td>
                <td><small>{{ \Carbon\Carbon::parse($s->archived_at)->format('M d, Y h:i A') }}</small></td>
                <td>
                  <a href="{{ route('admin.archive.restore', ['type' => 'summon', 'id' => $s->id]) }}" class="btn btn-success btn-sm" onclick="return confirm('Restore this case?')">
                    <i class="fas fa-undo"></i> Restore
                  </a>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>

  <!-- Archived Officials -->
  <div class="card">
    <div class="card-header">
      <h5><i class="fas fa-user-tie" style="color:var(--primary);margin-right:8px;"></i>Archived Barangay Officials ({{ count($officials) }})</h5>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Position</th>
            <th>Term</th>
            <th>Archived Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @if ($officials->isEmpty())
            <tr><td colspan="5" class="text-center text-muted" style="padding:20px;">No archived officials.</td></tr>
          @else
            @foreach ($officials as $o)
              <tr>
                <td style="font-weight:600;">{{ $o->name }}</td>
                <td><span class="badge bg-secondary">{{ $o->position }}</span></td>
                <td>
                  <small>
                    {{ $o->term_start ? \Carbon\Carbon::parse($o->term_start)->format('M d, Y') : '—' }}
                    –
                    {{ $o->term_end ? \Carbon\Carbon::parse($o->term_end)->format('M d, Y') : '—' }}
                  </small>
                </td>
                <td><small>{{ \Carbon\Carbon::parse($o->archived_at)->format('M d, Y h:i A') }}</small></td>
                <td>
                  <a href="{{ route('admin.archive.restore', ['type' => 'official', 'id' => $o->id]) }}" class="btn btn-success btn-sm" onclick="return confirm('Restore this official?')">
                    <i class="fas fa-undo"></i> Restore
                  </a>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>

  <!-- Archived Users -->
  <div class="card">
    <div class="card-header">
      <h5><i class="fas fa-user-shield" style="color:var(--primary);margin-right:8px;"></i>Archived Admin Accounts ({{ count($users) }})</h5>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Archived Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @if ($users->isEmpty())
            <tr><td colspan="5" class="text-center text-muted" style="padding:20px;">No archived users.</td></tr>
          @else
            @foreach ($users as $u)
              <tr>
                <td style="font-weight:600;">{{ $u->username }}</td>
                <td>{{ $u->email }}</td>
                <td><span class="badge bg-secondary">{{ ucfirst($u->role) }}</span></td>
                <td><small>{{ \Carbon\Carbon::parse($u->archived_at)->format('M d, Y h:i A') }}</small></td>
                <td>
                  <a href="{{ route('admin.archive.restore', ['type' => 'user', 'id' => $u->id]) }}" class="btn btn-success btn-sm" onclick="return confirm('Restore this account?')">
                    <i class="fas fa-undo"></i> Restore
                  </a>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>

  <!-- Archived Borrow Records -->
  <div class="card">
    <div class="card-header">
      <h5><i class="fas fa-hand-holding" style="color:var(--primary);margin-right:8px;"></i>Archived Borrow Records ({{ count($borrows) }})</h5>
      <small style="color:var(--gray); font-size:12px;"><i class="fas fa-info-circle"></i> Preserved for administrative history, reports, and accountability.</small>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Resident</th>
            <th>Items Borrowed</th>
            <th>Borrow Date</th>
            <th>Return Date</th>
            <th>Status</th>
            <th>Archived Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @if ($borrows->isEmpty())
            <tr><td colspan="7" class="text-center text-muted" style="padding:20px;">No archived borrow records.</td></tr>
          @else
            @foreach ($borrows as $b)
              <tr>
                <td>
                  <div style="font-weight:600;">{{ $b->resident ? $b->resident->full_name : '—' }}</div>
                  <div style="font-size:11px; color:var(--gray);">Borrow #{{ $b->id }}</div>
                </td>
                <td>
                  <div style="font-size:12px;">
                    @if($b->tent_quantity > 0)
                      <span style="display:inline-block; margin-right:6px;"><i class="fas fa-campground" style="color:#d97706;"></i> {{ $b->tent_quantity }} Tents</span>
                    @endif
                    @if($b->chair_quantity > 0)
                      <span style="display:inline-block; margin-right:6px;"><i class="fas fa-chair" style="color:#2563eb;"></i> {{ $b->chair_quantity }} Chairs</span>
                    @endif
                    @if($b->table_quantity > 0)
                      <span style="display:inline-block; margin-right:6px;"><i class="fas fa-table" style="color:#10b981;"></i> {{ $b->table_quantity }} Tables</span>
                    @endif
                  </div>
                </td>
                <td><small>{{ \Carbon\Carbon::parse($b->borrow_date)->format('M d, Y') }}</small></td>
                <td><small>{{ \Carbon\Carbon::parse($b->return_date)->format('M d, Y') }}</small></td>
                <td>
                  <span class="badge bg-{{ $b->status === 'returned' ? 'success' : ($b->status === 'rejected' ? 'danger' : 'secondary') }}">
                    {{ ucfirst($b->status) }}
                  </span>
                </td>
                <td><small>{{ \Carbon\Carbon::parse($b->archived_at)->format('M d, Y h:i A') }}</small></td>
                <td>
                  <a href="{{ route('admin.archive.restore', ['type' => 'borrow', 'id' => $b->id]) }}"
                     class="btn btn-success btn-sm"
                     onclick="return confirm('Restore this borrow record to the active list?')">
                    <i class="fas fa-undo"></i> Restore
                  </a>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection

