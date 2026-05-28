<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'persona_id';
    public $timestamps = false;

    protected $fillable = [
        'persona_rut',
        'persona_nombre',
        'persona_apellido',
        'persona_funcionario'
    ];

    public function usuario(): HasOne
    {
        return $this->hasOne(User::class, 'persona_id', 'persona_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->persona_nombre} {$this->persona_apellido}";
    }
}