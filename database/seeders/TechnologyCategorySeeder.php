<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class TechnologyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxonomy = [
            'Electrónica, Audio y Video' => [
                'Audio' => [
                    'Audífonos y auriculares' => [
                        'Bluetooth' => [],
                        'True Wireless' => [],
                        'In-Ear' => [],
                        'De diadema' => [],
                    ],
                    'Audio portátil y accesorios' => [
                        'Parlantes Bluetooth' => [],
                        'Reproductores de audio' => [],
                    ],
                    'Equipos de música' => [],
                    'Home Theater' => [],
                    'Micrófonos' => [],
                    'Amplificadores' => [],
                ],
                'Video' => [
                    'Televisores y Smart TVs' => [],
                    'Proyectores y pantallas' => [],
                    'Reproductores de streaming' => [
                        'Roku' => [],
                        'Apple TV' => [],
                        'Xiaomi TV Box' => [],
                    ],
                ],
                'Componentes Electrónicos' => [
                    'Placas de desarrollo' => [
                        'Arduino' => [],
                        'Raspberry Pi' => [],
                    ],
                    'Circuitos integrados' => [],
                    'Resistencias' => [],
                    'Condensadores' => [],
                ],
            ],
            'Computación' => [
                'Laptops y Accesorios' => [
                    'Laptops y notebooks' => [],
                    'Accesorios para laptops' => [
                        'Cargadores' => [],
                        'Baterías' => [],
                        'Fundas' => [],
                    ],
                ],
                'Componentes de PC' => [
                    'Almacenamiento' => [
                        'SSD' => [],
                        'Discos duros' => [],
                        'Memorias USB' => [],
                    ],
                    'Memorias RAM' => [],
                    'Procesadores' => [],
                    'Tarjetas de video' => [],
                    'Placas madre' => [],
                    'Fuentes de poder' => [],
                    'Coolers' => [],
                ],
                'Periféricos de PC' => [
                    'Mouses' => [],
                    'Teclados' => [],
                    'Monitores' => [],
                    'Audio para PC' => [
                        'Headsets gamer' => [],
                        'Parlantes de escritorio' => [],
                    ],
                ],
            ],
            'Celulares y Teléfonos' => [
                'Celulares y Smartphones' => [],
                'Accesorios para Celulares' => [
                    'Carcasas, fundas y protectores' => [],
                    'Cargadores y cables' => [
                        'USB-C' => [],
                        'Lightning' => [],
                    ],
                ],
                'Smartwatches y Accesorios' => [
                    'Relojes inteligentes' => [],
                    'Pulseras de actividad' => [],
                ],
            ],
            'Consolas y Videojuegos' => [
                'Consolas' => [
                    'PlayStation 5' => [],
                    'Nintendo Switch' => [],
                    'Xbox' => [],
                ],
                'Videojuegos' => [
                    'Físicos' => [],
                    'Digitales' => [],
                ],
                'Accesorios para Consolas' => [
                    'Mandos y controles' => [],
                    'Timones' => [],
                    'Cables de carga' => [],
                    'Docks' => [],
                ],
            ],
            'Cámaras y Accesorios' => [
                'Cámaras' => [
                    'Réflex' => [],
                    'Mirrorless' => [],
                    'Cámaras de acción' => [
                        'GoPro' => [],
                    ],
                ],
                'Lentes y Filtros' => [
                    'Lentes' => [],
                    'Filtros' => [],
                ],
                'Accesorios para Cámaras' => [
                    'Trípodes' => [],
                    'Flashes' => [],
                    'Mochilas fotográficas' => [],
                ],
                'Monitoreo y Seguridad' => [
                    'Cámaras IP' => [],
                    'CCTV' => [],
                ],
            ],
        ];

        $this->seedBranches($taxonomy);
    }

    /**
     * @param  array<string, array<string, mixed>>  $branches
     */
    private function seedBranches(array $branches, string $parentPath = ''): void
    {
        foreach ($branches as $name => $children) {
            $path = $parentPath === '' ? $name : $parentPath.' > '.$name;

            Category::query()->firstOrCreate(['name' => $path]);

            if ($children !== []) {
                $this->seedBranches($children, $path);
            }
        }
    }
}
