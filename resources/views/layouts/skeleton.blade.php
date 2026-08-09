<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Dashboard')) — {{ config('app.name') }}</title>
    @if (app()->getLocale() === 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        :root{--primary:#176b87;--primary-dark:#0d536b;--primary-soft:#e7f3f7;--teal:#35b69b;--ink:#17212b;--muted:#71808d;--line:#e6edf2;--page:#f4f7f9;--success:#159570;--warning:#d88919;--danger:#d94b55;--sidebar:#132b3a;--sidebar-dark:#0e202c;--sidebar-w:248px;--top-h:64px;--font:{{ app()->getLocale()==='ar' ? "'Cairo',sans-serif" : "'Inter',sans-serif" }}}
        *{box-sizing:border-box}body{margin:0;background:var(--page);color:var(--ink);font-family:var(--font);font-size:.9rem;overflow-x:hidden}a{color:var(--primary)}
        .sidebar{position:fixed;top:0;bottom:0;inset-inline-start:0;width:var(--sidebar-w);background:linear-gradient(180deg,var(--sidebar),var(--sidebar-dark));z-index:1040;display:flex;flex-direction:column;box-shadow:0 0 30px rgba(10,30,45,.16);transition:transform .2s ease}.sidebar-nav{flex:1;overflow:auto;padding:.7rem .65rem}html[dir="rtl"] .sidebar{inset-inline-start:auto;inset-inline-end:0}
        .brand{display:flex;align-items:center;gap:.8rem;padding:1.1rem 1.2rem;border-bottom:1px solid rgba(255,255,255,.08);text-decoration:none}.brand-mark{width:42px;height:42px;border-radius:13px;background:linear-gradient(135deg,var(--teal),var(--primary));display:grid;place-items:center;color:#fff}.brand-title{display:block;color:#fff;font-weight:800;font-size:.92rem}.brand-sub{display:block;color:#91a7b5;font-size:.67rem}.nav-section{padding:.75rem .7rem .35rem;color:#78909e;font-size:.63rem;font-weight:800;letter-spacing:.8px;text-transform:uppercase}.nav-linkx{display:flex;align-items:center;gap:.75rem;padding:.68rem .75rem;margin:.18rem 0;border-radius:10px;color:#c7d5dd;text-decoration:none;font-weight:600;font-size:.83rem}.nav-linkx:hover{background:rgba(255,255,255,.07);color:#fff}.nav-linkx.active{background:linear-gradient(90deg,rgba(42,174,153,.22),rgba(23,107,135,.35));color:#fff}.nav-icon{width:22px;text-align:center;color:#86a7b6}.nav-linkx.active .nav-icon{color:#61d0ba}.nav-badge{margin-inline-start:auto;background:var(--danger);color:#fff;border-radius:20px;padding:.14rem .45rem;font-size:.62rem}.sidebar-user{padding:.85rem 1rem;border-top:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:.65rem}.avatar{width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.2)}.user-name{color:#fff;font-size:.76rem;font-weight:700}.user-role{color:#8097a4;font-size:.65rem}
        .topbar{position:fixed;top:0;inset-inline-start:var(--sidebar-w);inset-inline-end:0;height:var(--top-h);background:rgba(255,255,255,.94);backdrop-filter:blur(12px);z-index:1030;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:.7rem;padding:0 1.2rem}.topbar-title{font-weight:800;font-size:1rem;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.top-action{width:38px;height:38px;border:1px solid var(--line);background:#fff;border-radius:10px;display:grid;place-items:center;color:#60717d;position:relative;transition:.15s ease}.top-action:hover{border-color:#b9d3dc;background:var(--primary-soft);color:var(--primary)}.topbar .dropdown{flex:0 0 auto}.main-content{margin-inline-start:var(--sidebar-w);padding:1.35rem;min-height:100vh;padding-top:calc(var(--top-h) + 1.35rem)}html[dir="rtl"] .main-content{margin-inline-start:0;margin-inline-end:var(--sidebar-w)}
        .page-header{background:#fff;border:1px solid var(--line);border-radius:16px;padding:1.05rem 1.2rem;margin-bottom:1rem;display:flex;align-items:center;gap:.85rem;min-width:0}.page-header>div:nth-child(2){min-width:0;flex:1}.page-header h1,.page-header h2,.page-header h3,.page-header h4,.page-header h5,.page-header h6,.page-header p{overflow-wrap:anywhere}.page-header-icon{width:44px;height:44px;flex:0 0 44px;border-radius:12px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center}.card{border:1px solid var(--line);border-radius:15px;background:#fff;box-shadow:0 3px 15px rgba(20,45,60,.045);overflow:hidden}.card-header{background:#fff;border-bottom:1px solid var(--line);padding:.85rem 1rem;display:flex;align-items:center;gap:.65rem}.card-body{padding:1rem}.stat-card{height:100%;min-height:105px;padding:1rem;border:1px solid var(--line);border-radius:15px;background:#fff;display:flex;align-items:center;gap:.85rem;position:relative;overflow:hidden}.stat-card:before{content:'';position:absolute;bottom:0;inset-inline:0;height:3px;background:var(--accent,var(--primary))}.stat-icon{width:46px;height:46px;border-radius:13px;background:var(--icon-bg,var(--primary-soft));color:var(--accent,var(--primary));display:grid;place-items:center}.stat-value{font-size:1.55rem;font-weight:800}.stat-label{color:var(--muted);font-size:.72rem;font-weight:600}.btn{border-radius:9px;font-weight:700;font-size:.8rem}.btn-primary{background:var(--primary);border-color:var(--primary)}.form-control,.form-select{border:1px solid #dce5ea;border-radius:9px;font-size:.82rem;padding:.58rem .72rem}.form-label{font-size:.75rem;font-weight:700;color:#536571}.table{font-size:.8rem}.table th{font-size:.68rem;color:#71808d;text-transform:uppercase;font-weight:800}.table td{vertical-align:middle}.badge{border-radius:7px;font-size:.66rem;font-weight:700}.progress{height:7px;border-radius:10px}.dropdown-menu{border:1px solid var(--line);border-radius:12px;box-shadow:0 12px 30px rgba(20,45,60,.12);font-size:.8rem}.notifications-dropdown{width:330px;max-height:420px;overflow:auto}.notif-item{padding:.7rem .85rem;border-bottom:1px solid #eef3f5}.notif-item.unread{border-inline-start:3px solid var(--primary);background:#f5fafb}.notification-dot{position:absolute;top:3px;inset-inline-end:3px;width:8px;height:8px;background:var(--danger);border:2px solid #fff;border-radius:50%}.leaflet-container{font-family:var(--font)}.mobile-only{display:none}
        @media(max-width:900px){:root{--sidebar-w:0px}.sidebar{width:248px;inset-inline-start:0;inset-inline-end:auto;transform:translateX(-100%)}html[dir="rtl"] .sidebar{inset-inline-start:auto;inset-inline-end:0;transform:translateX(100%)}.sidebar.open{transform:translateX(0)}.topbar{inset-inline:0!important}.main-content,html[dir="rtl"] .main-content{margin-inline:0!important}.mobile-only{display:grid}}
        @media(max-width:600px){.main-content{padding:1rem;padding-top:calc(var(--top-h) + 1rem)}.notifications-dropdown{width:290px}.page-header{align-items:flex-start;flex-wrap:wrap}.page-header>div:last-child{width:100%;margin-inline-start:0!important}.topbar{padding:0 .75rem}}
        @stack('styles')
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark"><i class="fa-solid fa-arrows-to-circle"></i></span><span><span class="brand-title">{{ __('Waste Operations') }}</span><span class="brand-sub">{{ __('GIS Management Platform') }}</span></span></a>
        <nav class="sidebar-nav">
            <div class="nav-section">{{ __('Workspace') }}</div>
            <a href="{{ route('dashboard') }}" class="nav-linkx {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-grid-2"></i></span>{{ __('Dashboard') }}</a>
            <a href="{{ route('map') }}" class="nav-linkx {{ request()->routeIs('map') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-map-location-dot"></i></span>{{ __('GIS Map') }}</a>
            <div class="nav-section">{{ __('Operations') }}</div>
            <a href="{{ route('containers.index') }}" class="nav-linkx {{ request()->routeIs('containers.*') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-box-open"></i></span>{{ __('Containers') }} @php($highFillContainers=\App\Models\Container::where('fill_level','>=',80)->count()) @if($highFillContainers>0)<span class="nav-badge">{{ $highFillContainers }}</span>@endif</a>
            <a href="{{ route('vehicles.index') }}" class="nav-linkx {{ request()->routeIs('vehicles.*') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-truck-fast"></i></span>{{ __('Vehicles') }}</a>
            <a href="{{ route('drivers.index') }}" class="nav-linkx {{ request()->routeIs('drivers.*') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-id-card-clip"></i></span>{{ __('Drivers') }}</a>
            <a href="{{ route('routes.index') }}" class="nav-linkx {{ request()->routeIs('routes.*') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-share-nodes"></i></span>{{ __('Routes') }}</a>
            <a href="{{ route('dumpsites.index') }}" class="nav-linkx {{ request()->routeIs('dumpsites.*') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-warehouse"></i></span>{{ __('Dumpsites') }}</a>
            <div class="nav-section">{{ __('Services') }}</div>
            <a href="{{ route('complaints.index') }}" class="nav-linkx {{ request()->routeIs('complaints.*') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-message-exclamation"></i></span>{{ __('Complaints') }} @php($openComplaints=\App\Models\Complaint::where('status','open')->count()) @if($openComplaints>0)<span class="nav-badge">{{ $openComplaints }}</span>@endif</a>
            <a href="{{ route('reports.index') }}" class="nav-linkx {{ request()->routeIs('reports.*') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-chart-column"></i></span>{{ __('Reports') }}</a>
            @if(auth()->user()->hasRole('admin'))
                <div class="nav-section">{{ __('Administration') }}</div>
                <a href="{{ route('users.index') }}" class="nav-linkx {{ request()->routeIs('users.*') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-users-gear"></i></span>{{ __('Users') }}</a>
                <a href="{{ route('roles.index') }}" class="nav-linkx {{ request()->routeIs('roles.*') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-shield-halved"></i></span>{{ __('Roles & Permissions') }}</a>
                <a href="{{ route('settings.index') }}" class="nav-linkx {{ request()->routeIs('settings.*') ? 'active' : '' }}"><span class="nav-icon"><i class="fa-solid fa-sliders"></i></span>{{ __('Settings') }}</a>
            @endif
        </nav>
        <div class="sidebar-user"><img src="{{ auth()->user()->avatar_url }}" class="avatar" alt=""><div><div class="user-name">{{ auth()->user()->name }}</div><div class="user-role">{{ auth()->user()->getRoleNames()->first() }}</div></div></div>
    </aside>

    <header class="topbar">
        <button class="top-action mobile-only" id="sidebarToggle" type="button" aria-label="{{ __('Toggle menu') }}"><i class="fa-solid fa-bars"></i></button>
        <div class="topbar-title">@yield('page-title', __('Dashboard'))</div>

        <div class="dropdown">
            <button class="top-action" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('Language') }}"><i class="fa-solid fa-language"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('locale.switch','ar') }}">🇵🇸 العربية</a></li>
                <li><a class="dropdown-item" href="{{ route('locale.switch','en') }}">🇬🇧 English</a></li>
            </ul>
        </div>

        <div class="dropdown position-relative">
            <button class="top-action" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('Notifications') }}"><i class="fa-regular fa-bell"></i>@if(auth()->user()->unreadNotifications->count()>0)<span class="notification-dot"></span>@endif</button>
            <div class="dropdown-menu dropdown-menu-end notifications-dropdown p-0">
                <div class="p-3 border-bottom d-flex justify-content-between"><strong>{{ __('Notifications') }}</strong><a href="#" id="markAllRead" class="small">{{ __('Mark all read') }}</a></div>
                @forelse(auth()->user()->notifications->take(8) as $notif)
                    <div class="notif-item {{ is_null($notif->read_at)?'unread':'' }}"><div class="fw-bold">{{ $notif->data['title'] ?? __('Notification') }}</div><div class="text-muted small">{{ $notif->data['message'] ?? '' }}</div><small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small></div>
                @empty
                    <div class="p-4 text-center text-muted">{{ __('No notifications') }}</div>
                @endforelse
            </div>
        </div>

        <div class="dropdown">
            <button class="top-action" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('Account') }}"><img src="{{ auth()->user()->avatar_url }}" class="avatar" alt="{{ auth()->user()->name }}"></button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><div class="px-3 py-2"><div class="fw-bold">{{ auth()->user()->name }}</div><div class="small text-muted">{{ auth()->user()->email }}</div></div></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fa-regular fa-user me-2"></i>{{ __('Profile') }}</a></li>
                <li><form action="{{ route('logout') }}" method="POST" class="m-0">@csrf<button class="dropdown-item text-danger" type="submit"><i class="fa-solid fa-right-from-bracket me-2"></i>{{ __('Logout') }}</button></form></li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST" class="m-0 d-none d-sm-block">
            @csrf
            <button class="top-action" type="submit" title="{{ __('Logout') }}" aria-label="{{ __('Logout') }}"><i class="fa-solid fa-right-from-bracket"></i></button>
        </form>
    </header>

    <main class="main-content">
        @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        const CSRF_TOKEN='{{ csrf_token() }}',APP_LOCALE='{{ app()->getLocale() }}';
        const DEFAULT_LAT={{ \App\Models\Setting::get('default_lat',31.9038) }},DEFAULT_LNG={{ \App\Models\Setting::get('default_lng',35.2034) }},DEFAULT_ZOOM={{ \App\Models\Setting::get('default_zoom',13) }};
        toastr.options={progressBar:true,positionClass:APP_LOCALE==='ar'?'toast-bottom-left':'toast-bottom-right',timeOut:3500,closeButton:true};
        document.getElementById('sidebarToggle')?.addEventListener('click',()=>document.getElementById('sidebar')?.classList.toggle('open'));
        document.getElementById('markAllRead')?.addEventListener('click',e=>{e.preventDefault();$.post('{{ route('notifications.read-all') }}',{_token:CSRF_TOKEN},()=>document.querySelector('.notification-dot')?.remove())});
        setTimeout(()=>document.querySelectorAll('.alert').forEach(a=>bootstrap.Alert.getOrCreateInstance(a).close()),5000);
        function confirmDelete(id){Swal.fire({title:APP_LOCALE==='ar'?'هل تريد الحذف؟':'Delete this item?',text:APP_LOCALE==='ar'?'لا يمكن التراجع عن العملية.':'This action cannot be undone.',icon:'warning',showCancelButton:true,confirmButtonColor:'#d94b55',cancelButtonColor:'#71808d',confirmButtonText:APP_LOCALE==='ar'?'حذف':'Delete',cancelButtonText:APP_LOCALE==='ar'?'إلغاء':'Cancel'}).then(r=>{if(r.isConfirmed)document.getElementById(id)?.submit()})}
    </script>
    @stack('scripts')
</body>
</html>
