<?php

namespace App\Models;

use App\Enums\GameStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'instructions',
        'status',
        'game_url',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(GameChatMessage::class);
    }

    public function isAccessibleBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->status === GameStatus::Published) {
            return true;
        }

        return $user->hasAnyRole(UserRole::Admin, UserRole::Manager);
    }

    public function playableUrl(array $query = []): string
    {
        $baseUrl = Str::startsWith($this->game_url, ['http://', 'https://'])
            ? $this->game_url
            : '/'.ltrim($this->game_url, '/');

        if ($query === []) {
            return $baseUrl;
        }

        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        return $baseUrl.$separator.http_build_query($query);
    }
}
