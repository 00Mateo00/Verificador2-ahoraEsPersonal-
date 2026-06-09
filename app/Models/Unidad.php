<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidad extends Model
{
    protected $table = 'unidad';
    protected $primaryKey = 'unidad_id';

    protected $fillable = [
        'unidad_nombre',
        'unidad_correo',
        'region_id'
    ];

    /**
     * Relación con las actividades asignadas a esta unidad.
     */
    public function actividadesAsignadas(): HasMany
    {
        return $this->hasMany(Actividad::class, 'unidad_id_asignada', 'unidad_id');
    }
}
