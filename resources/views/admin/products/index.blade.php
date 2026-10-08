@extends('layouts.admin')

@section('title', 'Productos')

@section('content')
<main class="admin-main">
    <div class="admin-page-heading"><div><p class="admin-kicker">Catálogo / Administración</p><h1>Tu colección</h1><p class="admin-muted">{{ $products->total() }} productos gestionados desde un solo lugar.</p></div><a href="{{ route('admin.productos.create') }}" class="admin-submit admin-create">+ Nuevo producto</a></div>
    <section class="admin-table-card">
        <div class="admin-table-head"><span>Producto</span><span>Categoría</span><span>Precio</span><span>Stock</span><span>Estado</span><span></span></div>
        @forelse ($products as $product)
            <div class="admin-product-row">
                <div class="admin-product-name"><div class="admin-thumb">@if ($product->image)<img src="{{ asset('storage/'.$product->image) }}" alt="">@else<span>{{ strtoupper(substr($product->name, 0, 2)) }}</span>@endif</div><div><strong>{{ $product->name }}</strong><small>{{ Str::limit($product->description, 54) }}</small></div></div>
                <span class="admin-category">{{ $product->category }}</span><strong>S/ {{ number_format($product->price, 2, ',', '.') }}</strong><span>{{ $product->stock }} uds.</span><span class="admin-status {{ $product->stock > 0 ? 'is-live' : 'is-empty' }}">{{ $product->stock > 0 ? 'Publicado' : 'Agotado' }}</span>
                <div class="admin-actions"><a href="{{ route('admin.productos.edit', $product) }}">Editar</a><form method="POST" action="{{ route('admin.productos.destroy', $product) }}" onsubmit="return confirm('¿Eliminar este producto?')">@csrf @method('DELETE')<button>Eliminar</button></form></div>
            </div>
        @empty
            <div class="admin-empty">Todavía no hay productos. Crea el primero para verlo en la tienda.</div>
        @endforelse
    </section>
    <div class="admin-pagination">{{ $products->links() }}</div>
</main>
@endsection
