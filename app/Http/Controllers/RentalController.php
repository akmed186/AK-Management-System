<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Rental;
use App\Models\Room;
use App\Models\Tenant;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentalController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $rentals = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('rentals.index', compact('rentals', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $rentals = $this->filteredQuery($search)->get();

        $headers = ['Tenant', 'Room', 'Start', 'End', 'Monthly Rent', 'Status'];
        $rows = $rentals->map(fn (Rental $rental) => [
            $rental->tenant->full_name,
            "{$rental->room->property->property_name} — {$rental->room->room_number}",
            $rental->start_date->format('M j, Y'),
            $rental->end_date?->format('M j, Y') ?? 'Ongoing',
            number_format($rental->monthly_rent, 2),
            ucfirst($rental->lease_status),
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('rentals.pdf', 'Rentals', $headers, $rows)
            : Exporter::csv('rentals.csv', $headers, $rows);
    }

    private function filteredQuery(string $search)
    {
        return Rental::with(['tenant', 'room.property'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('tenant', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })->orWhereHas('room', function ($query) use ($search) {
                        $query->where('room_number', 'like', "%{$search}%");
                    });
                });
            })
            ->latest();
    }

    public function create(): View
    {
        $tenants = Tenant::orderBy('first_name')->get();
        $rooms = Room::with('property')->orderBy('room_number')->get();

        return view('rentals.create', compact('tenants', 'rooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'lease_status' => ['required', 'in:active,expired,terminated'],
        ]);

        $rental = Rental::create($validated);

        $this->syncRoomStatus($rental->room);

        Activity::log('created', $rental, "Created rental for \"{$rental->tenant->full_name}\"");

        return redirect()->route('rentals.index')->with('status', 'Rental created.');
    }

    public function edit(Rental $rental): View
    {
        $tenants = Tenant::orderBy('first_name')->get();
        $rooms = Room::with('property')->orderBy('room_number')->get();

        return view('rentals.edit', compact('rental', 'tenants', 'rooms'));
    }

    public function update(Request $request, Rental $rental): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'lease_status' => ['required', 'in:active,expired,terminated'],
        ]);

        $previousRoom = $rental->room;

        $rental->update($validated);

        $this->syncRoomStatus($previousRoom->fresh());
        $this->syncRoomStatus($rental->room()->first());

        Activity::log('updated', $rental, "Updated rental for \"{$rental->tenant->full_name}\"");

        return redirect()->route('rentals.index')->with('status', 'Rental updated.');
    }

    public function destroy(Rental $rental): RedirectResponse
    {
        $room = $rental->room;

        Activity::log('deleted', null, "Deleted rental for \"{$rental->tenant->full_name}\"");

        $rental->delete();

        $this->syncRoomStatus($room);

        return redirect()->route('rentals.index')->with('status', 'Rental deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:rentals,id'],
        ]);

        $rentals = Rental::with('room')->whereIn('id', $validated['ids'])->get();
        $rooms = $rentals->pluck('room')->unique('id');

        Activity::log('deleted', null, 'Bulk deleted '.$rentals->count().' rentals');

        Rental::whereIn('id', $validated['ids'])->delete();

        $rooms->each(fn ($room) => $this->syncRoomStatus($room->fresh()));

        return redirect()->route('rentals.index')->with('status', 'Rentals deleted.');
    }

    /**
     * Keep a room's status consistent with whether it has an active lease.
     * Rooms explicitly marked "maintenance" are left alone when vacating.
     */
    private function syncRoomStatus(Room $room): void
    {
        $hasActiveLease = Rental::where('room_id', $room->id)->where('lease_status', 'active')->exists();

        if ($hasActiveLease) {
            $room->update(['status' => 'occupied']);
        } elseif ($room->status === 'occupied') {
            $room->update(['status' => 'vacant']);
        }
    }
}
