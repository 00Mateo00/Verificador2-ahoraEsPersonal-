<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StatisticalYearScope implements Scope
{
    /**
     * Aplica de forma global el filtro del Año Estadístico actual del sistema (Año en curso).
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getTable() . '.AÑO', (int) date('Y'));
    }
}