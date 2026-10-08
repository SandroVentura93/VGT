<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $orders = Order::query()
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('customer_name', 'like', "%{$search}%")
                ->orWhere('customer_dni', 'like', "%{$search}%")))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $results = [
            'orders' => $orders,
            'orderStatusLabels' => $orders->getCollection()->mapWithKeys(fn (Order $order): array => [
                $order->id => $this->statusLabel($order),
            ]),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.orders.partials.results', $results)->render(),
                'total' => $orders->total(),
            ]);
        }

        return view('admin.orders.index', $results + [
            'selectedStatus' => $status,
            'selectedSearch' => $search,
            'statuses' => $this->statuses(),
            'stats' => [
                'pendingValidation' => Order::query()->where('status', 'pending_validation')->count(),
                'pendingPayment' => Order::query()->where('status', 'pending_payment')->count(),
                'paidTotal' => Order::query()->whereIn('status', ['paid', 'packing', 'preparing', 'shipped', 'ready_for_pickup', 'completed'])->sum('total'),
            ],
        ]);
    }

    public function show(Order $order): View
    {
        $statuses = $this->statuses($order);
        $nextStatus = match ($order->status) {
            'pending_payment' => $order->payment_method === 'contra_entrega' ? 'packing' : null,
            'pending_validation', 'pending_confirmation' => 'packing',
            'paid', 'preparing', 'packing' => 'ready_for_pickup',
            'shipped', 'ready_for_pickup' => 'completed',
            default => null,
        };

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => $statuses,
            'currentStatusLabel' => $this->statusLabel($order),
            'nextStatus' => $nextStatus,
            'nextStage' => $nextStatus !== null ? $statuses[$nextStatus] : null,
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending_payment,pending_validation,packing,ready_for_pickup,completed,cancelled'],
        ]);

        $order->update($data);

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    private function statuses(?Order $order = null): array
    {
        return [
            'pending_payment' => 'Pendiente de pago',
            'pending_validation' => 'Validando pago',
            'packing' => 'En empaque',
            'ready_for_pickup' => $order === null
                ? 'Listo para recojo / entrega'
                : ($order->fulfillment_method === 'pickup' ? 'Listo para recojo' : 'Listo para entrega'),
            'completed' => $order === null
                ? 'Recogido / entregado'
                : ($order->fulfillment_method === 'pickup' ? 'Recogido' : 'Entregado'),
            'cancelled' => 'Cancelado',
        ];
    }

    private function statusLabel(Order $order): string
    {
        return match ($order->status) {
            'pending_confirmation' => $order->payment_method === 'contra_entrega' ? 'Pendiente de pago' : 'Validando pago',
            'paid', 'preparing' => 'En empaque',
            'shipped' => $order->fulfillment_method === 'pickup' ? 'Listo para recojo' : 'Listo para entrega',
            default => $this->statuses($order)[$order->status] ?? $order->status,
        };
    }
}
