<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $table = 'region';

    // Desactivar marcas de tiempo puesto que no están en el esquema original de la migración
    public $timestamps = false;

    protected $fillable = [
        'id',
        'region_nombre',
        'user_id',
    ];

    /**
     * Relación con el Director Regional asignado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relación con las Unidades Operativas pertenecientes a esta región.
     */
    public function unidades(): HasMany
    {
        return $this->hasMany(Unidad::class, 'region_id', 'id');
    }
}