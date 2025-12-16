<?php

namespace App\Events;

use App\Models\Department;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepartmentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $department;
    public $action;

    /**
     * Create a new event instance.
     * @param Department $department
     * @param string $action - 'created' | 'updated' | 'deleted'
     */
    public function __construct(Department $department, string $action = 'updated')
    {
        $this->department = $department;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('departments'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'department.' . $this->action;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'department' => $this->department->toArray(),
            'action' => $this->action,
        ];
    }
}
