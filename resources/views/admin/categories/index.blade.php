@extends('layouts.admin')

@section('title', 'Categorías')

@section('content')
<main class="admin-main admin-category-page">
    <section class="admin-category-hero">
        <div class="admin-category-hero-copy">
            <p class="admin-kicker"><span class="admin-category-live-dot"></span>Catálogo / Organización</p>
            <h1>Tu catálogo, <em>bien organizado</em></h1>
            <p class="admin-muted">Define rutas claras para que cada producto encuentre su lugar y tus clientes naveguen mejor.</p>
        </div>
        <div class="admin-category-summary" aria-label="Resumen del catálogo">
            <div class="admin-category-summary-item">
                <span>Rutas guardadas</span>
                <strong>{{ $categories->count() }}</strong>
                <small>en tu catálogo</small>
            </div>
            <span class="admin-category-summary-divider" aria-hidden="true"></span>
            <div class="admin-category-summary-item">
                <span>Productos clasificados</span>
                <strong>{{ $productCategories->sum() }}</strong>
                <small>con categoría asignada</small>
            </div>
        </div>
    </section>

    <section class="admin-category-create-card" aria-labelledby="new-category-title">
        <div class="admin-category-card-heading">
            <span class="admin-category-card-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </span>
            <div>
                <p class="admin-category-card-kicker">Nueva ruta</p>
                <h2 id="new-category-title">Nueva categoría</h2>
                <p>Usa <strong>&gt;</strong> para separar cada nivel de la jerarquía.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.categorias.store') }}" class="admin-category-create-form admin-category-guided-form">
            @csrf
            <div class="admin-category-input-row">
                <label for="category-parent">Categoría superior
                    <select id="category-parent" name="parent_category" data-category-parent>
                        <option value="" @selected(old('parent_category', '') === '')>Crear como categoría principal</option>
                        @foreach ($categories as $parentCategory)
                            <option value="{{ $parentCategory->name }}" @selected(old('parent_category') === $parentCategory->name)>{{ $parentCategory->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label for="category-segment">Nombre de la nueva categoría
                    <input id="category-segment" name="category_segment" value="{{ old('category_segment', old('name')) }}" required maxlength="255" placeholder="Ej. Almacenamiento" data-category-segment>
                </label>
                <button type="submit" class="admin-submit">Crear categoría <span aria-hidden="true">↗</span></button>
            </div>
            <div class="admin-category-route-preview"><span>Ruta que se guardará</span><strong data-category-path-preview>{{ old('name', old('category_segment', 'Nueva categoría')) }}</strong></div>
            <div class="admin-category-form-footer">
                <small class="admin-hint"><span aria-hidden="true">✦</span> Crea una categoría principal o selecciona una superior para agregar una subcategoría.</small>
                <span class="admin-category-limit">Máximo 255 caracteres</span>
            </div>
            @error('name')<small class="admin-category-error">{{ $message }}</small>@enderror
            @error('category_segment')<small class="admin-category-error">{{ $message }}</small>@enderror
            @error('parent_category')<small class="admin-category-error">Selecciona una categoría superior válida.</small>@enderror
        </form>
    </section>

    <div class="admin-category-list-heading">
        <div>
            <p class="admin-category-card-kicker">Estructura actual</p>
            <h2>Categorías disponibles</h2>
            <p>Gestiona las rutas que organizan los productos de tu tienda.</p>
        </div>
        <span class="admin-category-total"><strong>{{ $categories->count() }}</strong> {{ $categories->count() === 1 ? 'ruta' : 'rutas' }}</span>
    </div>

    <section class="admin-category-tree-panel" aria-label="Árbol de categorías">
        <ul class="admin-category-tree" role="tree" aria-label="Categorías principales y subcategorías">
            @forelse ($categoryTree as $node)
                @include('admin.categories.partials.tree-node', ['node' => $node])
            @empty
                <li role="none" class="admin-category-empty">
                    <span aria-hidden="true">✦</span>
                    <strong>Todavía no hay categorías</strong>
                    <small>Crea una ruta arriba para comenzar a organizar tu catálogo.</small>
                </li>
            @endforelse
        </ul>
    </section>
</main>
@endsection
