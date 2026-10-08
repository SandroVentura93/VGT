@extends('layouts.admin')

@section('title', 'Editar categoría')

@section('content')
<main class="admin-main admin-form-page">
    <a href="{{ route('admin.categorias.index') }}" class="admin-back-link">← Volver a categorías</a>
    <div class="admin-page-heading"><div><p class="admin-kicker">Catálogo / Organización</p><h1>Edita la categoría</h1><p class="admin-muted">Los productos asociados se actualizarán automáticamente.</p></div></div>
    <form method="POST" action="{{ route('admin.categorias.update', $category) }}" class="admin-category-edit-form">@csrf @method('PUT')<label>Ruta de categoría<input name="name" value="{{ old('name', $category->name) }}" required maxlength="255" placeholder="Electrónica > Componentes de computadoras > Dispositivos de almacenamiento de datos > Discos duros internos" autofocus>@error('name')<small class="checkout-error">{{ $message }}</small>@enderror</label><small class="admin-hint">Separa los niveles de la ruta con &gt;.</small><button type="submit" class="admin-submit">Guardar cambios <span>↗</span></button></form>
</main>
@endsection
