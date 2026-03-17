<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') | {{ config('app.name', 'Restaurant API') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page" data-public-auth>
        <div class="auth-shell">
            <div class="auth-brand auth-reveal">
                <span class="auth-brand-mark" aria-hidden="true"></span>
                <span>Selera Nusantara</span>
            </div>

            <div class="auth-grid">
                <section class="auth-panel auth-reveal auth-reveal-delay">
                    <div class="auth-eyebrow">Nusantara Dish For Everyone</div>
                    <h1 class="auth-title">@yield('hero_title')</h1>
                    <p class="auth-subtitle">@yield('hero_text')</p>

                    <div class="auth-highlight">
                        <article class="auth-highlight-card">
                            <strong>Pandan calm</strong>
                            <span>Soft greens and warm neutrals keep the screen grounded and restaurant-friendly.</span>
                        </article>
                        <article class="auth-highlight-card">
                            <strong>Sambal contrast</strong>
                            <span>Spice-toned accents drive the call to action and make the forms feel alive.</span>
                        </article>
                        <article class="auth-highlight-card">
                            <strong>Kopitiam comfort</strong>
                            <span>Rounded cards and layered gradients borrow the warmth of a late breakfast table.</span>
                        </article>
                    </div>

                    <div class="dish-board">
                        <article class="dish-card dish-card--lemak">
                            <h3>Nasi lemak</h3>
                            <p>Coconut rice tones, sambal warmth, and crisp structure set the overall visual direction.</p>
                        </article>
                        <article class="dish-card dish-card--satay">
                            <h3>Satay</h3>
                            <p>Toasted peanut and charred amber hues push the primary buttons and spotlight moments.</p>
                        </article>
                        <article class="dish-card dish-card--kuih">
                            <h3>Kuih lapis</h3>
                            <p>Layered surfaces and soft green notes make the supporting cards feel playful, not generic.</p>
                        </article>
                        <article class="dish-card dish-card--teh">
                            <h3>Teh tarik</h3>
                            <p>Milk-tea gradients and smooth motion help the page land with a familiar local warmth.</p>
                        </article>
                    </div>
                </section>

                <aside class="auth-form-panel auth-reveal auth-reveal-delay">
                    <nav class="auth-switch" aria-label="Authentication pages">
                        <a href="{{ route('login') }}"    class="{{ request()->routeIs('login')    ? 'is-active' : '' }}">Login</a>
                        <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'is-active' : '' }}">Register</a>
                    </nav>

                    <div class="auth-card">
                        @yield('form_intro')
                        @yield('form')
                        <div class="auth-footer-note">
                            Experience authentic Nusantara flavors with rich spices, traditional recipes, and warm hospitality. Perfect for family gatherings, dining, and unforgettable meals.
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <script>
        (() => {
            // ── Redirect if already logged in ─────────────────────────
            const existing = localStorage.getItem('token');
            const userData = localStorage.getItem('user');
            if (existing && userData) {
                const user = JSON.parse(userData);
                redirectByRole(user.role);
                return;
            }

            // ── Handle auth form submit ───────────────────────────────
            const form = document.querySelector('[data-auth-form]');
            if (!form) return;

            const feedback  = form.querySelector('[data-auth-feedback]');
            const tokenBox  = form.querySelector('[data-auth-token]');
            const submitBtn = form.querySelector('[type="submit"]');
            const endpoint  = form.dataset.endpoint;
            const isLogin   = form.dataset.authForm === 'login';

            form.addEventListener('submit', async e => {
                e.preventDefault();
                submitBtn.disabled = true;
                clearErrors();

                const body = {};
                new FormData(form).forEach((v, k) => body[k] = v);

                try {
                    const res  = await fetch(endpoint, {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body:    JSON.stringify(body),
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        // Laravel validation errors come back as { errors: { field: [...] } }
                        if (data.errors) {
                            Object.entries(data.errors).forEach(([field, msgs]) => {
                                const el = form.querySelector(`[data-error-for="${field}"]`);
                                if (el) { el.textContent = msgs[0]; el.classList.add('is-visible'); }
                            });
                        }
                        showFeedback(data.message ?? 'Something went wrong.', 'error');
                        return;
                    }

                    const token = data.token;
                    if (!token) { showFeedback('No token returned.', 'error'); return; }

                    // Save token then fetch the user profile to know the role
                    localStorage.setItem('token', token);

                    const meRes  = await fetch('/api/me', {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
                    });
                    const user = await meRes.json();
                    localStorage.setItem('user', JSON.stringify(user));

                    showFeedback(isLogin ? 'Logged in! Redirecting…' : 'Account created! Redirecting…', 'success');

                    setTimeout(() => redirectByRole(user.role), 800);

                } catch {
                    showFeedback('Network error. Please try again.', 'error');
                } finally {
                    submitBtn.disabled = false;
                }
            });

            function redirectByRole(role) {
                const map = {
                    admin:    '/admin/menu',
                    staff:    '/staff/orders',
                    customer: '/customer/orders',
                };
                window.location.href = map[role] ?? '/login';
            }

            function showFeedback(msg, type) {
                if (!feedback) return;
                feedback.textContent = msg;
                feedback.className   = `auth-feedback is-visible ${type === 'error' ? 'is-error' : 'is-success'}`;
            }

            function clearErrors() {
                form.querySelectorAll('[data-error-for]').forEach(el => {
                    el.textContent = '';
                    el.classList.remove('is-visible');
                });
                if (feedback) feedback.className = 'auth-feedback';
            }
        })();
        </script>
    </body>
</html>