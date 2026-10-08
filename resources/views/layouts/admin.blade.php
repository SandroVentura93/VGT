<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administración') · Ventura Global</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}"><link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
</head>
<body class="admin-body">
    <header class="admin-header">
        <a href="{{ route('admin.productos.index') }}" class="admin-brand"><span class="admin-brand-icon"><img src="{{ asset('images/logo.png') }}" alt=""></span><span><strong>VENTURA</strong><small>CONTROL CENTER</small></span></a>
        <div class="admin-header-actions">
            <nav class="admin-navigation" id="admin-navigation" data-admin-navigation aria-label="Menú de administración">
                <a href="{{ route('admin.productos.index') }}" @class(['admin-navigation-link', 'is-current' => request()->routeIs('admin.productos.*')])>Productos</a>
                <a href="{{ route('admin.pedidos.index') }}" @class(['admin-navigation-link', 'is-current' => request()->routeIs('admin.pedidos.*')])>Pedidos</a>
                <a href="{{ route('admin.categorias.index') }}" @class(['admin-navigation-link', 'is-current' => request()->routeIs('admin.categorias.*')])>Categorías</a>
                <a href="{{ route('store.home') }}" class="admin-navigation-link">Ver tienda <span aria-hidden="true">↗</span></a>
            </nav>
            <button type="button" class="admin-menu-toggle" data-admin-menu-toggle aria-expanded="false" aria-controls="admin-navigation" aria-label="Abrir menú de administración">
                <span>Menú</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"></path></svg>
            </button>
            <form method="POST" action="{{ route('admin.logout') }}" class="admin-header-logout">@csrf<button class="admin-logout">Salir</button></form>
        </div>
    </header>
    @if (session('success'))<div class="admin-alert admin-alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="admin-alert admin-alert-error"><strong>Revisa los datos:</strong> {{ $errors->first() }}</div>@endif
    @yield('content')
    @include('admin.partials.footer')
</body>
</html>
