<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Dashboard')) — {{ config('app.name') }}</title>

    <!-- Bootstrap 5 RTL/LTR -->
    @if(app()->getLocale() === 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    @endif

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        :root {
            --primary: #2d8a4e;
            --primary-dark: #1f6438;
            --primary-light: #e8f5ee;
            --secondary: #1a6b9a;
            --accent: #e07b39;
            --danger: #dc3545;
            --warning: #ffc107;
            --sidebar-width: 260px;
            --topbar-height: 60px;
            --font-main:
                {{ app()->getLocale() === 'ar' ? "'Cairo', sans-serif" : "'Inter', sans-serif" }}
            ;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-main);
            background: #f0f4f8;
            color: #2c3e50;
            overflow-x: hidden;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed;
            top: 0;
            {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}
            : 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1a3a2a 0%, #0f2218 100%);
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
            box-shadow: 4px 0 20px rgba(0, 0, 0, .3);
        }

        .sidebar-brand {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            display: flex;
            align-items: center;
            gap: .8rem;
            text-decoration: none;
        }

        .sidebar-brand .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
        }

        .sidebar-brand .brand-text {
            color: #fff;
            font-size: .95rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .sidebar-brand .brand-sub {
            color: rgba(255, 255, 255, .5);
            font-size: .7rem;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: .8rem 0;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .15);
            border-radius: 2px;
        }

        .nav-section {
            padding: .6rem 1.5rem .3rem;
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, .35);
            font-weight: 600;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: .65rem 1.5rem;
            color: rgba(255, 255, 255, .75);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            transition: all .2s;
            border-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: 3px solid transparent;
            margin: .1rem 0;
        }

        .nav-item-link:hover {
            background: rgba(255, 255, 255, .07);
            color: #fff;
            border-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}-color: var(--primary);
        }

        .nav-item-link.active {
            background: rgba(45, 138, 78, .2);
            color: #fff;
            border-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}-color: var(--primary);
        }

        .nav-item-link .nav-icon {
            width: 20px;
            text-align: center;
            font-size: .9rem;
        }

        .nav-badge {
            margin-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: auto;
            background: var(--danger);
            color: #fff;
            font-size: .65rem;
            padding: .15rem .45rem;
            border-radius: 10px;
            font-weight: 700;
        }

        /* ── TOPBAR ── */
        .topbar {
            position: fixed;
            top: 0;
            {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}
            : var(--sidebar-width);
            {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}
            : 0;
            height: var(--topbar-height);
            background: #fff;
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
            gap: 1rem;
        }

        /* Pagination fix */
        .pagination {
            margin: 0;
            flex-wrap: wrap;
            font-size: .82rem;
        }

        .pagination .page-link {
            padding: .3rem .6rem;
            font-size: .82rem;
            border-radius: 6px !important;
            margin: 0 2px;
        }

        .pagination svg {
            width: 14px;
            height: 14px;
        }

        .topbar-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a2e1e;
            flex: 1;
        }

        .topbar-action {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f0f4f8;
            color: #555;
            text-decoration: none;
            transition: all .2s;
            position: relative;
            border: none;
            cursor: pointer;
        }

        .topbar-action:hover {
            background: var(--primary-light);
            color: var(--primary);
        }

        .notification-dot {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 8px;
            height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-light);
            cursor: pointer;
        }

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 1.5rem;
            min-height: calc(100vh - var(--topbar-height));
        }

        /* ── CARDS ── */
        .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            transition: box-shadow .2s;
        }

        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, .1);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f4f8;
            border-radius: 14px 14px 0 0 !important;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .card-header h5,
        .card-header h6 {
            margin: 0;
            font-weight: 700;
            color: #1a2e1e;
        }

        /* ── STAT CARDS ── */
        .stat-card {
            border-radius: 14px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, .12);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}
            : -15px;
            bottom: -15px;
            background: rgba(255, 255, 255, .15);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            flex-shrink: 0;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
        }

        .stat-label {
            font-size: .8rem;
            color: rgba(255, 255, 255, .85);
            margin-top: .2rem;
        }

        /* ── TABLES ── */
        .table {
            font-size: .875rem;
             direction: ltr;
        }

        .table th {
            font-weight: 700;
            color: #555;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 2px solid #eef2f7;
        }

        .table td {
            vertical-align: middle;
            color: #2c3e50;
        }

        .table-hover tbody tr:hover {
            background: #f8fbff;
        }

        /* ── BADGES ── */
        .badge {
            font-size: .72rem;
            padding: .35em .65em;
            font-weight: 600;
            border-radius: 6px;
        }

        /* ── PROGRESS ── */
        .progress {
            border-radius: 10px;
        }

        .progress-bar {
            border-radius: 10px;
        }

        /* ── FORMS ── */
        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            font-size: .875rem;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(45, 138, 78, .1);
        }

        .form-label {
            font-size: .82rem;
            font-weight: 600;
            color: #555;
            margin-bottom: .4rem;
        }

        /* ── BUTTONS ── */
        .btn {
            border-radius: 8px;
            font-size: .875rem;
            font-weight: 600;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        /* ── PAGE HEADER ── */
        .page-header {
            background: #fff;
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
        }

        .page-header-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #fff;
            background: var(--primary);
            flex-shrink: 0;
        }

        .page-header h4 {
            margin: 0;
            font-weight: 800;
            color: #1a2e1e;
        }

        .page-header p {
            margin: 0;
            font-size: .82rem;
            color: #888;
        }

        /* ── FILL LEVEL INDICATOR ── */
        .fill-indicator {
            position: relative;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
        }

        .fill-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            border-radius: 3px;
            transition: width .5s;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            :root {
                --sidebar-width: 0px;
            }

            .sidebar {
                transform: translateX({{ app()->getLocale() === 'ar' ? '100%' : '-100%' }});
            }

            .sidebar.open {
                transform: translateX(0);
                --sidebar-width: 260px;
            }

            .main-content {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .topbar {
                left: 0 !important;
                right: 0 !important;
            }
        }

        /* ── LEAFLET OVERRIDES ── */
        .leaflet-container {
            border-radius: 12px;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .15);
        }

        /* ── NOTIFICATIONS DROPDOWN ── */
        .notifications-dropdown {
            width: 340px;
            max-height: 420px;
            overflow-y: auto;
        }

        .notif-item {
            padding: .75rem 1rem;
            border-bottom: 1px solid #f0f4f8;
            transition: background .15s;
        }

        .notif-item:hover {
            background: #f8fbff;
        }

        .notif-item.unread {
            border-left: 3px solid var(--primary);
        }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        /* Alert flash */
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- ══ SIDEBAR ══ --}}
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-recycle"></i></div>
            <div>
                <div class="brand-text">{{ __('Waste Management') }}</div>
                <div class="brand-sub">{{ __('Smart GIS System') }}</div>
            </div>
        </a>

        <nav class="sidebar-nav">
            <div class="nav-section">{{ __('Main') }}</div>
            <a href="{{ route('dashboard') }}"
                class="nav-item-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-chart-pie"></i></span>
                {{ __('Dashboard') }}
            </a>
            <a href="{{ route('map') }}" class="nav-item-link {{ request()->routeIs('map') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-map-marked-alt"></i></span>
                {{ __('GIS Map') }}
            </a>

            <div class="nav-section">{{ __('Operations') }}</div>
            <a href="{{ route('containers.index') }}"
                class="nav-item-link {{ request()->routeIs('containers.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-trash-alt"></i></span>
                {{ __('Containers') }}
                @php $needsEmptying = \App\Models\Container::where('fill_level', '>=', 80)->count(); @endphp
                @if($needsEmptying > 0)<span class="nav-badge">{{ $needsEmptying }}</span>@endif
            </a>
            <a href="{{ route('vehicles.index') }}"
                class="nav-item-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-truck"></i></span>
                {{ __('Vehicles') }}
            </a>
            <a href="{{ route('drivers.index') }}"
                class="nav-item-link {{ request()->routeIs('drivers.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-id-badge"></i></span>
                {{ __('Drivers') }}
            </a>
            <a href="{{ route('routes.index') }}"
                class="nav-item-link {{ request()->routeIs('routes.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-route"></i></span>
                {{ __('Routes') }}
            </a>
            <a href="{{ route('dumpsites.index') }}"
                class="nav-item-link {{ request()->routeIs('dumpsites.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-industry"></i></span>
                {{ __('Dumpsites') }}
            </a>

            <div class="nav-section">{{ __('Services') }}</div>
            <a href="{{ route('complaints.index') }}"
                class="nav-item-link {{ request()->routeIs('complaints.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-exclamation-circle"></i></span>
                {{ __('Complaints') }}
                @php $openComplaints = \App\Models\Complaint::where('status', 'open')->count(); @endphp
                @if($openComplaints > 0)<span class="nav-badge">{{ $openComplaints }}</span>@endif
            </a>
            <a href="{{ route('reports.index') }}"
                class="nav-item-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-file-chart-bar"></i></span>
                {{ __('Reports') }}
            </a>

            @role('admin')
            <div class="nav-section">{{ __('Administration') }}</div>
            <a href="{{ route('users.index') }}"
                class="nav-item-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-users"></i></span>
                {{ __('Users') }}
            </a>
            <a href="{{ route('roles.index') }}"
                class="nav-item-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-shield-alt"></i></span>
                {{ __('Roles & Permissions') }}
            </a>
            <a href="{{ route('settings.index') }}"
                class="nav-item-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fas fa-cog"></i></span>
                {{ __('Settings') }}
            </a>
            @endrole
        </nav>

        <div style="padding:1rem 1.5rem;border-top:1px solid rgba(255,255,255,.08);">
            <div style="display:flex;align-items:center;gap:.75rem;">
                <img src="{{ auth()->user()->avatar_url }}" class="user-avatar" style="width:36px;height:36px;" alt="">
                <div>
                    <div style="color:#fff;font-size:.8rem;font-weight:600;">{{ auth()->user()->name }}</div>
                    <div style="color:rgba(255,255,255,.4);font-size:.7rem;">
                        {{ auth()->user()->getRoleNames()->first() }}
                    </div>
                </div>
            </div>
        </div>
    </aside>

    {{-- ══ TOPBAR ══ --}}
    <header class="topbar">
        <button class="topbar-action d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-title">@yield('page-title', __('Dashboard'))</div>

        <!-- Language Switcher -->
        <div class="dropdown">
            <button class="topbar-action dropdown-toggle" data-bs-toggle="dropdown"
                style="width:auto;padding:0 .5rem;gap:.3rem;border-radius:8px;font-size:.8rem;">
                <i class="fas fa-globe"></i>
                {{ strtoupper(app()->getLocale()) }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <form action="{{ route('locale.switch', 'en') }}" method="GET">
                        <button type="submit" class="dropdown-item"><span class="me-2">🇬🇧</span> English</button>
                    </form>
                </li>
                <li>
                    <form action="{{ route('locale.switch', 'ar') }}" method="GET">
                        <button type="submit" class="dropdown-item"><span class="me-2">🇵🇸</span> العربية</button>
                    </form>
                </li>
            </ul>
        </div>

        <!-- Notifications -->
        <div class="dropdown">
            <button class="topbar-action" data-bs-toggle="dropdown" id="notifBtn">
                <i class="fas fa-bell"></i>
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="notification-dot"></span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end notifications-dropdown p-0">
                <div
                    style="padding:.75rem 1rem;border-bottom:1px solid #f0f4f8;display:flex;justify-content:space-between;align-items:center;">
                    <strong style="font-size:.875rem;">{{ __('Notifications') }}</strong>
                    <a href="#" style="font-size:.75rem;color:var(--primary);"
                        id="markAllRead">{{ __('Mark all read') }}</a>
                </div>
                @forelse(auth()->user()->notifications->take(8) as $notif)
                    <div class="notif-item {{ is_null($notif->read_at) ? 'unread' : '' }}">
                        <div style="font-size:.82rem;font-weight:600;">{{ $notif->data['title'] ?? __('Notification') }}
                        </div>
                        <div style="font-size:.75rem;color:#888;">{{ $notif->data['message'] ?? '' }}</div>
                        <div style="font-size:.7rem;color:#bbb;margin-top:.2rem;">{{ $notif->created_at->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div style="padding:2rem;text-align:center;color:#aaa;font-size:.82rem;">
                        <i class="fas fa-bell-slash" style="font-size:1.5rem;margin-bottom:.5rem;display:block;"></i>
                        {{ __('No notifications') }}
                    </div>
                @endforelse
            </div>
        </div>

        <!-- User Menu -->
        <div class="dropdown">
            <img src="{{ auth()->user()->avatar_url }}" class="user-avatar" data-bs-toggle="dropdown" alt="">
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:180px;">
                <li>
                    <div style="padding:.5rem 1rem;font-size:.82rem;color:#888;">{{ auth()->user()->email }}</div>
                </li>
                <li>
                    <hr class="dropdown-divider my-1">
                </li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>{{ __('Profile') }}</a></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>{{ __('Logout') }}
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    {{-- ══ MAIN CONTENT ══ --}}
    <main class="main-content">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const APP_LOCALE = '{{ app()->getLocale() }}';
        const DEFAULT_LAT = {{ \App\Models\Setting::get('default_lat', 31.9038) }};
        const DEFAULT_LNG = {{ \App\Models\Setting::get('default_lng', 35.2034) }};
        const DEFAULT_ZOOM = {{ \App\Models\Setting::get('default_zoom', 13) }};

        // Toastr config
        toastr.options = {
            progressBar: true, positionClass: APP_LOCALE === 'ar' ? 'toast-bottom-left' : 'toast-bottom-right',
            timeOut: 4000, closeButton: true
        };

        // Sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('open');
        });

        // Mark all notifications read
        document.getElementById('markAllRead')?.addEventListener('click', function (e) {
            e.preventDefault();
            $.post('{{ url("/notifications/read-all") }}', { _token: CSRF_TOKEN }, () => {
                document.querySelector('.notification-dot')?.remove();
            });
        });

        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(a => {
                bootstrap.Alert.getOrCreateInstance(a).close();
            });
        }, 5000);

        // Confirm delete helper
        function confirmDelete(formId) {
            Swal.fire({
                title: APP_LOCALE === 'ar' ? 'هل أنت متأكد؟' : 'Are you sure?',
                text: APP_LOCALE === 'ar' ? 'لن تتمكن من التراجع عن هذا!' : 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: APP_LOCALE === 'ar' ? 'نعم، احذف!' : 'Yes, delete!',
                cancelButtonText: APP_LOCALE === 'ar' ? 'إلغاء' : 'Cancel',
            }).then(r => { if (r.isConfirmed) document.getElementById(formId).submit(); });
        }
    </script>

    @stack('scripts')
</body>

</html>