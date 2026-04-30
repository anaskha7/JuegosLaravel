<?php

namespace App\Jobs;

use App\Models\IntegrationEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPlatformEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $integrationEventId)
    {
        $this->onConnection('rabbitmq');
        $this->onQueue('platform-events');
    }

    public function handle(): void
    {
        $event = IntegrationEvent::query()->find($this->integrationEventId);

        if (! $event) {
            return;
        }

        $event->markAsProcessing('Procesando evento interno en RabbitMQ.');

        Log::info('Procesando evento interno de plataforma', [
            'integration_event_id' => $event->id,
            'event_name' => $event->event_name,
        ]);

        $event->markAsProcessed([
            'handled_by' => 'queue-worker',
            'queue' => 'platform-events',
        ], 'Evento interno procesado correctamente.');
    }
}
