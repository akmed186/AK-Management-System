<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ComplaintCommentController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\RentPaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantPortalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UtilityBillController;
use App\Http\Controllers\UtilityTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Read-only self-service area for tenant accounts — scoped entirely to the
// logged-in user's own Tenant record, gated by role rather than the
// view/create/edit/delete permissions the staff-facing routes below use.
Route::middleware(['auth', 'role:tenant'])->prefix('my')->name('portal.')->group(function () {
    Route::get('dashboard', [TenantPortalController::class, 'index'])->name('dashboard');
    Route::get('payments/export', [TenantPortalController::class, 'exportPayments'])->name('payments.export');
    Route::get('payments', [TenantPortalController::class, 'payments'])->name('payments');
    Route::get('complaints/create', [TenantPortalController::class, 'createComplaint'])->name('complaints.create');
    Route::post('complaints', [TenantPortalController::class, 'storeComplaint'])->name('complaints.store');
    Route::get('complaints', [TenantPortalController::class, 'complaints'])->name('complaints.index');
    Route::get('maintenance-requests', [TenantPortalController::class, 'maintenanceRequests'])->name('maintenance-requests');
    Route::get('bills/export', [TenantPortalController::class, 'exportBills'])->name('bills.export');
    Route::get('bills', [TenantPortalController::class, 'bills'])->name('bills');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Every menu below is gated by its own permission, driven entirely by the
    // roles/permissions tables (see the Permission Matrix). No permission for
    // a given menu means no route access to it, regardless of role name.

    Route::middleware('permission:manage users')->group(function () {
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::patch('users/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('users.bulk-deactivate');
        Route::patch('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::resource('users', UserController::class)->except('create', 'store');
    });

    Route::middleware('permission:manage roles')->group(function () {
        Route::resource('roles', RoleController::class)->except('show');

        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
        Route::post('permissions/toggle', [PermissionController::class, 'toggle'])->name('permissions.toggle');
    });

    Route::middleware('permission:view reports')->group(function () {
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

    Route::middleware('permission:view activity logs')->group(function () {
        Route::get('activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    Route::middleware('permission:manage settings')->group(function () {
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    // Each resource below is split into four independent permissions —
    // view / create / edit / delete — instead of one all-or-nothing "manage"
    // permission, so a role can (for example) add records without being able
    // to edit or delete them.

    Route::middleware('permission:view owners')->group(function () {
        Route::get('owners/export', [OwnerController::class, 'export'])->name('owners.export');
        Route::resource('owners', OwnerController::class)->only('index');
    });
    Route::middleware('permission:create owners')->group(function () {
        Route::resource('owners', OwnerController::class)->only('create', 'store');
    });
    Route::middleware('permission:edit owners')->group(function () {
        Route::resource('owners', OwnerController::class)->only('edit', 'update');
    });
    Route::middleware('permission:delete owners')->group(function () {
        Route::delete('owners/bulk-destroy', [OwnerController::class, 'bulkDestroy'])->name('owners.bulk-destroy');
        Route::resource('owners', OwnerController::class)->only('destroy');
    });

    Route::middleware('permission:view properties')->group(function () {
        Route::get('properties/export', [PropertyController::class, 'export'])->name('properties.export');
        Route::resource('properties', PropertyController::class)->only('index');
    });
    Route::middleware('permission:create properties')->group(function () {
        Route::resource('properties', PropertyController::class)->only('create', 'store');
    });
    Route::middleware('permission:edit properties')->group(function () {
        Route::resource('properties', PropertyController::class)->only('edit', 'update');
    });
    Route::middleware('permission:delete properties')->group(function () {
        Route::delete('properties/bulk-destroy', [PropertyController::class, 'bulkDestroy'])->name('properties.bulk-destroy');
        Route::resource('properties', PropertyController::class)->only('destroy');
    });

    Route::middleware('permission:view rooms')->group(function () {
        Route::get('rooms/export', [RoomController::class, 'export'])->name('rooms.export');
        Route::resource('rooms', RoomController::class)->only('index');
    });
    Route::middleware('permission:create rooms')->group(function () {
        Route::resource('rooms', RoomController::class)->only('create', 'store');
    });
    Route::middleware('permission:edit rooms')->group(function () {
        Route::resource('rooms', RoomController::class)->only('edit', 'update');
    });
    Route::middleware('permission:delete rooms')->group(function () {
        Route::delete('rooms/bulk-destroy', [RoomController::class, 'bulkDestroy'])->name('rooms.bulk-destroy');
        Route::resource('rooms', RoomController::class)->only('destroy');
    });

    Route::middleware('permission:view tenants')->group(function () {
        Route::get('tenants/export', [TenantController::class, 'export'])->name('tenants.export');
        Route::resource('tenants', TenantController::class)->only('index');
    });
    Route::middleware('permission:create tenants')->group(function () {
        Route::resource('tenants', TenantController::class)->only('create', 'store');
    });
    Route::middleware('permission:edit tenants')->group(function () {
        Route::resource('tenants', TenantController::class)->only('edit', 'update');
    });
    Route::middleware('permission:delete tenants')->group(function () {
        Route::delete('tenants/bulk-destroy', [TenantController::class, 'bulkDestroy'])->name('tenants.bulk-destroy');
        Route::resource('tenants', TenantController::class)->only('destroy');
    });

    Route::middleware('permission:view rentals')->group(function () {
        Route::get('rentals/export', [RentalController::class, 'export'])->name('rentals.export');
        Route::resource('rentals', RentalController::class)->only('index');
    });
    Route::middleware('permission:create rentals')->group(function () {
        Route::resource('rentals', RentalController::class)->only('create', 'store');
    });
    Route::middleware('permission:edit rentals')->group(function () {
        Route::resource('rentals', RentalController::class)->only('edit', 'update');
    });
    Route::middleware('permission:delete rentals')->group(function () {
        Route::delete('rentals/bulk-destroy', [RentalController::class, 'bulkDestroy'])->name('rentals.bulk-destroy');
        Route::resource('rentals', RentalController::class)->only('destroy');
    });

    Route::middleware('permission:view expenses')->group(function () {
        Route::get('expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
        Route::resource('expenses', ExpenseController::class)->only('index');
    });
    Route::middleware('permission:create expenses')->group(function () {
        Route::resource('expenses', ExpenseController::class)->only('create', 'store');
    });
    Route::middleware('permission:edit expenses')->group(function () {
        Route::resource('expenses', ExpenseController::class)->only('edit', 'update');
    });
    Route::middleware('permission:delete expenses')->group(function () {
        Route::delete('expenses/bulk-destroy', [ExpenseController::class, 'bulkDestroy'])->name('expenses.bulk-destroy');
        Route::resource('expenses', ExpenseController::class)->only('destroy');
    });

    Route::middleware('permission:view maintenance requests')->group(function () {
        Route::get('maintenance-requests/export', [MaintenanceRequestController::class, 'export'])->name('maintenance-requests.export');
        Route::resource('maintenance-requests', MaintenanceRequestController::class)->only('index');
    });
    Route::middleware('permission:create maintenance requests')->group(function () {
        Route::resource('maintenance-requests', MaintenanceRequestController::class)->only('create', 'store');
    });
    Route::middleware('permission:edit maintenance requests')->group(function () {
        Route::resource('maintenance-requests', MaintenanceRequestController::class)->only('edit', 'update');
    });
    Route::middleware('permission:delete maintenance requests')->group(function () {
        Route::delete('maintenance-requests/bulk-destroy', [MaintenanceRequestController::class, 'bulkDestroy'])->name('maintenance-requests.bulk-destroy');
        Route::resource('maintenance-requests', MaintenanceRequestController::class)->only('destroy');
    });

    Route::middleware('permission:view payments')->group(function () {
        Route::get('payments/export', [RentPaymentController::class, 'export'])->name('payments.export');
        Route::resource('payments', RentPaymentController::class)->only('index');
    });
    Route::middleware('permission:create payments')->group(function () {
        Route::resource('payments', RentPaymentController::class)->only('create', 'store');
    });
    Route::middleware('permission:edit payments')->group(function () {
        Route::resource('payments', RentPaymentController::class)->only('edit', 'update');
    });
    Route::middleware('permission:delete payments')->group(function () {
        Route::delete('payments/bulk-destroy', [RentPaymentController::class, 'bulkDestroy'])->name('payments.bulk-destroy');
        Route::resource('payments', RentPaymentController::class)->only('destroy');
    });

    Route::middleware('permission:view utilities')->group(function () {
        Route::get('utility-types/export', [UtilityTypeController::class, 'export'])->name('utility-types.export');
        Route::resource('utility-types', UtilityTypeController::class)->only('index');

        Route::get('utility-bills/export', [UtilityBillController::class, 'export'])->name('utility-bills.export');
        Route::resource('utility-bills', UtilityBillController::class)->only('index');
    });
    Route::middleware('permission:create utilities')->group(function () {
        Route::resource('utility-types', UtilityTypeController::class)->only('create', 'store');
        Route::resource('utility-bills', UtilityBillController::class)->only('create', 'store');
    });
    Route::middleware('permission:edit utilities')->group(function () {
        Route::resource('utility-types', UtilityTypeController::class)->only('edit', 'update');
        Route::resource('utility-bills', UtilityBillController::class)->only('edit', 'update');
    });
    Route::middleware('permission:delete utilities')->group(function () {
        Route::delete('utility-types/bulk-destroy', [UtilityTypeController::class, 'bulkDestroy'])->name('utility-types.bulk-destroy');
        Route::resource('utility-types', UtilityTypeController::class)->only('destroy');

        Route::delete('utility-bills/bulk-destroy', [UtilityBillController::class, 'bulkDestroy'])->name('utility-bills.bulk-destroy');
        Route::resource('utility-bills', UtilityBillController::class)->only('destroy');
    });

    Route::middleware('permission:view complaints')->group(function () {
        Route::get('complaints/export', [ComplaintController::class, 'export'])->name('complaints.export');
        Route::resource('complaints', ComplaintController::class)->only('index');

        Route::get('complaint-comments/export', [ComplaintCommentController::class, 'export'])->name('complaint-comments.export');
        Route::resource('complaint-comments', ComplaintCommentController::class)->only('index');
    });
    Route::middleware('permission:create complaints')->group(function () {
        Route::resource('complaints', ComplaintController::class)->only('create', 'store');
        Route::resource('complaint-comments', ComplaintCommentController::class)->only('create', 'store');
    });
    Route::middleware('permission:edit complaints')->group(function () {
        Route::resource('complaints', ComplaintController::class)->only('edit', 'update');
        Route::resource('complaint-comments', ComplaintCommentController::class)->only('edit', 'update');
    });
    Route::middleware('permission:delete complaints')->group(function () {
        Route::delete('complaints/bulk-destroy', [ComplaintController::class, 'bulkDestroy'])->name('complaints.bulk-destroy');
        Route::resource('complaints', ComplaintController::class)->only('destroy');

        Route::delete('complaint-comments/bulk-destroy', [ComplaintCommentController::class, 'bulkDestroy'])->name('complaint-comments.bulk-destroy');
        Route::resource('complaint-comments', ComplaintCommentController::class)->only('destroy');
    });
});

require __DIR__.'/auth.php';
