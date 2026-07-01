<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable([
    'name',
    'email',
    'password',
    'role_id',
    'rol',
    'activo',
    'password_changed_at',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected $primaryKey = 'id';

    public $timestamps = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_changed_at' => 'datetime',
        ];
    }

    /**
     * Relación con el rol maestro.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Accessor para emular la antigua columna 'rol' usando la relación con la tabla 'roles'.
     * Garantiza compatibilidad retroactiva completa con todos los controladores y vistas.
     */
    public function getRolAttribute(): ?UserRole
    {
        if (! $this->role) {
            return null;
        }

        return UserRole::tryFrom($this->role->name);
    }

    /**
     * Mutator para interceptar la asignación del antiguo campo 'rol' y mapearlo a la nueva columna 'role_id'.
     * Garantiza compatibilidad retroactiva completa con Fortify, seeders y controladores.
     */
    public function setRolAttribute($value): void
    {
        $roleName = $value instanceof UserRole ? $value->value : $value;
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->attributes['role_id'] = $role->id;
        }
    }

    /**
     * Valida si el usuario posee un permiso a través de su rol asignado.
     */
    public function hasPermissionTo(string $permission): bool
    {
        if (! $this->role) {
            return false;
        }

        return $this->role->permissions()->where('name', $permission)->exists();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Relación con las cargas de Excel realizadas por este usuario.
     */
    public function cargasExcel(): HasMany
    {
        return $this->hasMany(CargaExcel::class, 'user_id', 'id');
    }

    /**
     * Relación con la unidad operativa a la que pertenece el usuario.
     */
    public function unidad()
    {
        return $this->hasOne(Unidad::class, 'user_id', 'id');
    }

    /**
     * Relación con la región que administra el usuario como Director.
     */
    public function region()
    {
        return $this->hasOne(Region::class, 'user_id', 'id');
    }
}
