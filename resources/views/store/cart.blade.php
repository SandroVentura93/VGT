@extends('layouts.store')

@section('title', 'Carrito')

@section('content')
<main class="cart-page mx-auto max-w-6xl px-6 py-14 lg:py-20" data-cart-page data-store-home="{{ route('store.home') }}">
    <div class="cart-heading"><div><p class="cart-kicker"><span>✦</span> Tu selección</p><h1>Carrito de compra</h1><p class="cart-subtitle">Lo que elegiste para llevar tu día un poco más lejos.</p></div><div class="cart-heading-orbit" aria-hidden="true"><span>✦</span><i></i><i></i></div></div>
    @if (count($cart))
        <div class="cart-layout"><div class="cart-items-panel">
            @foreach ($cart as $itemId => $item)
                <div class="cart-item" data-cart-item="{{ $itemId }}">
                    @if (! empty($item['image']))<div class="cart-item-image"><img src="{{ asset('storage/'.$item['image']) }}" alt="{{ $item['name'] }}"></div>@else<div class="cart-item-icon" aria-hidden="true">✦</div>@endif<div class="cart-item-info"><span class="cart-item-category">{{ $item['category'] ?? 'Tecnología' }}</span><h2>{{ $item['name'] }}</h2><p data-cart-item-description>{{ $item['quantity'] }} unidad(es) · S/ {{ number_format($item['price'], 2, ',', '.') }} c/u</p><div class="cart-quantity-controls"><form method="POST" action="{{ route('cart.decrease', $itemId) }}" data-cart-item-form><input type="hidden" name="_token" value="{{ csrf_token() }}"><button type="submit" aria-label="Disminuir {{ $item['name'] }}">−</button></form><strong data-cart-quantity>{{ $item['quantity'] }}</strong><form method="POST" action="{{ route('cart.add', $itemId) }}" data-cart-item-form><input type="hidden" name="_token" value="{{ csrf_token() }}"><button type="submit" aria-label="Agregar otra unidad de {{ $item['name'] }}">+</button></form></div></div><div class="cart-item-side"><span class="cart-item-price" data-cart-item-total>S/ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</span><form method="POST" action="{{ route('cart.remove', $itemId) }}" data-cart-item-form><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button class="cart-remove-button" type="submit">Eliminar</button></form></div>
                </div>
            @endforeach
            </div><aside class="cart-summary"><span class="cart-summary-label">Resumen de tu pedido</span><h2>Todo listo para<br><em>despegar.</em></h2><div class="cart-summary-line"><span>Productos</span><strong data-cart-total-items>{{ collect($cart)->sum('quantity') }} unidad(es)</strong></div><div class="cart-summary-total"><span>Total</span><strong data-cart-total>S/ {{ number_format($total, 2, ',', '.') }}</strong></div><a href="{{ route('checkout.create') }}" class="cart-checkout-button">Continuar al pago <span>↗</span></a><p class="cart-secure-note">✦ Compra segura · Atención personalizada</p></aside></div>
    @else
        <div class="cart-empty"><div class="cart-empty-icon" aria-hidden="true"><span>✦</span></div><p class="cart-empty-kicker">Tu próxima elección te espera</p><h2>Tu carrito está vacío.</h2><p>Añade algo especial y deja que la tecnología acompañe tu próximo paso.</p><a href="{{ route('store.home') }}" class="cart-back-button">Explorar la colección <span>↗</span></a></div>
    @endif
</main>
@endsection
