<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DockerOperationFailed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $jobId;
    public string $operation;
    public string $error;
    public ?string $callback;

    /**
     * Create a new event instance.
     */
    public function __construct(string $jobId, string $operation, string $error, ?string $callback = null)
    {
        $this->jobId = $jobId;
        $this->operation = $operation;
        $this->error = $error;
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
        return 'operation.failed';
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
            'error' => $this->error,
            'status' => 'failed',
            'timestamp' => now()->toIso8601String(),
        ];
    }
} 