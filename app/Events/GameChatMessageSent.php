<?php

namespace App\Events;

use App\Models\GameChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public GameChatMessage $chatMessage)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('games.'.$this->chatMessage->game_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->chatMessage->toChatPayload(),
        ];
    }
}
