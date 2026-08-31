<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login &middot; {{ config('app.name', 'JM INTERNATIONAL') }} Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --brand: #2563eb; --brand-dark: #1d4ed8; }
        * { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        html, body { height: 100%; margin: 0; }
        body { min-height: 100vh; overflow-x: hidden; }

        /* full-bleed wrapper — no container padding, no horizontal scroll */
        .auth-container { padding: 0 !important; max-width: 100% !important; overflow-x: hidden; }
        .auth-wrap { min-height: 100vh; margin: 0; }

        /* Left brand panel */
        .auth-brand {
            background: linear-gradient(150deg, #3b82f6 0%, #2563eb 45%, #1e40af 100%);
            color: #fff;
            position: relative;
            padding: 2.5rem 3rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        /* soft glow blobs */
        .auth-brand::before, .auth-brand::after {
            content: ""; position: absolute; border-radius: 50%; pointer-events: none;
            background: radial-gradient(circle, rgba(255,255,255,.22), transparent 70%);
        }
        .auth-brand::before { width: 420px; height: 420px; top: -140px; right: -120px; }
        .auth-brand::after  { width: 360px; height: 360px; bottom: -150px; left: -120px;
            background: radial-gradient(circle, rgba(99,102,241,.35), transparent 70%); }
        /* dotted pattern overlay */
        .auth-brand .pattern {
            position: absolute; inset: 0; pointer-events: none; opacity: .5;
            background-image: radial-gradient(rgba(255,255,255,.16) 1.4px, transparent 1.4px);
            background-size: 26px 26px;
            -webkit-mask-image: linear-gradient(160deg, #000 10%, transparent 65%);
                    mask-image: linear-gradient(160deg, #000 10%, transparent 65%);
        }
        .auth-brand > * { position: relative; z-index: 1; }

        .auth-brand .logo {
            display: inline-flex; align-items: center; gap: .6rem;
            font-size: 1.3rem; font-weight: 700;
        }
        .auth-brand .logo .badge-icon {
            width: 44px; height: 44px; border-radius: 11px;
            background: #fff; padding: 3px;
            box-shadow: 0 6px 18px rgba(0,0,0,.18);
            display: inline-flex; align-items: center; justify-content: center;
        }
        .auth-brand .logo .badge-icon img { width: 100%; height: 100%; object-fit: contain; }
        .auth-brand h1 { font-size: 2.5rem; font-weight: 800; line-height: 1.12; letter-spacing: -.5px;
            text-shadow: 0 6px 24px rgba(0,0,0,.16); }
        .auth-brand .lead-text { color: rgba(255,255,255,.88); max-width: 26rem; }
        .auth-brand .copyright { color: rgba(255,255,255,.7); font-size: .85rem; }

        /* feature chips */
        .auth-features { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 1rem; }
        .auth-features span {
            font-size: .78rem; font-weight: 500; color: #fff;
            background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22);
            padding: .3rem .7rem; border-radius: 999px; backdrop-filter: blur(2px);
        }
        .auth-features span i { margin-right: .35rem; }

        /* entrance animations */
        @keyframes fade-up { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        .anim-up { animation: fade-up .6s ease both; }
        .anim-up.d1 { animation-delay: .08s; }
        .anim-up.d2 { animation-delay: .16s; }
        .anim-up.d3 { animation-delay: .24s; }
        @media (prefers-reduced-motion: reduce) { .anim-up { animation: none; } }
        .auth-illustration {
            flex: 1 1 auto; min-height: 0; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem 0;
        }
        .auth-illustration svg {
            width: 100%; max-width: 340px; max-height: 100%; height: auto;
            filter: drop-shadow(0 18px 30px rgba(0,0,0,.18));
            animation: float-illus 4s ease-in-out infinite;
        }
        @keyframes float-illus { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @media (prefers-reduced-motion: reduce) { .auth-illustration svg { animation: none; } }

        /* Right form panel */
        .auth-form-panel {
            background: var(--bs-body-bg);
            display: flex; align-items: center; justify-content: center;
            padding: 2rem;
            position: relative;
        }
        .auth-form { width: 100%; max-width: 360px; }
        .auth-form h3 { font-size: 1.5rem; font-weight: 800; letter-spacing: -.3px; }
        .auth-form .form-label { font-size: .82rem; margin-bottom: .3rem; }
        .auth-form .form-control {
            padding: .58rem .85rem; font-size: .88rem; border-radius: .65rem;
            border-color: var(--bs-border-color); transition: border-color .15s, box-shadow .15s;
        }
        .auth-form .form-control:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 .2rem rgba(37,99,235,.18);
        }
        .auth-form .input-group:focus-within .input-group-text { border-color: var(--brand); }
        .auth-form .form-check-label,
        .auth-form a, .auth-form .text-secondary { font-size: .85rem; }
        .auth-form a { text-decoration: none; }
        .auth-form a:hover { text-decoration: underline; }
        .auth-form .btn-brand { font-size: .95rem; }
        .auth-form .demo-box .small,
        .auth-form .demo-box .btn { font-size: .8rem; }
        .btn-brand {
            background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; color: #fff;
            padding: .65rem; font-weight: 600; border-radius: .65rem;
            box-shadow: 0 8px 20px rgba(37,99,235,.32);
            transition: transform .15s ease, box-shadow .15s ease, filter .15s;
        }
        .btn-brand:hover { color: #fff; transform: translateY(-2px); filter: brightness(1.05);
            box-shadow: 0 12px 26px rgba(37,99,235,.42); }
        .btn-brand:active { transform: translateY(0); }
        .req { color: #ef4444; }
        .demo-box { border: 1px solid var(--bs-border-color); border-radius: .7rem; background: var(--bs-tertiary-bg); }
        .demo-box .btn {
            border-radius: .55rem; transition: border-color .15s, color .15s, background .15s, transform .12s;
        }
        .demo-box .btn:hover { border-color: var(--brand); color: var(--brand); background: rgba(37,99,235,.06); transform: translateY(-1px); }
        .theme-toggle { transition: background .15s, border-color .15s; }
        .theme-toggle:hover { background: var(--bs-tertiary-bg); border-color: var(--brand); }
        .theme-toggle {
            position: absolute; top: 1.25rem; right: 1.5rem;
            border: 1px solid var(--bs-border-color); background: transparent;
            width: 38px; height: 38px; border-radius: 9px; color: var(--bs-body-color);
        }
        .pw-toggle { cursor: pointer; }
        [data-bs-theme="dark"] .auth-brand { background: linear-gradient(150deg, #1e3a8a 0%, #1e40af 45%, #312e81 100%); }
    </style>
</head>
<body>
<div class="container-fluid auth-container">
    <div class="row auth-wrap g-0">

        {{-- Left: brand panel --}}
        <div class="col-lg-6 d-none d-lg-flex auth-brand">
            <span class="pattern"></span>
            <div class="logo anim-up">
                <span class="badge-icon"><img src="{{ asset('jm.png') }}" alt="Logo"></span>
                {{ config('app.name', 'JM INTERNATIONAL') }}
            </div>

            {{-- Cartoon illustration --}}
            <div class="auth-illustration">
                <svg viewBox="0 0 420 320" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Inventory illustration">
                    <defs>
                        <linearGradient id="box1" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#ffffff" stop-opacity=".95"/>
                            <stop offset="1" stop-color="#dbe4ff" stop-opacity=".9"/>
                        </linearGradient>
                    </defs>

                    {{-- floating accent dots --}}
                    <circle cx="60" cy="50" r="6" fill="#ffffff" opacity=".4"/>
                    <circle cx="370" cy="80" r="9" fill="#ffd166" opacity=".9"/>
                    <circle cx="350" cy="200" r="5" fill="#ffffff" opacity=".35"/>
                    <circle cx="40" cy="180" r="7" fill="#4ade80" opacity=".85"/>

                    {{-- ground shadow --}}
                    <ellipse cx="210" cy="288" rx="150" ry="16" fill="#1d4ed8" opacity=".45"/>

                    {{-- back box --}}
                    <g>
                        <rect x="118" y="120" width="120" height="100" rx="8" fill="url(#box1)"/>
                        <path d="M118 150 H238" stroke="#93b4ff" stroke-width="3"/>
                        <rect x="158" y="120" width="40" height="20" rx="4" fill="#c7d6ff"/>
                    </g>

                    {{-- right box --}}
                    <g>
                        <rect x="232" y="160" width="96" height="80" rx="8" fill="url(#box1)"/>
                        <path d="M232 186 H328" stroke="#93b4ff" stroke-width="3"/>
                        <rect x="262" y="160" width="36" height="16" rx="4" fill="#c7d6ff"/>
                    </g>

                    {{-- front box --}}
                    <g>
                        <rect x="96" y="186" width="110" height="92" rx="8" fill="#ffffff"/>
                        <path d="M96 214 H206" stroke="#93b4ff" stroke-width="3"/>
                        <rect x="132" y="186" width="38" height="18" rx="4" fill="#c7d6ff"/>
                    </g>

                    {{-- clipboard / checklist --}}
                    <g transform="translate(250 60)">
                        <rect x="0" y="0" width="96" height="120" rx="10" fill="#ffffff"/>
                        <rect x="34" y="-8" width="28" height="18" rx="5" fill="#2563eb"/>
                        <circle cx="18" cy="34" r="7" fill="#4ade80"/>
                        <path d="M14 34 l3 3 l6 -7" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="34" y="29" width="50" height="8" rx="4" fill="#dbe4ff"/>
                        <circle cx="18" cy="62" r="7" fill="#4ade80"/>
                        <path d="M14 62 l3 3 l6 -7" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="34" y="57" width="50" height="8" rx="4" fill="#dbe4ff"/>
                        <circle cx="18" cy="90" r="7" fill="#e5e7eb"/>
                        <rect x="34" y="85" width="40" height="8" rx="4" fill="#eef2ff"/>
                    </g>

                    {{-- magnifier --}}
                    <g transform="translate(40 96)">
                        <circle cx="34" cy="34" r="26" fill="none" stroke="#ffd166" stroke-width="8"/>
                        <line x1="54" y1="54" x2="76" y2="76" stroke="#ffd166" stroke-width="9" stroke-linecap="round"/>
                    </g>
                </svg>
            </div>

            <div class="anim-up d2">
                <h1 class="mb-3">Inventory<br>Management System</h1>
                <p class="lead-text mb-0">
                    Track products, stock levels, batches, expiry dates, and
                    supplier relationships — all in one secure place.
                </p>
                <div class="auth-features">
                    <span><i class="bi bi-box-seam"></i>Stock tracking</span>
                    <span><i class="bi bi-calendar-check"></i>Expiry alerts</span>
                    <span><i class="bi bi-graph-up-arrow"></i>Live reports</span>
                </div>
            </div>

            <div class="copyright mt-4 anim-up d3">&copy; {{ date('Y') }} {{ config('app.name', 'JM INTERNATIONAL') }}. All rights reserved.</div>
        </div>

        {{-- Right: form panel --}}
        <div class="col-lg-6 auth-form-panel">
            <button type="button" class="theme-toggle" id="themeToggle" title="Toggle theme">
                <i class="bi bi-moon-stars"></i>
            </button>

            <div class="auth-form anim-up d1">
                <h3 class="fw-bold mb-1">Welcome back</h3>
                <p class="text-secondary mb-4">Sign in to your inventory dashboard</p>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username or email <span class="req">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="form-control" placeholder="admin@example.com" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="req">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="password"
                                   class="form-control border-end-0" placeholder="Enter your password" required>
                            <span class="input-group-text bg-transparent pw-toggle" id="pwToggle">
                                <i class="bi bi-eye" id="pwIcon"></i>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check mb-0">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember" checked>
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="#" class="text-decoration-none small">Forgot password?</a>
                    </div>

                    <button class="btn btn-brand w-100 mb-4">Sign in</button>
                </form>

                {{-- Demo accounts --}}
                <div class="demo-box p-3 mb-3">
                    <p class="small text-secondary mb-2">
                        Demo accounts (password: <span class="text-primary fw-semibold">password</span>)
                    </p>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill demo-fill"
                                data-email="admin@example.com">Admin</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill demo-fill"
                                data-email="cashier@example.com">Cashier</button>
                    </div>
                </div>

                <p class="text-center text-secondary small mb-0">
                    <i class="bi bi-shield-lock me-1"></i>Secured with bank-grade encryption
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Password visibility toggle
    const pwToggle = document.getElementById('pwToggle');
    const pwInput = document.getElementById('password');
    const pwIcon = document.getElementById('pwIcon');
    pwToggle.addEventListener('click', () => {
        const show = pwInput.type === 'password';
        pwInput.type = show ? 'text' : 'password';
        pwIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    // Demo account quick-fill
    document.querySelectorAll('.demo-fill').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelector('input[name="email"]').value = btn.dataset.email;
            pwInput.value = 'password';
        });
    });

    // Theme toggle (persisted)
    const html = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const saved = localStorage.getItem('auth-theme');
    if (saved) html.setAttribute('data-bs-theme', saved);
    themeToggle.addEventListener('click', () => {
        const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('auth-theme', next);
        themeToggle.querySelector('i').className = next === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
    });
</script>
</body>
</html>
