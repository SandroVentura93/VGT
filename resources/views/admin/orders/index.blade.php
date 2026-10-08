@extends('layouts.admin')

@section('title', 'Pedidos')

@section('content')
<main class="admin-main">
    <div class="admin-page-heading"><div><p class="admin-kicker">Ventas / Gestión</p><h1>Pedidos</h1><p class="admin-muted">Revisa compras, valida pagos y acompaña cada entrega.</p></div></div>
    <div class="admin-order-stats"><div><span>Validar pagos</span><strong>{{ $stats['pendingValidation'] }}</strong><small>pendientes</small></div><div><span>Contra entrega</span><strong>{{ $stats['pendingPayment'] }}</strong><small>por coordinar</small></div><div><span>Ventas confirmadas</span><strong>S/ {{ number_format($stats['paidTotal'], 2, ',', '.') }}</strong><small>acumulado</small></div></div>
    <form class="admin-order-search" method="GET" action="{{ route('admin.pedidos.index') }}">
        @if ($selectedStatus !== '')
            <input type="hidden" name="status" value="{{ $selectedStatus }}">
        @endif
        <label for="order-search">Buscar por nombre o DNI</label>
        <div class="admin-order-search-control">
            <input id="order-search" type="search" name="search" value="{{ $selectedSearch }}" placeholder="Escribe el nombre o DNI del cliente" autocomplete="off" data-auto-submit-search>
            <button type="submit" aria-label="Buscar pedidos">Buscar</button>
        </div>
        <small>Los resultados se filtran mientras escribes.</small>
    </form>
    <div class="admin-order-filters"><a href="{{ route('admin.pedidos.index', ['search' => $selectedSearch !== '' ? $selectedSearch : null]) }}" class="{{ $selectedStatus === '' ? 'is-active' : '' }}">Todos <strong data-orders-total>{{ $orders->total() }}</strong></a>@foreach ($statuses as $value => $label)<a href="{{ route('admin.pedidos.index', ['status' => $value, 'search' => $selectedSearch !== '' ? $selectedSearch : null]) }}" class="{{ $selectedStatus === $value ? 'is-active' : '' }}">{{ $label }}</a>@endforeach</div>
    @include('admin.orders.partials.results')
</main>
@endsection
