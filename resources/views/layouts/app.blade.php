<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>@yield('title', 'Dashboard') — {{ config('app.name', 'Barangay Pili') }} System</title>
  <meta name="description" content="Barangay Pili Clearance and Certificate Processing System">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/pili_logo.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  @yield('styles')

  @if(str_contains(request()->header('User-Agent', ''), 'BrgyPiliApp'))
  <style>
    /* Native Mobile APK Specific Styles */
    body.native-mobile-app {
      -webkit-touch-callout: none;
      -webkit-user-select: none;
      user-select: none;
      overscroll-behavior-y: none;
    }

    body.native-mobile-app input,
    body.native-mobile-app textarea,
    body.native-mobile-app select {
      -webkit-user-select: text;
      user-select: text;
    }

    .native-mobile-app .topbar {
      padding-top: env(safe-area-inset-top, 0px);
    }

    /* The APK is a resident portal, not the desktop system. */
    .native-mobile-app .sidebar,
    .native-mobile-app .sidebar-overlay,
    .native-mobile-app #sidebar-toggle {
      display: none !important;
    }

    .native-mobile-app .main-content {
      margin-left: 0 !important;
      width: 100% !important;
    }

    .native-mobile-app .page-content {
      padding-bottom: calc(82px + env(safe-area-inset-bottom, 0px));
    }
  </style>
  @endif
