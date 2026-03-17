<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Restaurant Nusantara</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ── Drawer-specific tokens (additive, no overrides) ──── */
        :root {
            --drawer-w: 280px;
            --top-h:    62px;
        }

        /* ── Topbar ───────────────────────────────────────────── */
        .topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--top-h);
            z-index: 200;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0 24px;
            background: rgba(255, 250, 242, 0.82);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--line);
        }

        .topbar-logo {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--ink);
            flex: 1;
            letter-spacing: -0.02em;
        }
        .topbar-logo span { color: var(--sambal); }

        .topbar-role {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 999px;
            background: rgba(70, 106, 73, 0.1);
            color: var(--pandan);
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border: 1px solid rgba(70, 106, 73, 0.18);
        }

        /* ── Hamburger ────────────────────────────────────────── */
        .drawer-toggle {
            width: 36px; height: 36px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid var(--line);
            border-radius: 12px;
            cursor: pointer;
            padding: 0;
            transition: background 180ms ease, border-color 180ms ease;
            flex-shrink: 0;
        }
        .drawer-toggle:hover {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(81, 47, 31, 0.22);
        }
        .drawer-toggle span {
            display: block;
            width: 16px; height: 1.5px;
            background: var(--muted);
            border-radius: 2px;
            transition: transform 240ms ease, opacity 240ms ease, width 240ms ease;
            transform-origin: center;
        }
        .drawer-toggle.is-open span:nth-child(1) { transform: translateY(6.5px) rotate(45deg); }
        .drawer-toggle.is-open span:nth-child(2) { opacity: 0; width: 0; }
        .drawer-toggle.is-open span:nth-child(3) { transform: translateY(-6.5px) rotate(-45deg); }

        /* ── Backdrop ─────────────────────────────────────────── */
        .drawer-backdrop {
            position: fixed;
            inset: 0;
            z-index: 248;
            background: rgba(36, 21, 15, 0.38);
            backdrop-filter: blur(3px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 260ms ease;
        }
        .drawer-backdrop.is-open { opacity: 1; pointer-events: all; }

        /* ── Drawer ───────────────────────────────────────────── */
        .drawer {
            position: fixed;
            top: 0; left: 0;
            width: var(--drawer-w);
            height: 100%;
            z-index: 249;
            display: flex;
            flex-direction: column;
            padding-top: var(--top-h);
            background: rgba(255, 252, 246, 0.97);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--line);
            box-shadow: 8px 0 40px rgba(78, 43, 20, 0.13);
            transform: translateX(-100%);
            transition: transform 280ms cubic-bezier(0.4, 0, 0.2, 1);
        }
        .drawer.is-open { transform: translateX(0); }

        .drawer-inner {
            flex: 1;
            overflow-y: auto;
            padding: 28px 18px 18px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        /* User card */
        .drawer-user {
            margin-bottom: 20px;
            padding: 16px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.62);
            border: 1px solid rgba(81, 47, 31, 0.1);
        }
        .drawer-user-name  { font-weight: 600; font-size: 0.95rem; color: var(--ink); margin-bottom: 3px; }
        .drawer-user-email { font-size: 0.82rem; color: var(--muted); }
        .drawer-user-badge {
            display: inline-flex;
            margin-top: 10px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(70, 106, 73, 0.12);
            color: var(--pandan);
        }

        .drawer-section-label {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 0 10px;
            margin: 14px 0 6px;
            opacity: 0.65;
        }
        .drawer-section-label:first-child { margin-top: 0; }

        .drawer-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 16px;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 500;
            border: 1px solid transparent;
            transition: background 160ms ease, color 160ms ease, border-color 160ms ease;
        }
        .drawer-link:hover {
            background: rgba(255, 255, 255, 0.72);
            color: var(--ink);
            border-color: rgba(81, 47, 31, 0.1);
        }
        .drawer-link.is-active {
            background: linear-gradient(135deg, var(--ink), #4f2e1f);
            color: #fff8ee;
            border-color: transparent;
            box-shadow: 0 10px 20px rgba(36, 21, 15, 0.16);
        }
        .drawer-link svg        { flex-shrink: 0; opacity: 0.65; }
        .drawer-link.is-active svg { opacity: 1; }

        /* Footer / logout */
        .drawer-footer {
            padding: 18px;
            border-top: 1px solid var(--line);
        }
        .drawer-logout {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 11px 14px;
            border-radius: 16px;
            border: 1px solid rgba(166, 59, 34, 0.18);
            background: rgba(254, 242, 238, 0.7);
            color: var(--sambal);
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 160ms ease, border-color 160ms ease;
        }
        .drawer-logout:hover {
            background: rgba(253, 230, 222, 0.9);
            border-color: rgba(166, 59, 34, 0.3);
        }

        /* ── Page shell ───────────────────────────────────────── */
        .page-wrap { padding-top: var(--top-h); min-height: 100vh; }

        /* ── Hero ─────────────────────────────────────────────── */
        .page-hero {
            padding: 40px 40px 34px;
            border-bottom: 1px solid var(--line);
            background:
                linear-gradient(160deg, rgba(255, 252, 246, 0.86), rgba(248, 236, 212, 0.7)),
                linear-gradient(120deg, rgba(70, 106, 73, 0.06), rgba(166, 59, 34, 0.06));
            position: relative;
            overflow: hidden;
        }
        .page-hero::after {
            content: '';
            position: absolute;
            right: -80px; bottom: -80px;
            width: 280px; height: 280px;
            border-radius: 40% 60% 55% 45% / 50% 45% 55% 50%;
            background: linear-gradient(135deg, rgba(70,106,73,.1), rgba(166,59,34,.06));
            pointer-events: none;
        }
        .hero-eyebrow {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--pandan);
            margin-bottom: 10px;
        }
        .hero-title {
            font-family: var(--font-display);
            font-size: clamp(2rem, 3.5vw, 3.2rem);
            font-weight: 700;
            line-height: 1.0;
            letter-spacing: -0.03em;
            color: var(--ink);
            margin-bottom: 12px;
            max-width: 22ch;
        }
        .hero-text {
            font-size: 0.96rem;
            color: var(--muted);
            line-height: 1.75;
            max-width: 58ch;
        }

        /* ── Main content area ────────────────────────────────── */
        .page-content { padding: 32px 40px 60px; }

        @media (max-width: 1180px) {
            .page-hero    { padding: 28px 24px 26px; }
            .page-content { padding: 24px 24px 48px; }
        }
        @media (max-width: 720px) {
            .page-hero    { padding: 22px 18px 22px; }
            .page-content { padding: 18px 18px 40px; }
        }
    </style>
