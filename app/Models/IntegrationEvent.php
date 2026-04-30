<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationEvent extends Model
{
    protected $fillable = [
        'source',
        'event_name',
        'status',
        'queue_connection',
        'queue_name',
        'external_reference',
        'summary',
        'payload',
        'result',
        'processed_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'result' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function markAsQueued(?string $summary = null): void
    {
        $this->update([
            'status' => 'queued',
            'summary' => $summary ?? $this->summary,
            'last_error' => null,
        ]);
    }

    public function markAsProcessing(?string $summary = null): void
    {
        $this->update([
            'status' => 'processing',
            'summary' => $summary ?? $this->summary,
            'last_error' => null,
        ]);
    }

    public function markAsProcessed(array $result = [], ?string $summary = null): void
    {
        $this->update([
            'status' => 'processed',
            'result' => $result === [] ? $this->result : $result,
            'summary' => $summary ?? $this->summary,
            'processed_at' => now(),
            'last_error' => null,
        ]);
    }

    public function markAsIgnored(?string $summary = null): void
    {
        $this->update([
            'status' => 'ignored',
            'summary' => $summary ?? $this->summary,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error, ?string $summary = null): void
    {
        $this->update([
            'status' => 'failed',
            'summary' => $summary ?? $this->summary,
            'last_error' => $error,
            'processed_at' => now(),
        ]);
    }
}
