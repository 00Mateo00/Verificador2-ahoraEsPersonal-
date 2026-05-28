<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Actividad extends Model
{
    protected $table = 'actividad';
    protected $primaryKey = 'actividad_id';

    protected $fillable = [
        'usuario_id',
        'region',
        'tipo_unidad',
        'unidad_operativa',
        'tipo',
        'nombre_actividad',
        'objetivo',
        'n_participantes',
        'ubicacion',
        'observacion',
        'activo',
        'fecha_actividad'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'usuario_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class, 'actividad_id', 'actividad_id');
    }
}