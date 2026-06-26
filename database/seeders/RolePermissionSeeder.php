<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear Roles Maestros
        $roles = [
            ['name' => 'admin', 'description' => 'Administrador con acceso y control total.'],
            ['name' => 'auditor', 'description' => 'Auditor con accesos de lectura global.'],
            ['name' => 'cargador', 'description' => 'Operador encargado de la carga masiva de Excel.'],
            ['name' => 'director', 'description' => 'Director Regional con supervisión territorial.'],
            ['name' => 'unidad', 'description' => 'Unidad Operativa encargada de subir verificadores.'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                [
                    'description' => $role['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 2. Crear Permisos Granulares
        $permissions = [
            ['name' => 'actividades.importar', 'description' => 'Permite acceder al módulo de subida masiva de planillas Excel.'],
            ['name' => 'actividades.verificar', 'description' => 'Permite adjuntar archivos verificadores y transicionar actividades del estado Cargada a Verificada.'],
            ['name' => 'actividades.desactivar', 'description' => 'Permite dar de baja lógica a una actividad en modo de edición.'],
            ['name' => 'actividades.eliminar-verificador', 'description' => 'Permite suprimir archivos de respaldo asociados a una actividad en modo de edición.'],
            ['name' => 'actividades.adjuntar-administrativo', 'description' => 'Permite adjuntar respaldos a cualquier actividad de forma retroactiva en modo de edición.'],
            ['name' => 'historial.ver-global', 'description' => 'Permite consultar el historial completo de actividades de todas las regiones y unidades.'],
            ['name' => 'historial.ver-regional', 'description' => 'Restringe la consulta del historial únicamente a las unidades correspondientes a la jurisdicción regional del usuario.'],
            ['name' => 'historial.ver-unidad', 'description' => 'Restringe la consulta de historial únicamente a la unidad propia.'],
            ['name' => 'correos.ver-historial', 'description' => 'Permite visualizar el log completo de correos despachados por la plataforma.'],
            ['name' => 'correos.gestionar-fallidos', 'description' => 'Permite reintentar de forma individual o masiva el envío de correos pendientes o fallidos.'],
            ['name' => 'correos.eliminar-registro', 'description' => 'Permite purgar registros de logs de correo en modo de edición.'],
            ['name' => 'unidades.renotificar', 'description' => 'Permite gatillar renotificaciones manuales de actividades pendientes a las unidades de su jurisdicción.'],
            ['name' => 'usuarios.ver-catalogo', 'description' => 'Permite visualizar la lista global de usuarios y unidades del sistema.'],
            ['name' => 'usuarios.crear', 'description' => 'Permite dar de alta regiones, unidades y perfiles de sistema en modo de edición.'],
            ['name' => 'usuarios.mutar-estado', 'description' => 'Permite habilitar o deshabilitar cuentas de usuario en modo de edición.'],
            ['name' => 'configuracion.periodo', 'description' => 'Permite configurar el rango de fechas del Año Estadístico activo.'],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $perm['name']],
                [
                    'description' => $perm['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 3. Mapear Roles a Permisos
        $roleMappings = [
            'admin' => [
                'actividades.importar', 'actividades.verificar', 'actividades.desactivar',
                'actividades.eliminar-verificador', 'actividades.adjuntar-administrativo',
                'historial.ver-global', 'correos.ver-historial', 'correos.gestionar-fallidos',
                'correos.eliminar-registro', 'unidades.renotificar', 'usuarios.ver-catalogo',
                'usuarios.crear', 'usuarios.mutar-estado', 'configuracion.periodo',
            ],
            'auditor' => [
                'historial.ver-global', 'correos.ver-historial', 'correos.gestionar-fallidos',
                'unidades.renotificar',
            ],
            'director' => [
                'historial.ver-regional', 'unidades.renotificar',
            ],
            'cargador' => [
                'actividades.importar', 'historial.ver-global',
            ],
            'unidad' => [
                'actividades.verificar', 'historial.ver-unidad',
            ],
        ];

        foreach ($roleMappings as $roleName => $perms) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            if ($roleId) {
                foreach ($perms as $permName) {
                    $permissionId = DB::table('permissions')->where('name', $permName)->value('id');
                    if ($permissionId) {
                        DB::table('permission_role')->updateOrInsert(
                            [
                                'role_id' => $roleId,
                                'permission_id' => $permissionId,
                            ],
                            [
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }
        }
    }
}
