<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index(): View
    {
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::with('roles')->orderBy('name')->get();

        return view('permissions.index', compact('roles', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        $permission = Permission::create(['name' => $validated['name']]);

        Activity::log('created', $permission, "Created permission \"{$permission->name}\"");

        return redirect()->route('permissions.index')->with('status', 'Permission created.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        Activity::log('deleted', null, "Deleted permission \"{$permission->name}\"");

        $permission->delete();

        return redirect()->route('permissions.index')->with('status', 'Permission deleted.');
    }

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'permission_id' => ['required', 'integer', 'exists:permissions,id'],
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $permission = Permission::findOrFail($validated['permission_id']);

        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
            $granted = false;
        } else {
            $role->givePermissionTo($permission);
            $granted = true;
        }

        $verb = $granted ? 'Granted' : 'Revoked';
        $preposition = $granted ? 'to' : 'from';
        Activity::log(
            $granted ? 'granted' : 'revoked',
            $role,
            "{$verb} \"".Str::headline($permission->name)."\" permission {$preposition} role \"".Str::headline($role->name).'"'
        );

        return response()->json(['granted' => $granted]);
    }
}
