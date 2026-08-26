<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Each resource gets its own view/create/edit/delete permissions instead
     * of one all-or-nothing "manage" permission, so a role can (for example)
     * add records without being able to edit or delete them. Utilities and
     * complaints groups share one permission set across their two resources
     * (types+bills, complaints+comments) — same grouping the routes use.
     */
    private const RESOURCES = [
        'owners', 'properties', 'rooms', 'tenants', 'rentals',
        'payments', 'expenses', 'utilities', 'complaints', 'maintenance requests',
    ];

    private const ACTIONS = ['view', 'create', 'edit', 'delete'];

    public function run(): void
    {
        $resourcePermissions = collect(self::RESOURCES)
            ->crossJoin(self::ACTIONS)
            ->map(fn ($pair) => "{$pair[1]} {$pair[0]}")
            ->all();

        $standalonePermissions = [
            'view dashboard',
            'manage users',
            'manage roles',
            'view reports',
            'view activity logs',
            'manage settings',
        ];

        $permissions = array_merge($standalonePermissions, $resourcePermissions);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions($permissions);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(['view dashboard', 'manage users']);

        // Full read/write/delete access to every property-management resource.
        $propertyManager = Role::firstOrCreate(['name' => 'property-manager']);
        $propertyManager->syncPermissions(array_merge(['view dashboard', 'view reports'], $resourcePermissions));

        // Can add new records for every resource but can't change or remove
        // existing ones — for staff who only need to log data.
        $dataEntry = Role::firstOrCreate(['name' => 'data-entry']);
        $dataEntry->syncPermissions(array_merge(
            ['view dashboard'],
            collect(self::RESOURCES)->flatMap(fn ($resource) => ["view {$resource}", "create {$resource}"])->all()
        ));

        $supportStaff = Role::firstOrCreate(['name' => 'support-staff']);
        $supportStaff->syncPermissions(['view dashboard', 'view tenants']);

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions(['view dashboard']);

        // No admin permissions at all — access to the tenant self-service
        // portal is gated by this role directly (see routes/web.php), not by
        // the resource permissions used everywhere else.
        Role::firstOrCreate(['name' => 'tenant']);

        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('password')]
        );
        $superAdminUser->syncRoles(['super-admin']);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );
        $adminUser->syncRoles(['admin']);
    }
}
