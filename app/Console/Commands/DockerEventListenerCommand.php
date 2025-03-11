<?php

namespace App\Console\Commands;

use App\Services\DockerEventBroadcaster;
use Illuminate\Console\Command;

class DockerEventListenerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:listen-events 
                            {--filter=* : Event filters in format key=value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for Docker events and broadcast them to WebSocket clients';

    /**
     * Execute the console command.
     */
    public function handle(DockerEventBroadcaster $broadcaster)
    {
        $this->info('Starting Docker event listener...');
        
        $filters = [];
        $filterOptions = $this->option('filter');
        
        if (!empty($filterOptions)) {
            foreach ($filterOptions as $filter) {
                list($key, $value) = explode('=', $filter, 2);
                $filters[$key] = $value;
            }
            
            $this->info('Applied filters: ' . json_encode($filters));
        }
        
        $this->info('Listening for Docker events. Press Ctrl+C to stop.');
        
        try {
            $result = $broadcaster->start($filters);
            
            if (!$result) {
                $this->error('Failed to start Docker event broadcaster.');
                return 1;
            }
            
            // This will run until the command is interrupted
            while (true) {
                sleep(1);
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        } finally {
            $broadcaster->stop();
            $this->info('Docker event listener stopped.');
        }
        
        return 0;
    }
} 