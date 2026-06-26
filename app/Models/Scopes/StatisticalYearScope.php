<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StatisticalYearScope implements Scope
{
    /**
     * Aplica de forma global el filtro del Año Estadístico dinámico y auto-recuperable.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Determina de forma dinámica el año máximo de la tabla para evitar pantallas vacías por desfases temporales locales, con caché de 5 minutos
        $activeYear = \Illuminate\Support\Facades\Cache::remember('active_statistical_year_cache', 300, function () use ($model) {
            try {
                return \Illuminate\Support\Facades\DB::table($model->getTable())->max('AÑO') ?: (int) date('Y');
            } catch (\Throwable $e) {
                return (int) date('Y');
            }
        });

        $builder->where($model->getTable() . '.AÑO', $activeYear);
    }
}