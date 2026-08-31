<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventory') &middot; {{ config('app.name', 'Inventory') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #2563eb;
            --brand-soft: rgba(37,99,235,.10);
            --sidebar-w: 250px;
        }
        * { font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }

        [data-bs-theme="light"] {
            --app-bg: #f7f8fa;
            --panel-bg: #ffffff;
            --border: #eceef1;
            --muted: #8a93a2;
            --heading: #111827;
        }
        [data-bs-theme="dark"] {
            --app-bg: #0f1216;
            --panel-bg: #171b21;
            --border: #262b33;
            --muted: #8b95a3;
            --heading: #f3f4f6;
            --brand-soft: rgba(59,130,246,.18);
        }

        body { background: var(--app-bg); color: var(--heading); }

        /* ---------- Layout shell ---------- */
        .app-shell { display: flex; min-height: 100vh; }

        /* ---------- Sidebar ---------- */
        .sidebar {
            width: var(--sidebar-w); flex-shrink: 0;
            background: var(--panel-bg);
            border-right: 1px solid var(--border);
            position: sticky; top: 0; height: 100vh;
            display: flex; flex-direction: column;
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: .65rem;
            padding: 1.1rem 1.25rem; border-bottom: 1px solid var(--border);
        }
        .sidebar-brand .logo-box {
            width: 36px; height: 36px; border-radius: 9px; background: var(--brand);
            display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem;
        }
        .sidebar-brand .brand-logo { width: 38px; height: 38px; object-fit: contain; flex-shrink: 0; }
        .sidebar-brand .brand-name { font-weight: 700; line-height: 1.1; color: var(--heading); }
        .sidebar-brand .brand-sub { font-size: .72rem; color: var(--muted); }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: .75rem .75rem 1rem; }
        .sidebar-nav::-webkit-scrollbar { width: 6px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        .nav-section-label {
            font-size: .68rem; font-weight: 600; letter-spacing: .07em; text-transform: uppercase;
            color: var(--muted); padding: .9rem .65rem .35rem;
        }
        .nav-section-toggle {
            border: 0; background: transparent; border-radius: 8px; cursor: pointer;
            transition: color .12s;
        }
        .nav-section-toggle:hover { color: var(--heading); }
        .nav-section-toggle .chevron { font-size: .7rem; transition: transform .2s ease; }
        .nav-section-toggle[aria-expanded="true"] .chevron { transform: rotate(180deg); }
        .sidebar .nav-link {
            display: flex; align-items: center; gap: .7rem;
            color: #4b5563; font-size: .9rem; font-weight: 500;
            padding: .55rem .65rem; border-radius: 8px; margin-bottom: 2px;
            text-decoration: none; transition: background .12s, color .12s;
        }
        [data-bs-theme="dark"] .sidebar .nav-link { color: #c2c9d4; }
        .sidebar .nav-link i { font-size: 1.05rem; width: 1.2rem; text-align: center; }
        .sidebar .nav-link:hover { background: var(--app-bg); color: var(--heading); }
        .sidebar .nav-link.active { background: var(--brand-soft); color: var(--brand); font-weight: 600; }

        .sidebar-foot { padding: .75rem; border-top: 1px solid var(--border); }

        /* ---------- Main column ---------- */
        .main-col { flex: 1; min-width: 0; display: flex; flex-direction: column; }

        .topbar {
            background: var(--panel-bg); border-bottom: 1px solid var(--border);
            padding: .65rem 1.5rem; display: flex; align-items: center; gap: 1rem;
            position: sticky; top: 0; z-index: 20;
        }
        .topbar-search { flex: 1; max-width: 360px; position: relative; }
        .topbar-search i { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: var(--muted); }
        .topbar-search input {
            width: 100%; padding: .5rem .8rem .5rem 2.2rem; border-radius: 9px;
            border: 1px solid var(--border); background: var(--app-bg); color: var(--heading); font-size: .88rem;
        }
        .topbar-search input:focus { outline: none; border-color: var(--brand); background: var(--panel-bg); }

        .icon-btn {
            width: 38px; height: 38px; border-radius: 9px; border: 1px solid var(--border);
            background: transparent; color: var(--heading); display: inline-flex;
            align-items: center; justify-content: center; position: relative;
        }
        .icon-btn:hover { background: var(--app-bg); }
        .icon-btn .dot { position: absolute; top: 8px; right: 9px; width: 7px; height: 7px; border-radius: 50%; background: #ef4444; }

        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--brand), #0ea5e9);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: .8rem;
        }

        .content-area { padding: 1.5rem; }

        /* ---------- Cards / tables shared ---------- */
        .card { background: var(--panel-bg); border: 1px solid var(--border); border-radius: 12px; }
        .card-header { background: var(--panel-bg); border-bottom: 1px solid var(--border); }
        .table td, .table th { vertical-align: middle; }
        .text-truncate-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

        /* ---------- Page loader ---------- */
        .page-loader { position:fixed; inset:0; z-index:3000; display:flex; flex-direction:column;
            align-items:center; justify-content:center; background:rgba(247,248,250,.9);
            backdrop-filter:blur(2px); opacity:0; visibility:hidden; transition:opacity .2s ease; }
        [data-bs-theme="dark"] .page-loader { background: rgba(15,18,22,.9); }
        .page-loader.active { opacity:1; visibility:visible; }
        .loader-box { position:relative; width:70px; height:70px; display:flex; align-items:center; justify-content:center; }
        .loader-box .bi { font-size:1.7rem; color:var(--brand); animation:loader-bob 1s ease-in-out infinite; }
        .loader-ring { position:absolute; inset:0; border:4px solid var(--border); border-top-color:var(--brand);
            border-radius:50%; animation:loader-spin .8s linear infinite; }
        .loader-text { margin-top:.8rem; font-weight:600; letter-spacing:.08em; color:var(--muted); font-size:.85rem; text-transform:uppercase; }
        @keyframes loader-spin { to { transform:rotate(360deg); } }
        @keyframes loader-bob { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-5px); } }

        @media (max-width: 991.98px) {
            .sidebar { position: fixed; z-index: 1050; transform: translateX(-100%); transition: transform .2s; }
            .sidebar.show { transform: translateX(0); }
            .sidebar-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:1040; }
            .sidebar-backdrop.show { display:block; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div id="page-loader" class="page-loader" aria-hidden="true">
    <div class="loader-box">
        <div class="loader-ring"></div>
        <i class="bi bi-box-seam"></i>
    </div>
    <div class="loader-text">Loading…</div>
</div>

<div class="app-shell">
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    {{-- ---------- Sidebar ---------- --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('jm.png') }}" alt="Logo" class="brand-logo">
            <div>
                <div class="brand-name">{{ config('app.name', 'JM INTERNATIONAL') }}</div>
                <div class="brand-sub">Inventory</div>
            </div>
        </div>

        <div class="sidebar-nav">
            @include('layouts._nav')
        </div>

        <div class="sidebar-foot">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
            </form>
        </div>
    </aside>

    {{-- ---------- Main column ---------- --}}
    <div class="main-col">
        <header class="topbar">
            <button class="icon-btn d-lg-none" id="sidebarToggle"><i class="bi bi-list"></i></button>

            <form class="topbar-search" method="GET" action="{{ route('admin.products.index') }}">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products, SKU, suppliers...">
            </form>

            <div class="ms-auto d-flex align-items-center gap-2">
                <button class="icon-btn" id="themeToggle" title="Toggle theme"><i class="bi bi-moon-stars"></i></button>
                <a href="{{ Route::has('admin.stock.index') ? route('admin.stock.index') : '#' }}" class="icon-btn" title="Alerts">
                    <i class="bi bi-bell"></i><span class="dot"></span>
                </a>

                @php $initials = collect(explode(' ', auth()->user()->name ?? 'U'))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->implode(''); @endphp
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" style="color:var(--heading)" data-bs-toggle="dropdown">
                        <div class="avatar">{{ $initials }}</div>
                        <div class="d-none d-md-block" style="line-height:1.2">
                            <div class="small fw-semibold">{{ auth()->user()->name ?? '' }}</div>
                            <div style="font-size:.7rem;color:var(--muted)">{{ auth()->user()->roles->first()->name ?? 'User' }}</div>
                        </div>
                        <i class="bi bi-chevron-down small d-none d-md-block" style="color:var(--muted)"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width:210px">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-semibold small">{{ auth()->user()->name }}</div>
                            <div style="font-size:.72rem;color:var(--muted)">{{ auth()->user()->email }}</div>
                        </li>
                        <li><a class="dropdown-item py-2" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person me-2 text-primary"></i>My Profile</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('admin.profile.password') }}"><i class="bi bi-key me-2 text-warning"></i>Change Password</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content-area">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Theme toggle (persisted)
(function () {
    var html = document.documentElement;
    var btn = document.getElementById('themeToggle');
    var saved = localStorage.getItem('app-theme');
    if (saved) html.setAttribute('data-bs-theme', saved);
    function syncIcon() {
        var dark = html.getAttribute('data-bs-theme') === 'dark';
        btn.querySelector('i').className = dark ? 'bi bi-sun' : 'bi bi-moon-stars';
    }
    syncIcon();
    btn.addEventListener('click', function () {
        var next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('app-theme', next);
        syncIcon();
    });
})();

// Mobile sidebar
(function () {
    var sb = document.getElementById('sidebar');
    var bd = document.getElementById('sidebarBackdrop');
    var tg = document.getElementById('sidebarToggle');
    function toggle() { sb.classList.toggle('show'); bd.classList.toggle('show'); }
    if (tg) tg.addEventListener('click', toggle);
    if (bd) bd.addEventListener('click', toggle);
})();

// Auto-scroll the active menu item into view inside the sidebar
(function () {
    var nav = document.querySelector('.sidebar-nav');
    var active = nav && nav.querySelector('.nav-link.active');
    if (!nav || !active) return;
    // Center the active item within the sidebar's scroll area (no page jump).
    var offset = active.offsetTop - (nav.clientHeight / 2) + (active.offsetHeight / 2);
    nav.scrollTop = Math.max(0, offset);
})();
</script>

<script>
(function () {
    var loader = document.getElementById('page-loader');
    var show = function () { loader.classList.add('active'); };
    var hide = function () { loader.classList.remove('active'); };

    hide(); // ensure hidden once the page is ready

    // Show during same-origin link navigation.
    document.addEventListener('click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-bs-toggle')) return;
        if (href === '' || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;
        try { if (new URL(a.href).origin !== location.origin) return; } catch (_) { return; }
        show();
    });

    // Show on form submit (skip if a handler/confirm cancelled it).
    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;
        show();
    });

    // Hide when returning via back/forward (bfcache restore).
    window.addEventListener('pageshow', hide);
})();
</script>
@stack('scripts')
<script>
(function () {
    // Find all pagination elements on the page
    var paginations = document.querySelectorAll('.pagination');
    if (!paginations.length) return;

    paginations.forEach(function (pag) {
        var wrapper = pag.closest('.card-footer, nav, .d-flex');
        var card = pag.closest('.card') || (wrapper && wrapper.previousElementSibling);
        if (!card || !card.querySelector) {
            card = pag.parentElement && pag.parentElement.closest('.card');
        }
        if (!card) return;

        var tbody = card.querySelector('tbody');
        if (!tbody) return;

        // Find next page link
        var nextSel = '.page-item:not(.disabled) .page-link[rel="next"]';
        var nextLink = pag.querySelector(nextSel);
        if (!nextLink) {
            // Hide pagination if on last page
            if (wrapper) wrapper.style.display = 'none';
            return;
        }

        var nextUrl = nextLink.getAttribute('href');
        var loading = false;
        var done = false;

        // Hide traditional pagination
        if (wrapper) wrapper.style.display = 'none';

        // Create loading spinner
        var spinner = document.createElement('div');
        spinner.className = 'text-center py-3';
        spinner.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div><span class="ms-2 text-muted small">Loading more...</span>';
        spinner.style.display = 'none';
        card.appendChild(spinner);

        function loadMore() {
            if (loading || done || !nextUrl) return;
            loading = true;
            spinner.style.display = '';

            fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var rows = doc.querySelectorAll('tbody tr');

                    // Animate new rows in
                    rows.forEach(function (row) {
                        var imported = document.importNode(row, true);
                        imported.style.opacity = '0';
                        imported.style.transform = 'translateY(10px)';
                        imported.style.transition = 'opacity .3s ease, transform .3s ease';
                        tbody.appendChild(imported);
                        requestAnimationFrame(function () {
                            requestAnimationFrame(function () {
                                imported.style.opacity = '1';
                                imported.style.transform = 'translateY(0)';
                            });
                        });
                    });

                    // Find next page in fetched HTML
                    var newPag = doc.querySelector('.pagination');
                    var newNext = newPag && newPag.querySelector('.page-item:not(.disabled) .page-link[rel="next"]');
                    if (newNext) {
                        nextUrl = newNext.getAttribute('href');
                    } else {
                        nextUrl = null;
                        done = true;
                    }

                    loading = false;
                    spinner.style.display = 'none';
                })
                .catch(function () {
                    loading = false;
                    spinner.style.display = 'none';
                    done = true;
                });
        }

        // Scroll detection
        function checkScroll() {
            if (done || loading) return;
            var rect = card.getBoundingClientRect();
            if (rect.bottom - window.innerHeight < 300) {
                loadMore();
            }
        }

        window.addEventListener('scroll', checkScroll, { passive: true });
        // Initial check in case page is already scrolled / content is short
        setTimeout(checkScroll, 500);
    });
})();
</script>
</body>
</html>
