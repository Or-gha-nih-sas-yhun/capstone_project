@extends('layouts.app')

@section('title', 'Barangay Announcements')

@section('content')
<div style="margin-bottom:24px;">
  <h2 style="font-size:24px; font-weight:800; color:var(--dark); margin:0;">Barangay Public Bulletin</h2>
  <p class="text-muted" style="margin:4px 0 0;">Stay updated with the latest events, advisories, and activities in Barangay Pili.</p>
</div>

<!-- Announcements list -->
<div style="display:flex; flex-direction:column; gap:20px;">
  @if($bulletins->isEmpty())
    <div class="card" style="padding:40px; text-align:center;">
      <div style="font-size:40px; margin-bottom:12px; color:var(--primary);"><i class="fas fa-bullhorn"></i></div>
      <p class="text-muted" style="margin:0;">No announcements posted at this time.</p>
    </div>
  @else
    @foreach($bulletins as $b)
      <div class="card bulletin-card" style="margin:0; padding:20px; border-left: 5px solid {{ $b->is_pinned ? '#eab308' : ($b->category === 'Advisory' ? 'var(--primary)' : ($b->category === 'Event' ? '#2563eb' : '#0d9488')) }}; border-radius:14px; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
          <h3 style="font-size:17px; font-weight:800; color:#0f172a; margin:0; flex:1; min-width:200px; line-height:1.35;">{{ $b->title }}</h3>
          
          <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
            @if($b->is_pinned)
              <span style="display:inline-flex; align-items:center; gap:4px; background:#fef9c3; color:#a16207; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px; border:1px solid #fef08a;">
                <i class="fas fa-thumbtack"></i> PINNED
              </span>
            @endif
            <span class="badge bg-{{ $b->category === 'Advisory' ? 'danger' : ($b->category === 'Event' ? 'primary' : ($b->category === 'Meeting' ? 'info' : 'secondary')) }}" style="font-size:10.5px;">
              {{ $b->category }}
            </span>
          </div>
        </div>
        
        <div style="font-size:12px; color:#64748b; display:flex; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid #f1f5f9;">
          <span><i class="far fa-calendar-alt" style="margin-right:4px;"></i> {{ \Carbon\Carbon::parse($b->published_at)->format('M d, Y · h:i A') }}</span>
          <span><i class="far fa-user" style="margin-right:4px;"></i> {{ $b->creator->username ?? 'Official' }}</span>
        </div>

        @if($b->image_path && file_exists(public_path('assets/uploads/' . $b->image_path)))
          <img src="{{ asset('assets/uploads/' . $b->image_path) }}" alt="Image for {{ $b->title }}" style="display:block;width:100%;max-height:320px;object-fit:cover;border-radius:10px;margin:0 0 14px;">
        @endif

        <div style="font-size:14px; line-height:1.65; color:#334155; white-space:pre-line;">
          {{ $b->content }}
        </div>
      </div>
    @endforeach
  @endif
</div>

@if ($bulletins->hasPages())
  <div style="margin-top:24px; display:flex; justify-content:center;">
    {{ $bulletins->links('vendor.pagination.simple-default') }}
  </div>
@endif
@endsection
