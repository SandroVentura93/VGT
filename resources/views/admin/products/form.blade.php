@extends('layouts.admin')

@section('title', $product->exists ? 'Editar producto' : 'Nuevo producto')

@section('content')
@php
    $hasManualOffer = old('manual_offer', $product->exists && $product->offer_percentage > 0);
    $offerPercentage = (int) old('offer_percentage', $product->exists ? $product->offer_percentage : 20);
    $offerDurationHours = (int) old('offer_duration_hours', $product->offer_duration_hours ?: 72);
    $profitPercentage = (int) old('profit_percentage', $product->exists ? $product->profit_percentage : 50);
    $includesIgv = (bool) old('includes_igv', $product->exists ? $product->includes_igv : true);
    $includesSurcharge = (bool) old('includes_surcharge', $product->exists ? $product->includes_surcharge : true);
    $productImages = collect([$product->image, ...($product->images ?? [])])->filter()->unique()->values();
@endphp
<main class="admin-main admin-form-page">
    <a href="{{ route('admin.productos.index') }}" class="admin-back-link">← Volver al catálogo</a>
    <div class="admin-page-heading"><div><p class="admin-kicker">Catálogo / {{ $product->exists ? 'Editar' : 'Nuevo' }}</p><h1>{{ $product->exists ? 'Edita tu producto' : 'Añade una nueva pieza' }}</h1><p class="admin-muted">Los cambios se reflejarán inmediatamente en la web.</p></div></div>
    <form method="POST" action="{{ $product->exists ? route('admin.productos.update', $product) : route('admin.productos.store') }}" enctype="multipart/form-data" class="admin-editor">@csrf @if ($product->exists) @method('PUT') @endif
        <div class="admin-editor-main">
            <label class="suggestion-field">Nombre del producto<input name="name" list="product-name-suggestions" data-suggestions='@json($suggestedNames)' value="{{ old('name', $product->name) }}" required placeholder="Empieza a escribir para ver sugerencias"><datalist id="product-name-suggestions">@foreach ($suggestedNames as $suggestion)<option value="{{ $suggestion }}">@endforeach</datalist><span class="suggestion-menu" data-suggestion-menu></span></label>
            <label>Descripción<textarea name="description" rows="5" placeholder="Cuenta qué hace especial a este producto">{{ old('description', $product->description) }}</textarea><span class="admin-hint">Ideas rápidas: solución práctica · diseño cuidado · soporte experto · pensado para tu día a día.</span></label>
            <div class="admin-form-grid">
                <label>Precio base (S/)<input name="base_price" type="number" step="0.01" min="0" value="{{ old('base_price', $product->base_price ?? $product->price) }}" data-base-price required><span class="admin-hint">Costo total del producto.</span></label>
                <label>Margen<select name="profit_percentage" data-profit-percentage>@foreach (range(0, 100, 10) as $marginOption)<option value="{{ $marginOption }}" @selected($profitPercentage === $marginOption)>{{ $marginOption }}%</option>@endforeach</select><span class="admin-hint">Se suma directamente al costo; máximo 100 %.</span></label>
                <label>Precio de venta final (S/)<input name="sale_price" type="number" step="0.01" min="0" value="{{ old('sale_price', $product->sale_price ?? $product->price) }}" data-sale-price readonly><span class="admin-hint">Costo + margen, con IGV y recargo opcionales.</span></label>
                <label>Stock disponible<input name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required><span class="admin-hint">Unidades listas para vender.</span></label>
            </div>
            <section class="admin-price-adjustments" aria-label="Impuestos y recargos del precio">
                <p>Impuestos y cargos opcionales</p>
                <label class="admin-check"><input type="hidden" name="includes_igv" value="0"><input type="checkbox" name="includes_igv" value="1" data-include-igv @checked($includesIgv)> Incluir IGV (18 %)</label>
                <label class="admin-check"><input type="hidden" name="includes_surcharge" value="0"><input type="checkbox" name="includes_surcharge" value="1" data-include-surcharge @checked($includesSurcharge)> Añadir recargo del 1 % al total</label>
            </section>
            <div class="admin-price-preview" data-final-price-preview><span data-final-price-label>Precio de venta antes de promoción:</span> <strong data-final-price>S/ {{ number_format((float) ($product->price ?: $product->sale_price), 2, ',', '.') }}</strong></div>
            <section class="manual-offer-panel"><label class="admin-check"><input type="checkbox" name="manual_offer" value="1" data-manual-offer-toggle @checked($hasManualOffer)> Configurar promoción manualmente</label><p class="admin-hint">El descuento se aplicará sobre el precio de venta. Al dejarlo automático, el sistema elegirá entre 20% y 30% como máximo.</p><div class="manual-offer-fields" data-manual-offer-fields><div class="admin-form-grid"><label>Descuento<select name="offer_percentage" data-offer-percentage>@foreach (range(0, 100, 5) as $discountOption)<option value="{{ $discountOption }}" @selected($offerPercentage === $discountOption)>{{ $discountOption }}%</option>@endforeach</select></label><label>Duración (horas)<input name="offer_duration_hours" type="number" min="1" max="720" value="{{ $offerDurationHours }}"></label></div></div></section>
        </div>
        <aside class="admin-editor-side">
            <div class="admin-category-field">
                <div class="admin-category-field-heading">
                    <label for="product-category-input">Categoría</label>
                    <button type="button" class="new-category-button" data-open-category-modal>+ Nueva</button>
                </div>
                <input id="product-category-input" name="category" value="{{ old('category', $product->category ?? 'General') }}" required maxlength="255" placeholder="Electrónica > ..." aria-describedby="category-tree-hint">
                <span class="admin-hint" id="category-tree-hint">Elige una categoría principal y abre sus subcategorías. También puedes escribir una ruta manual.</span>
                <div class="category-picker">
                    <span class="category-quick-label">Explorar categorías</span>
                    <ul class="category-tree" data-category-tree aria-label="Jerarquía de categorías"></ul>
                    <div class="category-quick-list" data-category-quick-list hidden aria-hidden="true">
                        @foreach ($suggestedCategories->push('General')->unique()->sort()->values() as $category)
                            <button type="button" data-category-choice="{{ $category }}">{{ $category }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
            <label class="admin-upload dropzone" data-dropzone>Imágenes del producto<input name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple data-image-input><span class="dropzone-copy"><strong>Arrastra varias imágenes aquí</strong><small>o haz clic para seleccionar varias · JPG, PNG o WEBP · máx. 4 MB cada una</small></span><span class="dropzone-file" data-dropzone-file></span></label>
            <div class="new-image-gallery" data-new-image-gallery aria-live="polite"></div>
            @if ($productImages->isNotEmpty())
                <div class="admin-image-gallery">
                    @foreach ($productImages as $image)
                        <img class="admin-preview" src="{{ asset('storage/'.$image) }}" alt="Imagen de {{ $product->name }}">
                    @endforeach
                </div>
            @endif
            <label class="admin-check"><input name="featured" type="checkbox" value="1" @checked(old('featured', $product->featured))> Producto destacado</label>
            <button type="submit" class="admin-submit">{{ $product->exists ? 'Guardar cambios' : 'Publicar producto' }} <span>↗</span></button>
        </aside>
    </form>
    <div class="category-modal" data-category-modal aria-hidden="true"><div class="category-modal-card" role="dialog" aria-modal="true" aria-labelledby="category-modal-title"><button type="button" class="category-modal-close" data-close-category-modal aria-label="Cerrar">×</button><p class="admin-kicker">Catálogo / Organización</p><h2 id="category-modal-title">Nueva categoría</h2><p class="admin-muted">Créala una vez y estará disponible para tus próximos productos.</p><form action="{{ route('admin.categorias.store') }}" data-category-form class="category-modal-form">@csrf<label>Ruta de categoría<input name="name" required maxlength="255" placeholder="Electrónica > ..."></label><p class="admin-hint">Separa los niveles de la jerarquía con &gt;.</p><p class="category-modal-error" data-category-error></p><button type="submit" class="admin-submit">Guardar categoría <span>↗</span></button></form></div></div>
</main>
@endsection
