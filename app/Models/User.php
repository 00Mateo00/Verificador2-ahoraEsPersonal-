<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'usuario_nombre',
    'usuario_correo',
    'usuario_pass',
    'usuario_rol',
    'persona_id',
    'usuario_estado_id'
])]

#[Hidden([
    'usuario_pass',
    'remember_token'
])]

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuario';

    protected $primaryKey = 'usuario_id';

    public $timestamps = false;

    /**
     * Laravel usará esta columna como password
     */
    public function getAuthPassword(): string
    {
        return $this->usuario_pass;
    }

    /**
     * Laravel usará este campo como "email"
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->usuario_correo;
    }

    /**
     * Opcional: si quieres que Auth::user()->name funcione
     */
    public function getNameAttribute(): ?string
    {
        return $this->usuario_nombre;
    }

    /**
     * Opcional: si quieres que Auth::user()->email funcione
     */
    public function getEmailAttribute(): ?string
    {
        return $this->usuario_correo;
    }

    public function getRememberTokenName()
    {
        return null;
    }

    /**
     * Casts
     */
    protected function casts(): array
    {
        return [
            'usuario_pass' => 'hashed',
        ];
    }


/**
     * Iniciales
     */
    public function initials(): string
    {
        return Str::of($this->usuario_nombre)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

/**
     * Relación con los datos personales del funcionario
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'persona_id');
    }

/**
     * Relación con actividades registradas
     */
    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class, 'usuario_id', 'usuario_id');
    }

}