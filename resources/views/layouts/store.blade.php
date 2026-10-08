<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media (hover: hover) and (pointer: fine) {
            html, body { cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Cpath d='M3 2l9.5 24 3.2-9.3L25 13.5z' fill='%230b1928' stroke='%2317d4cf' stroke-width='1.8' stroke-linejoin='round'/%3E%3Crect x='17' y='17' width='11' height='11' rx='2' fill='%2317d4cf' stroke='%230b1928' stroke-width='1.4'/%3E%3Cpath d='M20 17v-2.5m5 2.5v-2.5m-5 13.5v2.5m5-2.5v2.5M17 20h-2.5m2.5 5h-2.5m13.5-5h2.5m-2.5 5h2.5' stroke='%2317d4cf' stroke-width='1.3' stroke-linecap='round'/%3E%3Crect x='20.5' y='20.5' width='4' height='4' rx='.8' fill='%230b1928'/%3E%3C/svg%3E") 3 2, auto; }
            a, button, [role="button"], summary, label[for], select, input[type="submit"], input[type="button"], input[type="checkbox"], input[type="radio"] { cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='13' fill='%2317d4cf' fill-opacity='.22' stroke='%2317d4cf' stroke-width='1.8'/%3E%3Cpath d='M9 16h4m6 0h4M16 9v4m0 6v4' stroke='%230b1928' stroke-width='1.8' stroke-linecap='round'/%3E%3Ccircle cx='16' cy='16' r='3.2' fill='%230b1928' stroke='%2317d4cf' stroke-width='1.4'/%3E%3C/svg%3E") 16 16, pointer; }
            input[type="text"], input[type="search"], input[type="email"], input[type="tel"], input[type="number"], input[type="password"], textarea { cursor: text; }
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}"><link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
</head>
<body class="min-h-screen antialiased">
    <header class="site-header">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <a href="{{ route('store.home') }}" class="brand-mark" aria-label="Ventura Global Technology">
                <span class="brand-symbol"><img src="{{ asset('images/logo.png') }}" alt="" aria-hidden="true"></span>
                <span class="brand-name"><strong>VENTURA</strong><small>GLOBAL TECHNOLOGY</small></span>
            </a>
            <nav class="magic-nav hidden md:flex">
                <a href="{{ route('store.home') }}#productos" class="nav-link"><span>Colección</span><i></i></a>
                <a href="{{ route('store.home') }}#servicios" class="nav-link"><span>Servicios</span><i></i></a>
                <a href="{{ route('store.home') }}#historia" class="nav-link"><span>Nuestra visión</span><i></i></a>
                <button type="button" class="nav-link nav-button" data-open-order-tracking><span>Seguimiento</span><i></i></button>
                <a href="{{ route('cart.index') }}" class="cart-link"><span>Carrito</span><span class="cart-count" data-cart-count>{{ collect(session('cart', []))->sum('quantity') }}</span></a>
            </nav>
            <div class="mobile-header-actions">
                <a href="{{ route('cart.index') }}" class="cart-link mobile-cart-link" aria-label="Ver carrito"><span>Carrito</span><span class="cart-count" data-cart-count>{{ collect(session('cart', []))->sum('quantity') }}</span></a>
                <button type="button" class="mobile-menu-toggle" data-mobile-menu-toggle aria-expanded="false" aria-controls="mobile-store-navigation" aria-label="Abrir menú de navegación">
                    <span>Menú</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"></path></svg>
                </button>
            </div>
            <nav id="mobile-store-navigation" class="mobile-site-nav" data-mobile-site-nav aria-label="Navegación principal" hidden>
                <a class="mobile-site-nav-link" href="{{ route('store.home') }}#productos"><span>Colección</span><small>Explora nuestros productos</small><span class="mobile-site-nav-arrow" aria-hidden="true">↗</span></a>
                <a class="mobile-site-nav-link" href="{{ route('store.home') }}#servicios"><span>Servicios</span><small>Soluciones para tu negocio</small><span class="mobile-site-nav-arrow" aria-hidden="true">↗</span></a>
                <a class="mobile-site-nav-link" href="{{ route('store.home') }}#historia"><span>Nuestra visión</span><small>Conoce a Ventura</small><span class="mobile-site-nav-arrow" aria-hidden="true">↗</span></a>
                <button type="button" class="mobile-site-nav-link" data-open-order-tracking><span>Seguimiento</span><small>Consulta el estado de tu pedido</small><span class="mobile-site-nav-arrow" aria-hidden="true">↗</span></button>
                <a class="mobile-site-nav-link mobile-site-nav-cart" href="{{ route('cart.index') }}"><span>Tu carrito</span><small>{{ collect(session('cart', []))->sum('quantity') }} productos</small><span class="mobile-site-nav-arrow" aria-hidden="true">↗</span></a>
            </nav>
        </div>
    </header>
    @if (session('success'))
        <div class="mx-auto mt-5 max-w-7xl px-6">
            <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
        </div>
    @endif
    @if (session('error'))
        <div class="mx-auto mt-5 max-w-7xl px-6"><div class="rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ session('error') }}</div></div>
    @endif
    @yield('content')
    @php
        $footerSocialLinks = [
            ['name' => 'WhatsApp', 'url' => config('services.social.whatsapp_url')],
            ['name' => 'Instagram', 'url' => config('services.social.instagram_url')],
            ['name' => 'Facebook', 'url' => config('services.social.facebook_url')],
            ['name' => 'TikTok', 'url' => config('services.social.tiktok_url')],
            ['name' => 'YouTube', 'url' => config('services.social.youtube_url')],
            ['name' => 'LinkedIn', 'url' => config('services.social.linkedin_url')],
            ['name' => 'X', 'url' => config('services.social.x_url')],
        ];
    @endphp
    <footer class="site-footer footer-premium">
        <div class="footer-premium-glow footer-premium-glow-one" aria-hidden="true"></div>
        <div class="footer-premium-glow footer-premium-glow-two" aria-hidden="true"></div>
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="footer-premium-grid">
                <section class="footer-brand-panel" aria-label="Ventura Global Technology">
                    <a href="{{ route('store.home') }}" class="footer-brand-link" aria-label="Ventura Global Technology, ir al inicio">
                        <span class="footer-brand-emblem"><img src="{{ asset('images/logo.png') }}" alt="" aria-hidden="true"></span>
                        <span class="footer-brand-name"><strong>VENTURA</strong><small>GLOBAL TECHNOLOGY</small></span>
                    </a>
                    <p class="footer-brand-copy">Tecnología para avanzar con intención. Soluciones cercanas, ideas que conectan y atención de persona a persona.</p>
                    <span class="footer-brand-status"><i aria-hidden="true"></i> Cajamarca · Perú</span>
                </section>

                <nav class="footer-explore" aria-label="Enlaces de la tienda">
                    <p class="footer-column-kicker">Explora</p>
                    <a href="{{ route('store.home') }}#productos">Colección <span aria-hidden="true">↗</span></a>
                    <a href="{{ route('store.home') }}#servicios">Servicios <span aria-hidden="true">↗</span></a>
                    <a href="{{ route('store.home') }}#historia">Nuestra visión <span aria-hidden="true">↗</span></a>
                    <button type="button" data-open-order-tracking>Seguimiento de pedidos <span aria-hidden="true">↗</span></button>
                    <a href="{{ route('cart.index') }}">Tu carrito <span aria-hidden="true">↗</span></a>
                </nav>

                <section class="footer-contact" aria-label="Contacto telefónico">
                    <p class="footer-column-kicker">Contacto directo</p>
                    <h2>¿Hablamos de tu próximo proyecto?</h2>
                    <p>Escríbenos o llámanos. Te ayudamos a encontrar la solución indicada.</p>
                    <a class="footer-contact-link" href="tel:{{ config('services.social.phone_uri') }}" aria-label="Llamar a Ventura Global Technology al {{ config('services.social.phone_label') }}">
                        <span class="footer-contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M7.2 3.8h2.9l1.4 4-2 1.8a15 15 0 0 0 5.2 5.2l1.8-2 4 1.4v2.9a2 2 0 0 1-2.2 2A16.5 16.5 0 0 1 5.2 6a2 2 0 0 1 2-2.2Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <span><small>Llámanos</small><strong>{{ config('services.social.phone_label') }}</strong></span>
                        <span class="footer-contact-arrow" aria-hidden="true">↗</span>
                    </a>
                </section>

                <section class="footer-social" aria-label="Redes y mensajería">
                    <p class="footer-column-kicker">Conecta con nosotros</p>
                    <div class="footer-social-grid">
                        @foreach ($footerSocialLinks as $socialLink)
                            @if (filled($socialLink['url']))
                                <a class="footer-social-icon-link" href="{{ $socialLink['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $socialLink['name'] }}" aria-label="Abrir {{ $socialLink['name'] }} de Ventura Global Technology">
                                    @include('store.partials.social-icon', ['platform' => $socialLink['name']])
                                </a>
                            @else
                                <span class="footer-social-icon-link is-unconfigured" title="Configura enlace de {{ $socialLink['name'] }} en .env" aria-label="{{ $socialLink['name'] }}: configura su enlace en el archivo .env" role="img">
                                    @include('store.partials.social-icon', ['platform' => $socialLink['name']])
                                </span>
                            @endif
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="footer-premium-bottom footer-bottom-aligned">
                <span>© {{ date('Y') }} VENTURA GLOBAL TECHNOLOGY</span>
                <div class="footer-bottom-center">
                    <a class="footer-made-by" href="https://wa.me/51967151428?text={{ rawurlencode('Hola Ventura Global Technology, vi su trabajo en la web y quiero cotizar un proyecto') }}" target="_blank" rel="noopener noreferrer" aria-label="Diseñado y desarrollado por Ventura Global Technology: cotiza tu proyecto por WhatsApp">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 8-4 4 4 4m8-8 4 4-4 4m-2.5-11-3 14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>Diseñado y desarrollado con <i aria-hidden="true">♥</i> por <strong>Ventura Global Technology</strong></span>
                    </a>
                    <p class="footer-tagline"><i aria-hidden="true"></i> Tecnología para avanzar con intención</p>
                </div>
                <a class="footer-back-link" href="{{ route('store.home') }}" aria-label="Volver al inicio">Volver al inicio <span aria-hidden="true">↑</span></a>
            </div>
            <style>
                .footer-bottom-aligned { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 1rem; }
                .footer-bottom-aligned > .footer-back-link { justify-self: end; }
                .footer-bottom-center { display: flex; flex-direction: column; align-items: center; gap: .15rem; }
                @media (max-width: 960px) { .footer-bottom-aligned { grid-template-columns: 1fr; justify-items: center; text-align: center; } .footer-bottom-aligned > .footer-back-link { justify-self: center; } }
                .footer-tagline { display: flex; align-items: center; justify-content: center; gap: .45rem; margin: 0; padding: 0; color: #b4c9d0; font-size: .7rem; letter-spacing: .08em; text-align: center; }
                .footer-tagline i { width: .34rem; height: .34rem; border-radius: 50%; background: #60d5cf; box-shadow: 0 0 9px rgba(96, 213, 207, .72); }
                .footer-made-by { display: flex; align-items: center; justify-content: center; gap: .6rem; margin: 0; padding: .3rem 0; color: #8fa7b0; font-size: .72rem; letter-spacing: .06em; text-align: center; text-decoration: none; transition: color .2s ease; }
                .footer-made-by:hover, .footer-made-by:focus-visible { color: #d6eef0; outline: none; }
                .footer-made-by:hover strong, .footer-made-by:focus-visible strong { text-shadow: 0 0 18px rgba(96, 213, 207, .55); }
                .footer-made-by svg { width: 1.1rem; height: 1.1rem; flex: none; color: #60d5cf; }
                .footer-made-by strong { background: linear-gradient(90deg, #8ce8e5, #5b8cff, #c58bff, #8ce8e5); background-size: 250% 100%; -webkit-background-clip: text; background-clip: text; color: transparent; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; animation: made-by-shift 6s linear infinite; }
                .footer-made-by i { display: inline; width: auto; height: auto; padding: 0; border: 0; border-radius: 0; background: none; box-shadow: none; color: #ff6b8b; font-style: normal; }
                @keyframes made-by-shift { to { background-position: 250% 0; } }
                @media (prefers-reduced-motion: reduce) { .footer-made-by strong { animation: none; } }
            </style>
        </div>
    </footer>
    <div class="order-tracking-modal" data-order-tracking-modal aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="order-tracking-title">
        <div class="order-tracking-card">
            <button type="button" class="order-tracking-close" data-close-order-tracking aria-label="Cerrar seguimiento">×</button>
            <p class="cart-kicker"><span>✦</span> Tu pedido, siempre cerca</p><h2 id="order-tracking-title">Seguimiento de pedidos</h2><p class="order-tracking-copy">Ingresa tu DNI y teléfono para consultar tus pedidos.</p>
            <form method="POST" action="{{ route('tracking.orders') }}" data-order-tracking-form>
                @csrf
                <label>DNI<input name="customer_dni" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" required></label>
                <label>Teléfono<input name="customer_phone" type="tel" maxlength="40" required></label>
                <button type="submit" class="order-tracking-submit"><span data-order-tracking-submit-label>Consultar pedidos</span><span class="order-tracking-spinner" data-order-tracking-spinner aria-hidden="true"></span></button>
            </form>
            <p class="order-tracking-error" data-order-tracking-error role="alert"></p><div class="order-tracking-results" data-order-tracking-results aria-live="polite"></div>
        </div>
    </div>
    @if (filled(config('services.social.whatsapp_url')))
        <a class="whatsapp-sales-float" href="{{ config('services.social.whatsapp_url') }}" target="_blank" rel="noopener noreferrer" aria-label="Contactar con un asesor por WhatsApp" title="Contactar con un asesor">
            <span class="whatsapp-sales-float-icon">@include('store.partials.social-icon', ['platform' => 'WhatsApp'])</span>
            <span>Contactar con un asesor</span>
            <span class="whatsapp-sales-float-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></span>
        </a>
    @endif
    <canvas class="comet-trail" data-comet-trail aria-hidden="true"></canvas>
    <style>
        .comet-trail { position: fixed; inset: 0; z-index: 9999; width: 100vw; height: 100vh; pointer-events: none; }
        @media (hover: none), (pointer: coarse), (prefers-reduced-motion: reduce) { .comet-trail { display: none; } }
        @media (max-width: 768px) {
            .whatsapp-sales-float { right: max(.8rem, env(safe-area-inset-right)); bottom: max(.8rem, env(safe-area-inset-bottom)); width: 3.3rem; height: 3.3rem; min-height: 0; justify-content: center; gap: 0; padding: 0; border-radius: 50%; }
            .whatsapp-sales-float > span:not(.whatsapp-sales-float-icon) { display: none; }
            .whatsapp-sales-float-icon { width: 100%; height: 100%; flex-basis: auto; border: 0; background: transparent; }
            .whatsapp-sales-float-icon .footer-social-icon { width: 1.7rem; height: 1.7rem; }
        }
    </style>
    <script>
        (() => {
            const canvas = document.querySelector('[data-comet-trail]');
            if (!canvas || !window.matchMedia('(hover: hover) and (pointer: fine)').matches || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            const ctx = canvas.getContext('2d');
            const particles = [];
            const maxParticles = 700;
            const tailX = 9;
            const tailY = 21;
            let width = 0;
            let height = 0;
            let running = false;
            let last = null;

            const resize = () => {
                const ratio = window.devicePixelRatio || 1;
                width = window.innerWidth;
                height = window.innerHeight;
                canvas.width = width * ratio;
                canvas.height = height * ratio;
                ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            };

            const draw = (now) => {
                ctx.clearRect(0, 0, width, height);
                ctx.globalCompositeOperation = 'lighter';
                for (let i = particles.length - 1; i >= 0; i--) {
                    const p = particles[i];
                    const life = 1 - (now - p.t) / p.ttl;
                    if (life <= 0) {
                        particles.splice(i, 1);
                        if (p.explodes) {
                            const fragments = 5 + Math.floor(Math.random() * 4);
                            for (let n = 0; n < fragments; n++) {
                                const angle = Math.random() * Math.PI * 2;
                                const speed = 1.2 + Math.random() * 2.8;
                                particles.push({ x: p.x, y: p.y, vx: Math.cos(angle) * speed, vy: Math.sin(angle) * speed, size: .5 + Math.random() * .9, hue: 168 + Math.random() * 32, t: now, ttl: 260 + Math.random() * 300, explodes: false });
                            }
                            particles.push({ x: p.x, y: p.y, vx: 0, vy: 0, size: 3.2, hue: p.hue, t: now, ttl: 150, explodes: false });
                        }
                        continue;
                    }
                    p.x += p.vx;
                    p.y += p.vy;
                    p.vx *= .975;
                    p.vy *= .975;
                    const radius = p.size * life;
                    const glow = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, radius * 2.4);
                    glow.addColorStop(0, `hsla(${p.hue}, 55%, 80%, ${life * .85})`);
                    glow.addColorStop(.35, `hsla(${p.hue}, 50%, 56%, ${life * .35})`);
                    glow.addColorStop(1, `hsla(${p.hue}, 50%, 50%, 0)`);
                    ctx.fillStyle = glow;
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, radius * 2.4, 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.globalCompositeOperation = 'source-over';
                if (particles.length) {
                    requestAnimationFrame(draw);
                } else {
                    ctx.clearRect(0, 0, width, height);
                    running = false;
                }
            };

            window.addEventListener('pointermove', (event) => {
                if (event.pointerType !== 'mouse') {
                    return;
                }
                const now = performance.now();
                if (last) {
                    const dx = event.clientX - last.x;
                    const dy = event.clientY - last.y;
                    const distance = Math.hypot(dx, dy);
                    if (distance > 0.5) {
                        const dirX = dx / distance;
                        const dirY = dy / distance;
                        const count = Math.min(9, Math.ceil(distance / 3));
                        for (let n = 0; n < count; n++) {
                            const along = n / count;
                            const spread = (Math.random() - .5) * 10;
                            particles.push({
                                x: last.x + tailX + dx * along - dirX * 2 - dirY * spread,
                                y: last.y + tailY + dy * along - dirY * 2 + dirX * spread,
                                vx: -dirX * (1 + Math.random() * 2.4) + (Math.random() - .5) * 1.1,
                                vy: -dirY * (1 + Math.random() * 2.4) + (Math.random() - .5) * 1.1,
                                size: .6 + Math.random() * 1.1,
                                hue: 172 + Math.random() * 26,
                                t: now,
                                ttl: 800 + Math.random() * 700,
                                explodes: Math.random() < .35,
                            });
                        }
                        if (particles.length > maxParticles) {
                            particles.splice(0, particles.length - maxParticles);
                        }
                        if (!running) {
                            running = true;
                            requestAnimationFrame(draw);
                        }
                    }
                }
                last = { x: event.clientX, y: event.clientY };
            }, { passive: true });
            document.addEventListener('pointerleave', () => { last = null; });
            window.addEventListener('resize', resize);
            resize();
        })();
    </script>
    <script>
        (() => {
            const base = document.title;
            const labels = { productos: 'Productos', servicios: 'Servicios', historia: 'Nosotros' };
            const sections = Object.keys(labels).map((id) => document.getElementById(id)).filter(Boolean);
            if (!sections.length) {
                return;
            }
            const update = () => {
                const line = window.innerHeight * .4;
                let current = null;
                sections.forEach((section) => {
                    if (section.getBoundingClientRect().top <= line) {
                        current = section;
                    }
                });
                document.title = current ? `${base} · ${labels[current.id]}` : base;
            };
            window.addEventListener('scroll', update, { passive: true });
            update();
        })();
    </script>
</body></html>