<?php

namespace App\Policies;

use App\Models\Actividad;
use App\Models\User;

use App\Enums\UserRole;

class ActividadPolicy
{
    /**
     * Determina si el usuario puede visualizar la actividad (Aislamiento Horizontal basado en capacidades y límites relacionales).
     */
    public function view(User $user, Actividad $actividad): bool
    {
        // 1. Acceso de visualización Global
        if ($user->hasPermissionTo('historial.ver-global')) {
            return true;
        }

        // 2. Acceso de visualización Regional (Director)
        if ($user->hasPermissionTo('historial.ver-regional')) {
            $userRegionId = $user->region ? $user->region->id : null;
            $actUnidad = $actividad->unidadAsignada;
            return $userRegionId !== null && $actUnidad !== null && (int)$actUnidad->region_id === (int)$userRegionId;
        }

        // 3. Acceso de visualización de Unidad propia
        if ($user->hasPermissionTo('historial.ver-unidad')) {
            $userUnidadId = $user->unidad ? $user->unidad->id : null;
            return $userUnidadId !== null && (int)$actividad->unidad_id_asignada === (int)$userUnidadId;
        }

        return false;
    }

    /**
     * Determina si el usuario puede verificar o subir respaldos de la actividad (Control de Mutaciones).
     */
    public function update(User $user, Actividad $actividad): bool
    {
        // Evaluamos primero el permiso global de verificación
        if (!$user->hasPermissionTo('actividades.verificar')) {
            return false;
        }

        // El Administrador tiene permisos globales sobre cualquier actividad
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Para unidades operativas, validamos que la actividad esté asignada a su propia unidad
        $userUnidadId = $user->unidad ? $user->unidad->id : null;
        return $userUnidadId !== null && (int)$actividad->unidad_id_asignada === (int)$userUnidadId;
    }
}