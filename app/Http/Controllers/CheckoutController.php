<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $cart = session('cart', []);

        if ($cart === []) {
            return redirect()->route('store.home')->with('error', 'Añade al menos un producto antes de continuar.');
        }

        return view('store.checkout', [
            'cart' => $cart,
            'total' => collect($cart)->sum(fn (array $item) => $item['price'] * $item['quantity']),
        ]);
    }

    public function store(Request $request): View|RedirectResponse
    {
        $cart = session('cart', []);

        if ($cart === []) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        $data = $request->validate([
            'customer_dni' => ['required', 'digits:8'],
            'customer_first_name' => ['required', 'string', 'max:80'],
            'customer_last_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:160'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'fulfillment_method' => ['required', 'in:delivery,pickup'],
            'department' => [Rule::requiredIf(fn (): bool => $request->input('fulfillment_method') === 'delivery'), 'nullable', 'string', 'max:100'],
            'province' => [Rule::requiredIf(fn (): bool => $request->input('fulfillment_method') === 'delivery'), 'nullable', 'string', 'max:100'],
            'district' => [Rule::requiredIf(fn (): bool => $request->input('fulfillment_method') === 'delivery'), 'nullable', 'string', 'max:100'],
            'customer_address' => [Rule::requiredIf(fn (): bool => $request->input('fulfillment_method') === 'delivery'), 'nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:yape,plin,transferencia,contra_entrega'],
            'payment_account_holder' => [Rule::requiredIf(fn (): bool => in_array($request->input('payment_method'), ['yape', 'plin', 'transferencia'], true)), 'nullable', 'string', 'max:120'],
            'payment_operation_number' => [Rule::requiredIf(fn (): bool => in_array($request->input('payment_method'), ['yape', 'plin', 'transferencia'], true)), 'nullable', 'string', 'max:60'],
        ]);

        if ($data['fulfillment_method'] === 'pickup') {
            $data['department'] = 'Cajamarca';
            $data['province'] = 'Cajamarca';
            $data['district'] = 'Cajamarca';
            $data['customer_address'] = 'Miguel Iglesias 991, Cajamarca';
        }

        $data['order_number'] = 'VGT-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        $data['customer_name'] = trim($data['customer_first_name'].' '.$data['customer_last_name']);
        $data['status'] = $data['payment_method'] === 'contra_entrega' ? 'pending_payment' : 'pending_validation';
        $data['total'] = collect($cart)->sum(fn (array $item) => $item['price'] * $item['quantity']);
        $data['items'] = collect($cart)->map(fn (array $item): array => [
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'image' => $item['image'] ?? null,
            'category' => $item['category'] ?? 'Tecnología',
        ])->values()->all();

        $order = DB::transaction(fn (): Order => Order::query()->create($data));
        session()->forget('cart');

        if ($order->payment_method === 'contra_entrega') {
            $items = collect($order->items)->map(fn (array $item): string => sprintf(
                "• %s\n  Categoría: %s\n  Cantidad: %d unidad(es)\n  Precio unitario: S/ %s\n  Subtotal: S/ %s",
                $item['name'],
                $item['category'] ?? 'Tecnología',
                $item['quantity'],
                number_format($item['price'], 2, '.', ''),
                number_format($item['price'] * $item['quantity'], 2, '.', ''),
            ))->implode("\n");
            $whatsappMessage = "Hola Ventura Global Technology, quiero confirmar este pedido contra entrega.\n\n"
                ."*Pedido:* {$order->order_number}\n"
                .'*Fecha:* '.$order->created_at?->format('d/m/Y H:i')."\n"
                ."*Cliente:* {$order->customer_name}\n"
                ."*DNI:* {$order->customer_dni}\n"
                ."*Correo:* {$order->customer_email}\n"
                ."*Teléfono:* {$order->customer_phone}\n"
                .'*Modalidad:* '.($order->fulfillment_method === 'pickup' ? 'Recojo en tienda' : 'Delivery')."\n"
                ."*Departamento:* {$order->department}\n"
                ."*Provincia:* {$order->province}\n"
                ."*Distrito:* {$order->district}\n"
                ."*Dirección exacta:* {$order->customer_address}\n\n"
                ."*Productos:*\n{$items}\n\n"
                .'*Total:* S/ '.number_format((float) $order->total, 2, '.', '')."\n"
                .($order->fulfillment_method === 'pickup'
                    ? '*Recojo en tienda:* Miguel Iglesias 991, Cajamarca'
                    : '*Delivery:* costo a cargo del cliente (se coordina aparte)')."\n"
                .'*Pago:* Contra entrega';

            return redirect()->away('https://wa.me/51967151428?text='.urlencode($whatsappMessage));
        }

        $statusLabel = $order->status === 'pending_payment'
            ? 'Pendiente de pago'
            : 'Validando pago';
        $confirmationNextStep = $order->fulfillment_method === 'pickup'
            ? 'Validaremos manualmente tu pago. Después, el pedido pasará a En empaque y te avisaremos cuando esté listo para recojo en tienda.'
            : 'Validaremos manualmente tu pago. Después, el pedido pasará a En empaque y coordinaremos el envío contigo.';

        return view('store.order-confirmation', compact('order', 'statusLabel', 'confirmationNextStep'));
    }
}
