<?php
namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with core types and Excel units.
     */
    public function run(): void
    {
        // 1. Poblar Tipos de Unidad (unidad_tipo) basado en los tipos del Excel
        $tipos = [
            ['unidad_tipo_desc' => 'Corporación de Asistencia Judicial', 'nombre_corto' => 'CJ'],
            ['unidad_tipo_desc' => 'Centro de Mediación', 'nombre_corto' => 'MED'],
            ['unidad_tipo_desc' => 'Oficina de Defensa Laboral', 'nombre_corto' => 'ODL'],
            ['unidad_tipo_desc' => 'Oficina Niñez y Adolescencia', 'nombre_corto' => 'NAD'],
            ['unidad_tipo_desc' => 'Programa Mi Abogado', 'nombre_corto' => 'PMA'],
            ['unidad_tipo_desc' => 'Programa Adulto Mayor', 'nombre_corto' => 'PAM'],
            ['unidad_tipo_desc' => 'Centro de Apoyo a Víctimas', 'nombre_corto' => 'CAVI'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('unidad_tipo')->updateOrInsert(
                ['nombre_corto' => $tipo['nombre_corto']],
                ['unidad_tipo_desc' => $tipo['unidad_tipo_desc']]
            );
        }

        // Obtener los IDs autogenerados de los tipos de unidad
        $tipoIds = DB::table('unidad_tipo')->pluck('unidad_tipo_id', 'nombre_corto');

        // 2. Poblar Unidades Operativas (unidad) con el listado oficial extraído del Excel
        $unidades = [
            ['UNIDAD MÓVIL COSTA', 'CJ'],
            ['UNIDAD MÓVIL CAUTÍN', 'CJ'],
            ['UNIDAD MÓVIL MALLECO', 'CJ'],
            ['CAJ ALTO BIO BIO', 'CJ'],
            ['UNIDAD MOVIL CONCEPCION', 'CJ'],
            ['CENTRO MEDIACION TEMUCO', 'MED'],
            ['CAJ FAMILIA PUERTO MONTT', 'CJ'],
            ['ODL LOS ANGELES', 'ODL'],
            ['CAJ TEMUCO FAMILIA', 'CJ'],
            ['ODL AYSÉN', 'ODL'],
            ['CAJ LOS ALAMOS', 'CJ'],
            ['ODL CHILLAN', 'ODL'],
            ['NAD CHILLAN', 'NAD'],
            ['PMA ARAUCANIA', 'PMA'],
            ['PMA CHILLÁN', 'PMA'],
            ['CAJ LONCOCHE', 'CJ'],
            ['ODL CASTRO', 'ODL'],
            ['PAM LOS LAGOS', 'PAM'],
            ['ODL COYHAIQUE', 'ODL'],
            ['CAJ COYHAIQUE', 'CJ'],
            ['CAJ CHILE CHICO', 'CJ'],
            ['CAVI COYHAIQUE', 'CAVI'],
            ['NAD COYHAIQUE', 'NAD'],
            ['PAM LOS RÍOS', 'PAM'],
            ['CAJ QUIRIHUE', 'CJ'],
            ['CAJ CHILLAN FAMILIA', 'CJ'],
            ['PMA LOS RÍOS', 'PMA'],
            ['CAJ COCHRANE', 'CJ'],
            ['CAJ PUERTO AYSEN', 'CJ'],
            ['CAJ TRAIGUEN', 'CJ'],
            ['CAJ ARAUCO', 'CJ'],
            ['CAJ LEBU', 'CJ'],
            ['CAJ ANGOL', 'CJ'],
            ['ODL PUERTO MONTT', 'ODL'],
            ['PAM AYSÉN', 'PAM'],
            ['CAJ CONCEPCION FAMILIA', 'CJ'],
            ['CAJ PANGUIPULLI', 'CJ'],
            ['CAJ CASTRO', 'CJ'],
            ['CAJ LOTA', 'CJ'],
            ['CAJ PENCO', 'CJ'],
            ['PMA LOS LAGOS', 'PMA'],
            ['CAJ PURRANQUE', 'CJ'],
            ['CAJ CURACAUTIN', 'CJ'],
            ['CAJ BARRIO NORTE', 'CJ'],
            ['CAJ NUEVA IMPERIAL', 'CJ'],
            ['CAJ CURARREHUE', 'CJ'],
            ['CAJ FAM LOS ANGELES', 'CJ'],
            ['CAJ CAÑETE', 'CJ'],
            ['CENTRO MEDIACION CONCEPCION', 'MED'],
            ['CAJ TOME', 'CJ'],
            ['CAJ LOS SAUCES', 'CJ'],
            ['CAVI TEMUCO', 'CAVI'],
            ['CAJ COELEMU', 'CJ'],
            ['CAJ CHIGUAYANTE', 'CJ'],
            ['CAJ PUERTO CISNES', 'CJ'],
            ['NAD ANGOL', 'NAD'],
            ['NAD CONCEPCIÓN', 'NAD'],
            ['PMA AYSEN', 'PMA'],
            ['PMA CONCEPCIÓN', 'PMA'],
        ];

        foreach ($unidades as $u) {
            $nombre = $u[0];
            $tipoCorto = $u[1];
            $tipoId = $tipoIds[$tipoCorto] ?? null;

            DB::table('unidad')->updateOrInsert(
                ['unidad_nombre' => $nombre],
                [
                    'unidad_tipo_id' => $tipoId,
                    'unidad_correo' => strtolower(str_replace(' ', '', $nombre)) . '@cajbiobio.cl',
                ]
            );
        }

        // 3. Poblar Personas
        $personas = [
            ['id' => 1, 'rut' => '11.111.111-1', 'nombre' => 'Admin', 'apellido' => 'General', 'es_funcionario' => 1],
            ['id' => 2, 'rut' => '22.222.222-2', 'nombre' => 'Cargador', 'apellido' => 'Excel', 'es_funcionario' => 1],
            ['id' => 3, 'rut' => '33.333.333-3', 'nombre' => 'Auditor', 'apellido' => 'Interno', 'es_funcionario' => 1],
            ['id' => 4, 'rut' => '44.444.444-4', 'nombre' => 'Funcionario', 'apellido' => 'Base', 'es_funcionario' => 1],
            ['id' => 5, 'rut' => '55.555.555-5', 'nombre' => 'Juan', 'apellido' => 'Pérez (ODL)', 'es_funcionario' => 1],
            ['id' => 6, 'rut' => '66.666.666-6', 'nombre' => 'María', 'apellido' => 'Soto (CAJ)', 'es_funcionario' => 1],
            ['id' => 7, 'rut' => '77.777.777-7', 'nombre' => 'Pedro', 'apellido' => 'Gómez (NAD)', 'es_funcionario' => 1],
        ];

        foreach ($personas as $p) {
            DB::table('persona')->updateOrInsert(
                ['persona_id' => $p['id']],
                [
                    'persona_rut' => $p['rut'],
                    'persona_nombre' => $p['nombre'],
                    'persona_apellido' => $p['apellido'],
                    'persona_funcionario' => $p['es_funcionario']
                ]
            );
        }

        // 4. Poblar Usuarios (Passwords con hash MD5 legado para compatibilidad con AuthController)
        $passMd5 = md5('password123'); // 482cf079f532240833a82118b6a2210d

        $usuarios = [
            ['id' => 1, 'persona_id' => 1, 'nombre' => 'admin_caj', 'correo' => 'admin@cajbiobio.cl', 'rol' => 'admin'],
            ['id' => 2, 'persona_id' => 2, 'nombre' => 'cargador_caj', 'correo' => 'cargador@cajbiobio.cl', 'rol' => 'cargador'],
            ['id' => 3, 'persona_id' => 3, 'nombre' => 'auditor_caj', 'correo' => 'auditor@cajbiobio.cl', 'rol' => 'auditor'],
            ['id' => 4, 'persona_id' => 4, 'nombre' => 'funcionario_caj', 'correo' => 'funcionario@cajbiobio.cl', 'rol' => 'usuario'],
            ['id' => 5, 'persona_id' => 5, 'nombre' => 'juan_odl', 'correo' => 'juan.perez@cajbiobio.cl', 'rol' => 'usuario'],
            ['id' => 6, 'persona_id' => 6, 'nombre' => 'maria_caj', 'correo' => 'maria.soto@cajbiobio.cl', 'rol' => 'usuario'],
            ['id' => 7, 'persona_id' => 7, 'nombre' => 'pedro_nad', 'correo' => 'pedro.gomez@cajbiobio.cl', 'rol' => 'usuario'],
        ];

        foreach ($usuarios as $u) {
            DB::table('usuario')->updateOrInsert(
                ['usuario_id' => $u['id']],
                [
                    'persona_id' => $u['persona_id'],
                    'usuario_estado_id' => 1,
                    'usuario_nombre' => $u['nombre'],
                    'usuario_pass' => $passMd5,
                    'usuario_correo' => $u['correo'],
                    'usuario_rol' => $u['rol']
                ]
            );
        }

        // 5. Vincular Funcionales con sus Unidades en unidad_persona
        // Obtener IDs de las unidades correspondientes
        $odlLosAngelesId = DB::table('unidad')->where('unidad_nombre', 'ODL LOS ANGELES')->value('unidad_id');
        $cajLoncocheId = DB::table('unidad')->where('unidad_nombre', 'CAJ LONCOCHE')->value('unidad_id');
        $nadChillanId = DB::table('unidad')->where('unidad_nombre', 'NAD CHILLAN')->value('unidad_id');

        $vinculos = [
            ['persona_id' => 5, 'unidad_id' => $odlLosAngelesId, 'jefe_id' => 1], // Juan en ODL LOS ANGELES
            ['persona_id' => 6, 'unidad_id' => $cajLoncocheId, 'jefe_id' => 1],   // María en CAJ LONCOCHE
            ['persona_id' => 7, 'unidad_id' => $nadChillanId, 'jefe_id' => 1],    // Pedro en NAD CHILLAN
        ];

        foreach ($vinculos as $v) {
            if ($v['unidad_id']) {
                DB::table('unidad_persona')->updateOrInsert(
                    ['persona_id' => $v['persona_id'], 'unidad_id' => $v['unidad_id']],
                    ['jefe_id' => $v['jefe_id']]
                );
            }
        }
    }
}