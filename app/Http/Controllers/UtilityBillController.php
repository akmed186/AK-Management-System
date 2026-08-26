<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Room;
use App\Models\UtilityBill;
use App\Models\UtilityType;
use App\Notifications\BillIssued;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UtilityBillController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $utilityBills = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('utility-bills.index', compact('utilityBills', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $utilityBills = $this->filteredQuery($search)->get();

        $headers = ['Room', 'Utility', 'Consumption', 'Amount', 'Due', 'Status'];
        $rows = $utilityBills->map(fn (UtilityBill $bill) => [
            "{$bill->room->property->property_name} — {$bill->room->room_number}",
            $bill->utilityType->utility_name,
            "{$bill->consumption_units} {$bill->utilityType->unit_of_measure}",
            number_format($bill->total_amount, 2),
            $bill->due_date->format('M j, Y'),
            ucfirst($bill->status),
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('utility-bills.pdf', 'Utility Bills', $headers, $rows)
            : Exporter::csv('utility-bills.csv', $headers, $rows);
    }

    private function filteredQuery(string $search)
    {
        return UtilityBill::with(['room.property', 'utilityType'])
            ->when($search, function ($query, $search) {
                $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('room', function ($query) use ($search) {
                        $query->where('room_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('utilityType', function ($query) use ($search) {
                        $query->where('utility_name', 'like', "%{$search}%");
                    });
            })
            ->latest('due_date');
    }

    public function create(): View
    {
        return view('utility-bills.create', [
            'rooms' => Room::with('property')->orderBy('room_number')->get(),
            'utilityTypes' => UtilityType::orderBy('utility_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'utility_type_id' => ['required', 'exists:utility_types,id'],
            'billing_month' => ['required', 'date'],
            'consumption_units' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'status' => ['required', 'in:unpaid,paid'],
        ]);

        $utilityBill = UtilityBill::create($validated);

        Activity::log('created', $utilityBill, 'Created utility bill for "'.$utilityBill->room->room_number.'"');

        $tenant = $utilityBill->room->rentals()->where('lease_status', 'active')->latest()->first()?->tenant;

        if ($tenant?->user) {
            try {
                $tenant->user->notify(new BillIssued($utilityBill));
            } catch (\Throwable $e) {
                Log::warning('Failed to notify tenant of new utility bill', ['bill_id' => $utilityBill->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('utility-bills.index')->with('status', 'Utility bill created.');
    }

    public function edit(UtilityBill $utilityBill): View
    {
        return view('utility-bills.edit', [
            'utilityBill' => $utilityBill,
            'rooms' => Room::with('property')->orderBy('room_number')->get(),
            'utilityTypes' => UtilityType::orderBy('utility_name')->get(),
        ]);
    }

    public function update(Request $request, UtilityBill $utilityBill): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'utility_type_id' => ['required', 'exists:utility_types,id'],
            'billing_month' => ['required', 'date'],
            'consumption_units' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'status' => ['required', 'in:unpaid,paid'],
        ]);

        $utilityBill->update($validated);

        Activity::log('updated', $utilityBill, 'Updated utility bill for "'.$utilityBill->room->room_number.'"');

        return redirect()->route('utility-bills.index')->with('status', 'Utility bill updated.');
    }

    public function destroy(UtilityBill $utilityBill): RedirectResponse
    {
        Activity::log('deleted', null, 'Deleted utility bill for "'.$utilityBill->room->room_number.'"');

        $utilityBill->delete();

        return redirect()->route('utility-bills.index')->with('status', 'Utility bill deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:utility_bills,id'],
        ]);

        Activity::log('deleted', null, 'Bulk deleted '.count($validated['ids']).' utility bills');

        UtilityBill::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('utility-bills.index')->with('status', 'Utility bills deleted.');
    }
}
