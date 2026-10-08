@extends('layouts.store')

@section('title', 'Soluciones que impulsan tu mundo')

@section('content')
@php
    $whatsappBase = 'https://wa.me/51967151428';
    $sideServices = [
        ['class' => 'side-title-left', 'label' => 'Mantenimiento de Computadoras', 'message' => 'Hola Ventura Global Technology, quiero información sobre el servicio de Mantenimiento de Computadoras'],
        ['class' => 'side-title-right', 'label' => 'Instalación y Soporte de Software', 'message' => 'Hola Ventura Global Technology, quiero información sobre el servicio de Instalación y Soporte de Software'],
    ];
@endphp
<style>
    .hero-logo { border-radius: 50%; animation: logo-disc-spin 24s linear infinite, logo-glow 3.6s ease-in-out infinite; }
    @keyframes logo-disc-spin { to { transform: rotate(360deg); } }
    @keyframes logo-glow { 0%, 100% { filter: drop-shadow(0 0 6px rgba(23, 212, 207, .35)) drop-shadow(0 10px 18px rgba(18, 91, 98, .22)) saturate(1.05); } 50% { filter: drop-shadow(0 0 20px rgba(23, 212, 207, .75)) drop-shadow(0 0 40px rgba(91, 140, 255, .35)) drop-shadow(0 10px 18px rgba(18, 91, 98, .22)) saturate(1.2) brightness(1.06); } }
    .hero-card-main { overflow: hidden; }
    .hero-card-main::after { position: absolute; top: -20%; left: -70%; z-index: 2; width: 45%; height: 140%; transform: skewX(-20deg); background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .75), transparent); content: ''; pointer-events: none; animation: card-shine 6s ease-in-out infinite; }
    @keyframes card-shine { 0%, 60% { left: -70%; } 100% { left: 150%; } }
    @media (prefers-reduced-motion: reduce) { .hero-logo { animation: none; } .hero-card-main::after { animation: none; display: none; } }
    .side-title { position: fixed; top: 50%; z-index: 30; display: flex; align-items: center; gap: .8rem; padding: 1.5rem .85rem; writing-mode: vertical-rl; color: #fff; font-size: .8rem; font-weight: 900; letter-spacing: .22em; text-transform: uppercase; white-space: nowrap; text-decoration: none; border: 2px solid rgba(255, 255, 255, .85); background: linear-gradient(180deg, #17d4cf, #079b9d 55%, #0a7f95); box-shadow: 0 0 0 4px rgba(23, 212, 207, .22), 0 18px 40px rgba(7, 155, 157, .45); transition: padding .25s ease, box-shadow .25s ease, filter .25s ease; animation: side-title-glow 2.8s ease-in-out infinite; }
    .side-title svg { width: 1.35rem; height: 1.35rem; flex: none; fill: currentColor; writing-mode: horizontal-tb; }
    .side-title-left { left: 0; transform: translateY(-50%) rotate(180deg); border-right: 0; border-radius: 1.1rem 0 0 1.1rem; }
    .side-title-left svg { transform: rotate(180deg); }
    .side-title-right { right: 0; transform: translateY(-50%); border-right: 0; border-radius: 1.1rem 0 0 1.1rem; }
    .side-title:hover, .side-title:focus-visible { padding-block: 1.9rem; filter: brightness(1.08); box-shadow: 0 0 0 6px rgba(23, 212, 207, .35), 0 22px 50px rgba(7, 155, 157, .6); outline: none; }
    @keyframes side-title-glow { 0%, 100% { box-shadow: 0 0 0 4px rgba(23, 212, 207, .22), 0 18px 40px rgba(7, 155, 157, .45); } 50% { box-shadow: 0 0 0 9px rgba(23, 212, 207, .1), 0 18px 46px rgba(7, 155, 157, .65); } }
    @keyframes side-title-attention { 0%, 100% { filter: brightness(1); } 25%, 75% { filter: brightness(1.25); box-shadow: 0 0 0 14px rgba(23, 212, 207, .4), 0 22px 54px rgba(7, 155, 157, .8); } }
    body:has(#servicios:target) .side-title { animation: side-title-attention 1.2s ease-in-out 3; }
    @media (prefers-reduced-motion: reduce) { .side-title, body:has(#servicios:target) .side-title { animation: none; } }
    @media (max-width: 1279px) { .side-title { display: none; } }
</style>
@foreach ($sideServices as $service)
    <a class="side-title {{ $service['class'] }}" href="{{ $whatsappBase }}?text={{ rawurlencode($service['message']) }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $service['label'] }}: contactar por WhatsApp">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.9L2 22l5.25-1.38A9.9 9.9 0 1 0 12.04 2Zm5.8 14.05c-.25.7-1.45 1.34-2 1.4-.52.06-1.17.09-1.9-.12a17 17 0 0 1-1.72-.64c-3.03-1.3-5-4.37-5.15-4.57-.15-.2-1.23-1.64-1.23-3.13s.78-2.22 1.06-2.52c.27-.3.6-.38.8-.38h.58c.19 0 .43-.07.67.5.25.6.84 2.07.92 2.22.07.15.12.32.02.52-.1.2-.15.32-.3.5-.15.17-.31.38-.44.51-.15.15-.3.31-.13.6.17.3.77 1.27 1.65 2.05 1.13 1 2.09 1.31 2.39 1.46.3.15.47.12.65-.07.17-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.27.1 1.73.82 2.03.97.3.15.5.22.57.35.07.12.07.72-.18 1.42Z"/></svg>
        <span>{{ $service['label'] }}</span>
    </a>
@endforeach
<main>
    <section class="hero-shell">
        <div class="hero-glow hero-glow-one"></div><div class="hero-glow hero-glow-two"></div>
        <div class="galaxy-field" aria-hidden="true">@for ($particle = 0; $particle < 100; $particle++)<span @if ($particle >= 42) style="--x: {{ (($particle * 37) % 95) + 2 }}%; --y: {{ (($particle * 61) % 90) + 4 }}%; --size: {{ $particle % 11 === 0 ? 4 : ($particle % 5 === 0 ? 3 : 2) }}px; --speed: {{ 7 + ($particle % 8) }}s; --twinkle: {{ 2.4 + (($particle % 6) * .3) }}s; --delay: -{{ 1 + ($particle % 12) }}s; --twinkle-delay: -{{ 1 + ($particle % 5) }}s;" @endif></span>@endfor</div>
        <div class="mx-auto grid max-w-7xl gap-12 px-6 pt-16 pb-3 lg:grid-cols-[1.05fr_.95fr] lg:items-center lg:px-8 lg:py-16">
            <div class="relative z-10">
                <p class="eyebrow"><span class="eyebrow-line"></span> Ventura Global Technology</p>
                <h1 class="hero-title">La tecnología que <em>eleva</em> tu forma de avanzar.</h1>
                <p class="hero-copy">Una selección cuidada de soluciones digitales, accesorios y soporte para que cada idea llegue más lejos.</p>
                <div class="mt-8 flex flex-wrap items-center gap-4"><a href="#productos" class="primary-button">Descubrir la colección <span>↗</span></a><a href="#historia" class="secondary-button">Conócenos <span>↓</span></a></div>
                <div class="mt-12 flex items-center gap-8 border-t border-white/15 pt-6 text-sm text-slate-300"><div><strong class="block text-2xl text-white">+4</strong><span>soluciones únicas</span></div><div><strong class="block text-2xl text-white">100%</strong><span>atención cercana</span></div></div>
            </div>
            <div class="hero-visual">
                <div class="hero-orbit hero-orbit-one"></div><div class="hero-orbit hero-orbit-two"></div>
                <div class="hero-card-main"><img src="{{ asset('images/logo.png') }}" alt="Logo Ventura Global Technology" class="hero-logo"><span class="hero-card-label">Est. 2026 · Perú</span></div>
                <div class="hero-floating-card"><span class="floating-icon">✦</span><span><strong>Diseñado para ti</strong><small>Simple. Útil. Extraordinario.</small></span></div>
                <div class="hero-feature-rail" aria-label="Tecnología y servicios especializados">
                    <div class="hero-feature-item">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="7" y="7" width="10" height="10" rx="2"></rect><path d="M9 2v3m6-3v3M9 19v3m6-3v3M2 9h3m-3 6h3m14-6h3m-3 6h3M10 10h4v4h-4z"></path></svg>
                        <span>Tecnología con propósito</span>
                    </div>
                    <div class="hero-feature-item">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m12 3 8 9-8 9-8-9 8-9Z"></path><path d="m9 12 2 2 4-4"></path></svg>
                        <span>Entregas coordinadas</span>
                    </div>
                    <div class="hero-feature-item">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v8a2.5 2.5 0 0 1-2.5 2.5H11l-5 4v-4.3A2.5 2.5 0 0 1 4 13.5v-8Z"></path><path d="M8 9c1.1-1.2 2.2 1.2 3.3 0s2.2 1.2 3.3 0"></path></svg>
                        <span>Atención cercana</span>
                    </div>
                </div>
                <div class="hero-service-note hero-service-note-ai">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3v2m0 14v2M3 12h2m14 0h2M5.64 5.64l1.42 1.42m9.88 9.88 1.42 1.42m0-12.72-1.42 1.42m-9.88 9.88-1.42 1.42"></path><circle cx="12" cy="12" r="5"></circle><path d="M10 12h4m-2-2v4"></path></svg>
                    <span>IA APLICADA</span>
                </div>
                <div class="hero-service-note hero-service-note-systems">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="4" y="4" width="16" height="6" rx="1.5"></rect><rect x="4" y="14" width="16" height="6" rx="1.5"></rect><path d="M8 7h.01M8 17h.01M12 7h4m-4 10h4"></path></svg>
                    <span>INGENIERÍA DE SISTEMAS</span>
                </div>
                <span class="hero-side-note">INNOVACIÓN<br><i>CON PROPÓSITO</i></span>
            </div>
        </div>
    </section>

    <section class="trust-strip"><div><span class="trust-icon">◈</span><strong>Selección consciente</strong><span>Solo lo que aporta valor</span></div><div><span class="trust-icon">↗</span><strong>Envío cuidado</strong><span>De nuestra puerta a la tuya</span></div><div><span class="trust-icon">∞</span><strong>Soporte real</strong><span>Personas, no respuestas automáticas</span></div></section>

    <section id="productos" class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
        <div class="mb-10 flex items-end justify-between gap-4">
            <div><p class="eyebrow eyebrow-light">Nuestra selección</p><h2 class="section-title">Pequeñas cosas.<br><em>Grandes diferencias.</em></h2></div><span class="hidden max-w-xs text-right text-sm leading-6 text-slate-500 sm:block">Objetos y servicios elegidos para vivir la tecnología de una forma más humana.</span>
        </div>
        <div class="category-filter-shell" data-category-filter-shell>
            <div class="category-filter-heading">
                <div>
                    <span class="category-filter-kicker">Explora por universo</span>
                    <p class="category-filter-title">Encuentra lo que <em>imaginas.</em></p>
                    <p class="category-filter-description">Entra en una sección y descubre sus categorías, paso a paso.</p>
                </div>
                <span class="category-result-count" data-category-result-count>{{ $products->where('offer_percentage', '>', 0)->filter(fn ($product) => $product->offer_ends_at?->isFuture())->count() }} ofertas</span>
            </div>

            <div class="category-browser-toolbar">
                <button type="button" class="category-offers-button is-active" data-category-filter="__offers__" aria-pressed="true">
                    <span aria-hidden="true">✦</span><span>Ofertas</span>
                    <small>{{ $products->where('offer_percentage', '>', 0)->filter(fn ($product) => $product->offer_ends_at?->isFuture())->count() }}</small>
                </button>
                <nav class="category-breadcrumbs" data-category-breadcrumbs aria-label="Ruta de navegación por categorías"></nav>
                <div class="category-product-search">
                    <label for="catalog-product-search">Buscar productos</label>
                    <div class="category-product-search-control">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path></svg>
                        <input id="catalog-product-search" type="search" placeholder="Nombre, categoría o descripción…" autocomplete="off" aria-describedby="catalog-product-search-hint" data-product-search>
                    </div>
                    <small id="catalog-product-search-hint" data-product-search-hint>Encuentra productos aunque escribas un nombre parecido.</small>
                </div>
            </div>

            <div class="category-level-heading">
                <div>
                    <span class="category-filter-kicker" data-category-level-kicker>CATÁLOGO</span>
                    <h3 data-category-level-title>Secciones principales</h3>
                    <p data-category-level-description>Selecciona un universo para explorar sus subcategorías.</p>
                </div>
                <span class="category-level-count" data-category-level-count></span>
            </div>

            <div class="category-explorer" data-category-explorer aria-live="polite"></div>
            <div class="category-route-index" data-category-route-index hidden aria-hidden="true">
                @foreach ($categories as $category)
                    <span data-category-route="{{ $category }}" data-category-count="{{ $categoryCounts[$category] ?? 0 }}"></span>
                @endforeach
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4" data-product-grid>
            @forelse ($products as $product)
                @php($productImages = collect([$product->image, ...($product->images ?? [])])->filter()->unique()->values())
                @php($productCategoryLabel = collect(explode(' > ', $product->category))->last())
                <article class="product-card group" data-product-card data-product-category="{{ $product->category }}" data-product-offer="{{ $product->isOnOffer() ? 'true' : 'false' }}" data-product-search-text="{{ $product->name }} {{ $product->description }} {{ $product->category }}" data-product-search-similar="{{ $product->name }} {{ $productCategoryLabel }}">
                    <div class="card-magic" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                    <div class="product-art">
                        @if ($productImages->isNotEmpty())
                            <div class="product-carousel" data-product-carousel>
                                @foreach ($productImages as $image)
                                    <img src="{{ asset('storage/'.$image) }}" alt="{{ $product->name }} · imagen {{ $loop->iteration }}" class="product-image{{ $loop->first ? ' is-active' : '' }}">
                                @endforeach
                            </div>
                        @else
                            <span class="product-initials">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                        @endif
                        <span class="product-number">0{{ $loop->iteration }}</span>
                        <span class="product-category">{{ $productCategoryLabel }}</span>
                        @if ($product->isOnOffer())<span class="offer-badge">OFERTA · -{{ $product->offer_percentage }}%</span>@endif
                    </div>
                    <div class="product-details p-5">
                        <div class="product-meta"><span class="availability-dot">● Disponible · {{ $product->stock }} uds.</span></div>
                        <h3 class="mt-3 font-bold text-slate-900">{{ $product->name }}</h3>
                        <p class="mt-2 min-h-12 text-sm leading-6 text-slate-500">{{ $product->description }}</p>
                        @if ($product->isOnOffer())
                            <div class="offer-countdown" data-countdown="{{ $product->offer_ends_at->toIso8601String() }}"><span>Oferta termina en</span><strong><b data-hours>00</b><small>h</small><b data-minutes>00</b><small>m</small><b data-seconds>00</b><small>s</small></strong></div>
                        @endif
                        <div class="mt-4 flex items-end justify-between gap-3">
                            <div>
                                @if ($product->isOnOffer())
                                    <span class="normal-price-label">Precio normal</span>
                                    <del class="original-price">S/ {{ number_format($product->original_price, 2, ',', '.') }}</del>
                                    <span class="promo-price-label">Precio promocional</span>
                                @endif
                                <span class="block text-lg font-black text-slate-900">S/ {{ number_format($product->price, 2, ',', '.') }}</span>
                            </div>
                            <form method="POST" action="{{ route('cart.add', $product) }}" data-cart-form>
                                @csrf
                                <button class="add-button" aria-label="Añadir {{ $product->name }} al carrito"><span class="cart-spark">✦</span><span class="add-button-label">Añadir</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H7M10 20a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm9 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/></svg></button></form></div></div>
                </article>
            @empty
                <p class="text-slate-500">Aún no hay productos disponibles.</p>
            @endforelse
        </div>
        <div class="category-empty-state" data-category-empty hidden><span>✦</span><strong data-product-empty-title>Este universo aún está expandiéndose</strong><p data-product-empty-copy>No hay productos disponibles en esta categoría por ahora.</p><button type="button" data-category-reset data-product-empty-reset>Ver ofertas</button></div>
    </section>

    <section id="servicios" class="services-section mx-auto mb-20 max-w-7xl px-6 lg:px-8"><div class="services-particle-field" aria-hidden="true">@for ($particle = 0; $particle < 36; $particle++)<i style="--x: {{ (($particle * 29) % 94) + 3 }}%; --y: {{ (($particle * 47) % 86) + 5 }}%; --size: {{ $particle % 9 === 0 ? 4 : ($particle % 4 === 0 ? 3 : 2) }}px; --speed: {{ 6 + ($particle % 8) }}s; --delay: -{{ 1 + ($particle % 10) }}s;"></i>@endfor</div><div class="services-heading"><div><p class="eyebrow eyebrow-light">Ingeniería de sistemas</p><h2 class="section-title">Ideas digitales que se convierten en <em>soluciones.</em></h2></div><p>Diseñamos tecnología útil, escalable y pensada para que tu operación avance con claridad.</p></div><div class="services-grid"><article class="service-card service-card-featured"><span class="service-index">01</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v8a2.5 2.5 0 0 1-2.5 2.5H11l-5 4v-4.3A2.5 2.5 0 0 1 4 13.5v-8Z"></path><path d="M8 9c1.1-1.2 2.2 1.2 3.3 0s2.2 1.2 3.3 0"></path></svg><h3>Páginas web</h3><p>Experiencias web rápidas, elegantes y preparadas para convertir visitas en oportunidades.</p><a href="https://wa.me/51967151428?text=Hola%20Ventura%2C%20quiero%20cotizar%20una%20p%C3%A1gina%20web" target="_blank" rel="noopener">Conversemos <span>↗</span></a></article><article class="service-card"><span class="service-index">02</span><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="16" height="6" rx="1.5"></rect><rect x="4" y="14" width="16" height="6" rx="1.5"></rect><path d="M8 7h.01M8 17h.01M12 7h4m-4 10h4"></path></svg><h3>Sistemas empresariales</h3><p>Software a medida para ordenar procesos, datos, inventario y decisiones.</p><a href="https://wa.me/51967151428?text=Hola%20Ventura%2C%20quiero%20cotizar%20un%20sistema" target="_blank" rel="noopener">Explorar solución <span>↗</span></a></article><article class="service-card"><span class="service-index">03</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a7 7 0 1 0 7 7"></path><path d="M12 3v4m0 0h4"></path><path d="M5 18.5A9 9 0 0 0 21 12"></path></svg><h3>Automatización e IA</h3><p>Flujos inteligentes que reducen tareas repetitivas y multiplican tu capacidad.</p><a href="https://wa.me/51967151428?text=Hola%20Ventura%2C%20quiero%20conocer%20sus%20servicios%20de%20IA" target="_blank" rel="noopener">Hablar con un experto <span>↗</span></a></article><article class="service-card"><span class="service-index">04</span><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M12 2v3m0 14v3M2 12h3m14 0h3m-16.1-7.1 2.1 2.1m9.9 9.9 2.1 2.1m0-14.1-2.1 2.1m-9.9 9.9-2.1 2.1"></path></svg><h3>Arquitectura y soporte</h3><p>Acompañamiento técnico para integrar, mantener y hacer crecer tus soluciones.</p><a href="https://wa.me/51967151428?text=Hola%20Ventura%2C%20quiero%20asesor%C3%ADa%20tecnol%C3%B3gica" target="_blank" rel="noopener">Solicitar asesoría <span>↗</span></a></article></div></section>

    <section id="historia" class="mx-auto mb-20 max-w-7xl px-6 lg:px-8"><div class="story-panel"><div><p class="eyebrow">Nuestra visión</p><h2 class="mt-4 max-w-xl text-3xl font-black leading-tight text-white md:text-4xl">Lo digital también puede sentirse <em>cercano.</em></h2></div><div class="max-w-md"><p class="leading-7 text-slate-300">Creemos en una tecnología que acompaña, simplifica y abre posibilidades. Por eso cada elección de Ventura nace de una pregunta: ¿cómo puede hacer tu día un poco mejor?</p><a href="#productos" class="mt-6 inline-flex font-bold text-cyan-300 transition hover:text-white">Ver la colección <span class="ml-2">↗</span></a></div></div></section>
</main>
<div class="scroll-progress" data-scroll-progress aria-hidden="true"></div>
<style>
    .scroll-progress { position: fixed; top: 0; left: 0; z-index: 80; width: 100%; height: 3px; transform: scaleX(0); transform-origin: left; background: linear-gradient(90deg, #17d4cf, #8ce8e5, #5b8cff); box-shadow: 0 0 12px rgba(23, 212, 207, .7); pointer-events: none; }
    .will-reveal { opacity: 0; transform: translateY(28px); transition: opacity .8s cubic-bezier(.2, .7, .2, 1), transform .8s cubic-bezier(.2, .7, .2, 1); transition-delay: var(--reveal-delay, 0s); }
    .will-reveal.is-visible { opacity: 1; transform: none; }
    .card-spot { position: absolute; inset: 0; z-index: 2; border-radius: inherit; opacity: 0; pointer-events: none; background: radial-gradient(260px circle at var(--mx, 50%) var(--my, 50%), rgba(23, 212, 207, .22), transparent 65%); transition: opacity .3s ease; }
    .has-spot:hover > .card-spot { opacity: 1; }
    .tilt-card { transition: transform .25s ease, box-shadow .3s ease; will-change: transform; }
    .primary-button { position: relative; overflow: hidden; }
    .primary-button::after { position: absolute; top: 0; left: -80%; width: 50%; height: 100%; transform: skewX(-22deg); background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .45), transparent); content: ''; animation: button-shine 4.5s ease-in-out infinite; pointer-events: none; }
    @keyframes button-shine { 0%, 55% { left: -80%; } 100% { left: 140%; } }
    @media (prefers-reduced-motion: reduce) { .will-reveal { opacity: 1; transform: none; transition: none; } .primary-button::after { animation: none; } .scroll-progress { display: none; } }
</style>
<script>
    (() => {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const progress = document.querySelector('[data-scroll-progress]');
        const updateProgress = () => {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            progress.style.transform = `scaleX(${max > 0 ? Math.min(window.scrollY / max, 1) : 0})`;
        };
        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();

        if (!reduceMotion && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: .12 });
            document.querySelectorAll('.trust-strip > div, .services-heading, .service-card, .story-panel, #productos .section-title').forEach((element, index) => {
                element.classList.add('will-reveal');
                element.style.setProperty('--reveal-delay', `${(index % 4) * .09}s`);
                observer.observe(element);
            });
        }

        if (!reduceMotion && window.matchMedia('(hover: hover)').matches) {
            const cardSelector = '.service-card, .product-card';
            document.addEventListener('pointermove', (event) => {
                const card = event.target.closest?.(cardSelector);
                if (!card) {
                    return;
                }
                if (!card.querySelector(':scope > .card-spot')) {
                    if (getComputedStyle(card).position === 'static') {
                        card.style.position = 'relative';
                    }
                    card.classList.add('has-spot', 'tilt-card');
                    card.insertAdjacentHTML('afterbegin', '<span class="card-spot" aria-hidden="true"></span>');
                }
                const rect = card.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;
                card.style.setProperty('--mx', `${x}px`);
                card.style.setProperty('--my', `${y}px`);
                const rotateY = ((x / rect.width) - .5) * 7;
                const rotateX = (.5 - (y / rect.height)) * 7;
                card.style.transform = `perspective(900px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
            });
            document.addEventListener('pointerout', (event) => {
                const card = event.target.closest?.(cardSelector);
                if (card && !card.contains(event.relatedTarget)) {
                    card.style.transform = '';
                }
            });
        }
    })();
</script>
@endsection
