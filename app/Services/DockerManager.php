<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class DockerManager
{
    /**
     * The Docker API version
     *
     * @var string
     */
    protected string $apiVersion = 'v1.41';

    /**
     * The Docker socket path
     *
     * @var string
     */
    protected string $dockerSocket = '/var/run/docker.sock';

    /**
     * Get a preconfigured HTTP client instance for Docker API calls
     *
     * @return PendingRequest
     */
    protected function getHttpClient(): PendingRequest
    {
        return Http::baseUrl("http://localhost/{$this->apiVersion}")
            ->withOptions([
                'curl' => [
                    CURLOPT_UNIX_SOCKET_PATH => $this->dockerSocket,
                ],
            ])
            ->acceptJson()
            ->timeout(60);
    }

    /**
     * Process the API response and handle errors
     *
     * @param Response $response
     * @param string $errorMessage
     * @return array
     * @throws \Exception
     */
    protected function processResponse(Response $response, string $errorMessage = 'Docker API request failed'): array
    {
        if ($response->successful()) {
            return $response->json() ?: [];
        }

        $message = $response->json('message') ?? $errorMessage;
        $statusCode = $response->status();
        
        throw new \Exception("{$errorMessage}: {$message} (Status code: {$statusCode})");
    }

    /**
     * List all Docker containers
     *
     * @param bool $all Include stopped containers
     * @return array
     * @throws \Exception
     */
    public function listContainers(bool $all = true): array
    {
        $response = $this->getHttpClient()->get('/containers/json', [
            'all' => $all ? 'true' : 'false',
        ]);

        return $this->processResponse($response, 'Failed to list containers');
    }

    /**
     * List all Docker images
     *
     * @return array
     * @throws \Exception
     */
    public function listImages(): array
    {
        $response = $this->getHttpClient()->get('/images/json');
        
        return $this->processResponse($response, 'Failed to list images');
    }

    /**
     * Start a Docker container
     *
     * @param string $containerId
     * @return array
     * @throws \Exception
     */
    public function startContainer(string $containerId): array
    {
        $this->validateContainerId($containerId);
        
        $response = $this->getHttpClient()->post("/containers/{$containerId}/start");
        
        // Start operation returns an empty response when successful
        if ($response->noContent() || $response->successful()) {
            return ['success' => true, 'message' => "Container {$containerId} started successfully"];
        }
        
        return $this->processResponse($response, "Failed to start container {$containerId}");
    }

    /**
     * Stop a Docker container
     *
     * @param string $containerId
     * @param int $timeout Seconds to wait before killing the container
     * @return array
     * @throws \Exception
     */
    public function stopContainer(string $containerId, int $timeout = 10): array
    {
        $this->validateContainerId($containerId);
        
        $response = $this->getHttpClient()->post("/containers/{$containerId}/stop", [
            't' => $timeout,
        ]);
        
        // Stop operation returns an empty response when successful
        if ($response->noContent() || $response->successful()) {
            return ['success' => true, 'message' => "Container {$containerId} stopped successfully"];
        }
        
        return $this->processResponse($response, "Failed to stop container {$containerId}");
    }

    /**
     * Restart a Docker container
     *
     * @param string $containerId
     * @param int $timeout Seconds to wait before killing the container
     * @return array
     * @throws \Exception
     */
    public function restartContainer(string $containerId, int $timeout = 10): array
    {
        $this->validateContainerId($containerId);
        
        $response = $this->getHttpClient()->post("/containers/{$containerId}/restart", [
            't' => $timeout,
        ]);
        
        // Restart operation returns an empty response when successful
        if ($response->noContent() || $response->successful()) {
            return ['success' => true, 'message' => "Container {$containerId} restarted successfully"];
        }
        
        return $this->processResponse($response, "Failed to restart container {$containerId}");
    }

    /**
     * Remove a Docker container
     *
     * @param string $containerId
     * @param bool $force Whether to forcefully remove the container
     * @param bool $removeVolumes Remove volumes attached to the container
     * @return array
     * @throws \Exception
     */
    public function removeContainer(string $containerId, bool $force = false, bool $removeVolumes = false): array
    {
        $this->validateContainerId($containerId);
        
        $response = $this->getHttpClient()->delete("/containers/{$containerId}", [
            'force' => $force ? 'true' : 'false',
            'v' => $removeVolumes ? 'true' : 'false',
        ]);
        
        // Remove operation returns an empty response when successful
        if ($response->noContent() || $response->successful()) {
            return ['success' => true, 'message' => "Container {$containerId} removed successfully"];
        }
        
        return $this->processResponse($response, "Failed to remove container {$containerId}");
    }

    /**
     * Get logs from a Docker container
     *
     * @param string $containerId
     * @param bool $stdout Include stdout logs
     * @param bool $stderr Include stderr logs
     * @param int $tail Number of lines to show from the end of the logs
     * @return array
     * @throws \Exception
     */
    public function getContainerLogs(string $containerId, bool $stdout = true, bool $stderr = true, int $tail = 100): array
    {
        $this->validateContainerId($containerId);
        
        $response = $this->getHttpClient()->get("/containers/{$containerId}/logs", [
            'stdout' => $stdout ? 'true' : 'false',
            'stderr' => $stderr ? 'true' : 'false',
            'tail' => $tail,
        ]);
        
        if ($response->successful()) {
            // Logs are returned as a string, not JSON
            return ['logs' => $response->body()];
        }
        
        return $this->processResponse($response, "Failed to get logs for container {$containerId}");
    }

    /**
     * Execute a command in a running Docker container
     *
     * @param string $containerId
     * @param array $command
     * @param bool $detach Run in background
     * @return array
     * @throws \Exception
     */
    public function executeCommandInContainer(string $containerId, array $command, bool $detach = false): array
    {
        $this->validateContainerId($containerId);
        
        // Step 1: Create exec instance
        $createResponse = $this->getHttpClient()->post("/containers/{$containerId}/exec", [
            'AttachStdin' => false,
            'AttachStdout' => true,
            'AttachStderr' => true,
            'Tty' => false,
            'Cmd' => $command,
            'Detach' => $detach,
        ]);
        
        $execData = $this->processResponse($createResponse, "Failed to create exec instance for container {$containerId}");
        $execId = $execData['Id'] ?? null;
        
        if (!$execId) {
            throw new \Exception("Failed to get exec ID for container {$containerId}");
        }
        
        // Step 2: Start exec instance
        $startResponse = $this->getHttpClient()->post("/exec/{$execId}/start", [
            'Detach' => $detach,
            'Tty' => false,
        ]);
        
        if ($startResponse->successful()) {
            if ($detach) {
                return ['success' => true, 'message' => "Command executed in background in container {$containerId}"];
            } else {
                return ['output' => $startResponse->body()];
            }
        }
        
        return $this->processResponse($startResponse, "Failed to execute command in container {$containerId}");
    }

    /**
     * Run a new Docker container
     *
     * @param string $imageName
     * @param string|null $containerName
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function runContainer(string $imageName, ?string $containerName = null, array $options = []): array
    {
        $this->validateImageName($imageName);
        
        // Prepare container creation payload
        $payload = [
            'Image' => $imageName,
            'HostConfig' => [],
        ];
        
        // Add container name if provided
        if ($containerName !== null) {
            // Validate container name
            if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $containerName)) {
                throw new \Exception('Invalid container name format');
            }
            
            $payload['name'] = $containerName;
        }
        
        // Process common options
        if (!empty($options['cmd']) && is_array($options['cmd'])) {
            $payload['Cmd'] = $options['cmd'];
        }
        
        if (!empty($options['env']) && is_array($options['env'])) {
            $payload['Env'] = $options['env'];
        }
        
        if (!empty($options['ports']) && is_array($options['ports'])) {
            $payload['HostConfig']['PortBindings'] = [];
            foreach ($options['ports'] as $containerPort => $hostPort) {
                $payload['HostConfig']['PortBindings']["$containerPort/tcp"] = [
                    ['HostPort' => (string) $hostPort]
                ];
            }
            $payload['ExposedPorts'] = array_fill_keys(array_map(fn($port) => "$port/tcp", array_keys($options['ports'])), (object) []);
        }
        
        if (!empty($options['volumes']) && is_array($options['volumes'])) {
            $payload['HostConfig']['Binds'] = [];
            foreach ($options['volumes'] as $hostPath => $containerPath) {
                $payload['HostConfig']['Binds'][] = "$hostPath:$containerPath";
            }
        }
        
        // Step 1: Create the container
        $createResponse = $this->getHttpClient()->post('/containers/create', $payload, ['name' => $containerName]);
        $createData = $this->processResponse($createResponse, "Failed to create container from image {$imageName}");
        
        $containerId = $createData['Id'] ?? null;
        if (!$containerId) {
            throw new \Exception("Failed to get container ID after creation");
        }
        
        // Step 2: Start the container
        $startResponse = $this->getHttpClient()->post("/containers/{$containerId}/start");
        
        if ($startResponse->successful() || $startResponse->noContent()) {
            return [
                'success' => true,
                'message' => "Container created and started successfully",
                'container_id' => $containerId
            ];
        }
        
        return $this->processResponse($startResponse, "Failed to start newly created container {$containerId}");
    }

    /**
     * Pull a Docker image
     *
     * @param string $imageName
     * @return array
     * @throws \Exception
     */
    public function pullImage(string $imageName): array
    {
        $this->validateImageName($imageName);
        
        $response = $this->getHttpClient()->post('/images/create', [], [
            'fromImage' => $imageName,
        ]);
        
        if ($response->successful()) {
            return [
                'success' => true,
                'message' => "Image {$imageName} pulled successfully",
                'details' => $response->body()
            ];
        }
        
        return $this->processResponse($response, "Failed to pull image {$imageName}");
    }

    /**
     * Remove a Docker image
     *
     * @param string $imageId
     * @param bool $force Force removal
     * @return array
     * @throws \Exception
     */
    public function removeImage(string $imageId, bool $force = false): array
    {
        $this->validateImageName($imageId);
        
        $response = $this->getHttpClient()->delete("/images/{$imageId}", [
            'force' => $force ? 'true' : 'false',
        ]);
        
        if ($response->successful()) {
            return [
                'success' => true,
                'message' => "Image {$imageId} removed successfully",
                'details' => $response->json()
            ];
        }
        
        return $this->processResponse($response, "Failed to remove image {$imageId}");
    }

    /**
     * List all Docker volumes
     *
     * @return array
     * @throws \Exception
     */
    public function listVolumes(): array
    {
        $response = $this->getHttpClient()->get('/volumes');
        
        return $this->processResponse($response, 'Failed to list volumes');
    }

    /**
     * Create a Docker volume
     *
     * @param string $volumeName
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function createVolume(string $volumeName, array $options = []): array
    {
        // Validate volume name
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $volumeName)) {
            throw new \Exception('Invalid volume name format');
        }
        
        $payload = [
            'Name' => $volumeName,
            'Driver' => 'local',
        ];
        
        if (!empty($options)) {
            $payload['DriverOpts'] = $options;
        }
        
        $response = $this->getHttpClient()->post('/volumes/create', $payload);
        
        return $this->processResponse($response, "Failed to create volume {$volumeName}");
    }

    /**
     * Remove a Docker volume
     *
     * @param string $volumeName
     * @param bool $force Force removal
     * @return array
     * @throws \Exception
     */
    public function removeVolume(string $volumeName, bool $force = false): array
    {
        // Validate volume name
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $volumeName)) {
            throw new \Exception('Invalid volume name format');
        }
        
        $response = $this->getHttpClient()->delete("/volumes/{$volumeName}", [
            'force' => $force ? 'true' : 'false',
        ]);
        
        if ($response->successful() || $response->noContent()) {
            return [
                'success' => true,
                'message' => "Volume {$volumeName} removed successfully"
            ];
        }
        
        return $this->processResponse($response, "Failed to remove volume {$volumeName}");
    }

    /**
     * List all Docker networks
     *
     * @return array
     * @throws \Exception
     */
    public function listNetworks(): array
    {
        $response = $this->getHttpClient()->get('/networks');
        
        return $this->processResponse($response, 'Failed to list networks');
    }

    /**
     * Create a Docker network
     *
     * @param string $networkName
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function createNetwork(string $networkName, array $options = []): array
    {
        // Validate network name
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $networkName)) {
            throw new \Exception('Invalid network name format');
        }
        
        $payload = [
            'Name' => $networkName,
            'CheckDuplicate' => true,
        ];
        
        if (!empty($options['driver'])) {
            $payload['Driver'] = $options['driver'];
            unset($options['driver']);
        }
        
        if (!empty($options)) {
            $payload['Options'] = $options;
        }
        
        $response = $this->getHttpClient()->post('/networks/create', $payload);
        
        return $this->processResponse($response, "Failed to create network {$networkName}");
    }

    /**
     * Remove a Docker network
     *
     * @param string $networkName
     * @return array
     * @throws \Exception
     */
    public function removeNetwork(string $networkName): array
    {
        // Validate network name
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $networkName)) {
            throw new \Exception('Invalid network name format');
        }
        
        $response = $this->getHttpClient()->delete("/networks/{$networkName}");
        
        if ($response->successful() || $response->noContent()) {
            return [
                'success' => true,
                'message' => "Network {$networkName} removed successfully"
            ];
        }
        
        return $this->processResponse($response, "Failed to remove network {$networkName}");
    }

    /**
     * Initialize a new Docker Swarm
     *
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function initSwarm(array $options = []): array
    {
        $payload = [
            'ListenAddr' => '0.0.0.0:2377',
            'AdvertiseAddr' => '0.0.0.0:2377',
        ];
        
        if (!empty($options)) {
            $payload = array_merge($payload, $options);
        }
        
        $response = $this->getHttpClient()->post('/swarm/init', $payload);
        
        if ($response->successful()) {
            // The response body contains the swarm ID as a string
            return [
                'success' => true,
                'message' => 'Swarm initialized successfully',
                'swarm_id' => $response->body()
            ];
        }
        
        return $this->processResponse($response, 'Failed to initialize swarm');
    }

    /**
     * Join a Docker Swarm
     *
     * @param string $joinToken
     * @param string $managerAddress
     * @return array
     * @throws \Exception
     */
    public function joinSwarm(string $joinToken, string $managerAddress): array
    {
        // Validate join token (alphanumeric and dashes)
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $joinToken)) {
            throw new \Exception('Invalid join token format');
        }
        
        // Basic validation for manager address (IP:PORT or hostname:PORT)
        if (!preg_match('/^[a-zA-Z0-9\-\.]+:[0-9]+$/', $managerAddress)) {
            throw new \Exception('Invalid manager address format');
        }
        
        $response = $this->getHttpClient()->post('/swarm/join', [
            'JoinToken' => $joinToken,
            'RemoteAddrs' => [$managerAddress],
            'ListenAddr' => '0.0.0.0:2377',
        ]);
        
        if ($response->successful() || $response->noContent()) {
            return [
                'success' => true,
                'message' => 'Successfully joined the swarm'
            ];
        }
        
        return $this->processResponse($response, 'Failed to join swarm');
    }

    /**
     * Leave a Docker Swarm
     *
     * @param bool $force
     * @return array
     * @throws \Exception
     */
    public function leaveSwarm(bool $force = false): array
    {
        $response = $this->getHttpClient()->post('/swarm/leave', [
            'force' => $force,
        ]);
        
        if ($response->successful() || $response->noContent()) {
            return [
                'success' => true,
                'message' => 'Successfully left the swarm'
            ];
        }
        
        return $this->processResponse($response, 'Failed to leave swarm');
    }

    /**
     * List all nodes in a Docker Swarm
     *
     * @return array
     * @throws \Exception
     */
    public function listSwarmNodes(): array
    {
        $response = $this->getHttpClient()->get('/nodes');
        
        return $this->processResponse($response, 'Failed to list swarm nodes');
    }

    /**
     * List all services in a Docker Swarm
     *
     * @return array
     * @throws \Exception
     */
    public function listSwarmServices(): array
    {
        $response = $this->getHttpClient()->get('/services');
        
        return $this->processResponse($response, 'Failed to list swarm services');
    }

    /**
     * Validate container ID to prevent command injection
     * 
     * @param string $containerId
     * @return bool
     * @throws \Exception
     */
    protected function validateContainerId(string $containerId): bool
    {
        // Container IDs are alphanumeric
        if (!preg_match('/^[a-zA-Z0-9]+$/', $containerId)) {
            throw new \Exception('Invalid container ID format');
        }
        
        return true;
    }

    /**
     * Validate image name to prevent command injection
     * 
     * @param string $imageName
     * @return bool
     * @throws \Exception
     */
    protected function validateImageName(string $imageName): bool
    {
        // Docker image names can contain alphanumeric, dashes, underscores, periods, and slashes
        if (!preg_match('/^[a-zA-Z0-9\-_.\/]+$/', $imageName)) {
            throw new \Exception('Invalid image name format');
        }
        
        return true;
    }

    /**
     * Run a Docker operation asynchronously
     *
     * @param string $operation The name of the operation method
     * @param array $parameters Parameters for the operation
     * @param string|null $callback Optional callback identifier
     * @return string The job ID
     */
    public function runAsync(string $operation, array $parameters = [], ?string $callback = null): string
    {
        $job = new \App\Jobs\DockerOperationJob($operation, $parameters, $callback);
        dispatch($job);
        
        return $job->getJobId();
    }

    /**
     * Get the status of an async job
     *
     * @param string $jobId The ID of the job
     * @return array|null The job status or null if not found
     */
    public function getJobStatus(string $jobId): ?array
    {
        return \Illuminate\Support\Facades\Cache::get("docker_job:{$jobId}");
    }

    /**
     * Stream container logs in real-time
     *
     * @param string $containerId The ID of the container
     * @return \Illuminate\Http\Client\Response The streaming response
     */
    public function streamContainerLogs(string $containerId): \Illuminate\Http\Client\Response
    {
        $this->validateContainerId($containerId);
        
        // Configure client for streaming response
        $client = $this->getHttpClient();
        
        return $client->withOptions([
            'stream' => true,
            'read_timeout' => 0,
        ])->get("/containers/{$containerId}/logs", [
            'follow' => true,
            'stdout' => true,
            'stderr' => true,
            'timestamps' => true,
        ]);
    }

    /**
     * Stream Docker events in real-time
     *
     * @param array $filters Optional filters for events
     * @return \Illuminate\Http\Client\Response The streaming response
     */
    public function streamEvents(array $filters = []): \Illuminate\Http\Client\Response
    {
        // Configure client for streaming response
        $client = $this->getHttpClient();
        
        $queryParams = [];
        if (!empty($filters)) {
            $queryParams['filters'] = json_encode($filters);
        }
        
        return $client->withOptions([
            'stream' => true,
            'read_timeout' => 0,
        ])->get("/events", $queryParams);
    }
} 