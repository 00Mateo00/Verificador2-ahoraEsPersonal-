<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CargaExcel extends Model
{
    protected $table = 'carga_excel';
    protected $primaryKey = 'carga_id';

    protected $fillable = [
        'user_id',
        'nombre_archivo',
        'hash_archivo',
        'total_filas',
        'estado'
    ];

    /**
     * El usuario cargador que subió el archivo.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Actividades vinculadas a esta carga en específico.
     */
    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class, 'carga_id', 'carga_id');
    }
}
