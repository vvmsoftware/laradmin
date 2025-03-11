<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Broadcast;
use App\Events\DockerEventReceived;

class DockerEventBroadcaster
{
    protected DockerManager $dockerManager;
    protected bool $running = false;
    protected string $processId;

    /**
     * Create a new broadcaster instance.
     */
    public function __construct(DockerManager $dockerManager)
    {
        $this->dockerManager = $dockerManager;
        $this->processId = uniqid('docker_broadcaster_');
    }

    /**
     * Start listening for Docker events and broadcasting them.
     *
     * @param array $filters Filters to apply to Docker events
     * @return bool
     */
    public function start(array $filters = []): bool
    {
        if ($this->running) {
            return false;
        }

        $this->running = true;
        Cache::put("docker_broadcaster:{$this->processId}", [
            'started_at' => now()->toIso8601String(),
            'filters' => $filters,
            'status' => 'running',
        ], now()->addDay());

        // Run in a separate process or job for production
        // Here's a simplified version for demonstration
        try {
            $stream = $this->dockerManager->streamEvents($filters)->toPsrResponse()->getBody();
            
            while (!$stream->eof() && $this->running) {
                $chunk = $stream->read(1024);
                if (empty($chunk)) {
                    continue;
                }
                
                // Parse and broadcast events
                $events = $this->parseEvents($chunk);
                foreach ($events as $event) {
                    broadcast(new DockerEventReceived($event));
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Docker event broadcaster error: ' . $e->getMessage());
            $this->running = false;
            Cache::put("docker_broadcaster:{$this->processId}", [
                'started_at' => now()->toIso8601String(),
                'filters' => $filters,
                'status' => 'error',
                'error' => $e->getMessage(),
            ], now()->addDay());
            
            return false;
        }
    }

    /**
     * Stop the event broadcasting.
     *
     * @return bool
     */
    public function stop(): bool
    {
        $this->running = false;
        Cache::put("docker_broadcaster:{$this->processId}", [
            'status' => 'stopped',
            'stopped_at' => now()->toIso8601String(),
        ], now()->addDay());
        
        return true;
    }

    /**
     * Parse Docker events from a chunk of data.
     *
     * @param string $chunk
     * @return array
     */
    protected function parseEvents(string $chunk): array
    {
        $events = [];
        $lines = explode("\n", $chunk);
        
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }
            
            try {
                $event = json_decode($line, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($event)) {
                    $events[] = $event;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to parse Docker event: ' . $e->getMessage());
            }
        }
        
        return $events;
    }
} 