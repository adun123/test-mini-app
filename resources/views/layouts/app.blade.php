<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Mini Attendance App' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #eef2f7; color: #1f2937; }
        a { color: inherit; text-decoration: none; }
        h1, h2, h3, p { margin-top: 0; }
        .app-shell { min-height: 100vh; display: grid; grid-template-columns: 264px minmax(0, 1fr); }
        .mobile-topbar { display: none; }
        .sidebar-overlay { display: none; }
        .sidebar { background: #172033; color: #fff; padding: 22px 18px; display: flex; flex-direction: column; gap: 16px; position: sticky; top: 0; height: 100vh; overflow-y: auto; }
        .brand { display: flex; gap: 12px; align-items: center; padding: 6px 6px 16px; border-bottom: 1px solid rgba(255,255,255,.12); }
        .brand-mark { width: 42px; height: 42px; border-radius: 8px; background: #2dd4bf; color: #0f172a; display: grid; place-items: center; font-weight: 800; }
        .brand-title { font-weight: 800; line-height: 1.2; }
        .brand-subtitle { color: #a7b0c3; font-size: 12px; margin-top: 3px; }
        .user-card { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.1); border-radius: 8px; padding: 14px; }
        .user-name { font-weight: 700; margin-bottom: 4px; }
        .user-role { color: #bfdbfe; font-size: 13px; text-transform: capitalize; }
        .nav { display: grid; gap: 8px; }
        .nav-label { color: #8fa0bd; font-size: 12px; font-weight: 700; text-transform: uppercase; margin: 4px 8px; }
        .nav a, .link-button { width: 100%; min-height: 42px; display: flex; align-items: center; gap: 10px; color: #dce5f5; background: transparent; border: 0; border-radius: 7px; cursor: pointer; font-size: 14px; padding: 10px 12px; text-align: left; }
        .nav a:hover, .link-button:hover { background: rgba(255,255,255,.08); }
        .nav a.active { background: #2563eb; color: #fff; font-weight: 700; }
        .nav-icon { width: 24px; height: 24px; border-radius: 6px; background: rgba(255,255,255,.1); display: grid; place-items: center; flex: 0 0 auto; }
        .nav-icon svg { width: 15px; height: 15px; stroke: currentColor; stroke-width: 2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
        .sidebar-footer { margin-top: auto; position: sticky; bottom: 0; background: #172033; padding-top: 10px; }
        .content-area { min-width: 0; padding: 28px; }
        .page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; margin-bottom: 18px; }
        .page-title { margin-bottom: 6px; font-size: 28px; }
        .page-subtitle { color: #64748b; margin-bottom: 0; }
        .container { width: min(1180px, 100%); margin: 0 auto; }
        .panel { background: #fff; border: 1px solid #dfe6ef; border-radius: 8px; padding: 22px; box-shadow: 0 12px 30px rgba(15, 23, 42, .06); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; }
        .stat { background: #fff; border: 1px solid #dfe6ef; border-radius: 8px; padding: 18px; box-shadow: 0 10px 24px rgba(15, 23, 42, .05); position: relative; overflow: hidden; }
        .stat:before { content: ""; position: absolute; inset: 0 auto 0 0; width: 4px; background: #2563eb; }
        .stat strong { display: block; font-size: 30px; margin-top: 8px; color: #0f172a; }
        .stat span { color: #64748b; font-size: 13px; font-weight: 700; text-transform: uppercase; }
        .actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .button { min-height: 40px; display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 6px; background: #2563eb; color: #fff; padding: 10px 14px; cursor: pointer; font-weight: 700; }
        .button.secondary { background: #475569; }
        .button.danger { background: #dc2626; }
        .button.light { background: #e2e8f0; color: #0f172a; }
        .button.small { min-height: 32px; padding: 7px 10px; font-size: 13px; border-radius: 5px; }
        .icon-button { width: 42px; height: 42px; display: inline-grid; place-items: center; border: 0; border-radius: 7px; background: #172033; color: #fff; cursor: pointer; }
        .icon-button svg { width: 22px; height: 22px; stroke: currentColor; stroke-width: 2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
        .input, select, textarea { width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 11px 12px; background: #fff; outline: none; }
        .input:focus, select:focus, textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
        label { display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px; color: #334155; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; }
        .field { margin-bottom: 14px; }
        .table-wrap { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: #fff; min-width: 760px; }
        th, td { padding: 13px 12px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: middle; }
        th { background: #f8fafc; font-size: 12px; color: #475569; text-transform: uppercase; }
        tbody tr:hover { background: #f8fafc; }
        .badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 5px 9px; font-size: 12px; font-weight: 700; background: #e2e8f0; color: #334155; }
        .badge.success { background: #dcfce7; color: #166534; }
        .badge.warning { background: #fef3c7; color: #92400e; }
        .badge.danger { background: #fee2e2; color: #991b1b; }
        .attendance-card { display: grid; gap: 18px; }
        .attendance-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .attendance-item { border: 1px solid #dfe6ef; border-radius: 8px; padding: 14px; background: #f8fafc; }
        .attendance-item span { display: block; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 7px; }
        .attendance-item strong { display: block; color: #0f172a; font-size: 24px; line-height: 1.1; word-break: break-word; }
        .attendance-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .attendance-form { margin: 0; }
        .attendance-button { width: 100%; min-height: 56px; font-size: 16px; gap: 8px; }
        .attendance-button svg { width: 18px; height: 18px; stroke: currentColor; stroke-width: 2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
        .attendance-location { display: flex; align-items: center; gap: 8px; color: #64748b; font-size: 13px; }
        .modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, .72); display: none; align-items: center; justify-content: center; padding: 18px; z-index: 50; }
        .modal-backdrop.show { display: flex; }
        .camera-modal { width: min(520px, 100%); max-height: calc(100vh - 36px); overflow-y: auto; background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 24px 80px rgba(0, 0, 0, .35); }
        .camera-box { background: #0f172a; border-radius: 8px; overflow: hidden; aspect-ratio: 4 / 3; display: grid; place-items: center; margin: 14px 0; }
        .camera-box video, .camera-box canvas, .camera-box img { width: 100%; height: 100%; object-fit: cover; }
        .camera-preview { display: none; }
        .modal-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 12px; }
        .modal-actions .button { width: 100%; }
        .message { padding: 12px 14px; border-radius: 6px; margin-bottom: 16px; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .muted { color: #64748b; }
        .auth { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; padding: 24px; background: linear-gradient(rgba(15, 23, 42, .48), rgba(15, 23, 42, .48)), url('https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1600&q=80') center / cover no-repeat; }
        .auth > .message { width: min(420px, 100%); margin-bottom: 0; }
        .auth-form { width: min(420px, 100%); background: rgba(255,255,255,.96); border: 1px solid rgba(255,255,255,.65); border-radius: 8px; padding: 28px; box-shadow: 0 24px 60px rgba(15, 23, 42, .22); }
        .auth-form.wide { width: min(520px, 100%); }
        .auth-heading { text-align: center; margin-bottom: 22px; }
        .auth-heading h1 { font-size: 26px; margin-bottom: 6px; }
        .auth-form .button { width: 100%; }
        .auth-link { margin-top: 16px; text-align: center; color: #64748b; font-size: 14px; }
        .auth-link a { color: #2563eb; font-weight: 700; }
        @media (max-width: 860px) {
            .app-shell { display: block; }
            .mobile-topbar { height: 62px; display: flex; align-items: center; justify-content: space-between; gap: 12px; background: #fff; border-bottom: 1px solid #dfe6ef; padding: 10px 18px; position: sticky; top: 0; z-index: 30; }
            .mobile-title { font-weight: 800; color: #0f172a; }
            .sidebar { position: fixed; top: 0; left: 0; width: min(82vw, 292px); height: 100vh; z-index: 45; transform: translateX(-105%); transition: transform .2s ease; box-shadow: 18px 0 40px rgba(15, 23, 42, .25); }
            .sidebar.is-open { transform: translateX(0); }
            .sidebar-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, .48); z-index: 40; }
            .sidebar-overlay.show { display: block; }
            .content-area { padding: 18px; }
            .page-header { flex-direction: column; }
            .auth { padding: 18px; }
            .auth-form { padding: 22px; }
            .attendance-summary { grid-template-columns: 1fr 1fr; }
            .attendance-actions { grid-template-columns: 1fr; }
            .attendance-button { min-height: 62px; }
            .modal-actions { grid-template-columns: 1fr; }
            table { min-width: 680px; }
            th, td { font-size: 13px; }
        }
        @media (max-width: 480px) {
            .page-title { font-size: 24px; }
            .panel { padding: 18px; }
            .attendance-summary { grid-template-columns: 1fr; }
            .attendance-item { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
            .attendance-item span { margin-bottom: 0; }
            .attendance-item strong { font-size: 22px; text-align: right; }
        }
    </style>
</head>
<body>
    @auth
        <div class="app-shell">
            <div class="mobile-topbar">
                <button class="icon-button" id="openSidebar" type="button" aria-label="Open menu">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 6h16"></path>
                        <path d="M4 12h16"></path>
                        <path d="M4 18h16"></path>
                    </svg>
                </button>
                <div class="mobile-title">Mini Attendance</div>
            </div>
            <div class="sidebar-overlay" id="sidebarOverlay"></div>
            <aside class="sidebar">
                <div class="brand">
                    <div class="brand-mark">MA</div>
                    <div>
                        <div class="brand-title">Mini Attendance</div>
                        <div class="brand-subtitle">HR Dashboard</div>
                    </div>
                </div>

                <div class="user-card">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->role }}</div>
                </div>

                <nav class="nav">
                    <div class="nav-label">Menu</div>
                    <a data-sidebar-link href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="8"></rect>
                                <rect x="14" y="3" width="7" height="5"></rect>
                                <rect x="14" y="12" width="7" height="9"></rect>
                                <rect x="3" y="15" width="7" height="6"></rect>
                            </svg>
                        </span>
                        Dashboard
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a data-sidebar-link href="{{ route('admin.employees.index') }}" class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </span>
                            Employees
                        </a>
                        <a data-sidebar-link href="{{ route('admin.departments.index') }}" class="{{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 21h18"></path>
                                    <path d="M5 21V7l8-4v18"></path>
                                    <path d="M19 21V11l-6-4"></path>
                                    <path d="M9 9h1"></path>
                                    <path d="M9 13h1"></path>
                                    <path d="M9 17h1"></path>
                                </svg>
                            </span>
                            Departments
                        </a>
                        <a data-sidebar-link href="{{ route('admin.positions.index') }}" class="{{ request()->routeIs('admin.positions.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="3" y="7" width="18" height="13" rx="2"></rect>
                                    <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <path d="M12 12v3"></path>
                                </svg>
                            </span>
                            Positions
                        </a>
                        <a data-sidebar-link href="{{ route('admin.attendance-settings.index') }}" class="{{ request()->routeIs('admin.attendance-settings.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path d="M12 7v5l3 2"></path>
                                    <path d="M4 4l3 3"></path>
                                    <path d="M20 4l-3 3"></path>
                                </svg>
                            </span>
                            Work Schedules
                        </a>
                        <a data-sidebar-link href="{{ route('admin.corrections.index') }}" class="{{ request()->routeIs('admin.corrections.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                </svg>
                            </span>
                            Corrections
                        </a>
                    @endif
                    <a data-sidebar-link href="{{ route('attendance.history') }}" class="{{ request()->routeIs('attendance.history') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="M12 7v5l3 2"></path>
                            </svg>
                        </span>
                        Attendance History
                    </a>
                </nav>

                <div class="sidebar-footer">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="link-button" type="submit">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <path d="M16 17l5-5-5-5"></path>
                                    <path d="M21 12H9"></path>
                                </svg>
                            </span>
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            <main class="content-area">
                <div class="container">
                    @if(session('success'))
                        <div class="message success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="message error">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="message error">{{ $errors->first() }}</div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
        <script>
            const sidebar = document.querySelector('.sidebar');
            const openSidebar = document.getElementById('openSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            function closeMobileSidebar() {
                sidebar.classList.remove('is-open');
                sidebarOverlay.classList.remove('show');
            }

            openSidebar?.addEventListener('click', function () {
                sidebar.classList.add('is-open');
                sidebarOverlay.classList.add('show');
            });

            sidebarOverlay?.addEventListener('click', closeMobileSidebar);
            document.querySelectorAll('[data-sidebar-link]').forEach(function (link) {
                link.addEventListener('click', closeMobileSidebar);
            });
        </script>
    @else
        <main class="auth">
            @if(session('success'))
                <div class="message success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="message error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="message error">{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </main>
    @endauth
</body>
</html>
