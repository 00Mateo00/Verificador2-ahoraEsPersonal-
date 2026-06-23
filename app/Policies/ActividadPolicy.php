<?php

namespace App\Policies;

use App\Models\Actividad;
use App\Models\User;

use App\Enums\UserRole;

class ActividadPolicy
{
    /**
     * Determina si el usuario puede visualizar la actividad (Aislamiento Horizontal).
     */
    public function view(User $user, Actividad $actividad): bool
    {
        $rol = $user->rol;

        // Admin, Auditor y Cargador tienen acceso de visualizacion global
        if (in_array($rol, [UserRole::Admin, UserRole::Auditor, UserRole::Cargador], true)) {
            return true;
        }

        // Rol Unidad: Solo puede visualizar si la actividad está asignada a su propia unidad operativa
        if ($rol === UserRole::Unidad) {
            $userUnidadId = $user->unidad ? $user->unidad->id : null;
            return $userUnidadId !== null && (int)$actividad->unidad_id_asignada === (int)$userUnidadId;
        }

        // Rol Director: Solo puede visualizar si la unidad de la actividad pertenece a su region asignada
        if ($rol === UserRole::Director) {
            $userRegionId = $user->region ? $user->region->id : null;
            $actUnidad = $actividad->unidadAsignada;
            return $userRegionId !== null && $actUnidad !== null && (int)$actUnidad->region_id === (int)$userRegionId;
        }

        return false;
    }

    /**
     * Determina si el usuario puede verificar o subir respaldos de la actividad (Control de Mutaciones).
     */
    public function update(User $user, Actividad $actividad): bool
    {
        $rol = $user->rol;

        // El auditor nunca puede realizar mutaciones de escritura
        if ($rol === UserRole::Auditor) {
            return false;
        }

        // Admin tiene permisos globales de escritura
        if ($rol === UserRole::Admin) {
            return true;
        }

        // Rol Unidad: Puede subir verificadores únicamente si la actividad le pertenece
        if ($rol === UserRole::Unidad) {
            $userUnidadId = $user->unidad ? $user->unidad->id : null;
            return $userUnidadId !== null && (int)$actividad->unidad_id_asignada === (int)$userUnidadId;
        }

        return false;
    }
}