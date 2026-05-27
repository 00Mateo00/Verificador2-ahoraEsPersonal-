<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Archivo extends Model
{
    protected $table = 'archivos';
    protected $primaryKey = 'archivo_id';

    protected $fillable = [
        'actividad_id',
        'archivo_nombre',
        'archivo_ruta',
        'archivo_tipo',
        'archivo_size'
    ];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'actividad_id', 'actividad_id');
    }
}