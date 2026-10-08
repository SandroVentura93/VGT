<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::query()->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', $this->formOptions() + ['product' => new Product]);
    }

    public function store(Request $request): RedirectResponse
    {
        $product = new Product;
        $this->saveProduct($product, $request);

        return redirect()->route('admin.productos.index')->with('success', 'Producto creado y publicado en la tienda.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', $this->formOptions() + compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->saveProduct($product, $request);

        return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Storage::disk('public')->delete(collect([$product->image, ...($product->images ?? [])])->filter()->unique()->all());

        $product->delete();

        return back()->with('success', 'Producto eliminado.');
    }

    private function saveProduct(Product $product, Request $request): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'profit_percentage' => ['nullable', 'integer', 'in:0,10,20,30,40,50,60,70,80,90,100'],
            'includes_igv' => ['nullable', 'boolean'],
            'includes_surcharge' => ['nullable', 'boolean'],
            'category' => ['required', 'string', 'max:255'],
            'stock' => ['required', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
            'manual_offer' => ['nullable', 'boolean'],
            'offer_percentage' => ['nullable', 'integer', Rule::in(range(0, 100, 5))],
            'offer_duration_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'name.required' => 'Ingresa el nombre del producto.',
            'base_price.required' => 'Ingresa el precio base del producto.',
            'category.required' => 'Selecciona o escribe una categoría.',
            'stock.required' => 'Ingresa el stock disponible.',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['featured'] = $request->boolean('featured');
        $data['profit_percentage'] = (int) ($data['profit_percentage'] ?? 50);
        $data['includes_igv'] = array_key_exists('includes_igv', $data)
            ? $request->boolean('includes_igv')
            : ($product->exists ? (bool) $product->includes_igv : true);
        $data['includes_surcharge'] = array_key_exists('includes_surcharge', $data)
            ? $request->boolean('includes_surcharge')
            : ($product->exists ? (bool) $product->includes_surcharge : true);
        $basePrice = (float) $data['base_price'];
        $marginAmount = $basePrice * ($data['profit_percentage'] / 100);
        $salePrice = $basePrice + $marginAmount;

        if ($data['includes_igv']) {
            $salePrice *= 1.18;
        }

        if ($data['includes_surcharge']) {
            $salePrice *= 1.01;
        }

        $data['sale_price'] = round($salePrice + 0.0000001, 2);
        $data['price'] = $data['sale_price'];

        if ($request->boolean('manual_offer')) {
            $data['offer_percentage'] = (int) $request->input('offer_percentage', 20);
            $data['offer_duration_hours'] = (int) $request->input('offer_duration_hours', 72);
            $data['offer_ends_at'] = now()->addHours($data['offer_duration_hours']);
        } elseif (! $product->exists) {
            $data['offer_percentage'] = collect([20, 30])->random();
            $data['offer_duration_hours'] = random_int(36, 120);
            $data['offer_ends_at'] = now()->addHours($data['offer_duration_hours']);
        }

        $offerPercentage = (int) ($data['offer_percentage'] ?? $product->offer_percentage ?? 0);

        if ($offerPercentage > 0) {
            $data['price'] = round((float) $data['sale_price'] * (1 - ($offerPercentage / 100)), 2);
        }

        $uploadedImages = $request->file('images', []);
        if (! is_array($uploadedImages)) {
            $uploadedImages = [$uploadedImages];
        }
        if ($request->hasFile('image')) {
            $uploadedImages[] = $request->file('image');
        }

        if ($uploadedImages) {
            $existingImages = collect([$product->image, ...($product->images ?? [])])->filter()->unique()->values();
            $newImagePaths = collect($uploadedImages)->map(fn ($image): string => $image->store('products', 'public'))->values();
            $imagePaths = $existingImages->merge($newImagePaths)->values()->all();
            $data['image'] = $imagePaths[0] ?? null;
            $data['images'] = $imagePaths;
        }

        $product->fill($data)->save();
    }

    private function formOptions(): array
    {
        return [
            'suggestedNames' => Product::query()->whereNotNull('name')->orderBy('name')->pluck('name'),
            'suggestedCategories' => Category::query()->orderBy('name')->pluck('name')->merge(
                Product::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')
            )->unique()->sort()->values(),
        ];
    }
}
