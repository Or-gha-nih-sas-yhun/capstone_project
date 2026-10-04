@extends('layouts.app')

@section('title', 'Barangay Pili')

@section('content')
<div style="max-width:700px;margin:0 auto;">
  <section style="background:#b91c1c;color:#fff;padding:24px;border-radius:0 0 18px 18px;margin:-24px -24px 22px;">
    <div style="font-size:13px;opacity:.85;">Barangay Pili Resident Portal</div>
    <h2 style="margin:6px 0;font-size:24px;">Hello, {{ $resident->first_name }}!</h2>
    <p style="margin:0;font-size:14px;opacity:.95;">Request and monitor your barangay documents easily.</p>
  </section>

  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:22px;">
    <a class="card" href="{{ route('resident.request') }}" style="text-decoration:none;text-align:center;padding:16px 8px;color:#1f2937;">
      <i class="fas fa-file-circle-plus" style="font-size:22px;color:#b91c1c;"></i><div style="font-size:12px;font-weight:700;margin-top:8px;">Request Document</div>
    </a>
    <a class="card" href="{{ route('resident.my_requests') }}" style="text-decoration:none;text-align:center;padding:16px 8px;color:#1f2937;">
      <i class="fas fa-file-lines" style="font-size:22px;color:#b91c1c;"></i><div style="font-size:12px;font-weight:700;margin-top:8px;">My Requests</div>
    </a>
    <a class="card" href="{{ route('resident.bulletins') }}" style="text-decoration:none;text-align:center;padding:16px 8px;color:#1f2937;">
      <i class="fas fa-bell" style="font-size:22px;color:#b91c1c;"></i><div style="font-size:12px;font-weight:700;margin-top:8px;">Notifications</div>
    </a>
  </div>

  <h5 style="margin:0 0 12px;">Barangay Services</h5>
  <div class="card" style="margin-bottom:22px;">
    @foreach ([
      ['Barangay Clearance', 'Request a barangay clearance.', 'resident.request', 'fa-file-shield'],
      ['Certificate of Indigency', 'Request a certificate of indigency.', 'resident.request', 'fa-hand-holding-heart'],
      ['Certificate of Residency', 'Request proof of residency.', 'resident.request', 'fa-house-user'],
      ['Business Clearance', 'Request local business clearance.', 'resident.request', 'fa-briefcase'],
      ['Blotter', 'View barangay incident information.', 'resident.summons', 'fa-clipboard-list'],
      ['Summons', 'View mediation or hearing summons.', 'resident.summons', 'fa-gavel'],
      ['Borrow Equipment', 'Request available equipment or facilities.', 'resident.borrows', 'fa-people-carry-box']
    ] as [$name, $description, $route, $icon])
      <a href="{{ route($route) }}" style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-bottom:1px solid #e5e7eb;text-decoration:none;color:#1f2937;">
        <i class="fas {{ $icon }}" style="width:22px;color:#b91c1c;"></i>
        <span style="flex:1;"><strong style="display:block;font-size:14px;">{{ $name }}</strong><small style="color:#6b7280;">{{ $description }}</small></span>
        <i class="fas fa-chevron-right" style="color:#9ca3af;"></i>
      </a>
    @endforeach
  </div>

  <h5 style="margin:0 0 12px;">Recent Requests</h5>
  <div class="card">
    @forelse($recentRequests as $request)
      <a href="{{ route('resident.my_requests') }}" style="display:flex;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid #e5e7eb;text-decoration:none;color:#1f2937;">
        <span><strong style="display:block;font-size:14px;">{{ $request->certificate->name }}</strong><small style="color:#6b7280;">{{ $request->tracking_number }}</small></span>
        <span class="badge bg-{{ $request->status === 'rejected' ? 'danger' : ($request->status === 'pending' ? 'warning' : 'success') }}">{{ ucfirst($request->status) }}</span>
      </a>
    @empty
      <div style="padding:20px 16px;color:#6b7280;font-size:14px;">You have no requests yet.</div>
    @endforelse
  </div>
</div>
@endsection
