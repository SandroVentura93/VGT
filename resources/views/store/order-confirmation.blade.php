@extends('layouts.store')

@section('title', 'Pedido recibido')

@section('content')
<main class="order-confirmation-page">
    <section class="confirmation-card" aria-labelledby="confirmation-title">
        <header class="confirmation-header">
            <div class="confirmation-icon" aria-hidden="true">✓</div>
            <div class="confirmation-heading">
                <p class="cart-kicker">Pedido recibido correctamente</p>
                <h1 id="confirmation-title">Gracias por confiar en nosotros.</h1>
                <p class="confirmation-copy">Hemos registrado tu pedido <strong>{{ $order->order_number }}</strong>.</p>
            </div>
        </header>

        <div class="confirmation-next-step">{{ $confirmationNextStep }}</div>

        <section class="confirmation-details" aria-label="Detalles del pedido">
            <div class="confirmation-detail-card"><span>Cliente</span><strong>{{ $order->customer_name }}</strong></div>
            <div class="confirmation-detail-card"><span>Método de pago</span><strong>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</strong></div>
            <div class="confirmation-detail-card"><span>Modalidad</span><strong>{{ $order->fulfillment_method === 'pickup' ? 'Recojo en tienda' : 'Delivery' }}</strong></div>
            <div class="confirmation-detail-card"><span>Total</span><strong>S/ {{ number_format($order->total, 2, ',', '.') }}</strong></div>
            <div class="confirmation-detail-card"><span>{{ $order->fulfillment_method === 'pickup' ? 'Punto de recojo' : 'Delivery' }}</span><strong>{{ $order->fulfillment_method === 'pickup' ? 'Miguel Iglesias 991, Cajamarca' : 'Costo a cargo del cliente (se coordina aparte)' }}</strong></div>
            <div class="confirmation-detail-card"><span>Estado</span><strong>{{ $statusLabel }}</strong></div>
        </section>

        <a href="{{ route('store.home') }}" class="cart-back-button">Volver a la tienda <span aria-hidden="true">↗</span></a>
    </section>
</main>
@endsection
