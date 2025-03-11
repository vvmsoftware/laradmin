<?php

namespace App\Console\Commands\Permission;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class UserGrantRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:user-grant-role {user} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant a role to a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = $this->argument('user');
        $role = $this->argument('role');

        $user = User::query()->where('email', $user)->first();
        if (! $user) {
            $this->error("User not found");
            return 1;
        }

        $role = Role::query()->where('name', $role)->first();
        if (! $role) {
            $this->error("Role not found");
            return 2;
        }

        $user->assignRole($role);
        $this->info("Role [{$role->name}] granted to [{$user->email}]");
        return 0;
    }
}
