<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Rental;
use App\Models\RentPayment;
use App\Models\Room;
use App\Models\Tenant;
use App\Support\Exporter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Each report reads live data instead of a fixed date range, except
     * financial/tenant/maintenance which respect the from/to filter below.
     */
    private const REPORTS = [
        'occupancy' => ['name' => 'Occupancy Report', 'description' => 'Every room with its current status and base rent.'],
        'financial' => ['name' => 'Financial Summary', 'description' => 'Rent payments received and expenses recorded in the selected range.'],
        'tenants' => ['name' => 'Tenant Report', 'description' => 'Tenants with their current unit and lease status.'],
        'maintenance' => ['name' => 'Maintenance Overview', 'description' => 'Maintenance requests filed in the selected range.'],
    ];

    /**
     * How many trailing months the financial trend chart covers.
     */
    private const TREND_MONTHS = 6;

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = isset($validated['date_from']) ? Carbon::parse($validated['date_from'])->startOfDay() : null;
        $dateTo = isset($validated['date_to']) ? Carbon::parse($validated['date_to'])->endOfDay() : null;

        $income = RentPayment::where('status', 'completed')
            ->when($dateFrom, fn ($q) => $q->where('payment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('payment_date', '<=', $dateTo))
            ->sum('amount_paid');

        $expenses = Expense::when($dateFrom, fn ($q) => $q->where('expense_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('expense_date', '<=', $dateTo))
            ->sum('amount');

        $stats = [
            'income' => $income,
            'expenses' => $expenses,
            'net' => $income - $expenses,
        ];

        return view('reports.index', [
            'stats' => $stats,
            'reports' => self::REPORTS,
            'dateFrom' => $validated['date_from'] ?? '',
            'dateTo' => $validated['date_to'] ?? '',
            'occupancy' => $this->occupancySummary(),
            'financial' => $this->financialSummary($dateFrom, $dateTo),
            'tenantSummary' => $this->tenantSummary($dateFrom, $dateTo),
            'maintenanceSummary' => $this->maintenanceSummary($dateFrom, $dateTo),
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:'.implode(',', array_keys(self::REPORTS))],
            'format' => ['nullable', 'in:csv,pdf'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $format = $validated['format'] ?? 'csv';
        $dateFrom = isset($validated['date_from']) ? Carbon::parse($validated['date_from'])->startOfDay() : null;
        $dateTo = isset($validated['date_to']) ? Carbon::parse($validated['date_to'])->endOfDay() : null;

        [$headers, $rows] = match ($validated['type']) {
            'occupancy' => $this->occupancyRows(),
            'financial' => $this->financialRows($dateFrom, $dateTo),
            'tenants' => $this->tenantsRows($dateFrom, $dateTo),
            'maintenance' => $this->maintenanceRows($dateFrom, $dateTo),
        };

        $title = self::REPORTS[$validated['type']]['name'];
        $filename = $validated['type'];

        return $format === 'pdf'
            ? Exporter::pdf("{$filename}.pdf", $title, $headers, $rows)
            : Exporter::csv("{$filename}.csv", $headers, $rows);
    }

    /**
     * Room-status breakdown for the whole portfolio, plus a per-property
     * table — the per-property occupancy rate isn't shown anywhere else in
     * the app today.
     */
    private function occupancySummary(): array
    {
        $rooms = Room::count();
        $occupied = Room::where('status', 'occupied')->count();
        $vacant = Room::where('status', 'vacant')->count();
        $maintenance = Room::where('status', 'maintenance')->count();

        $byProperty = Property::withCount([
            'rooms',
            'rooms as occupied_rooms_count' => fn ($q) => $q->where('status', 'occupied'),
            'rooms as vacant_rooms_count' => fn ($q) => $q->where('status', 'vacant'),
            'rooms as maintenance_rooms_count' => fn ($q) => $q->where('status', 'maintenance'),
        ])
            ->having('rooms_count', '>', 0)
            ->orderByDesc('rooms_count')
            ->get()
            ->map(fn (Property $property) => [
                'name' => $property->property_name,
                'total' => $property->rooms_count,
                'occupied' => $property->occupied_rooms_count,
                'vacant' => $property->vacant_rooms_count,
                'maintenance' => $property->maintenance_rooms_count,
                'rate' => $property->rooms_count > 0 ? round($property->occupied_rooms_count / $property->rooms_count * 100) : 0,
            ]);

        return [
            'total' => $rooms,
            'occupied' => $occupied,
            'vacant' => $vacant,
            'maintenance' => $maintenance,
            'rate' => $rooms > 0 ? round($occupied / $rooms * 100) : 0,
            'avg_rent' => Room::avg('base_rent_amount'),
            'by_property' => $byProperty,
        ];
    }

    /**
     * Income by method / expenses by category, plus a trailing-months
     * income-vs-expense trend independent of the from/to filter.
     */
    private function financialSummary(?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $byMethod = RentPayment::where('status', 'completed')
            ->when($dateFrom, fn ($q) => $q->where('payment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('payment_date', '<=', $dateTo))
            ->selectRaw('payment_method, sum(amount_paid) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        $byCategory = Expense::when($dateFrom, fn ($q) => $q->where('expense_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('expense_date', '<=', $dateTo))
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $trend = collect(range(self::TREND_MONTHS - 1, 0))->map(function (int $monthsAgo) {
            $month = Carbon::today()->subMonthsNoOverflow($monthsAgo);

            $income = RentPayment::where('status', 'completed')
                ->whereBetween('payment_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount_paid');

            $expenses = Expense::whereBetween('expense_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');

            return [
                'label' => $month->format('M'),
                'income' => (float) $income,
                'expenses' => (float) $expenses,
            ];
        });

        return [
            'by_method' => $byMethod,
            'by_category' => $byCategory,
            'trend' => $trend,
            'trend_max' => max($trend->flatMap(fn ($m) => [$m['income'], $m['expenses']])->max(), 1),
        ];
    }

    private function tenantSummary(?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $tenants = Tenant::with('currentRental')
            ->when($dateFrom, fn ($q) => $q->whereHas('rentals', fn ($r) => $r->where('start_date', '>=', $dateFrom)))
            ->when($dateTo, fn ($q) => $q->whereHas('rentals', fn ($r) => $r->where('start_date', '<=', $dateTo)))
            ->get();

        $byLeaseStatus = $tenants->map(fn (Tenant $tenant) => $tenant->currentRental?->lease_status ?? 'unassigned')
            ->countBy();

        return [
            'total' => $tenants->count(),
            'with_unit' => $tenants->filter(fn (Tenant $tenant) => $tenant->currentRental !== null)->count(),
            'unassigned' => $tenants->filter(fn (Tenant $tenant) => $tenant->currentRental === null)->count(),
            'by_lease_status' => $byLeaseStatus,
        ];
    }

    private function maintenanceSummary(?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $requests = MaintenanceRequest::when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('created_at', '<=', $dateTo))
            ->get();

        return [
            'total' => $requests->count(),
            'by_status' => $requests->countBy('status'),
            'total_cost' => $requests->sum('cost'),
            'avg_cost' => $requests->whereNotNull('cost')->count() > 0
                ? $requests->whereNotNull('cost')->avg('cost')
                : 0,
        ];
    }

    private function occupancyRows(): array
    {
        $rooms = Room::with('property')->orderBy('property_id')->get();

        $headers = ['Property', 'Room', 'Floor', 'Base Rent', 'Status'];
        $rows = $rooms->map(fn (Room $room) => [
            $room->property->property_name,
            $room->room_number,
            $room->floor ?: '—',
            number_format($room->base_rent_amount, 2),
            ucfirst($room->status),
        ]);

        return [$headers, $rows];
    }

    private function financialRows(?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $payments = RentPayment::with('rental.tenant')
            ->where('status', 'completed')
            ->when($dateFrom, fn ($q) => $q->where('payment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('payment_date', '<=', $dateTo))
            ->get()
            ->map(fn (RentPayment $payment) => [
                $payment->payment_date->format('Y-m-d'),
                'Income',
                'Rent — '.$payment->rental->tenant->full_name,
                number_format($payment->amount_paid, 2),
            ]);

        $expenses = Expense::with('property')
            ->when($dateFrom, fn ($q) => $q->where('expense_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('expense_date', '<=', $dateTo))
            ->get()
            ->map(fn (Expense $expense) => [
                $expense->expense_date->format('Y-m-d'),
                'Expense',
                $expense->category.' — '.$expense->property->property_name,
                number_format($expense->amount, 2),
            ]);

        $rows = $payments->concat($expenses)->sortBy(0)->values();

        return [['Date', 'Type', 'Description', 'Amount'], $rows];
    }

    private function tenantsRows(?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $tenants = Tenant::with(['currentRental.room.property'])
            ->when($dateFrom, fn ($q) => $q->whereHas('rentals', fn ($r) => $r->where('start_date', '>=', $dateFrom)))
            ->when($dateTo, fn ($q) => $q->whereHas('rentals', fn ($r) => $r->where('start_date', '<=', $dateTo)))
            ->orderBy('first_name')
            ->get();

        $headers = ['Tenant', 'Email', 'Phone', 'Unit', 'Lease Status'];
        $rows = $tenants->map(fn (Tenant $tenant) => [
            $tenant->full_name,
            $tenant->email ?: '—',
            $tenant->phone_number ?: '—',
            $tenant->currentRental
                ? $tenant->currentRental->room->property->property_name.' — '.$tenant->currentRental->room->room_number
                : 'Unassigned',
            $tenant->currentRental ? ucfirst($tenant->currentRental->lease_status) : '—',
        ]);

        return [$headers, $rows];
    }

    private function maintenanceRows(?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $requests = MaintenanceRequest::with(['property', 'room', 'assignedTo'])
            ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('created_at', '<=', $dateTo))
            ->orderBy('created_at')
            ->get();

        $headers = ['Property', 'Room', 'Assigned To', 'Cost', 'Status', 'Filed'];
        $rows = $requests->map(fn (MaintenanceRequest $request) => [
            $request->property->property_name,
            $request->room?->room_number ?? '—',
            $request->assignedTo?->name ?? 'Unassigned',
            $request->cost !== null ? number_format($request->cost, 2) : '—',
            Str::headline($request->status),
            $request->created_at->format('Y-m-d'),
        ]);

        return [$headers, $rows];
    }
}
