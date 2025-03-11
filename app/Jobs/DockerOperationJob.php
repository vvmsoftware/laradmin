<?php

namespace App\Jobs;

use App\Services\DockerManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DockerOperationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $operation;
    protected array $parameters;
    protected string $jobId;
    protected ?string $callback = null;

    /**
     * Create a new job instance.
     */
    public function __construct(string $operation, array $parameters = [], ?string $callback = null)
    {
        $this->operation = $operation;
        $this->parameters = $parameters;
        $this->jobId = Str::uuid()->toString();
        $this->callback = $callback;
        
        // Store initial status in cache
        $this->updateStatus('queued');
    }

    /**
     * Execute the job.
     */
    public function handle(DockerManager $dockerManager): void
    {
        $this->updateStatus('running');
        
        try {
            // Call the appropriate method on DockerManager
            $result = null;
            
            if (method_exists($dockerManager, $this->operation)) {
                $result = $dockerManager->{$this->operation}(...array_values($this->parameters));
            } else {
                throw new \Exception("Operation {$this->operation} not supported");
            }
            
            $this->updateStatus('completed', $result);
            
            // Trigger callback if provided
            if ($this->callback) {
                event(new \App\Events\DockerOperationCompleted(
                    $this->jobId,
                    $this->operation,
                    $result,
                    $this->callback
                ));
            }
        } catch (\Exception $e) {
            $this->updateStatus('failed', null, $e->getMessage());
            
            if ($this->callback) {
                event(new \App\Events\DockerOperationFailed(
                    $this->jobId,
                    $this->operation,
                    $e->getMessage(),
                    $this->callback
                ));
            }
        }
    }
    
    /**
     * Update the status of the job in the cache.
     */
    protected function updateStatus(string $status, $result = null, ?string $error = null): void
    {
        Cache::put(
            "docker_job:{$this->jobId}",
            [
                'id' => $this->jobId,
                'operation' => $this->operation,
                'parameters' => $this->parameters,
                'status' => $status,
                'result' => $result,
                'error' => $error,
                'updated_at' => now()->toIso8601String()
            ],
            now()->addDay()
        );
    }
    
    /**
     * Get the job ID.
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }
} 