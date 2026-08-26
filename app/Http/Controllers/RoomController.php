<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Amenity;
use App\Models\Property;
use App\Models\Room;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $rooms = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('rooms.index', compact('rooms', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $rooms = $this->filteredQuery($search)->get();

        $headers = ['Room', 'Property', 'Floor', 'Base Rent', 'Status', 'Amenities'];
        $rows = $rooms->map(fn (Room $room) => [
            $room->room_number,
            $room->property->property_name,
            $room->floor ?: '—',
            number_format($room->base_rent_amount, 2),
            ucfirst($room->status),
            $room->amenities->pluck('name')->join(', ') ?: '—',
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('rooms.pdf', 'Rooms', $headers, $rows)
            : Exporter::csv('rooms.csv', $headers, $rows);
    }

    private function filteredQuery(string $search)
    {
        return Room::with('property', 'amenities')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('room_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('property', function ($query) use ($search) {
                            $query->where('property_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest();
    }

    public function create(): View
    {
        $properties = Property::orderBy('property_name')->get();
        $amenities = Amenity::orderBy('name')->get();

        return view('rooms.create', compact('properties', 'amenities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'room_number' => ['required', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:50'],
            'size_sqft' => ['nullable', 'numeric', 'min:0'],
            'base_rent_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:vacant,occupied,maintenance'],
            'amenities' => ['array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
        ]);

        $amenityIds = $validated['amenities'] ?? [];
        unset($validated['amenities']);

        $room = Room::create($validated);
        $room->amenities()->sync($amenityIds);

        Activity::log('created', $room, "Created room \"{$room->room_number}\"");

        return redirect()->route('rooms.index')->with('status', 'Room created.');
    }

    public function edit(Room $room): View
    {
        $properties = Property::orderBy('property_name')->get();
        $amenities = Amenity::orderBy('name')->get();
        $room->load('amenities');

        return view('rooms.edit', compact('room', 'properties', 'amenities'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'room_number' => ['required', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:50'],
            'size_sqft' => ['nullable', 'numeric', 'min:0'],
            'base_rent_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:vacant,occupied,maintenance'],
            'amenities' => ['array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
        ]);

        $amenityIds = $validated['amenities'] ?? [];
        unset($validated['amenities']);

        $room->update($validated);
        $room->amenities()->sync($amenityIds);

        Activity::log('updated', $room, "Updated room \"{$room->room_number}\"");

        return redirect()->route('rooms.index')->with('status', 'Room updated.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        Activity::log('deleted', null, "Deleted room \"{$room->room_number}\"");

        $room->delete();

        return redirect()->route('rooms.index')->with('status', 'Room deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:rooms,id'],
        ]);

        $numbers = Room::whereIn('id', $validated['ids'])->pluck('room_number');
        Activity::log('deleted', null, 'Bulk deleted '.$numbers->count().' rooms', ['numbers' => $numbers->all()]);

        Room::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('rooms.index')->with('status', 'Rooms deleted.');
    }
}
