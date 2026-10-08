<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $productCategories = Product::query()->select('category')->selectRaw('count(*) as products_count')->groupBy('category')->pluck('products_count', 'category');

        return view('admin.categories.index', [
            'categories' => $categories,
            'productCategories' => $productCategories,
            'categoryTree' => $this->buildCategoryTree($categories, $productCategories),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->exists('category_segment')) {
            $segment = trim((string) $request->input('category_segment'));
            $parentCategory = trim((string) $request->input('parent_category'));
            $request->merge(['name' => $parentCategory === '' ? $segment : $parentCategory.' > '.$segment]);
        } else {
            $request->merge(['name' => trim((string) $request->input('name'))]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'category_segment' => ['sometimes', 'required', 'string', 'max:255', 'not_regex:/\\s*>\\s*/'],
            'parent_category' => ['nullable', 'string', 'max:255', 'exists:categories,name'],
        ]);

        $category = Category::create(['name' => $data['name']]);

        if (! $request->expectsJson()) {
            return redirect()->route('admin.categorias.index')->with('success', 'Categoría creada correctamente.');
        }

        return response()->json(['category' => $category->name], 201);
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);
        $name = trim($data['name']);

        DB::transaction(function () use ($category, $name): void {
            Product::query()->where('category', $category->name)->update(['category' => $name]);
            $category->update(['name' => $name]);
        });

        return redirect()->route('admin.categorias.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if (Product::query()->where('category', $category->name)->exists()) {
            return back()->with('error', 'No puedes eliminar una categoría que todavía tiene productos.');
        }

        $category->delete();

        return back()->with('success', 'Categoría eliminada.');
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @param  Collection<string, int>  $productCategories
     * @return array<int, array{name: string, path: string, category: ?Category, productCount: int, totalProducts: int, children: array}>
     */
    private function buildCategoryTree(Collection $categories, Collection $productCategories): array
    {
        $tree = [];

        foreach ($categories as $category) {
            $branches = &$tree;
            $path = '';

            foreach (explode(' > ', $category->name) as $segment) {
                $path = $path === '' ? $segment : $path.' > '.$segment;

                if (! isset($branches[$segment])) {
                    $branches[$segment] = [
                        'name' => $segment,
                        'path' => $path,
                        'category' => null,
                        'productCount' => 0,
                        'totalProducts' => 0,
                        'children' => [],
                    ];
                }

                if ($path === $category->name) {
                    $branches[$segment]['category'] = $category;
                    $branches[$segment]['productCount'] = (int) ($productCategories[$path] ?? 0);
                }

                $branches = &$branches[$segment]['children'];
            }

            unset($branches);
        }

        $calculateTotals = function (array &$branches) use (&$calculateTotals): int {
            $total = 0;

            foreach ($branches as &$branch) {
                $branch['totalProducts'] = $branch['productCount'] + $calculateTotals($branch['children']);
                $total += $branch['totalProducts'];
            }

            unset($branch);

            return $total;
        };

        $calculateTotals($tree);

        return array_values($tree);
    }
}
