@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-history" style="color:var(--primary);margin-right:8px;"></i>
      System Audit Logs <span style="font-size:13px;font-weight:400;color:#6b7280;">({{ $logs->total() }})</span></h5>
  </div>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>User</th>
          <th>Action</th>
          <th>Module</th>
          <th>Description</th>
          <th>IP Address</th>
          <th>Location</th>
          <th>Timestamp</th>
        </tr>
      </thead>
      <tbody>
        @if ($logs->isEmpty())
          <tr><td colspan="7" class="text-center text-muted" style="padding:40px;">No system audit logs found.</td></tr>
        @else
          @foreach ($logs as $log)
            <tr>
              <td style="font-weight:600;">{{ $log->user ? $log->user->username : 'System' }}</td>
              <td><code>{{ $log->action }}</code></td>
              <td><span class="badge bg-secondary">{{ $log->module ?? 'General' }}</span></td>
              <td>{{ $log->description }}</td>
              <td><small>{{ $log->ip_address }}</small></td>
              <td>
                @if ($log->location)
                  @php
                    $parts = explode(',', $log->location, 2);
                    $isCoords = count($parts) === 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]));
                  @endphp
                  @if ($isCoords)
                    <a href="https://maps.google.com/?q={{ urlencode(trim($parts[0])) }},{{ urlencode(trim($parts[1])) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       title="View location on Google Maps ({{ $log->location }})"
                       style="display:inline-flex;align-items:center;gap:4px;color:#b91c1c;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;">
                      <i class="fas fa-location-dot"></i> View on Map
                    </a>
                  @else
                    <small>{{ $log->location }}</small>
                  @endif
                @else
                  <span style="color:#9ca3af;">—</span>
                @endif
              </td>
              <td><small>{{ $log->created_at->format('M d, Y h:i A') }}</small></td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
  @if ($logs->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $logs->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>
@endsection