</head>
<body class="{{ str_contains(request()->header('User-Agent', ''), 'BrgyPiliApp') ? 'native-mobile-app' : '' }}">
  <!-- Mobile sidebar overlay -->
  <div class="sidebar-overlay" id="sidebar-overlay"></div>

  <div class="wrapper">
    <!-- ── Sidebar ─────────────────────────────────────────── -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-brand">
        <div class="brand-logo" style="background: transparent;">
          <img src="{{ asset('assets/images/pili_logo.png') }}" alt="Pili Logo" style="width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
        </div>
        <div class="brand-text">
          <strong>Brgy. Pili</strong>
          <small>Clearance System</small>
        </div>
      </div>

      <nav class="sidebar-nav">
        @if(in_array(Auth::user()->role, ['admin', 'staff']))
          <span class="nav-section-label">Main</span>
          <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> Dashboard
          </a>
          <span class="nav-section-label">Management</span>
          <a href="{{ route('admin.requests') }}" class="nav-link {{ Route::is('admin.requests') ? 'active' : '' }}">
            <i class="fas fa-file-alt"></i> Requests
          </a>
          <a href="{{ route('admin.payments') }}" class="nav-link {{ Route::is('admin.payments') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave"></i> Payments
          </a>
          <a href="{{ route('admin.summons') }}" class="nav-link {{ Route::is('admin.summons') ? 'active' : '' }}">
            <i class="fas fa-gavel"></i> Summons / Blotters
          </a>
          <a href="{{ route('admin.bulletins') }}" class="nav-link {{ Route::is('admin.bulletins') ? 'active' : '' }}">
            <i class="fas fa-bullhorn"></i> Bulletins / Notices
          </a>
          <a href="{{ route('admin.borrows') }}" class="nav-link {{ Route::is('admin.borrows') ? 'active' : '' }}">
            <i class="fas fa-hand-holding"></i> Borrow Requests
          </a>
          <a href="{{ route('admin.residents') }}" class="nav-link {{ Route::is('admin.residents') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Residents
          </a>
          <a href="{{ route('admin.certificates') }}" class="nav-link {{ Route::is('admin.certificates') ? 'active' : '' }}">
            <i class="fas fa-certificate"></i> Certificate Types
          </a>
          <a href="{{ route('admin.officials') }}" class="nav-link {{ Route::is('admin.officials') ? 'active' : '' }}">
            <i class="fas fa-user-tie"></i> Officials
          </a>
          @if(Auth::user()->role === 'admin')
            <span class="nav-section-label">Administration</span>
            <a href="{{ route('admin.users') }}" class="nav-link {{ Route::is('admin.users') ? 'active' : '' }}">
              <i class="fas fa-user-shield"></i> User Management
            </a>
            <a href="{{ route('admin.archive') }}" class="nav-link {{ Route::is('admin.archive') ? 'active' : '' }}">
              <i class="fas fa-box-archive"></i> Archive
            </a>
            <a href="{{ route('admin.reports') }}" class="nav-link {{ Route::is('admin.reports') ? 'active' : '' }}">
              <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="{{ route('admin.activity_logs') }}" class="nav-link {{ Route::is('admin.activity_logs') ? 'active' : '' }}">
              <i class="fas fa-history"></i> Activity Logs
            </a>
          @endif
        @else
          <span class="nav-section-label">My Account</span>
          <a href="{{ route('resident.request') }}" class="nav-link {{ Route::is('resident.request') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i> New Request
          </a>
          <a href="{{ route('resident.my_requests') }}" class="nav-link {{ Route::is('resident.my_requests') ? 'active' : '' }}">
            <i class="fas fa-list"></i> My Requests
          </a>
          <a href="{{ route('resident.borrows') }}" class="nav-link {{ Route::is('resident.borrows') ? 'active' : '' }}">
            <i class="fas fa-hand-holding"></i> Borrow Equipment
          </a>
          <a href="{{ route('resident.summons') }}" class="nav-link {{ Route::is('resident.summons') ? 'active' : '' }}">
            <i class="fas fa-gavel"></i> My Summons
          </a>
          <a href="{{ route('resident.bulletins') }}" class="nav-link {{ Route::is('resident.bulletins') ? 'active' : '' }}">
            <i class="fas fa-bullhorn"></i> Announcements
          </a>
          <a href="{{ route('resident.profile') }}" class="nav-link {{ Route::is('resident.profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i> My Profile
          </a>
        @endif

        <span class="nav-section-label">General</span>
        <form method="POST" action="{{ route('logout') }}" id="logout-form" style="margin:0;">
          @csrf
        </form>
        <button type="button" class="nav-link" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;"
          onclick="document.getElementById('logoutModal').style.display='flex'">
          <i class="fas fa-sign-out-alt"></i> Logout
        </button>
      </nav>

      <div class="sidebar-footer">
        <a class="user-info" href="{{ in_array(Auth::user()->role, ['admin', 'staff']) ? route('admin.profile') : route('resident.profile') }}" style="text-decoration:none;">
          <div class="user-avatar">
            @php
              $photo = Auth::user()->role === 'resident' 
                ? (Auth::user()->resident->photo ?? '') 
                : Auth::user()->photo;
            @endphp
            @if($photo)
              <img src="{{ asset('assets/uploads/' . $photo) }}" alt="Profile photo">
            @else
              {{ strtoupper(substr(Auth::user()->resident ? Auth::user()->resident->first_name : Auth::user()->username, 0, 1)) }}
            @endif
          </div>
          <div>
            <div class="user-name">
              {{ Auth::user()->resident ? Auth::user()->resident->full_name : Auth::user()->username }}
            </div>
            <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
          </div>
        </a>
      </div>
    </aside>

    <!-- ── Main ───────────────────────────────────────────── -->
    <div class="main-content">
      <!-- Topbar -->
      <header class="topbar no-print">
        <div class="topbar-left">
          <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;font-size:20px;color:#374151;">
            <i class="fas fa-bars"></i>
          </button>
          @if(str_contains(request()->header('User-Agent', ''), 'BrgyPiliApp') && Auth::user()->role === 'resident')
            <img src="{{ asset('assets/images/pili_logo.png') }}" alt="Barangay Pili logo" style="width:34px;height:34px;object-fit:contain;margin-right:8px;">
          @endif
          <span class="topbar-title">@yield('title', 'Dashboard')</span>
        </div>
        <div class="topbar-right">
          @if(str_contains(request()->header('User-Agent', ''), 'BrgyPiliApp') && Auth::user()->role === 'resident')
            <button type="button" class="btn btn-outline-secondary btn-sm" title="Log out"
              onclick="document.getElementById('logoutModal').style.display='flex'">
              <i class="fas fa-sign-out-alt"></i> Logout
            </button>
          @else
            <a href="{{ in_array(Auth::user()->role, ['admin', 'staff']) ? route('admin.profile') : route('resident.profile') }}" class="topbar-profile-link" title="Profile">
              <span class="topbar-avatar">
                @if($photo)
                  <img src="{{ asset('assets/uploads/' . $photo) }}" alt="Profile photo">
                @else
                  {{ strtoupper(substr(Auth::user()->resident ? Auth::user()->resident->first_name : Auth::user()->username, 0, 1)) }}
                @endif
              </span>
              <span class="topbar-profile-name">
                {{ Auth::user()->resident ? Auth::user()->resident->first_name : Auth::user()->username }}
              </span>
            </a>
            <a href="{{ in_array(Auth::user()->role, ['admin', 'staff']) ? route('admin.dashboard') : route('resident.my_requests') }}" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-home"></i>
            </a>
          @endif
        </div>
      </header>

      <!-- Flash messages -->
      @if(session('success'))
        <div id="flash-message" style="margin:16px 24px 0;">
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
          </div>
        </div>
      @endif
      @if(session('error'))
        <div id="flash-message" style="margin:16px 24px 0;">
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
          </div>
        </div>
      @endif
      @if($errors->any())
        <div id="flash-message" style="margin:16px 24px 0;">
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <ul style="margin: 0; padding-left: 20px;">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif

      <div class="page-content">
        @yield('content')
      </div>
    </div>
  </div>

  <!-- Mobile Bottom Navigation -->
  <nav class="mobile-bottom-nav" id="mobile-bottom-nav">
    @if(str_contains(request()->header('User-Agent', ''), 'BrgyPiliApp') && Auth::user()->role === 'resident')
      <a href="{{ route('resident.dashboard') }}" class="mobile-nav-item {{ Route::is('resident.dashboard') ? 'active' : '' }}">
        <i class="fas fa-house"></i>
        <span>Home</span>
      </a>
      <a href="{{ route('resident.my_requests') }}" class="mobile-nav-item {{ Route::is('resident.my_requests') || Route::is('resident.request') ? 'active' : '' }}">
        <i class="fas fa-file-lines"></i>
        <span>Requests</span>
      </a>
      <a href="{{ route('resident.bulletins') }}" class="mobile-nav-item {{ Route::is('resident.bulletins') ? 'active' : '' }}">
        <i class="fas fa-bell"></i>
        <span>Notifications</span>
      </a>
      <a href="{{ route('resident.profile') }}" class="mobile-nav-item {{ Route::is('resident.profile') ? 'active' : '' }}">
        <i class="fas fa-user"></i>
        <span>Profile</span>
      </a>
    @elseif(in_array(Auth::user()->role, ['admin', 'staff']))
      <a href="{{ route('admin.dashboard') }}" class="mobile-nav-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-chart-pie"></i>
        <span>Dashboard</span>
      </a>
      <a href="{{ route('admin.requests') }}" class="mobile-nav-item {{ Route::is('admin.requests') ? 'active' : '' }}">
        <i class="fas fa-file-alt"></i>
        <span>Requests</span>
      </a>
      <a href="{{ route('admin.residents') }}" class="mobile-nav-item {{ Route::is('admin.residents') ? 'active' : '' }}">
        <i class="fas fa-users"></i>
        <span>Residents</span>
      </a>
      <a href="{{ route('admin.payments') }}" class="mobile-nav-item {{ Route::is('admin.payments') ? 'active' : '' }}">
        <i class="fas fa-money-bill-wave"></i>
        <span>Payments</span>
      </a>
      <button class="mobile-nav-item" onclick="document.getElementById('sidebar').classList.toggle('open'); document.getElementById('sidebar-overlay').classList.toggle('show');">
        <i class="fas fa-bars"></i>
        <span>More</span>
      </button>
      <button class="mobile-nav-item" style="color:#dc2626;" onclick="document.getElementById('logoutModal').style.display='flex'">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </button>
    @else
      <a href="{{ route('resident.my_requests') }}" class="mobile-nav-item {{ Route::is('resident.my_requests') ? 'active' : '' }}">
        <i class="fas fa-list"></i>
        <span>Requests</span>
      </a>
      <a href="{{ route('resident.request') }}" class="mobile-nav-item {{ Route::is('resident.request') ? 'active' : '' }}">
        <i class="fas fa-plus-circle"></i>
        <span>New</span>
      </a>
      <a href="{{ route('resident.borrows') }}" class="mobile-nav-item {{ Route::is('resident.borrows') ? 'active' : '' }}">
        <i class="fas fa-hand-holding"></i>
        <span>Borrow</span>
      </a>
      <a href="{{ route('resident.bulletins') }}" class="mobile-nav-item {{ Route::is('resident.bulletins') ? 'active' : '' }}">
        <i class="fas fa-bullhorn"></i>
        <span>Notices</span>
      </a>
      <button class="mobile-nav-item" onclick="document.getElementById('sidebar').classList.toggle('open'); document.getElementById('sidebar-overlay').classList.toggle('show');">
        <i class="fas fa-bars"></i>
        <span>More</span>
      </button>
      <button class="mobile-nav-item" style="color:#dc2626;" onclick="document.getElementById('logoutModal').style.display='flex'">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </button>
    @endif
  </nav>

  <script src="{{ asset('assets/js/main.js') }}"></script>
  @yield('scripts')

  {{-- Logout Confirmation Modal (works in all WebViews / APK) --}}
  <div id="logoutModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:16px; max-width:320px; width:100%; padding:28px 24px; box-shadow:0 20px 50px rgba(0,0,0,0.25); text-align:center;">
      <div style="width:56px; height:56px; background:#fef2f2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
        <i class="fas fa-sign-out-alt" style="font-size:22px; color:#b91c1c;"></i>
      </div>
      <h5 style="margin:0 0 8px; font-size:17px; font-weight:700; color:#111;">Log Out?</h5>
      <p style="margin:0 0 24px; font-size:13.5px; color:#6b7280;">Are you sure you want to log out of your account?</p>
      <div style="display:flex; gap:10px;">
        <button onclick="document.getElementById('logoutModal').style.display='none'"
          style="flex:1; padding:11px; border-radius:8px; border:1.5px solid #e5e7eb; background:#f9fafb; font-size:14px; font-weight:600; cursor:pointer; color:#374151;">
          Cancel
        </button>
        <button onclick="document.getElementById('logout-form').submit()"
          style="flex:1; padding:11px; border-radius:8px; border:none; background:#b91c1c; font-size:14px; font-weight:700; cursor:pointer; color:#fff;">
          <i class="fas fa-sign-out-alt"></i> Log Out
        </button>
      </div>
    </div>
  </div>

</body>
</html>
