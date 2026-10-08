@extends('layouts.store')

@section('title', 'Finalizar pedido')

@section('content')
<main class="checkout-page mx-auto px-6 py-10 lg:py-14">
    <header class="checkout-heading">
        <div class="checkout-heading-copy">
            <p class="cart-kicker"><span>✦</span> Último paso</p>
            <h1>Finaliza tu pedido</h1>
            <p>Completa tus datos y elige cómo recibir y pagar tu pedido.</p>
        </div>
        <ol class="checkout-progress" aria-label="Pasos para completar tu pedido">
            <li class="is-current" data-checkout-progress-step="1" aria-current="step"><span>01</span><small>Datos</small></li>
            <li data-checkout-progress-step="2"><span>02</span><small>Entrega</small></li>
            <li data-checkout-progress-step="3"><span>03</span><small>Pago</small></li>
        </ol>
    </header>

    <form method="POST" action="{{ route('checkout.store') }}" class="checkout-layout">
        @csrf
        <section class="checkout-form-panel">
            <section class="checkout-step checkout-personal-step" data-checkout-step="1">
                <div class="checkout-section-title">
                    <span>01</span>
                    <div><h2>Datos personales</h2><p>Los usaremos para confirmar y coordinar tu pedido.</p></div>
                </div>
                <div class="checkout-fields">
                    <label>DNI<input name="customer_dni" inputmode="numeric" pattern="[0-9]{8}" value="{{ old('customer_dni') }}" required maxlength="8" autocomplete="off" placeholder="12345678">@error('customer_dni')<small class="checkout-error">{{ $message }}</small>@enderror</label>
                    <label>Nombres<input name="customer_first_name" value="{{ old('customer_first_name') }}" required maxlength="80" autocomplete="given-name" placeholder="Ej. Ana María">@error('customer_first_name')<small class="checkout-error">{{ $message }}</small>@enderror</label>
                    <label>Apellidos<input name="customer_last_name" value="{{ old('customer_last_name') }}" required maxlength="120" autocomplete="family-name" placeholder="Ej. Ventura Pérez">@error('customer_last_name')<small class="checkout-error">{{ $message }}</small>@enderror</label>
                    <label>Correo electrónico<input name="customer_email" type="email" value="{{ old('customer_email') }}" required maxlength="160" autocomplete="email" placeholder="tu@correo.com">@error('customer_email')<small class="checkout-error">{{ $message }}</small>@enderror</label>
                    <label>Teléfono<input name="customer_phone" value="{{ old('customer_phone') }}" required maxlength="40" autocomplete="tel" placeholder="999 999 999">@error('customer_phone')<small class="checkout-error">{{ $message }}</small>@enderror</label>
                </div>
                <div class="checkout-step-actions checkout-step-actions-next"><button type="button" class="checkout-step-button" data-checkout-next>Continuar a entrega <span>→</span></button></div>
            </section>

            <section class="checkout-step checkout-delivery-step" data-checkout-step="2" hidden>
                <div class="checkout-section-title">
                    <span>02</span>
                    <div><h2>Modalidad y dirección</h2><p>Elige delivery o recojo en tienda para continuar.</p></div>
                </div>
                <div class="fulfillment-block">
                    <p class="delivery-location-title">¿Cómo quieres recibirlo?</p>
                    <div class="payment-options fulfillment-options">
                        <label><input type="radio" name="fulfillment_method" value="delivery" @checked(old('fulfillment_method', 'delivery') === 'delivery')><span><strong>Delivery</strong><small>Costo a cargo del cliente</small></span></label>
                        <label><input type="radio" name="fulfillment_method" value="pickup" @checked(old('fulfillment_method') === 'pickup')><span><strong>Recojo en tienda</strong><small>Miguel Iglesias 991, Cajamarca</small></span></label>
                    </div>
                    @error('fulfillment_method')<small class="checkout-error">{{ $message }}</small>@enderror
                </div>
                <div class="delivery-location" data-ubigeo-fields data-ubigeo-source="https://unpkg.com/ubigeo-peru@2.0.2/src/ubigeo-reniec.json" data-old-department="{{ old('department') }}" data-old-province="{{ old('province') }}" data-old-district="{{ old('district') }}">
                    <p class="delivery-location-title">Dirección de entrega</p>
                    <div class="delivery-location-grid">
                        <label>Departamento<select name="department" data-ubigeo-department required><option value="">Selecciona un departamento</option></select>@error('department')<small class="checkout-error">{{ $message }}</small>@enderror</label>
                        <label>Provincia<select name="province" data-ubigeo-province required disabled><option value="">Selecciona una provincia</option></select>@error('province')<small class="checkout-error">{{ $message }}</small>@enderror</label>
                        <label>Distrito<select name="district" data-ubigeo-district required disabled><option value="">Selecciona un distrito</option></select>@error('district')<small class="checkout-error">{{ $message }}</small>@enderror</label>
                        <label class="checkout-field-full delivery-address-field">Dirección exacta<textarea name="customer_address" rows="3" required maxlength="500" autocomplete="street-address" placeholder="Calle, número, urbanización y referencia">{{ old('customer_address') }}</textarea>@error('customer_address')<small class="checkout-error">{{ $message }}</small>@enderror</label>
                    </div>
                </div>
                <div class="checkout-step-actions"><button type="button" class="checkout-step-button checkout-step-button-secondary" data-checkout-back>← Anterior</button><button type="button" class="checkout-step-button" data-checkout-next>Continuar a pago <span>→</span></button></div>
            </section>

            <section class="checkout-step checkout-payment-block" data-checkout-step="3" hidden>
                <div class="checkout-section-title checkout-payment-title">
                    <span>03</span>
                    <div><h2>Método de pago</h2><p>Pago manual y seguro. Confirmaremos tu pedido personalmente.</p></div>
                </div>
                <div class="payment-options">
                    <label><input type="radio" name="payment_method" value="yape" required @checked(old('payment_method') === 'yape')><span><strong>Yape</strong><small>Coordinaremos el pago</small></span></label>
                    <label><input type="radio" name="payment_method" value="plin" @checked(old('payment_method') === 'plin')><span><strong>Plin</strong><small>Coordinaremos el pago</small></span></label>
                    <label><input type="radio" name="payment_method" value="transferencia" @checked(old('payment_method') === 'transferencia')><span><strong>Transferencia</strong><small>Coordinaremos el pago</small></span></label>
                    <label><input type="radio" name="payment_method" value="contra_entrega" @checked(old('payment_method') === 'contra_entrega')><span><strong>Contra entrega</strong><small>Paga al recibir</small></span></label>
                </div>
                @error('payment_method')<small class="checkout-error">{{ $message }}</small>@enderror
                <section class="bank-transfer-instructions" data-bank-transfer-instructions hidden aria-label="Datos para transferir a Interbank">
                    <div class="bank-transfer-heading">
                        <span class="bank-transfer-mark" aria-hidden="true">IB</span>
                        <div><p class="bank-transfer-kicker">Transferencia bancaria</p><h3>{{ config('services.interbank_transfer.bank') }} · {{ config('services.interbank_transfer.currency') }}</h3></div>
                        <span class="bank-transfer-secure">Cuenta receptora</span>
                    </div>
                    <div class="bank-transfer-data">
                        <div><span>{{ config('services.interbank_transfer.account_type') }}</span><strong>{{ config('services.interbank_transfer.account_number') }}</strong></div>
                        <div><span>CCI Interbank</span><strong>{{ config('services.interbank_transfer.cci') }}</strong></div>
                        <div><span>Titular de la cuenta</span><strong>{{ config('services.interbank_transfer.account_holder') }}</strong></div>
                    </div>
                    <div class="bank-transfer-amount"><span>Transfiere el total exacto de tu pedido</span><strong>S/ {{ number_format($total, 2, ',', '.') }}</strong></div>
                </section>
                <div class="yape-instructions" data-yape-instructions @if (old('payment_method') !== 'yape') hidden @endif>
                    <div class="yape-instructions-copy">
                        <p class="yape-instructions-kicker"><span aria-hidden="true">✦</span> Pago con Yape</p>
                        <h3>Escanea y yapea el total exacto</h3>
                        <p class="yape-instructions-lead">Escanea el QR del resumen y confirma la operación aquí abajo.</p>
                        <div class="yape-exact-amount"><span>Monto exacto a yapear</span><strong>S/ {{ number_format($total, 2, ',', '.') }}</strong></div>
                    </div>
                </div>
                <div class="payment-details" data-payment-details @if (! in_array(old('payment_method'), ['yape', 'plin', 'transferencia'], true)) hidden @endif>
                    <p class="payment-details-title">Datos de la operación</p>
                    <div class="checkout-fields">
                        <label>Nombre del titular<input name="payment_account_holder" value="{{ old('payment_account_holder') }}" maxlength="120" data-payment-field placeholder="Nombre que figura en la cuenta">@error('payment_account_holder')<small class="checkout-error">{{ $message }}</small>@enderror</label>
                        <label>Número de operación<input name="payment_operation_number" value="{{ old('payment_operation_number') }}" maxlength="60" data-payment-field placeholder="Ej. 123456789">@error('payment_operation_number')<small class="checkout-error">{{ $message }}</small>@enderror</label>
                    </div>
                </div>
                <p class="checkout-simple-note">Después de confirmar coordinaremos el pago contigo; no necesitas subir archivos.</p>
                <div class="checkout-step-actions"><button type="button" class="checkout-step-button checkout-step-button-secondary" data-checkout-back>← Anterior</button><button type="submit" class="checkout-step-button" data-checkout-submit hidden>Confirmar pedido <span>↗</span></button></div>
            </section>
        </section>

        <div class="checkout-right-column">
            <aside class="checkout-order-summary">
                <span class="cart-summary-label">Tu pedido</span>
                <h2>Resumen <em>de compra</em></h2>
                <div class="yape-qr-sidebar" data-payment-qr-card="yape" @if (old('payment_method') !== 'yape') hidden @endif aria-label="Pago con Yape">
                    <div class="yape-qr-sidebar-copy">
                        <p class="yape-instructions-kicker"><span aria-hidden="true">✦</span> Yape · escanea aquí</p>
                        <span>Yapea exactamente</span>
                        <strong>S/ {{ number_format($total, 2, ',', '.') }}</strong>
                        <small>Titular: {{ config('services.interbank_transfer.account_holder') }}</small>
                        <small>Abre Yape y apunta al código QR.</small>
                    </div>
                    <figure class="yape-qr-frame">
                        <img src="{{ asset('images/yape.jpeg') }}" alt="Código QR de Yape para pagar S/ {{ number_format($total, 2, ',', '.') }}">
                        <figcaption>Escanea con Yape</figcaption>
                    </figure>
                </div>
                <div class="yape-qr-sidebar plin-qr-card" data-payment-qr-card="plin" @if (old('payment_method') !== 'plin') hidden @endif aria-label="Pago con Plin">
                    <div class="yape-qr-sidebar-copy">
                        <p class="yape-instructions-kicker"><span aria-hidden="true">✦</span> Plin · pago seguro</p>
                        <span>Importe exacto</span>
                        <strong>S/ {{ number_format($total, 2, ',', '.') }}</strong>
                        <small>Titular: {{ config('services.interbank_transfer.account_holder') }}</small>
                        <small>Confirma el pago desde tu app y registra la operación.</small>
                    </div>
                    <figure class="yape-qr-frame plin-qr-frame">
                        @if (file_exists(public_path('images/PLIN.jpeg')))
                            <img src="{{ asset('images/PLIN.jpeg') }}" alt="Código QR de Plin para pagar S/ {{ number_format($total, 2, ',', '.') }}">
                            <figcaption>Escanea con Plin</figcaption>
                        @else
                            <div class="plin-qr-missing"><span aria-hidden="true">QR</span><strong>QR de Plin pendiente</strong><small>Agrega el código QR oficial de tu cuenta para mostrarlo aquí.</small></div>
                        @endif
                    </figure>
                </div>
                <div class="checkout-order-items">
                    @foreach ($cart as $item)
                        <div><span>{{ $item['quantity'] }} × {{ $item['name'] }}</span><strong>S/ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</strong></div>
                    @endforeach
                </div>
                <div class="cart-summary-total"><span>Total</span><strong>S/ {{ number_format($total, 2, ',', '.') }}</strong></div>
                <a href="{{ route('cart.index') }}" class="checkout-back-link">← Volver al carrito</a>
            </aside>
            <aside class="checkout-delivery-notice" data-fulfillment-notice aria-live="polite" aria-label="Información de entrega">
                <span aria-hidden="true">✦</span>
                <strong data-fulfillment-title>Delivery:</strong>
                <span data-fulfillment-copy>costo a cargo del cliente</span>
                <small data-fulfillment-extra>(se coordina aparte)</small>
            </aside>
        </div>
    </form>
</main>
@endsection
