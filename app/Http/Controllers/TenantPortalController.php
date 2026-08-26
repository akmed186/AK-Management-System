<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Complaint;
use App\Models\MaintenanceRequest;
use App\Models\Rental;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UtilityBill;
use App\Notifications\ComplaintFiled;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class TenantPortalController extends Controller
{
    public function index(): View
    {
        $tenant = Auth::user()->tenant;

        if (! $tenant) {
            return view('portal.no-profile');
        }

        $tenant->load('currentRental.room.property', 'currentRental.room.amenities');

        $recentPayments = RentPayment::whereHas('rental', fn ($query) => $query->where('tenant_id', $tenant->id))
            ->with('rental')
            ->latest('payment_date')
            ->take(5)
            ->get();

        $openComplaints = Complaint::where('tenant_id', $tenant->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $pendingMaintenance = MaintenanceRequest::whereHas('complaint', fn ($query) => $query->where('tenant_id', $tenant->id))
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->count();

        $unpaidBills = $this->billsQuery($tenant)->where('status', 'unpaid')->get();

        return view('portal.dashboard', [
            'tenant' => $tenant,
            'recentPayments' => $recentPayments,
            'openComplaints' => $openComplaints,
            'pendingMaintenance' => $pendingMaintenance,
            'unpaidBills' => $unpaidBills,
        ]);
    }

    public function payments(): View
    {
        $tenant = Auth::user()->tenant;

        if (! $tenant) {
            return view('portal.no-profile');
        }

        $payments = $this->paymentsQuery($tenant)->paginate(15);

        return view('portal.payments.index', compact('payments'));
    }

    public function exportPayments(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (! $tenant) {
            return view('portal.no-profile');
        }

        $format = $request->string('format')->toString() ?: 'csv';

        $payments = $this->paymentsQuery($tenant)->get();

        $headers = ['Unit', 'Amount', 'Date', 'Method', 'Status', 'Reference'];
        $rows = $payments->map(fn (RentPayment $payment) => [
            "{$payment->rental->room->property->property_name} — {$payment->rental->room->room_number}",
            number_format($payment->amount_paid, 2),
            $payment->payment_date->format('M j, Y'),
            $payment->payment_method,
            ucfirst($payment->status),
            $payment->transaction_reference ?? '—',
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('my-payments.pdf', 'My Payments — '.$tenant->full_name, $headers, $rows)
            : Exporter::csv('my-payments.csv', $headers, $rows);
    }

    private function paymentsQuery(Tenant $tenant)
    {
        return RentPayment::whereHas('rental', fn ($query) => $query->where('tenant_id', $tenant->id))
            ->with('rental.room.property')
            ->latest('payment_date');
    }

    public function bills(): View
    {
        $tenant = Auth::user()->tenant;

        if (! $tenant) {
            return view('portal.no-profile');
        }

        $bills = $this->billsQuery($tenant)->paginate(15);

        return view('portal.bills.index', compact('bills'));
    }

    public function exportBills(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (! $tenant) {
            return view('portal.no-profile');
        }

        $format = $request->string('format')->toString() ?: 'csv';

        $bills = $this->billsQuery($tenant)->get();

        $headers = ['Utility', 'Unit', 'Billing Month', 'Consumption', 'Amount', 'Due', 'Status'];
        $rows = $bills->map(fn (UtilityBill $bill) => [
            $bill->utilityType->utility_name,
            "{$bill->room->property->property_name} — {$bill->room->room_number}",
            $bill->billing_month->format('M Y'),
            "{$bill->consumption_units} {$bill->utilityType->unit_of_measure}",
            number_format($bill->total_amount, 2),
            $bill->due_date->format('M j, Y'),
            ucfirst($bill->status),
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('my-bills.pdf', 'My Bills — '.$tenant->full_name, $headers, $rows)
            : Exporter::csv('my-bills.csv', $headers, $rows);
    }

    /**
     * Bills for every room this tenant has ever rented, not just their
     * current one — mirrors how paymentsQuery() spans all of their rentals.
     */
    private function billsQuery(Tenant $tenant)
    {
        $roomIds = Rental::where('tenant_id', $tenant->id)->pluck('room_id');

        return UtilityBill::whereIn('room_id', $roomIds)
            ->with(['utilityType', 'room.property'])
            ->latest('due_date');
    }

    public function complaints(): View
    {
        $tenant = Auth::user()->tenant;

        if (! $tenant) {
            return view('portal.no-profile');
        }

        $complaints = Complaint::where('tenant_id', $tenant->id)
            ->with(['room.property', 'comments.user'])
            ->latest()
            ->paginate(10);

        return view('portal.complaints.index', compact('complaints'));
    }

    public function createComplaint(): View|RedirectResponse
    {
        $tenant = Auth::user()->tenant;

        if (! $tenant) {
            return view('portal.no-profile');
        }

        $tenant->load('currentRental.room.property');

        if (! $tenant->currentRental) {
            return redirect()->route('portal.complaints.index')
                ->with('status', "You don't have an active rental on file, so a complaint can't be filed. Contact your property manager.");
        }

        return view('portal.complaints.create', compact('tenant'));
    }

    public function storeComplaint(Request $request): RedirectResponse
    {
        $tenant = Auth::user()->tenant;

        abort_if(! $tenant, 403);

        $rental = $tenant->currentRental;

        abort_if(! $rental, 422, "You don't have an active rental on file, so a complaint can't be filed.");

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,emergency'],
        ]);

        $complaint = Complaint::create([
            ...$validated,
            'tenant_id' => $tenant->id,
            'room_id' => $rental->room_id,
            'status' => 'open',
        ]);

        Activity::log('created', $complaint, "Tenant filed complaint \"{$complaint->title}\"");

        try {
            Notification::send(User::permission('edit complaints')->get(), new ComplaintFiled($complaint));
        } catch (\Throwable $e) {
            Log::warning('Failed to notify staff of new complaint', ['complaint_id' => $complaint->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('portal.complaints.index')->with('status', 'Complaint submitted — the property team has been notified.');
    }

    public function maintenanceRequests(): View
    {
        $tenant = Auth::user()->tenant;

        if (! $tenant) {
            return view('portal.no-profile');
        }

        $maintenanceRequests = MaintenanceRequest::whereHas('complaint', fn ($query) => $query->where('tenant_id', $tenant->id))
            ->with(['complaint', 'property', 'room'])
            ->latest()
            ->paginate(10);

        return view('portal.maintenance-requests.index', compact('maintenanceRequests'));
    }
}
