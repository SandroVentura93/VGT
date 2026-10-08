<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function trackOrders(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_dni' => ['required', 'digits:8'],
            'customer_phone' => ['required', 'string', 'max:40'],
        ]);

        $statusLabels = [
            'pending_payment' => 'Pendiente de pago',
            'pending_validation' => 'Validando pago',
            'packing' => 'En empaque',
            'cancelled' => 'Cancelado',
        ];

        $orders = Order::query()
            ->where('customer_dni', $data['customer_dni'])
            ->where('customer_phone', $data['customer_phone'])
            ->latest()
            ->get()
            ->map(fn (Order $order): array => [
                'order_number' => $order->order_number,
                'created_at' => $order->created_at?->toISOString(),
                'status' => $order->status,
                'status_label' => match ($order->status) {
                    'pending_confirmation' => $order->payment_method === 'contra_entrega' ? 'Pendiente de pago' : 'Validando pago',
                    'paid', 'preparing' => 'En empaque',
                    'shipped' => $order->fulfillment_method === 'pickup' ? 'Listo para recojo' : 'Listo para entrega',
                    'ready_for_pickup' => $order->fulfillment_method === 'pickup' ? 'Listo para recojo' : 'Listo para entrega',
                    'completed' => $order->fulfillment_method === 'pickup' ? 'Recogido' : 'Entregado',
                    default => $statusLabels[$order->status] ?? 'En revisión',
                },
                'payment_method' => $order->payment_method,
                'fulfillment_method' => $order->fulfillment_method,
                'total' => $order->total,
                'department' => $order->department,
                'province' => $order->province,
                'district' => $order->district,
                'customer_address' => $order->customer_address,
                'items' => collect($order->items ?? [])->map(fn (array $item): array => [
                    'name' => $item['name'] ?? 'Producto',
                    'quantity' => $item['quantity'] ?? 0,
                    'price' => $item['price'] ?? 0,
                ])->values()->all(),
            ])->values();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No encontramos pedidos con esos datos.'], 404);
        }

        return response()->json(['orders' => $orders]);
    }

    public function index(): View
    {
        $products = Product::query()->where('stock', '>', 0)->latest()->get();
        $categoryCounts = $products->countBy('category');
        $categories = Category::query()->orderBy('name')->pluck('name')
            ->merge($products->pluck('category'))
            ->filter()
            ->unique()
            ->filter(fn (string $category): bool => ($categoryCounts[$category] ?? 0) > 0)
            ->sort()
            ->values();

        return view('store.home', [
            'products' => $products,
            'categories' => $categories,
            'categoryCounts' => $categoryCounts,
            'featured' => Product::query()->where('featured', true)->where('stock', '>', 0)->get(),
            'cartCount' => collect(session('cart', []))->sum('quantity'),
        ]);
    }

    public function addToCart(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $cart = session('cart', []);
        $stockRemaining = 0;

        DB::transaction(function () use ($product, &$cart, &$stockRemaining): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            abort_if($lockedProduct->stock < 1, 409, 'Este producto ya no tiene stock disponible.');

            $item = $cart[$lockedProduct->id] ?? [
                'name' => $lockedProduct->name,
                'price' => (float) $lockedProduct->price,
                'quantity' => 0,
            ];
            $item['image'] = $item['image'] ?? $lockedProduct->image;
            $item['category'] = $item['category'] ?? $lockedProduct->category;
            $item['quantity']++;
            $cart[$lockedProduct->id] = $item;
            $lockedProduct->decrement('stock');
            $stockRemaining = $lockedProduct->stock;
        });

        session(['cart' => $cart]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Producto añadido a tu carrito.',
                'cart_count' => collect($cart)->sum('quantity'),
                'stock_remaining' => $stockRemaining,
                'quantity' => $cart[$product->id]['quantity'],
                'item_total' => $cart[$product->id]['price'] * $cart[$product->id]['quantity'],
                'cart_total' => collect($cart)->sum(fn (array $item) => $item['price'] * $item['quantity']),
            ]);
        }

        return back()->with('success', 'Producto añadido a tu carrito.');
    }

    public function cart(): Response
    {
        $cart = session('cart', []);
        $total = collect($cart)->sum(fn (array $item) => $item['price'] * $item['quantity']);

        return response()->view('store.cart', compact('cart', 'total'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function decreaseCartItem(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $cart = session('cart', []);
        $productId = (string) $product->id;

        if (! isset($cart[$productId])) {
            return back();
        }

        DB::transaction(function () use ($product, $productId, &$cart): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $cart[$productId]['quantity']--;
            $lockedProduct->increment('stock');

            if ($cart[$productId]['quantity'] < 1) {
                unset($cart[$productId]);
            }
        });

        session(['cart' => $cart]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cantidad actualizada.',
                'cart_count' => collect($cart)->sum('quantity'),
                'quantity' => $cart[$productId]['quantity'] ?? 0,
                'item_total' => isset($cart[$productId]) ? $cart[$productId]['price'] * $cart[$productId]['quantity'] : 0,
                'cart_total' => collect($cart)->sum(fn (array $item) => $item['price'] * $item['quantity']),
                'removed' => ! isset($cart[$productId]),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Cantidad actualizada.');
    }

    public function removeFromCart(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $cart = session('cart', []);
        $productId = (string) $product->id;

        if (! isset($cart[$productId])) {
            return back();
        }

        DB::transaction(function () use ($product, $productId, &$cart): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $lockedProduct->increment('stock', $cart[$productId]['quantity']);
            unset($cart[$productId]);
        });

        session(['cart' => $cart]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Producto eliminado del carrito.',
                'cart_count' => collect($cart)->sum('quantity'),
                'cart_total' => collect($cart)->sum(fn (array $item) => $item['price'] * $item['quantity']),
                'removed' => true,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Producto eliminado del carrito.');
    }
}
