<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Old permission name => the resource base name used by the new
     * view/create/edit/delete permissions (matches routes/web.php).
     */
    private const OLD_TO_RESOURCE = [
        'manage owners' => 'owners',
        'manage properties' => 'properties',
        'manage rooms' => 'rooms',
        'manage tenants' => 'tenants',
        'view tenant records' => 'tenants',
        'manage rentals' => 'rentals',
        'manage payments' => 'payments',
        'manage expenses' => 'expenses',
        'manage utilities' => 'utilities',
        'manage complaints' => 'complaints',
        'manage maintenance requests' => 'maintenance requests',
    ];

    private const ACTIONS = ['view', 'create', 'edit', 'delete'];

    /**
     * Every role that held one of the old bundled permissions keeps the same
     * effective access: "manage X" (full CRUD) maps to all four new
     * permissions, while the read-only "view tenant records" maps to just
     * "view tenants". Existing role customizations (e.g. an admin manually
     * granting extra permissions via the Permission Matrix UI) are preserved
     * because we translate per-role instead of re-seeding from scratch.
     */
    public function up(): void
    {
        if (! class_exists(Permission::class)) {
            return;
        }

        foreach (Role::with('permissions')->get() as $role) {
            $oldNames = $role->permissions->pluck('name')->intersect(array_keys(self::OLD_TO_RESOURCE));

            if ($oldNames->isEmpty()) {
                continue;
            }

            foreach ($oldNames as $oldName) {
                $resource = self::OLD_TO_RESOURCE[$oldName];
                $newActions = $oldName === 'view tenant records' ? ['view'] : self::ACTIONS;

                foreach ($newActions as $action) {
                    $permission = Permission::firstOrCreate(['name' => "{$action} {$resource}"]);

                    if (! $role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }

            $role->revokePermissionTo($oldNames->all());
        }

        Permission::whereIn('name', array_keys(self::OLD_TO_RESOURCE))->delete();
    }

    public function down(): void
    {
        // One-way data migration: the old bundled permissions are gone from
        // the routes, so there's nothing meaningful to restore.
    }
};
