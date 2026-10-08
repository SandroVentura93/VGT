<div id="orders-results" class="admin-order-results" data-order-results aria-live="polite" aria-busy="false">
    <section class="admin-table-card admin-orders-table">
        <div class="admin-table-head"><span>Pedido</span><span>Cliente</span><span>Total</span><span>Pago</span><span>Estado</span><span></span></div>
        @forelse ($orders as $order)
            <a class="admin-order-row" href="{{ route('admin.pedidos.show', $order) }}">
                <div><strong>{{ $order->order_number }}</strong><small>{{ $order->created_at->format('d/m/Y H:i') }}</small></div>
                <div><strong>{{ $order->customer_name }}</strong><small>{{ $order->customer_phone }}</small></div>
                <strong>S/ {{ number_format($order->total, 2, ',', '.') }}</strong>
                <span class="admin-payment-method">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                <span class="admin-status admin-status-{{ $order->status }}">{{ $orderStatusLabels[$order->id] }}</span>
                <span class="admin-order-arrow">→</span>
            </a>
        @empty
            <div class="admin-empty">No encontramos pedidos con esos datos.</div>
        @endforelse
    </section>
    <div class="admin-pagination">{{ $orders->links() }}</div>
</div>