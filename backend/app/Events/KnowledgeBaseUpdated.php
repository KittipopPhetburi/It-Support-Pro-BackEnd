<?php

namespace App\Events;

use App\Models\KbArticle;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KnowledgeBaseUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $article;
    public $action;

    /**
     * Create a new event instance.
     * @param KbArticle $article
     * @param string $action - 'created' | 'updated' | 'deleted'
     */
    public function __construct(KbArticle $article, string $action = 'updated')
    {
        $this->article = $article;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('knowledge-base'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'article.' . $this->action;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'article' => $this->article->toArray(),
            'action' => $this->action,
        ];
    }
}
