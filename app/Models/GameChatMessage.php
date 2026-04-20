<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'user_id',
        'message',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toChatPayload(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_label' => $this->created_at?->format('H:i'),
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'role_label' => $this->user?->role_label,
            ],
        ];
    }
}