</head>
<body class="panel-page">

{{-- ── Topbar ─────────────────────────────────────────────────── --}}
<header class="topbar">
    <button class="drawer-toggle" id="drawerToggle"
            aria-label="Toggle navigation menu"
            aria-expanded="false"
            aria-controls="drawer">
        <span></span><span></span><span></span>
    </button>

    <div class="topbar-logo">Restaurant<span>Nusantara</span></div>

    <span class="topbar-role">
        {{ auth()->user()->role ?? 'Guest' }}
    </span>
</header>

{{-- ── Backdrop ─────────────────────────────────────────────────── --}}
<div class="drawer-backdrop" id="drawerBackdrop" aria-hidden="true"></div>

{{-- ── Drawer ───────────────────────────────────────────────────── --}}
<nav class="drawer" id="drawer" role="navigation" aria-label="Site navigation">

    <div class="drawer-inner">

        {{-- Authenticated user card ──────────────────────────── --}}
        @auth
            <div class="drawer-user">
                <div class="drawer-user-name">{{ auth()->user()->name }}</div>
                <div class="drawer-user-email">{{ auth()->user()->email }}</div>
                <span class="drawer-user-badge">{{ auth()->user()->role }}</span>
            </div>
        @endauth

        <span class="drawer-section-label">Navigation</span>

        @php $role = auth()->user()->role ?? null; @endphp

        {{-- Customer ─── My Orders only ─────────────────────── --}}
        @if($role === 'customer')
            <a href="{{ route('customer.orders') }}"
               class="drawer-link {{ request()->routeIs('customer.orders*') ? 'is-active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                    <path d="M9 12h6M9 16h4"/>
                </svg>
                My Orders
            </a>
        @endif

        {{-- Staff ─── Order Management only ─────────────────── --}}
        @if($role === 'staff')
            <a href="{{ route('staff.orders') }}"
               class="drawer-link {{ request()->routeIs('staff.orders*') ? 'is-active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                    <path d="M9 12h6M9 16h4"/>
                </svg>
                Order Management
            </a>
        @endif

        {{-- Admin ─── Menu Management + All Orders ──────────── --}}
        @if($role === 'admin')
            <a href="{{ route('admin.menu') }}"
               class="drawer-link {{ request()->routeIs('admin.menu*') ? 'is-active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Menu Management
            </a>
            <a href="{{ route('admin.orders') }}"
               class="drawer-link {{ request()->routeIs('admin.orders*') ? 'is-active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                    <path d="M9 12h6M9 16h4"/>
                </svg>
                All Orders
            </a>
        @endif

    </div>{{-- /drawer-inner --}}

    <div class="drawer-footer">
        <button type="button" class="drawer-logout" id="logoutBtn">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
            </svg>
            Sign out
        </button>
    </div>

</nav>{{-- /drawer --}}

{{-- ── Page content ────────────────────────────────────────────── --}}
<div class="page-wrap">

    <div class="page-hero">
        <p class="hero-eyebrow">@yield('hero_eyebrow')</p>
        <h1 class="hero-title">@yield('hero_title')</h1>
        <p class="hero-text">@yield('hero_text')</p>
    </div>

    <main class="page-content">
        @yield('content')
    </main>

</div>{{-- /page-wrap --}}

<script>
    (() => {
        const toggle   = document.getElementById('drawerToggle');
        const drawer   = document.getElementById('drawer');
        const backdrop = document.getElementById('drawerBackdrop');

        const open = () => {
            drawer.classList.add('is-open');
            backdrop.classList.add('is-open');
            toggle.classList.add('is-open');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        };

        const close = () => {
            drawer.classList.remove('is-open');
            backdrop.classList.remove('is-open');
            toggle.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        };

        toggle.addEventListener('click', () =>
            drawer.classList.contains('is-open') ? close() : open()
        );

        backdrop.addEventListener('click', close);

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
        });

        // ── Logout ────────────────────────────────────────────────
        document.getElementById('logoutBtn').addEventListener('click', async () => {
            const token = localStorage.getItem('restaurant-api-token') || localStorage.getItem('token');

            try {
                if (token) {
                    await fetch('/api/logout', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                        },
                    });
                }
            } catch {
                // Continue with local cleanup even if API logout fails.
            } finally {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                localStorage.removeItem('restaurant-api-token');
                localStorage.removeItem('restaurant-api-user');
                sessionStorage.clear();
                window.location.assign('{{ route('login') }}');
            }
        });
    })();
</script>

@stack('scripts')

</body>
</html>