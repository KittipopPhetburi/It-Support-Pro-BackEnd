<?php

namespace App\Events;

use App\Models\AssetRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssetRequestUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $action;

    /**
     * Create a new event instance.
     * @param AssetRequest $request
     * @param string $action - 'created' | 'updated' | 'deleted' | 'status-changed'
     */
    public function __construct(AssetRequest $request, string $action = 'updated')
    {
        $this->request = $request;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('asset-requests'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'request.' . $this->action;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'request' => $this->request->load(['user', 'asset', 'approver'])->toArray(),
            'action' => $this->action,
        ];
    }
}
