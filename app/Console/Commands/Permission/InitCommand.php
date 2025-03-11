<?php

namespace App\Console\Commands\Permission;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default permissions and roles for the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Run the command to create the default permissions
        $this->call('permission:cache-reset');
        $this->call('permission:create-permission', ['name' => 'dashboard view']);
        $this->call('permission:create-permission', ['name' => 'users list']);
        $this->call('permission:create-permission', ['name' => 'users create']);
        $this->call('permission:create-permission', ['name' => 'users edit']);
        $this->call('permission:create-permission', ['name' => 'users delete']);
        $this->call('permission:create-permission', ['name' => 'roles list']);
        $this->call('permission:create-permission', ['name' => 'roles create']);
        $this->call('permission:create-permission', ['name' => 'roles edit']);
        $this->call('permission:create-permission', ['name' => 'roles delete']);
        $this->call('permission:create-permission', ['name' => 'permissions edit']);

        // Run the command to create the default roles
        $this->call('permission:create-role', ['name' => 'admin']);
        $this->call('permission:create-role', ['name' => 'support']);
        $this->call('permission:create-role', ['name' => 'user']);
        $this->call('permission:create-role', ['name' => 'pending']);

        // Assign the permissions to the roles
        $admin = Role::findByName('admin');
        $admin->givePermissionTo(Permission::all());
    }
}
