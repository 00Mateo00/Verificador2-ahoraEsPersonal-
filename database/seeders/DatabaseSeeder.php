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
        // Registrar e inicializar la matriz relacional de Roles y Permisos.
        $this->call(RolePermissionSeeder::class);

        $regiones = [
            [
                8,
                'Biobio',
                'mateo.ossa.b+biobio@gmail.com',
            ],
            [
                9,
                'Araucanía',
                'mateo.ossa.b+araucania@gmail.com',
            ],
            [
                10,
                'Los Lagos',
                'mateo.ossa.b+loslagos@gmail.com',
            ],
            [
                11,
                'Aysén',
                'mateo.ossa.b+aysen@gmail.com',
            ],
            [
                14,
                'Los Ríos',
                'mateo.ossa.b+losrios@gmail.com',
            ],
            [
                16,
                'Ñuble',
                'mateo.ossa.b+nuble@gmail.com',
            ],
        ];

        foreach ($regiones as $r) {
            $id = $r[0];
            $nombre = $r[1];
            $correo = $r[2];

            $director = User::factory()->create([
                'name' => "Director Regional {$nombre}",
                'email' => $correo,
                'rol' => 'director',
            ]);

            DB::table('region')->updateOrInsert(
                ['id' => $id],
                [
                    'region_nombre' => $nombre,
                    'user_id' => $director->id,
                ]
            );
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

            $userId = User::factory()->create([
                'name' => $nombre,
                'email' => $correo,
                'rol' => 'unidad',
            ])->id;

            DB::table('unidad')->updateOrInsert(
                ['id' => $id],
                [
                    'region_id' => $region_id,
                    'user_id' => $userId,
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
                    'name' => $u['nombre'],
                    'email' => $u['correo'],
                    'rol' => $u['rol'],
                ]

            );
        }
    }
}
