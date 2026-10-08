<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_tracking_returns_not_found_without_matching_orders(): void
    {
        $this->postJson(route('tracking.orders'), [
            'customer_dni' => '12345678',
            'customer_phone' => '999999999',
        ])->assertNotFound()->assertJson([
            'message' => 'No encontramos pedidos con esos datos.',
        ]);
    }

    public function test_order_tracking_returns_sanitized_order_data(): void
    {
        $order = Order::create([
            'order_number' => 'VGT-260926-ABC123',
            'customer_name' => 'Cliente de prueba',
            'customer_dni' => '12345678',
            'customer_first_name' => 'Cliente',
            'customer_last_name' => 'de prueba',
            'customer_phone' => '999999999',
            'customer_email' => 'cliente@example.com',
            'department' => 'Lima',
            'province' => 'Lima',
            'district' => 'Miraflores',
            'customer_address' => 'Av. Principal 123',
            'fulfillment_method' => 'delivery',
            'payment_method' => 'yape',
            'payment_operation_number' => 'OPERACION-SECRETA',
            'status' => 'pending_validation',
            'total' => 48,
            'items' => [['name' => 'Producto de prueba', 'quantity' => 2, 'price' => 24, 'image' => 'privada.png']],
        ]);

        $this->postJson(route('tracking.orders'), [
            'customer_dni' => '12345678',
            'customer_phone' => '999999999',
        ])->assertOk()->assertJsonPath('orders.0.order_number', 'VGT-260926-ABC123')
            ->assertJsonPath('orders.0.status', 'pending_validation')
            ->assertJsonPath('orders.0.status_label', 'Validando pago')
            ->assertJsonMissingPath('orders.0.payment_operation_number')
            ->assertJsonMissingPath('orders.0.customer_email');

        foreach ([
            ['pending_payment', 'delivery', 'Pendiente de pago'],
            ['packing', 'delivery', 'En empaque'],
            ['ready_for_pickup', 'delivery', 'Listo para entrega'],
            ['ready_for_pickup', 'pickup', 'Listo para recojo'],
            ['completed', 'delivery', 'Entregado'],
            ['completed', 'pickup', 'Recogido'],
        ] as [$status, $fulfillmentMethod, $label]) {
            $order->update(['status' => $status, 'fulfillment_method' => $fulfillmentMethod]);

            $this->postJson(route('tracking.orders'), [
                'customer_dni' => '12345678',
                'customer_phone' => '999999999',
            ])->assertOk()->assertJsonPath('orders.0.status_label', $label);
        }
    }

    public function test_original_offer_price_uses_the_discount_formula(): void
    {
        $product = Product::create([
            'name' => 'Oferta calculada',
            'slug' => 'oferta-calculada',
            'price' => 40,
            'offer_percentage' => 50,
            'offer_ends_at' => now()->addDay(),
            'stock' => 1,
        ]);

        $this->assertSame(80.0, $product->original_price);
    }

    public function test_store_home_displays_categories_and_products_grouped_by_category(): void
    {
        Category::create(['name' => 'Accesorios']);
        Category::create(['name' => 'Categoría sin productos']);
        Product::create([
            'name' => 'Producto visible',
            'slug' => 'producto-visible',
            'description' => 'Producto para la colección.',
            'price' => 30,
            'category' => 'Accesorios',
            'stock' => 5,
            'offer_percentage' => 20,
            'offer_ends_at' => now()->addDay(),
        ]);
        Product::create([
            'name' => 'Producto sin oferta',
            'slug' => 'producto-sin-oferta',
            'description' => 'Producto que no debe aparecer en la vitrina de ofertas.',
            'price' => 20,
            'category' => 'Accesorios',
            'stock' => 5,
        ]);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('data-mobile-menu-toggle', false)
            ->assertSee('aria-controls="mobile-store-navigation"', false)
            ->assertSee('data-mobile-site-nav', false)
            ->assertSee('Colección')
            ->assertSee('Seguimiento')
            ->assertSee('Accesorios')
            ->assertSee('data-category-route="Accesorios"', false)
            ->assertSee('data-category-count="2"', false)
            ->assertDontSee('data-category-route="Categoría sin productos"', false)
            ->assertSee('id="catalog-product-search"', false)
            ->assertSee('data-product-search-text="Producto visible', false)
            ->assertSee('data-product-search-similar="Producto visible Accesorios"', false)
            ->assertSee('data-product-category="Accesorios"', false)
            ->assertSee('Producto visible')
            ->assertSee('Producto sin oferta')
            ->assertSee('data-product-offer="false"', false)
            ->assertSee('data-category-filter="__offers__"', false)
            ->assertSee('href="tel:+51967151428"', false)
            ->assertSee('href="https://wa.me/51967151428?text=', false)
            ->assertSee('class="whatsapp-sales-float"', false)
            ->assertSee('aria-label="Contactar con un asesor por WhatsApp"', false)
            ->assertSee('Contactar con un asesor')
            ->assertSee('Llamar a Ventura Global Technology al +51 967 151 428');
    }

    public function test_footer_social_profiles_render_configured_urls_and_placeholders(): void
    {
        config([
            'services.social.instagram_url' => 'https://instagram.com/ventura-test',
            'services.social.facebook_url' => '',
            'services.social.tiktok_url' => '',
            'services.social.youtube_url' => '',
            'services.social.linkedin_url' => '',
            'services.social.x_url' => '',
        ]);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('href="https://instagram.com/ventura-test"', false)
            ->assertSee('WhatsApp')
            ->assertSee('Instagram')
            ->assertSee('Facebook')
            ->assertSee('TikTok')
            ->assertSee('YouTube')
            ->assertSee('LinkedIn')
            ->assertSee('Configura enlace')
            ->assertSee('href="tel:+51967151428"', false);
    }

    public function test_ajax_add_to_cart_returns_count_without_redirecting(): void
    {
        $product = Product::create([
            'name' => 'Producto carrito',
            'slug' => 'producto-carrito',
            'description' => 'Producto para probar el carrito.',
            'price' => 30,
            'category' => 'Pruebas',
            'stock' => 5,
            'image' => 'products/producto-carrito.png',
        ]);

        $response = $this->postJson(route('cart.add', $product));

        $response->assertOk()->assertJson([
            'cart_count' => 1,
            'stock_remaining' => 4,
            'message' => 'Producto añadido a tu carrito.',
        ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 4]);
        $this->assertSame(1, session('cart')[$product->id]['quantity']);
        $this->assertSame('products/producto-carrito.png', session('cart')[$product->id]['image']);
        $this->assertSame('Pruebas', session('cart')[$product->id]['category']);
    }

    public function test_cart_displays_product_image_and_order_details(): void
    {
        $product = Product::create([
            'name' => 'Producto visual',
            'slug' => 'producto-visual',
            'price' => 48,
            'image' => 'products/visual.png',
            'category' => 'Accesorios',
            'stock' => 3,
        ]);

        $this->withSession(['cart' => [
            $product->id => [
                'name' => $product->name,
                'price' => 48,
                'quantity' => 2,
                'image' => $product->image,
                'category' => $product->category,
            ],
        ]])->get(route('cart.index'))
            ->assertOk()
            ->assertSee('storage/products/visual.png', false)
            ->assertSee('Accesorios')
            ->assertSee('2 unidad(es)')
            ->assertSee('S/ 96,00');
    }

    public function test_cart_response_is_not_cached_and_reads_the_current_session_quantity(): void
    {
        $product = Product::create([
            'name' => 'Producto carrito sin caché',
            'slug' => 'producto-carrito-sin-cache',
            'price' => 16,
            'category' => 'Pruebas',
            'stock' => 2,
        ]);

        $response = $this->withSession(['cart' => [
            $product->id => [
                'name' => $product->name,
                'price' => 16,
                'quantity' => 2,
            ],
        ]])->get(route('cart.index'));

        $response->assertOk()
            ->assertSee('2 unidad(es)')
            ->assertHeader('Pragma', 'no-cache');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame(2, session('cart')[$product->id]['quantity']);
    }

    public function test_cart_remains_after_visiting_checkout_without_confirming_order(): void
    {
        $product = Product::create([
            'name' => 'Producto para conservar en carrito',
            'slug' => 'producto-para-conservar-en-carrito',
            'price' => 48,
            'category' => 'Accesorios',
            'stock' => 3,
        ]);

        $this->withSession(['cart' => [
            $product->id => [
                'name' => $product->name,
                'price' => 48,
                'quantity' => 2,
                'category' => $product->category,
            ],
        ]])->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('data-checkout-step="3"', false)
            ->assertSee('data-checkout-submit', false)
            ->assertSee('data-yape-instructions', false)
            ->assertSee('data-payment-qr-card="yape"', false)
            ->assertSee('data-payment-qr-card="plin"', false)
            ->assertSee('images/PLIN.jpeg')
            ->assertDontSee('QR de Plin pendiente')
            ->assertSee('Cuenta Simple Soles')
            ->assertSee('898 3364503905')
            ->assertSee('00389801336450390547')
            ->assertSee('Sandro E. Ventura Mendoza')
            ->assertSee('Titular: Sandro E. Ventura Mendoza')
            ->assertSee('data-bank-transfer-instructions', false)
            ->assertSee('images/yape.jpeg')
            ->assertSee('S/ 96,00')
            ->assertSee('Escanea y yapea el total exacto')
            ->assertSee('Confirmar pedido');

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('2 unidad(es)');

        $this->assertSame(2, session('cart')[$product->id]['quantity']);
    }

    public function test_cart_can_decrease_and_remove_a_product_while_restoring_stock(): void
    {
        $product = Product::create([
            'name' => 'Producto editable del carrito',
            'slug' => 'producto-editable-del-carrito',
            'price' => 25,
            'category' => 'Pruebas',
            'stock' => 3,
        ]);

        $session = [
            'cart' => [
                $product->id => [
                    'name' => $product->name,
                    'price' => 25,
                    'quantity' => 2,
                    'category' => $product->category,
                ],
            ],
        ];

        $this->withSession($session)
            ->post(route('cart.decrease', $product))
            ->assertRedirect(route('cart.index'));

        $product->refresh();
        $this->assertSame(4, $product->stock);
        $this->assertSame(1, session('cart')[$product->id]['quantity']);

        $this->delete(route('cart.remove', $product))->assertRedirect(route('cart.index'));

        $product->refresh();
        $this->assertSame(5, $product->stock);
        $this->assertArrayNotHasKey($product->id, session('cart', []));
    }

    public function test_customer_can_create_a_manual_payment_order_from_the_cart(): void
    {
        $product = Product::create([
            'name' => 'Producto para pedido',
            'slug' => 'producto-para-pedido',
            'price' => 48,
            'category' => 'Accesorios',
            'stock' => 2,
        ]);

        $response = $this->withSession(['cart' => [
            $product->id => [
                'name' => $product->name,
                'price' => 48,
                'quantity' => 1,
                'category' => $product->category,
            ],
        ]])->post(route('checkout.store'), [
            'customer_dni' => '12345678',
            'customer_first_name' => 'Ana',
            'customer_last_name' => 'Ventura',
            'customer_email' => 'ana@example.com',
            'customer_phone' => '999999999',
            'fulfillment_method' => 'delivery',
            'department' => 'Lima',
            'province' => 'Lima',
            'district' => 'Miraflores',
            'customer_address' => 'Av. Principal 123, Lima',
            'payment_method' => 'yape',
            'payment_account_holder' => 'Ana Ventura',
            'payment_operation_number' => '123456789',
        ]);

        $response->assertOk()->assertSee('Pedido recibido correctamente');
        $response->assertSee('Validando pago')
            ->assertSee('En empaque')
            ->assertSee('coordinaremos el envío contigo')
            ->assertSee('Delivery');
        $this->assertDatabaseHas('orders', [
            'customer_dni' => '12345678',
            'customer_first_name' => 'Ana',
            'customer_last_name' => 'Ventura',
            'customer_email' => 'ana@example.com',
            'payment_method' => 'yape',
            'total' => '48.00',
            'status' => 'pending_validation',
            'fulfillment_method' => 'delivery',
        ]);
        $this->assertDatabaseHas('orders', ['payment_account_holder' => 'Ana Ventura', 'payment_operation_number' => '123456789']);
        $this->assertDatabaseHas('orders', ['department' => 'Lima', 'province' => 'Lima', 'district' => 'Miraflores']);
        $this->assertSame([], session('cart', []));
    }

    public function test_pickup_confirmation_shows_the_pickup_specific_next_steps(): void
    {
        $product = Product::create([
            'name' => 'Producto para recojo pagado',
            'slug' => 'producto-recojo-pagado',
            'price' => 25,
            'category' => 'Pruebas',
            'stock' => 1,
        ]);

        $response = $this->withSession(['cart' => [
            $product->id => [
                'name' => $product->name,
                'price' => 25,
                'quantity' => 1,
                'category' => $product->category,
            ],
        ]])->post(route('checkout.store'), [
            'customer_dni' => '11223344',
            'customer_first_name' => 'María',
            'customer_last_name' => 'Recojo',
            'customer_email' => 'pickup@example.com',
            'customer_phone' => '966666666',
            'fulfillment_method' => 'pickup',
            'payment_method' => 'yape',
            'payment_account_holder' => 'María Recojo',
            'payment_operation_number' => 'PICKUP-001',
        ]);

        $response->assertOk()
            ->assertSee('Validando pago')
            ->assertSee('En empaque')
            ->assertSee('listo para recojo en tienda')
            ->assertSee('Recojo en tienda')
            ->assertSee('Miguel Iglesias 991, Cajamarca');

        $this->assertDatabaseHas('orders', [
            'customer_dni' => '11223344',
            'fulfillment_method' => 'pickup',
            'status' => 'pending_validation',
        ]);
    }

    public function test_cash_on_delivery_opens_whatsapp_with_the_complete_order_details(): void
    {
        $product = Product::create([
            'name' => 'Producto contra entrega',
            'slug' => 'producto-contra-entrega',
            'price' => 35,
            'category' => 'Pruebas',
            'stock' => 1,
        ]);

        $response = $this->withSession(['cart' => [
            $product->id => [
                'name' => $product->name,
                'price' => 35,
                'quantity' => 1,
                'category' => $product->category,
            ],
        ]])->post(route('checkout.store'), [
            'customer_dni' => '87654321',
            'customer_first_name' => 'Luis',
            'customer_last_name' => 'Prueba',
            'customer_email' => 'luis@example.com',
            'customer_phone' => '987654321',
            'fulfillment_method' => 'delivery',
            'department' => 'Lima',
            'province' => 'Lima',
            'district' => 'Surco',
            'customer_address' => 'Av. Test 456',
            'payment_method' => 'contra_entrega',
        ]);

        $response->assertRedirectContains('https://wa.me/51967151428');
        $whatsappMessage = urldecode((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY));

        $this->assertMatchesRegularExpression('/\*Fecha:\* \d{2}\/\d{2}\/\d{4} \d{2}:\d{2}/', $whatsappMessage);
        $this->assertStringContainsString('*Cliente:* Luis Prueba', $whatsappMessage);
        $this->assertStringContainsString('*DNI:* 87654321', $whatsappMessage);
        $this->assertStringContainsString('*Correo:* luis@example.com', $whatsappMessage);
        $this->assertStringContainsString('*Teléfono:* 987654321', $whatsappMessage);
        $this->assertStringContainsString('Categoría: Pruebas', $whatsappMessage);
        $this->assertStringContainsString('Cantidad: 1 unidad(es)', $whatsappMessage);
        $this->assertStringContainsString('Precio unitario: S/ 35.00', $whatsappMessage);
        $this->assertStringContainsString('Subtotal: S/ 35.00', $whatsappMessage);
        $this->assertStringContainsString('*Total:* S/ 35.00', $whatsappMessage);
        $this->assertStringContainsString('*Pago:* Contra entrega', $whatsappMessage);
        $this->assertStringContainsString('Producto+contra+entrega', $response->headers->get('Location'));
        $this->assertStringContainsString('87654321', $response->headers->get('Location'));
        $this->assertStringContainsString('%2ADepartamento%3A%2A+Lima', $response->headers->get('Location'));
        $this->assertStringContainsString('%2AProvincia%3A%2A+Lima', $response->headers->get('Location'));
        $this->assertStringContainsString('%2ADistrito%3A%2A+Surco', $response->headers->get('Location'));
        $this->assertStringContainsString('%2ADirecci%C3%B3n+exacta%3A%2A+Av.+Test+456', $response->headers->get('Location'));
        $this->assertStringContainsString('%2AModalidad%3A%2A+Delivery', $response->headers->get('Location'));
        $this->assertStringContainsString('%2ADelivery%3A%2A+costo+a+cargo+del+cliente', $response->headers->get('Location'));
        $this->assertDatabaseHas('orders', [
            'payment_method' => 'contra_entrega',
            'customer_dni' => '87654321',
            'fulfillment_method' => 'delivery',
            'status' => 'pending_payment',
        ]);
    }

    public function test_pickup_order_uses_the_store_address_and_whatsapp_instructions(): void
    {
        $product = Product::create([
            'name' => 'Producto para recojo',
            'slug' => 'producto-para-recojo',
            'price' => 25,
            'category' => 'Pruebas',
            'stock' => 1,
        ]);

        $response = $this->withSession(['cart' => [
            $product->id => [
                'name' => $product->name,
                'price' => 25,
                'quantity' => 1,
                'category' => $product->category,
            ],
        ]])->post(route('checkout.store'), [
            'customer_dni' => '11223344',
            'customer_first_name' => 'María',
            'customer_last_name' => 'Recojo',
            'customer_email' => 'pickup@example.com',
            'customer_phone' => '966666666',
            'fulfillment_method' => 'pickup',
            'payment_method' => 'contra_entrega',
        ]);

        $response->assertRedirectContains('https://wa.me/51967151428');
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('%2AModalidad%3A%2A+Recojo+en+tienda', $location);
        $this->assertStringContainsString('%2ARecojo+en+tienda%3A%2A+Miguel+Iglesias+991%2C+Cajamarca', $location);
        $this->assertDatabaseHas('orders', [
            'customer_dni' => '11223344',
            'fulfillment_method' => 'pickup',
            'department' => 'Cajamarca',
            'province' => 'Cajamarca',
            'district' => 'Cajamarca',
            'customer_address' => 'Miguel Iglesias 991, Cajamarca',
        ]);
    }
}
