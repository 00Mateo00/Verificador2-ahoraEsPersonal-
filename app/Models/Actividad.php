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
        // Columnas espejo del Excel
        'CONSIDERAR_SI_NO',
        'MODALIDAD_MODIFICADO',
        'MODALIDAD_COD',
        'TIPO_MODIFICADO',
        'TIPO_ACT_COD',
        'CAJ_ID',
        'SUB_TIPO_MODIFICADO',
        'SUB_TIPO_COD',
        'COD',
        'FECHA',
        'FECHA_SAJ',
        'MODALIDAD',
        'TIPO_ACTIVIDAD',
        'SUB_TIPO_ACTIVIDAD',
        'PARTICIPANTES',
        'TOTAL_HOMBRES',
        'TOTAL_MUJERES',
        'TOTAL_NOBINARIO',
        'FUNCIONARIO',
        'UNIDAD',
        'TIPO_UNIDAD',
        'REGION',
        'MES',
        'AÑO',
        'DET_ACTIVIDAD',

        // Columnas de control interno
        'estado',
        'carga_id',
        'usuario_id_asignado',
        'unidad_id_asignada',
        'activo'
    ];

    /**
     * Relación con el funcionario interno asignado para adjuntar el verificador.
     */
    public function usuarioAsignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id_asignado', 'usuario_id');
    }

    /**
     * Relación con la unidad del sistema que debe gestionar esta actividad.
     */
    public function unidadAsignada(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id_asignada', 'unidad_id');
    }

    /**
     * Relación con los archivos de respaldo o verificadores adjuntos.
     */
    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class, 'actividad_id', 'actividad_id');
    }
}