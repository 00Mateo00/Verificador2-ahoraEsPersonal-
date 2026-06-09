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
            $id = $u[0];
            $nombre = $u[1];
            $correo = $u[2] ?? null;

            DB::table('unidad')->updateOrInsert(
                ['unidad_id' => $id],
                [
                    'unidad_nombre' => $nombre,
                    'unidad_correo' => $correo,
                ]
            );
        }


        // TO-DO : hacer que los usuarios ed las unidades se generan automaticamente a partir de los datos en $unidades


        $usuarios = [
            ['nombre' => 'admin_caj', 'correo' => 'admin@cajbiobio.cl', 'rol' => 'admin'],
            ['nombre' => 'cargador_caj', 'correo' => 'cargador@cajbiobio.cl', 'rol' => 'cargador'],
            ['nombre' => 'auditor_caj', 'correo' => 'auditor@cajbiobio.cl', 'rol' => 'auditor'],
            ['nombre' => 'director_caj', 'correo' => 'region@cajbiobio.cl', 'rol' => 'director'],
            ['unidad_id' => 17, 'nombre' => 'cchiguayante', 'correo' => 'cchiguayante@cajbiobio.cl', 'rol' => 'unidad'],
            ['unidad_id' => 12, 'nombre' => 'ccanete', 'correo' => 'ccanete@cajbiobio.cl', 'rol' => 'unidad'],
            ['unidad_id' => 10, 'nombre' => 'ccabrero', 'correo' => 'ccabrero@cajbiobio.cl', 'rol' => 'unidad'],
            ['unidad_id' => 8, 'nombre' => 'cbarrionorte', 'correo' => 'cbarrionorte@cajbiobio.cl', 'rol' => 'unidad'],
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
            if (isset($u['unidad_id'])) {
                $user->unidad_id = $u['unidad_id'];
                $user->save();
            }
        }
    }
}
