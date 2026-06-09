<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Seed the application's database with core types and Excel units.
     */
    public function run(): void
    {

        $regiones = [
            [
                8,
                "Biobio"
            ],
            [
                9,
                "Araucanía"
            ],
            [
                10,
                "Los Lagos"
            ],
            [
                11,
                "Aysén"
            ],
            [
                14,
                "Los Ríos"
            ],
            [
                16,
                "Ñuble"
            ]
        ];

        foreach ($regiones as $r) {
            $id = $r[0];
            $name = $r[1];
            DB::table('region')->updateOrInsert(['region_id' => $id], ['region_nombre' => $name]);
        }


        /* 
        * FORMATO DE UNIDADES: [[ID, NOMBRE, CORREO], ...]
        */
        $unidades = require 'unidades.php';



        foreach ($unidades as $u) {
            $id = $u[0];
            $nombre = $u[1];
            $correo = $u[2] ?? null;
            $region_id = $u[3] ?? null;

            DB::table('unidad')->updateOrInsert(
                ['unidad_id' => $id],
                [
                    'unidad_nombre' => $nombre,
                    'unidad_correo' => $correo,
                    'region_id' => $region_id,
                ]
            );

            // crear usuarios de rol "unidad"
            User::factory()->create(
                [
                    'estado' => 1,
                    'name' => $nombre,
                    'email' => $correo,
                    'rol' => 'unidad',
                    'unidad_id' => $id
                ]

            );
        }


        $usuariosPersonas = [
            ['nombre' => 'admin_caj', 'correo' => 'admin@cajbiobio.cl', 'rol' => 'admin'],
            ['nombre' => 'cargador_caj', 'correo' => 'cargador@cajbiobio.cl', 'rol' => 'cargador'],
            ['nombre' => 'auditor_caj', 'correo' => 'auditor@cajbiobio.cl', 'rol' => 'auditor'],
            ['nombre' => 'director_caj', 'correo' => 'region@cajbiobio.cl', 'rol' => 'director'],

        ];

        $usuariosUnidades = [];

        foreach ($usuariosPersonas as $u) {
            User::factory()->create(
                [
                    'estado' => 1,
                    'name' => $u['nombre'],
                    'email' => $u['correo'],
                    'rol' => $u['rol']
                ]

            );
        }
    }
}
