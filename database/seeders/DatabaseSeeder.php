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
        /* 
        * FORMATO DE UNIDADES: [[ID, NOMBRE, CORREO], ...]
        */
        $unidades = require 'unidades.php';


        foreach ($unidades as $u) {
            $nombre = $u[0];

            DB::table('unidad')->updateOrInsert(
                ['unidad_nombre' => $nombre],
                [
                    'unidad_correo' => $u[1],
                ]
            );
        }



        $usuarios = [
            ['nombre' => 'admin_caj', 'correo' => 'admin@cajbiobio.cl', 'rol' => 'admin'],
            ['nombre' => 'cargador_caj', 'correo' => 'cargador@cajbiobio.cl', 'rol' => 'cargador'],
            ['nombre' => 'auditor_caj', 'correo' => 'auditor@cajbiobio.cl', 'rol' => 'auditor'],
            ['nombre' => 'director_caj', 'correo' => 'region@cajbiobio.cl', 'rol' => 'director'],
            ['nombre' => 'cchiguayante', 'correo' => 'cchiguayante@cajbiobio.cl', 'rol' => 'unidad'],
            ['nombre' => 'ccanete', 'correo' => 'ccanete@cajbiobio.cl', 'rol' => 'unidad'],
            ['nombre' => 'ccabrero', 'correo' => 'ccabrero@cajbiobio.cl', 'rol' => 'unidad'],
            ['nombre' => 'cbarrionorte', 'correo' => 'cbarrionorte@cajbiobio.cl', 'rol' => 'unidad'],
        ];

        foreach ($usuarios as $u) {
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
