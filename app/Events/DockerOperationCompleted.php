<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DockerOperationCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $jobId;
    public string $operation;
    public $result;
    public ?string $callback;

    /**
     * Create a new event instance.
     */
    public function __construct(string $jobId, string $operation, $result, ?string $callback = null)
    {
        $this->jobId = $jobId;
        $this->operation = $operation;
        $this->result = $result;
        $this->callback = $callback;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('docker-operations'),
            new PrivateChannel("docker-job.{$this->jobId}"),
        ];
    }
    
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'operation.completed';
    }
    
    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'jobId' => $this->jobId,
            'operation' => $this->operation,
            'result' => $this->result,
            'status' => 'completed',
            'timestamp' => now()->toIso8601String(),
        ];
    }
} 