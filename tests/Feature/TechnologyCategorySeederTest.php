<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnologyCategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_adds_the_complete_taxonomy_without_overwriting_existing_data(): void
    {
        Category::create(['name' => 'Categoría existente']);
        Product::create([
            'name' => 'Producto existente',
            'slug' => 'producto-existente',
            'price' => 25,
            'category' => 'Categoría existente',
            'stock' => 3,
        ]);

        $this->seed();

        $categoryCount = Category::query()->count();
        $categoryNames = Category::query()->pluck('name');

        $this->seed();

        $this->assertSame($categoryCount, Category::query()->count());
        $this->assertDatabaseHas('categories', ['name' => 'Categoría existente']);
        $this->assertDatabaseHas('products', [
            'slug' => 'producto-existente',
            'category' => 'Categoría existente',
        ]);

        foreach ([
            'Electrónica, Audio y Video > Audio > Audífonos y auriculares > True Wireless',
            'Electrónica, Audio y Video > Video > Reproductores de streaming > Xiaomi TV Box',
            'Electrónica, Audio y Video > Componentes Electrónicos > Placas de desarrollo > Raspberry Pi',
            'Computación > Componentes de PC > Almacenamiento > SSD',
            'Computación > Periféricos de PC > Audio para PC > Headsets gamer',
            'Celulares y Teléfonos > Accesorios para Celulares > Cargadores y cables > Lightning',
            'Consolas y Videojuegos > Accesorios para Consolas > Mandos y controles',
            'Cámaras y Accesorios > Monitoreo y Seguridad > Cámaras IP',
        ] as $categoryName) {
            $this->assertDatabaseHas('categories', ['name' => $categoryName]);
        }

        $categoryPath = 'Computación > Componentes de PC > Almacenamiento > SSD';
        config(['services.admin_username' => 'test-admin', 'services.admin_password' => 'test-password']);
        $this->post('/admin/login', ['username' => 'test-admin', 'password' => 'test-password']);

        $this->get(route('admin.productos.create'))
            ->assertOk()
            ->assertSee('data-category-choice="'.e($categoryPath).'"', false);

        $this->post(route('admin.productos.store'), [
            'name' => 'SSD categorizado',
            'base_price' => '100.00',
            'profit_percentage' => 50,
            'category' => $categoryPath,
            'stock' => 2,
        ])->assertRedirect(route('admin.productos.index'));

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('data-category-route="'.e($categoryPath).'"', false)
            ->assertSee('data-category-count="1"', false)
            ->assertDontSee('data-category-route="Cámaras y Accesorios > Cámaras"', false)
            ->assertSee('data-product-category="'.e($categoryPath).'"', false);

        $this->assertLessThanOrEqual(255, $categoryNames->max(
            fn (string $name): int => mb_strlen($name),
        ));
    }
}
