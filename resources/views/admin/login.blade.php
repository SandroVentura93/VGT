<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Acceso · Ventura Global</title><link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}"><link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="admin-login-page">
    <div class="admin-login-glow"></div>
    <div class="admin-galaxy" aria-hidden="true">@for ($particle = 0; $particle < 26; $particle++)<span style="--x: {{ ($particle * 37 + 7) % 100 }}%; --y: {{ ($particle * 61 + 11) % 100 }}%; --delay: -{{ ($particle % 9) + 1 }}s; --size: {{ ($particle % 3) + 2 }}px;"></span>@endfor</div>
    <main class="admin-login-card">
        <div class="admin-login-symbol"><img src="{{ asset('images/logo.png') }}" alt="Ventura Global Technology"></div>
        <p class="admin-kicker">Ventura Global Technology</p>
        <h1>Panel de control</h1>
        <p class="admin-login-copy">Gestiona tu colección, actualiza detalles y publica novedades en segundos.</p>
        <form method="POST" action="{{ route('admin.login.store') }}" class="admin-form">@csrf
            <label for="username">Usuario</label>
            <input id="username" name="username" type="text" required autofocus autocomplete="username" placeholder="Usuario administrador">
            <label for="password">Contraseña de administración</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Introduce tu contraseña">
            <button type="submit" class="admin-submit">Entrar al panel <span>↗</span></button>
        </form>
        <a href="{{ route('store.home') }}" class="admin-back-link">← Volver a la tienda</a>
    </main>
    @include('admin.partials.footer')
</body>
</html>
