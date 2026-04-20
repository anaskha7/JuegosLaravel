<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'api_token',
        'face_reference_path',
    ];

    /**
     * Los atributos que deben ocultarse al serializar.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'face_reference_path',
    ];

    /**
     * Obtiene los atributos que deben convertirse.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function createdGames(): HasMany
    {
        return $this->hasMany(Game::class, 'created_by');
    }

    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(GameChatMessage::class);
    }

    public function hasAnyRole(UserRole|string ...$roles): bool
    {
        $roleValues = array_map(
            fn (UserRole|string $role) => $role instanceof UserRole ? $role->value : $role,
            $roles
        );

        $userRoles = $this->relationLoaded('roles')
            ? $this->roles
            : $this->roles()->get();

        return $userRoles->pluck('name')->intersect($roleValues)->isNotEmpty();
    }

    public function syncRoles(array $roles): void
    {
        $roleIds = collect($roles)
            ->map(fn (UserRole|string $role) => $role instanceof UserRole ? $role->value : $role)
            ->map(fn (string $roleName) => Role::query()->firstOrCreate(
                ['name' => $roleName],
                ['label' => UserRole::from($roleName)->label()]
            )->id)
            ->all();

        $this->roles()->sync($roleIds);
        $this->load('roles');
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->relationLoaded('roles')
            ? $this->roles->first()?->name
            : $this->roles()->value('name');
    }

    public function getRoleLabelAttribute(): ?string
    {
        $roleName = $this->role_name;

        if (! $roleName) {
            return null;
        }

        return UserRole::from($roleName)->label();
    }

    public function hasFaceReference(): bool
    {
        return filled($this->face_reference_path);
    }
}
