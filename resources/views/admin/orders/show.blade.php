@extends('layouts.admin')

@section('title', 'Pedido '.$order->order_number)

@section('content')
<main class="admin-main admin-order-detail">
    <a href="{{ route('admin.pedidos.index') }}" class="admin-back-link">← Volver a pedidos</a>
    <p class="admin-muted admin-order-delivery-note">{{ $order->fulfillment_method === 'pickup' ? 'Recojo en tienda · Miguel Iglesias 991, Cajamarca' : 'Delivery · costo a cargo del cliente, se coordina aparte' }}</p>
    <div class="admin-page-heading admin-order-heading"><div><p class="admin-kicker">Pedido / {{ $order->order_number }}</p><h1>{{ $order->customer_name }}</h1><p class="admin-muted">Registrado el {{ $order->created_at->format('d/m/Y \a las H:i') }}</p></div><span class="admin-status admin-status-{{ $order->status }}">{{ $currentStatusLabel }}</span></div>
    @if ($nextStatus)
        <section class="admin-order-stage">
            <div>
                <p class="admin-order-stage-kicker">Siguiente etapa</p>
                <h2>{{ $nextStage }}</h2>
                <p>{{ $order->status === 'pending_validation' ? 'Valida el pago para iniciar el empaque.' : ($order->status === 'pending_payment' ? 'Confirma el pedido contra entrega para iniciar el empaque.' : 'Avanza el pedido según la modalidad de entrega seleccionada.') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.pedidos.update', $order) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $nextStatus }}">
                <button type="submit" class="admin-submit">{{ $order->status === 'pending_validation' ? 'Validar pago y preparar pedido' : ($order->status === 'pending_payment' ? 'Confirmar y preparar pedido' : 'Avanzar a '.$nextStage) }} <span>↗</span></button>
            </form>
        </section>
    @endif
    <div class="admin-order-layout"><section class="admin-order-card"><div class="admin-order-card-title"><span>01</span><div><h2>Resumen de compra</h2><p>Productos incluidos en este pedido.</p></div></div><div class="admin-order-products">@foreach ($order->items as $item)<div><div class="admin-order-product-image">@if (! empty($item['image']))<img src="{{ asset('storage/'.$item['image']) }}" alt="">@else<span>✦</span>@endif</div><div><strong>{{ $item['name'] }}</strong><small>{{ $item['quantity'] }} unidad(es) · S/ {{ number_format($item['price'], 2, ',', '.') }} c/u</small></div><b>S/ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</b></div>@endforeach</div><div class="admin-order-total"><span>Total de venta</span><strong>S/ {{ number_format($order->total, 2, ',', '.') }}</strong></div></section><aside class="admin-order-card"><div class="admin-order-card-title"><span>02</span><div><h2>Validar pedido</h2><p>Actualiza el avance de la venta.</p></div></div><form method="POST" action="{{ route('admin.pedidos.update', $order) }}" class="admin-order-status-form">@csrf @method('PATCH')<label>Estado del pedido<select name="status">@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>@endforeach</select></label><button type="submit" class="admin-submit">Guardar estado <span>↗</span></button></form><div class="admin-payment-box"><span>Método de pago</span><strong>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</strong>@if ($order->payment_account_holder)<small>Titular: {{ $order->payment_account_holder }}</small><small>Operación: {{ $order->payment_operation_number }}</small>@else<small>Pago coordinado contra entrega.</small>@endif</div></aside><section class="admin-order-card admin-customer-card"><div class="admin-order-card-title"><span>03</span><div><h2>Datos del cliente y entrega</h2><p>Información para validar y coordinar.</p></div></div><div class="admin-customer-grid"><div><span>DNI</span><strong>{{ $order->customer_dni }}</strong></div><div><span>Teléfono</span><strong>{{ $order->customer_phone }}</strong></div><div><span>Correo</span><strong>{{ $order->customer_email }}</strong></div><div><span>Departamento</span><strong>{{ $order->department }}</strong></div><div><span>Provincia</span><strong>{{ $order->province }}</strong></div><div><span>Distrito</span><strong>{{ $order->district }}</strong></div><div class="admin-customer-address"><span>Dirección exacta</span><strong>{{ $order->customer_address }}</strong></div></div></section></div>
</main>
@endsection
