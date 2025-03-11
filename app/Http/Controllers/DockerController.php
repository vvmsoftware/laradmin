<?php

namespace App\Http\Controllers;

use App\Services\DockerManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DockerController extends Controller
{
    /**
     * The Docker Manager instance
     *
     * @var DockerManager
     */
    protected DockerManager $dockerManager;

    /**
     * Create a new controller instance
     *
     * @param DockerManager $dockerManager
     */
    public function __construct(DockerManager $dockerManager)
    {
        $this->dockerManager = $dockerManager;
    }

    /**
     * List all Docker images
     *
     * @return JsonResponse
     */
    public function listImages(): JsonResponse
    {
        try {
            $data = $this->dockerManager->listImages();
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all Docker containers
     *
     * @return JsonResponse
     */
    public function listContainers(): JsonResponse
    {
        try {
            $data = $this->dockerManager->listContainers();
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start a Docker container
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function startContainer(Request $request): JsonResponse
    {
        $request->validate([
            'container_id' => 'required|string'
        ]);

        try {
            $result = $this->dockerManager->startContainer($request->container_id);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stop a Docker container
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stopContainer(Request $request): JsonResponse
    {
        $request->validate([
            'container_id' => 'required|string',
            'timeout' => 'sometimes|integer|min:1'
        ]);

        try {
            $timeout = $request->input('timeout', 10);
            $result = $this->dockerManager->stopContainer($request->container_id, $timeout);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restart a Docker container
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function restartContainer(Request $request): JsonResponse
    {
        $request->validate([
            'container_id' => 'required|string',
            'timeout' => 'sometimes|integer|min:1'
        ]);

        try {
            $timeout = $request->input('timeout', 10);
            $result = $this->dockerManager->restartContainer($request->container_id, $timeout);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a Docker container
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeContainer(Request $request): JsonResponse
    {
        $request->validate([
            'container_id' => 'required|string',
            'force' => 'sometimes|boolean',
            'remove_volumes' => 'sometimes|boolean'
        ]);

        try {
            $force = $request->input('force', false);
            $removeVolumes = $request->input('remove_volumes', false);
            $result = $this->dockerManager->removeContainer($request->container_id, $force, $removeVolumes);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get logs from a Docker container
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getContainerLogs(Request $request): JsonResponse
    {
        $request->validate([
            'container_id' => 'required|string',
            'stdout' => 'sometimes|boolean',
            'stderr' => 'sometimes|boolean',
            'tail' => 'sometimes|integer|min:1'
        ]);

        try {
            $stdout = $request->input('stdout', true);
            $stderr = $request->input('stderr', true);
            $tail = $request->input('tail', 100);
            $result = $this->dockerManager->getContainerLogs($request->container_id, $stdout, $stderr, $tail);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute a command in a Docker container
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function executeCommandInContainer(Request $request): JsonResponse
    {
        $request->validate([
            'container_id' => 'required|string',
            'command' => 'required|array',
            'detach' => 'sometimes|boolean'
        ]);

        try {
            $detach = $request->input('detach', false);
            $result = $this->dockerManager->executeCommandInContainer(
                $request->container_id,
                $request->command,
                $detach
            );
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run a new Docker container
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function runContainer(Request $request): JsonResponse
    {
        $request->validate([
            'image_name' => 'required|string',
            'container_name' => 'sometimes|string',
            'options' => 'sometimes|array',
            'options.cmd' => 'sometimes|array',
            'options.env' => 'sometimes|array',
            'options.ports' => 'sometimes|array',
            'options.volumes' => 'sometimes|array'
        ]);

        try {
            $result = $this->dockerManager->runContainer(
                $request->image_name,
                $request->container_name,
                $request->input('options', [])
            );
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pull a Docker image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pullImage(Request $request): JsonResponse
    {
        $request->validate([
            'image_name' => 'required|string'
        ]);

        try {
            $result = $this->dockerManager->pullImage($request->image_name);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a Docker image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeImage(Request $request): JsonResponse
    {
        $request->validate([
            'image_id' => 'required|string',
            'force' => 'sometimes|boolean'
        ]);

        try {
            $force = $request->input('force', false);
            $result = $this->dockerManager->removeImage($request->image_id, $force);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all Docker volumes
     *
     * @return JsonResponse
     */
    public function listVolumes(): JsonResponse
    {
        try {
            $result = $this->dockerManager->listVolumes();
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a Docker volume
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createVolume(Request $request): JsonResponse
    {
        $request->validate([
            'volume_name' => 'required|string',
            'options' => 'sometimes|array'
        ]);

        try {
            $result = $this->dockerManager->createVolume(
                $request->volume_name,
                $request->input('options', [])
            );
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a Docker volume
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeVolume(Request $request): JsonResponse
    {
        $request->validate([
            'volume_name' => 'required|string',
            'force' => 'sometimes|boolean'
        ]);

        try {
            $force = $request->input('force', false);
            $result = $this->dockerManager->removeVolume($request->volume_name, $force);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all Docker networks
     *
     * @return JsonResponse
     */
    public function listNetworks(): JsonResponse
    {
        try {
            $result = $this->dockerManager->listNetworks();
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a Docker network
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createNetwork(Request $request): JsonResponse
    {
        $request->validate([
            'network_name' => 'required|string',
            'options' => 'sometimes|array',
            'options.driver' => 'sometimes|string'
        ]);

        try {
            $result = $this->dockerManager->createNetwork(
                $request->network_name,
                $request->input('options', [])
            );
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a Docker network
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeNetwork(Request $request): JsonResponse
    {
        $request->validate([
            'network_name' => 'required|string'
        ]);

        try {
            $result = $this->dockerManager->removeNetwork($request->network_name);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize a Docker Swarm
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function initSwarm(Request $request): JsonResponse
    {
        $request->validate([
            'options' => 'sometimes|array'
        ]);

        try {
            $result = $this->dockerManager->initSwarm($request->input('options', []));
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Join a Docker Swarm
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function joinSwarm(Request $request): JsonResponse
    {
        $request->validate([
            'join_token' => 'required|string',
            'manager_address' => 'required|string'
        ]);

        try {
            $result = $this->dockerManager->joinSwarm(
                $request->join_token,
                $request->manager_address
            );
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Leave a Docker Swarm
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function leaveSwarm(Request $request): JsonResponse
    {
        $request->validate([
            'force' => 'sometimes|boolean'
        ]);

        try {
            $force = $request->input('force', false);
            $result = $this->dockerManager->leaveSwarm($force);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all nodes in a Docker Swarm
     *
     * @return JsonResponse
     */
    public function listSwarmNodes(): JsonResponse
    {
        try {
            $result = $this->dockerManager->listSwarmNodes();
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all services in a Docker Swarm
     *
     * @return JsonResponse
     */
    public function listSwarmServices(): JsonResponse
    {
        try {
            $result = $this->dockerManager->listSwarmServices();
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run a Docker operation asynchronously
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function runAsync(Request $request)
    {
        try {
            $validated = $request->validate([
                'operation' => 'required|string',
                'parameters' => 'sometimes|array',
                'callback' => 'sometimes|nullable|string',
            ]);

            $operation = $validated['operation'];
            $parameters = $validated['parameters'] ?? [];
            $callback = $validated['callback'] ?? null;

            // Check if the operation method exists
            if (!method_exists($this->dockerManager, $operation)) {
                return response()->json([
                    'success' => false,
                    'message' => "Operation '{$operation}' is not supported",
                ], 400);
            }

            $jobId = $this->dockerManager->runAsync($operation, $parameters, $callback);

            return response()->json([
                'success' => true,
                'data' => [
                    'jobId' => $jobId,
                    'operation' => $operation,
                    'status' => 'queued',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the status of an asynchronous job
     *
     * @param string $jobId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getJobStatus(string $jobId)
    {
        try {
            $status = $this->dockerManager->getJobStatus($jobId);

            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => "Job with ID '{$jobId}' not found",
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stream container logs
     *
     * @param string $containerId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamContainerLogs(string $containerId)
    {
        return response()->stream(function () use ($containerId) {
            try {
                $response = $this->dockerManager->streamContainerLogs($containerId);
                
                $stream = $response->toPsrResponse()->getBody();
                
                while (!$stream->eof()) {
                    echo $stream->read(1024);
                    ob_flush();
                    flush();
                }
            } catch (\Exception $e) {
                echo json_encode([
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ]);
                exit;
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Stream Docker events
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamEvents(Request $request)
    {
        $filters = $request->input('filters', []);
        
        return response()->stream(function () use ($filters) {
            try {
                $response = $this->dockerManager->streamEvents($filters);
                
                $stream = $response->toPsrResponse()->getBody();
                
                while (!$stream->eof()) {
                    $chunk = $stream->read(1024);
                    
                    // Format chunk as server-sent event
                    echo "data: " . $chunk . "\n\n";
                    ob_flush();
                    flush();
                }
            } catch (\Exception $e) {
                echo "data: " . json_encode([
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ]) . "\n\n";
                exit;
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
}
