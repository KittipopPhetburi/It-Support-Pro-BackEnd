<?php

namespace App\Events;

use App\Models\Branch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BranchUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $branch;
    public $action;

    /**
     * Create a new event instance.
     * @param Branch $branch
     * @param string $action - 'created' | 'updated' | 'deleted'
     */
    public function __construct(Branch $branch, string $action = 'updated')
    {
        $this->branch = $branch;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('branches'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'branch.' . $this->action;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'branch' => $this->branch->toArray(),
            'action' => $this->action,
        ];
    }
}
