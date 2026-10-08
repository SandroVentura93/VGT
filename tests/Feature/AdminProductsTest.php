<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.admin_username' => 'test-admin', 'services.admin_password' => 'test-password']);
    }

    public function test_admin_catalog_requires_login(): void
    {
        $this->get('/admin/productos')->assertRedirect('/admin/login');
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('class="admin-footer"', false)
            ->assertSee('PANEL DE ADMINISTRACIÓN');
    }

    public function test_admin_can_create_a_product(): void
    {
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password'])->assertRedirect('/admin/productos');
        $this->get(route('admin.productos.index'))
            ->assertOk()
            ->assertSee('class="admin-footer"', false)
            ->assertSee('PANEL DE ADMINISTRACIÓN')
            ->assertSee('data-admin-menu-toggle', false)
            ->assertSee('aria-controls="admin-navigation"', false)
            ->assertSee('class="admin-navigation-link is-current">Productos</a>', false)
            ->assertSee('Pedidos')
            ->assertSee('Categorías')
            ->assertSee('Ver tienda');

        $this->post('/admin/productos', [
            'name' => 'Producto de prueba',
            'description' => 'Descripción de prueba',
            'base_price' => '49.90',
            'sale_price' => '39.90',
            'profit_percentage' => 20,
            'category' => 'Pruebas',
            'stock' => 7,
            'featured' => 1,
        ])->assertRedirect('/admin/productos');

        $this->assertDatabaseHas('products', [
            'name' => 'Producto de prueba',
            'slug' => 'producto-de-prueba',
            'stock' => 7,
            'base_price' => '49.90',
            'sale_price' => '71.36',
            'profit_percentage' => 20,
            'includes_igv' => 1,
            'includes_surcharge' => 1,
        ]);

        $this->assertContains(
            Product::query()->where('slug', 'producto-de-prueba')->value('offer_percentage'),
            [20, 30],
        );
    }

    public function test_admin_can_create_a_product_without_sending_calculated_price_fields(): void
    {
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->post('/admin/productos', [
            'name' => 'Producto precio calculado',
            'base_price' => '40.00',
            'category' => 'Pruebas',
            'stock' => 2,
        ])->assertRedirect('/admin/productos');

        $this->assertDatabaseHas('products', [
            'name' => 'Producto precio calculado',
            'sale_price' => '71.51',
            'profit_percentage' => 50,
            'includes_igv' => 1,
            'includes_surcharge' => 1,
        ]);
    }

    public function test_admin_can_create_a_category(): void
    {
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->postJson('/admin/categorias', ['name' => 'Hogar inteligente'])
            ->assertCreated()
            ->assertJson(['category' => 'Hogar inteligente']);

        $this->assertDatabaseHas('categories', ['name' => 'Hogar inteligente']);
    }

    public function test_admin_can_create_and_display_a_complete_hierarchical_category_path(): void
    {
        $categoryPath = 'Electrónica > Componentes de computadoras > Dispositivos de almacenamiento de datos > Discos duros internos';
        $this->assertGreaterThan(80, strlen($categoryPath));

        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->postJson('/admin/categorias', ['name' => $categoryPath])
            ->assertCreated()
            ->assertJson(['category' => $categoryPath]);

        $this->post('/admin/productos', [
            'name' => 'Disco duro interno de prueba',
            'base_price' => '149.90',
            'profit_percentage' => 50,
            'category' => $categoryPath,
            'stock' => 4,
        ])->assertRedirect(route('admin.productos.index'));

        $this->assertDatabaseHas('categories', ['name' => $categoryPath]);
        $this->assertDatabaseHas('products', [
            'name' => 'Disco duro interno de prueba',
            'category' => $categoryPath,
        ]);

        $this->get(route('admin.categorias.index'))
            ->assertOk()
            ->assertSee($categoryPath)
            ->assertSee('data-category-branch-toggle', false)
            ->assertSee('role="tree"', false);
        $this->get(route('admin.productos.index'))->assertOk()->assertSee($categoryPath);
        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee($categoryPath)
            ->assertSee('data-category-route="'.e($categoryPath).'"', false)
            ->assertSee('data-category-count="1"', false)
            ->assertSee('data-product-category="'.e($categoryPath).'"', false);
    }

    public function test_admin_can_create_category_from_categories_crud_page(): void
    {
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->get(route('admin.categorias.index'))
            ->assertOk()
            ->assertSee('Nueva categoría');

        $this->post(route('admin.categorias.store'), ['name' => '  Redes y sistemas  '])
            ->assertRedirect(route('admin.categorias.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Redes y sistemas']);
    }

    public function test_admin_can_create_a_subcategory_from_its_parent_category(): void
    {
        $parent = Category::create(['name' => 'Computación']);
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->get(route('admin.categorias.index'))
            ->assertOk()
            ->assertSee('Categoría superior')
            ->assertSee('data-category-path-preview', false)
            ->assertSee('value="Computación"', false);

        $this->post(route('admin.categorias.store'), [
            'parent_category' => $parent->name,
            'category_segment' => 'Almacenamiento',
        ])->assertRedirect(route('admin.categorias.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Computación > Almacenamiento']);
    }

    public function test_admin_can_edit_categories_and_rename_associated_products(): void
    {
        $category = Category::create(['name' => 'Antigua']);
        Product::create([
            'name' => 'Producto categorizado',
            'slug' => 'producto-categorizado',
            'price' => 20,
            'category' => 'Antigua',
            'stock' => 1,
        ]);

        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);
        $this->get(route('admin.categorias.index'))->assertOk()->assertSee('Antigua');
        $this->put(route('admin.categorias.update', $category), ['name' => 'Nueva categoría'])->assertRedirect(route('admin.categorias.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Nueva categoría']);
        $this->assertDatabaseHas('products', ['slug' => 'producto-categorizado', 'category' => 'Nueva categoría']);
    }

    public function test_admin_can_review_and_update_an_order_status(): void
    {
        $order = Order::create([
            'order_number' => 'VGT-TEST-001',
            'customer_name' => 'Cliente Prueba',
            'customer_dni' => '12345678',
            'customer_first_name' => 'Cliente',
            'customer_last_name' => 'Prueba',
            'customer_email' => 'cliente@example.com',
            'customer_phone' => '999999999',
            'department' => 'Lima',
            'province' => 'Lima',
            'district' => 'Miraflores',
            'customer_address' => 'Av. Principal 123',
            'payment_method' => 'yape',
            'payment_account_holder' => 'Cliente Prueba',
            'payment_operation_number' => '987654321',
            'status' => 'pending_validation',
            'total' => 48,
            'items' => [['name' => 'Producto prueba', 'price' => 48, 'quantity' => 1]],
        ]);

        $this->get(route('admin.pedidos.index'))->assertRedirect('/admin/login');
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->get(route('admin.pedidos.index'))->assertOk()->assertSee('VGT-TEST-001');
        $this->get(route('admin.pedidos.show', $order))
            ->assertOk()
            ->assertSee('class="admin-main admin-order-detail"', false)
            ->assertSee('admin-order-delivery-note', false)
            ->assertSee('987654321')
            ->assertSee('Validar pago y preparar pedido')
            ->assertSee('En empaque')
            ->assertSee('value="packing"', false)
            ->assertDontSee('value="paid"', false);
        $this->patch(route('admin.pedidos.update', $order), ['status' => 'packing'])->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'packing']);
        $this->get(route('admin.pedidos.show', $order))
            ->assertOk()
            ->assertSee('Siguiente etapa')
            ->assertSee('Listo para entrega')
            ->assertSee('value="ready_for_pickup"', false)
            ->assertDontSee('Pago validado');

        $this->patch(route('admin.pedidos.update', $order), ['status' => 'paid'])->assertSessionHasErrors('status');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'packing']);
    }

    public function test_admin_next_stage_depends_on_delivery_or_pickup(): void
    {
        $baseOrder = [
            'customer_name' => 'Cliente Prueba',
            'customer_dni' => '12345678',
            'customer_first_name' => 'Cliente',
            'customer_last_name' => 'Prueba',
            'customer_email' => 'cliente@example.com',
            'customer_phone' => '999999999',
            'department' => 'Lima',
            'province' => 'Lima',
            'district' => 'Miraflores',
            'customer_address' => 'Av. Principal 123',
            'payment_method' => 'yape',
            'status' => 'packing',
            'total' => 48,
            'items' => [['name' => 'Producto prueba', 'price' => 48, 'quantity' => 1]],
        ];
        $deliveryOrder = Order::create($baseOrder + [
            'order_number' => 'VGT-TEST-DELIVERY',
            'fulfillment_method' => 'delivery',
        ]);
        $pickupOrder = Order::create($baseOrder + [
            'order_number' => 'VGT-TEST-PICKUP',
            'fulfillment_method' => 'pickup',
        ]);

        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->patch(route('admin.pedidos.update', $deliveryOrder), ['status' => 'packing'])->assertRedirect();
        $this->get(route('admin.pedidos.show', $deliveryOrder))
            ->assertOk()
            ->assertSee('Listo para entrega')
            ->assertSee('value="ready_for_pickup"', false);
        $this->assertDatabaseHas('orders', ['id' => $deliveryOrder->id, 'status' => 'packing']);
        $this->patch(route('admin.pedidos.update', $deliveryOrder), ['status' => 'ready_for_pickup'])->assertRedirect();
        $this->get(route('admin.pedidos.show', $deliveryOrder))
            ->assertOk()
            ->assertSee('Entregado')
            ->assertSee('value="completed"', false);

        $this->patch(route('admin.pedidos.update', $pickupOrder), ['status' => 'packing'])->assertRedirect();
        $this->get(route('admin.pedidos.show', $pickupOrder))
            ->assertOk()
            ->assertSee('Listo para recojo')
            ->assertSee('value="ready_for_pickup"', false);
        $this->assertDatabaseHas('orders', ['id' => $pickupOrder->id, 'status' => 'packing']);
        $this->patch(route('admin.pedidos.update', $pickupOrder), ['status' => 'ready_for_pickup'])->assertRedirect();
        $this->get(route('admin.pedidos.show', $pickupOrder))
            ->assertOk()
            ->assertSee('Recogido')
            ->assertSee('value="completed"', false);

        $pickupOrder->update(['status' => 'preparing']);
        $this->get(route('admin.pedidos.show', $pickupOrder))
            ->assertOk()
            ->assertSee('En empaque');
    }

    public function test_admin_can_prepare_a_cash_on_delivery_order_without_payment_validation(): void
    {
        $order = Order::create([
            'order_number' => 'VGT-TEST-CASH',
            'customer_name' => 'Cliente Contra Entrega',
            'customer_dni' => '12345678',
            'customer_first_name' => 'Cliente',
            'customer_last_name' => 'Contra Entrega',
            'customer_email' => 'cliente@example.com',
            'customer_phone' => '999999999',
            'department' => 'Cajamarca',
            'province' => 'Cajamarca',
            'district' => 'Cajamarca',
            'customer_address' => 'Miguel Iglesias 991',
            'fulfillment_method' => 'pickup',
            'payment_method' => 'contra_entrega',
            'status' => 'pending_payment',
            'total' => 48,
            'items' => [['name' => 'Producto prueba', 'price' => 48, 'quantity' => 1]],
        ]);

        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->get(route('admin.pedidos.show', $order))
            ->assertOk()
            ->assertSee('Pendiente de pago')
            ->assertSee('Confirmar y preparar pedido')
            ->assertSee('En empaque');

        $this->patch(route('admin.pedidos.update', $order), ['status' => 'packing'])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'packing']);
    }

    public function test_admin_can_search_orders_by_name_or_dni_and_keep_search_when_paginating(): void
    {
        foreach (range(1, 17) as $number) {
            $orderDate = now()->subMinutes($number);
            $order = Order::create([
                'order_number' => sprintf('VGT-SEARCH-%02d', $number),
                'customer_name' => sprintf('Cliente Test %02d', $number),
                'customer_dni' => sprintf('1234%04d', $number),
                'customer_first_name' => 'Cliente',
                'customer_last_name' => 'Test',
                'customer_email' => 'cliente@example.com',
                'customer_phone' => '999999999',
                'department' => 'Lima',
                'province' => 'Lima',
                'district' => 'Miraflores',
                'customer_address' => 'Av. Principal 123',
                'fulfillment_method' => 'delivery',
                'payment_method' => 'yape',
                'status' => 'packing',
                'total' => 48,
                'items' => [['name' => 'Producto prueba', 'price' => 48, 'quantity' => 1]],
            ]);
            Order::query()->whereKey($order->id)->update(['created_at' => $orderDate]);
        }

        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->get(route('admin.pedidos.index', ['search' => 'Cliente Test']))
            ->assertOk()
            ->assertSee('name="search"', false)
            ->assertSee('VGT-SEARCH-01')
            ->assertSee('VGT-SEARCH-10')
            ->assertDontSee('VGT-SEARCH-11')
            ->assertSee('search=Cliente%20Test', false);

        $this->get(route('admin.pedidos.index', ['search' => 'Cliente Test', 'page' => 2]))
            ->assertOk()
            ->assertSee('VGT-SEARCH-17');

        $this->get(route('admin.pedidos.index', ['status' => 'packing', 'search' => '12340017']))
            ->assertOk()
            ->assertSee('value="12340017"', false)
            ->assertSee('VGT-SEARCH-17')
            ->assertDontSee('VGT-SEARCH-16')
            ->assertSee('search=12340017', false);

        $partialResponse = $this->get(route('admin.pedidos.index', ['search' => 'Cliente Test']), [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->assertOk()->assertJsonPath('total', 17);

        $partialHtml = $partialResponse->json('html');
        $this->assertStringContainsString('VGT-SEARCH-01', $partialHtml);
        $this->assertStringContainsString('VGT-SEARCH-10', $partialHtml);
        $this->assertStringNotContainsString('VGT-SEARCH-11', $partialHtml);
        $this->assertStringContainsString('search=Cliente%20Test&amp;page=2', $partialHtml);
        $this->assertStringNotContainsString('admin-order-stats', $partialHtml);
    }

    public function test_product_form_displays_all_available_categories(): void
    {
        Category::create(['name' => 'Oficina']);
        Product::create([
            'name' => 'Producto conectado',
            'slug' => 'producto-conectado',
            'price' => 25,
            'category' => 'Conectividad',
            'stock' => 2,
        ]);

        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->get(route('admin.productos.create'))
            ->assertOk()
            ->assertSee('Stock disponible')
            ->assertDontSee('Detalle del cálculo')
            ->assertSee('data-include-igv checked', false)
            ->assertSee('data-include-surcharge checked', false)
            ->assertSee('value="100"', false)
            ->assertSee('value="5"', false)
            ->assertSee('value="0"', false)
            ->assertSee('data-category-tree', false)
            ->assertSee('data-category-quick-list hidden', false)
            ->assertDontSee('list="category-suggestions"', false)
            ->assertSee('data-category-choice="Oficina"', false)
            ->assertSee('data-category-choice="Conectividad"', false);
    }

    public function test_admin_can_set_a_manual_offer_over_the_sale_price(): void
    {
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->post('/admin/productos', [
            'name' => 'Oferta manual',
            'description' => 'Producto con promoción controlada.',
            'base_price' => '40.00',
            'sale_price' => '30.00',
            'profit_percentage' => 50,
            'category' => 'Promociones',
            'stock' => 3,
            'manual_offer' => 1,
            'offer_percentage' => 50,
            'offer_duration_hours' => 48,
        ])->assertRedirect('/admin/productos');

        $this->assertDatabaseHas('products', [
            'name' => 'Oferta manual',
            'base_price' => '40.00',
            'sale_price' => '71.51',
            'price' => '35.76',
            'profit_percentage' => 50,
            'offer_percentage' => 50,
            'offer_duration_hours' => 48,
        ]);
    }

    public function test_igv_and_surcharge_are_optional_and_full_discount_is_allowed(): void
    {
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        foreach ([
            ['Solo IGV', 40, 50, 1, 0, '70.80'],
            ['Solo recargo', 40, 50, 0, 1, '60.60'],
            ['Sin ajustes', 40, 50, 0, 0, '60.00'],
            ['Margen sobre costo', 30, 50, 0, 0, '45.00'],
            ['Margen al 100', 30, 100, 0, 0, '60.00'],
        ] as [$name, $basePrice, $margin, $includesIgv, $includesSurcharge, $salePrice]) {
            $this->post('/admin/productos', [
                'name' => $name,
                'base_price' => $basePrice,
                'profit_percentage' => $margin,
                'includes_igv' => $includesIgv,
                'includes_surcharge' => $includesSurcharge,
                'category' => 'Pruebas',
                'stock' => 1,
                'manual_offer' => 1,
                'offer_percentage' => 10,
                'offer_duration_hours' => 72,
            ])->assertRedirect(route('admin.productos.index'));

            $this->assertDatabaseHas('products', [
                'name' => $name,
                'sale_price' => $salePrice,
                'includes_igv' => $includesIgv,
                'includes_surcharge' => $includesSurcharge,
            ]);
        }

        $this->post('/admin/productos', [
            'name' => 'Descuento total',
            'base_price' => '40.00',
            'profit_percentage' => 50,
            'category' => 'Pruebas',
            'stock' => 1,
            'manual_offer' => 1,
            'offer_percentage' => 100,
            'offer_duration_hours' => 72,
        ])->assertRedirect(route('admin.productos.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Descuento total',
            'sale_price' => '71.51',
            'price' => '0.00',
            'offer_percentage' => 100,
        ]);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Descuento total')
            ->assertSee('S/ 71,51');

        $this->post('/admin/productos', [
            'name' => 'Sin descuento manual',
            'base_price' => '30.00',
            'profit_percentage' => 50,
            'includes_igv' => 0,
            'includes_surcharge' => 0,
            'category' => 'Pruebas',
            'stock' => 1,
            'manual_offer' => 1,
            'offer_percentage' => 0,
            'offer_duration_hours' => 72,
        ])->assertRedirect(route('admin.productos.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Sin descuento manual',
            'sale_price' => '45.00',
            'price' => '45.00',
            'offer_percentage' => 0,
        ]);
    }

    public function test_admin_can_edit_a_product_and_append_its_image(): void
    {
        Storage::fake('public');
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);
        $product = Product::create([
            'name' => 'Producto editable',
            'slug' => 'producto-editable',
            'description' => 'Descripción original',
            'price' => 30,
            'includes_igv' => false,
            'includes_surcharge' => true,
            'category' => 'Pruebas',
            'stock' => 4,
            'offer_percentage' => 50,
            'offer_ends_at' => now()->addDay(),
        ]);

        $this->get(route('admin.productos.edit', $product))->assertOk()->assertSee('Edita tu producto');
        $this->put(route('admin.productos.update', $product), [
            'name' => 'Producto actualizado',
            'description' => 'Descripción nueva',
            'base_price' => 40,
            'sale_price' => 50,
            'profit_percentage' => 20,
            'category' => 'Novedades',
            'stock' => 9,
            'image' => UploadedFile::fake()->image('producto.png'),
        ])->assertRedirect(route('admin.productos.index'));

        $product->refresh();
        $this->assertSame('Producto actualizado', $product->name);
        $this->assertSame(50, $product->offer_percentage);
        $this->assertSame('24.24', number_format((float) $product->price, 2, '.', ''));
        $this->assertSame('40.00', number_format((float) $product->base_price, 2, '.', ''));
        $this->assertSame('48.48', number_format((float) $product->sale_price, 2, '.', ''));
        $this->assertFalse($product->includes_igv);
        $this->assertTrue($product->includes_surcharge);
        $this->assertTrue(Storage::disk('public')->exists($product->image));
        $this->assertCount(1, $product->images);
    }

    public function test_admin_appends_images_to_an_existing_gallery(): void
    {
        Storage::fake('public');
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);
        $originalImage = UploadedFile::fake()->image('original.png');

        $this->post('/admin/productos', [
            'name' => 'Galería acumulativa',
            'base_price' => 40,
            'profit_percentage' => 50,
            'category' => 'Pruebas',
            'stock' => 3,
            'images' => [$originalImage],
        ]);
        $product = Product::query()->where('slug', 'galeria-acumulativa')->firstOrFail();
        $originalPath = $product->image;

        $this->put(route('admin.productos.update', $product), [
            'name' => $product->name,
            'base_price' => 40,
            'profit_percentage' => 50,
            'category' => $product->category,
            'stock' => 3,
            'images' => [UploadedFile::fake()->image('nueva.png')],
        ])->assertRedirect(route('admin.productos.index'));

        $product->refresh();
        $this->assertCount(2, $product->images);
        $this->assertSame($originalPath, $product->images[0]);
        $this->assertTrue(Storage::disk('public')->exists($originalPath));
        $this->assertTrue(Storage::disk('public')->exists($product->images[1]));
    }

    public function test_admin_can_upload_multiple_product_images(): void
    {
        Storage::fake('public');
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->post('/admin/productos', [
            'name' => 'Producto con galería',
            'description' => 'Producto con varias imágenes.',
            'base_price' => 40,
            'profit_percentage' => 50,
            'category' => 'Pruebas',
            'stock' => 4,
            'images' => [
                UploadedFile::fake()->image('frontal.png'),
                UploadedFile::fake()->image('lateral.png'),
            ],
        ])->assertRedirect(route('admin.productos.index'));

        $product = Product::query()->where('slug', 'producto-con-galeria')->firstOrFail();

        $this->assertCount(2, $product->images);
        $this->assertSame($product->images[0], $product->image);
        $this->assertTrue(Storage::disk('public')->exists($product->images[0]));
        $this->assertTrue(Storage::disk('public')->exists($product->images[1]));
    }
}
