<?php

namespace App\Events;

use App\Models\ServiceCatalogItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceCatalogUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $item;
    public $action;

    /**
     * Create a new event instance.
     * @param ServiceCatalogItem $item
     * @param string $action - 'created' | 'updated' | 'deleted'
     */
    public function __construct(ServiceCatalogItem $item, string $action = 'updated')
    {
        $this->item = $item;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('service-catalog'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'catalog.' . $this->action;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'item' => $this->item->toArray(),
            'action' => $this->action,
        ];
    }
}
