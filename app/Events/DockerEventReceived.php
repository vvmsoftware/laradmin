<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DockerEventReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $event;

    /**
     * Create a new event instance.
     */
    public function __construct(array $event)
    {
        $this->event = $event;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [new Channel('docker-events')];
        
        // Add specific channels based on event type/action
        if (isset($this->event['Type'])) {
            $channels[] = new Channel("docker-events.{$this->event['Type']}");
            
            if (isset($this->event['Action'])) {
                $channels[] = new Channel("docker-events.{$this->event['Type']}.{$this->event['Action']}");
            }
            
            // If it's a container event, add a channel for the specific container
            if ($this->event['Type'] === 'container' && isset($this->event['Actor']['ID'])) {
                $channels[] = new PrivateChannel("docker-container.{$this->event['Actor']['ID']}");
            }
        }
        
        return $channels;
    }
    
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'docker.event';
    }
    
    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'event' => $this->event,
            'timestamp' => now()->toIso8601String(),
        ];
    }
} 