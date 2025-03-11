<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\CreateRole;
use App\Http\Requests\System\DeleteRole;
use App\Http\Requests\System\EditRoles;
use App\Http\Requests\System\ViewRoles;
use App\Models\UserRole;
use Illuminate\Http\Request;
use App\Traits\HasRequestSorting;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    use HasRequestSorting;
    public function index(ViewRoles $request)
    {
        $roles = UserRole::query()->withCount('users');
        $roles = $this->getSearch($request, $roles, ['name']);
        $sorting = $this->getSorting($request, ['name', 'id','created_at','updated_at']);
        $roles->orderBy($sorting[0], $sorting[1]);
        $limit = $this->getLimit($request);

        return Inertia::render('system/roles/Index',[
            'availablePermissions' => Permission::all()->pluck('name'),
            'roles' => $roles->paginate($limit)->through(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'users_count' => $role->users_count,
                    'created_at' => $role->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $role->updated_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    public function show(ViewRoles $request, Role $role)
    {
        $role->load('permissions');
        return Inertia::render('system/roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
            'availablePermissions' => Permission::all()->pluck('name'),
        ]);
    }

    public function update(EditRoles $request, Role $role)
    {
        $validated = $request->validated();
        $role->update(['name' => strtolower($validated['name'])]);
        $role->syncPermissions($validated['permissions']);
        return redirect()->route('roles.index');
    }

    public function empty(EditRoles $request, Role $role)
    {
        $role->users()->detach();
        return redirect()->route('roles.index');
    }

    public function destroy(DeleteRole $request, Role $role)
    {
        $role->users()->detach();
        $role->delete();
        return redirect()->route('roles.index');
    }

    public function create(ViewRoles $request)
    {
        return Inertia::render('system/roles/Create', [
            'availablePermissions' => Permission::all()->pluck('name'),
        ]);
    }

    public function store(CreateRole $request)
    {
        $validated = $request->validated();
        $role = Role::create(['name' => strtolower($validated['name']), 'guard_name' => 'web']);
        $role->givePermissionTo($validated['permissions']);
        return redirect()->route('roles.index');
    }
}
