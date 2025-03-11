<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\CreateUser;
use App\Http\Requests\System\DeleteUser;
use App\Http\Requests\System\EditPassword;
use App\Http\Requests\System\EditPermissions;
use App\Http\Requests\System\EditProfile;
use App\Http\Requests\System\EditUser;
use App\Http\Requests\System\ViewUsers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index(ViewUsers $request)
    {
        $users = User::query()->with('roles');
        // Add search

        // This is required for Postgres, as it is case sensitive
        // SQLite and MySQL are case insensitive by default, unless you specify the collation otherwise
        $like = 'like';
        if (DB::connection() instanceof \Illuminate\Database\PostgresConnection) {
            $like = 'ilike';
        }
        if ($request->has('search') && $request->search != '' && $request->search != null) {
            $users->where('name', $like, '%'.$request->search.'%')
                ->orWhere('email', $like, '%'.$request->search.'%');
        }

        // Add status search
        if ($request->has('statuses') && is_array($request->statuses) && count($request->statuses) > 0) {
            $users = $users->where(function ($query) use ($request) {
                foreach ($request->statuses as $status) {
                    if ($status == 'Active') {
                        $query->orWhere('enabled', true);
                    } elseif ($status == 'Inactive') {
                        $query->orWhere('enabled', false);
                    }
                }
            });
        }

        // Add role search
        if ($request->has('roles') && is_array($request->roles) && count($request->roles) > 0) {
            $users = $users->whereHas('roles', function ($query) use ($request) {
                $query->whereIn('name', $request->roles);
            });
        }

        // Add sorting
        $sortBy = 'id';
        $sortDir = 'desc';

        if ($request->has('sortBy') && $request->sortBy != null) {
            if (in_array($request->sortBy, ['name', 'email','created_at','updated_at','id','enabled'])) {
                $sortBy = $request->sortBy;
            }
            if ($request->has('sortDir') && in_array($request->sortDir, ['asc', 'desc'])) {
                $sortDir = $request->sortDir;
            }
        }
        $users->orderBy($sortBy, $sortDir);

        // Add pagination limit
        $limit = $request->has('perPage') ? (int)$request->perPage : 20;
        if ($limit < 1 || $limit > 100) {
            $limit = 20;
        }

        return Inertia::render('system/users/Index', [
            'users' => $users->paginate($limit)->through(fn ($user) => [
                'id' => $user->id,
                'avatar' => $user->avatar,
                'enabled' => $user->enabled,
                'roles' => $user->roles->pluck('name')->implode(', '),
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ]),
            'roles' => Role::all()->pluck('name'),
            'permissions' => Permission::all()->pluck('name'),
        ]);
    }

    public function create()
    {
        return Inertia::render('system/users/Create', [
            'roles' => Role::all()->pluck('name'),
            'permissions' => Permission::all()->pluck('name'),
        ]);
    }

    public function edit(ViewUsers $request, User $user)
    {
        $user->load('roles');
        return Inertia::render('system/users/Edit', [
            'roles' => Role::all()->pluck('name'),
            'permissions' => Permission::all()->pluck('name'),
            'user' => [
                'id' => $user->id,
                'avatar' => $user->avatar,
                'enabled' => (bool)$user->enabled,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->permissions->pluck('name'),
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function editProfile(EditProfile $request, User $user)
    {
        $user->update($request->validated());
        return redirect()->route('users.index');
    }

    public function editPassword(EditPassword $request, User $user)
    {
        $user->update(['password', Hash::make($request->password)]);
        return redirect()->route('users.index');
    }

    public function editPermissions(EditPermissions $request, User $user)
    {
        $user->syncRoles($request->roles);
        $user->syncPermissions($request->permissions);
        return redirect()->route('users.index');
    }

    public function destroy(DeleteUser $request, User $user)
    {
        if ($user->id == auth()->id()) {
            session()->flash('error', 'You cannot delete yourself.');
            return redirect()->route('users.index');
        }
        $user->delete();
        return redirect()->route('users.index');
    }

    public function store(CreateUser $request)
    {
        $user = User::create($request->validated());
        $user->syncRoles($request->roles);
        $user->syncPermissions($request->permissions);

        return redirect()->route('users.index');
    }
}
