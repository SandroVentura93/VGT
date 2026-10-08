@php
    $hasChildren = $node['children'] !== [];
    $category = $node['category'];
    $childrenId = 'category-branch-'.md5($node['path']);
@endphp
<li class="admin-category-tree-node{{ $hasChildren ? ' has-children' : ' is-leaf' }}" role="treeitem" @if ($hasChildren) aria-expanded="false" @endif>
    <div class="admin-category-tree-row">
        <div class="admin-category-tree-main">
            @if ($hasChildren)
                <button type="button" class="admin-category-tree-toggle" data-category-branch-toggle aria-expanded="false" aria-controls="{{ $childrenId }}" aria-label="Mostrar subcategorías de {{ $node['name'] }}"><span aria-hidden="true">›</span></button>
            @else
                <span class="admin-category-tree-leaf-mark" aria-hidden="true"></span>
            @endif
            <span class="admin-category-tree-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M3.5 6.5h6l2 2h9v9.8a1.7 1.7 0 0 1-1.7 1.7H5.2a1.7 1.7 0 0 1-1.7-1.7V6.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M3.8 9h17" stroke="currentColor" stroke-width="1.4"/></svg>
            </span>
            <span class="admin-category-tree-copy" title="{{ $node['path'] }}">
                <strong>{{ $node['name'] }}</strong>
                <small>{{ $node['totalProducts'] }} {{ $node['totalProducts'] === 1 ? 'producto' : 'productos' }}{{ $hasChildren ? ' · '.count($node['children']).' subcategorías' : '' }}</small>
            </span>
        </div>
        @if ($category)
            <span class="admin-category-tree-count" aria-label="{{ $node['productCount'] }} productos en esta ruta">{{ $node['productCount'] }} <span>directos</span></span>
            <div class="admin-category-tree-actions">
                <a href="{{ route('admin.categorias.edit', $category) }}" aria-label="Editar {{ $node['path'] }}">Editar</a>
                <form method="POST" action="{{ route('admin.categorias.destroy', $category) }}" onsubmit="return confirm('¿Eliminar esta categoría?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" aria-label="Eliminar {{ $node['path'] }}">Eliminar</button>
                </form>
            </div>
        @endif
    </div>
    @if ($hasChildren)
        <ul id="{{ $childrenId }}" class="admin-category-tree-children" role="group" hidden>
            @foreach ($node['children'] as $child)
                @include('admin.categories.partials.tree-node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>