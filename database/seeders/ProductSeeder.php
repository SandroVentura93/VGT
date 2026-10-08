<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Kit Tecnología Esencial', 'slug' => 'kit-tecnologia-esencial', 'description' => 'Todo lo que necesitas para empezar a trabajar mejor.', 'price' => 49.90, 'category' => 'Destacados', 'featured' => true, 'stock' => 18],
            ['name' => 'Accesorios Smart Office', 'slug' => 'accesorios-smart-office', 'description' => 'Accesorios seleccionados para tu espacio digital.', 'price' => 29.90, 'category' => 'Oficina', 'featured' => true, 'stock' => 25],
            ['name' => 'Pack Conectividad Pro', 'slug' => 'pack-conectividad-pro', 'description' => 'Conecta todos tus dispositivos de forma sencilla.', 'price' => 79.00, 'category' => 'Conectividad', 'featured' => false, 'stock' => 12],
            ['name' => 'Soporte Premium', 'slug' => 'soporte-premium', 'description' => 'Acompañamiento experto para tus soluciones tecnológicas.', 'price' => 99.00, 'category' => 'Servicios', 'featured' => true, 'stock' => 8],
        ];

        foreach ($products as $product) {
            $product['offer_percentage'] = collect([20, 30])->random();
            $product['offer_duration_hours'] = random_int(36, 120);
            $product['offer_ends_at'] = now()->addHours($product['offer_duration_hours']);
            Product::updateOrCreate(['slug' => $product['slug']], $product);
        }
    }
}
