<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\MaintenanceRequest;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Rental;
use App\Models\RentPayment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UtilityBill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Leases inside this window are flagged for renewal outreach.
     */
    private const EXPIRING_SOON_DAYS = 30;

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('tenant')) {
            return redirect()->route('portal.dashboard');
        }

        $stats = [];

        // Every figure is only computed (and only ever shown) when the viewer
        // holds the matching permission — the dashboard mirrors the same
        // access rules as the rest of the app instead of exposing counts for
        // menus the user can't otherwise open.
        if ($user->can('view owners')) {
            $stats['owners'] = Owner::count();
        }

        if ($user->can('view properties')) {
            $stats['properties'] = Property::count();
        }

        if ($user->can('view rooms')) {
            $stats['rooms'] = Room::count();
            $stats['rooms_vacant'] = Room::where('status', 'vacant')->count();
            $stats['rooms_occupied'] = Room::where('status', 'occupied')->count();
            $stats['rooms_maintenance'] = Room::where('status', 'maintenance')->count();
        }

        if ($user->can('view tenants')) {
            $stats['tenants'] = Tenant::count();
        }

        if ($user->can('view rentals')) {
            $stats['active_rentals'] = Rental::where('lease_status', 'active')->count();
        }

        if ($user->can('manage users')) {
            $stats['users'] = User::count();
            $stats['unassigned_users'] = User::doesntHave('roles')->count();
        }

        if ($user->can('view payments')) {
            $thisMonth = RentPayment::where('status', 'completed')
                ->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount_paid');

            $lastMonth = RentPayment::where('status', 'completed')
                ->whereBetween('payment_date', [
                    now()->subMonthNoOverflow()->startOfMonth(),
                    now()->subMonthNoOverflow()->endOfMonth(),
                ])
                ->sum('amount_paid');

            $stats['revenue_this_month'] = $thisMonth;
            $stats['revenue_change'] = $lastMonth > 0
                ? round((($thisMonth - $lastMonth) / $lastMonth) * 100)
                : ($thisMonth > 0 ? 100 : 0);
        }

        if ($user->can('view complaints')) {
            $stats['open_complaints'] = Complaint::whereIn('status', ['open', 'in_progress'])->count();
        }

        if ($user->can('view maintenance requests')) {
            $stats['pending_maintenance'] = MaintenanceRequest::whereIn('status', ['scheduled', 'in_progress'])->count();
        }

        if ($user->can('view utilities')) {
            $stats['unpaid_bills'] = UtilityBill::where('status', 'unpaid')->count();
        }

        $recentProperties = $user->can('view properties')
            ? Property::with('owner')->withCount('rooms')->latest()->take(5)->get()
            : null;

        $expiringRentals = $user->can('view rentals')
            ? Rental::with(['tenant', 'room.property'])
                ->where('lease_status', 'active')
                ->whereNotNull('end_date')
                ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(self::EXPIRING_SOON_DAYS)])
                ->orderBy('end_date')
                ->get()
            : null;

        // Self-registered accounts (via /register) start with no role at all,
        // so they can't do anything until an admin assigns one — surface them
        // here so that step doesn't get missed.
        $unassignedUsers = $user->can('manage users')
            ? User::doesntHave('roles')->latest()->take(5)->get()
            : null;

        return view('dashboard', compact('stats', 'recentProperties', 'expiringRentals', 'unassignedUsers'));
    }
}
