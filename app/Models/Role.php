<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'description'];

    /**
     * Relación muchos a muchos con los permisos de sistema.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Relación uno a muchos con los usuarios.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}