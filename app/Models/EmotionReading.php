<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmotionReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_session_id',
        'emotion',
        'confidence',
        'captured_at',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'context' => 'array',
            'confidence' => 'float',
        ];
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }
}
