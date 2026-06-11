<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unidad extends Model
{
    protected $table = 'unidad';

    protected $fillable = [
        'id',
        'region_id',
        'user_id',
    ];

    /**
     * Relación con el usuario operador asignado a la unidad.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relación con la región a la que pertenece la unidad.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    /**
     * Relación con las actividades asignadas a esta unidad.
     */
    public function actividadesAsignadas(): HasMany
    {
        return $this->hasMany(Actividad::class, 'unidad_id_asignada', 'id');
    }
}
