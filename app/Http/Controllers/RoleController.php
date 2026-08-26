<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Role names the app relies on elsewhere (route middleware, dashboard
     * gating). Renaming or deleting them would silently break access control.
     */
    private const PROTECTED_ROLES = ['super-admin', 'admin', 'user'];

    public function index(): View
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('name')->get();

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get();

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        Activity::log('created', $role, "Created role \"{$role->name}\"");

        return redirect()->route('roles.index')->with('status', 'Role created.');
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::orderBy('name')->get();
        $role->loadMissing('permissions');

        return view('roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
            'isProtected' => in_array($role->name, self::PROTECTED_ROLES, true),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $isProtected = in_array($role->name, self::PROTECTED_ROLES, true);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        // System roles are referenced by name in route middleware, so their name can't change.
        $role->update(['name' => $isProtected ? $role->name : $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        Activity::log('updated', $role, "Updated role \"{$role->name}\"");

        return redirect()->route('roles.index')->with('status', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return redirect()->route('roles.index')->with('status', "The \"{$role->name}\" role is required by the system and can't be deleted.");
        }

        Activity::log('deleted', null, "Deleted role \"{$role->name}\"");

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role deleted.');
    }
}
