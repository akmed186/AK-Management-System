<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Owner;
use App\Models\Property;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $properties = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('properties.index', compact('properties', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $properties = $this->filteredQuery($search)->get();

        $headers = ['Property', 'Address', 'City / State', 'Owner', 'Type', 'Rooms'];
        $rows = $properties->map(fn (Property $property) => [
            $property->property_name,
            $property->address,
            "{$property->city}, {$property->state} {$property->zip_code}",
            $property->owner->name,
            $property->property_type,
            $property->rooms_count,
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('properties.pdf', 'Properties', $headers, $rows)
            : Exporter::csv('properties.csv', $headers, $rows);
    }

    private function filteredQuery(string $search)
    {
        return Property::with('owner')->withCount('rooms')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('property_name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('zip_code', 'like', "%{$search}%")
                        ->orWhere('property_type', 'like', "%{$search}%")
                        ->orWhereHas('owner', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest();
    }

    public function create(): View
    {
        $owners = Owner::orderBy('name')->get();

        return view('properties.create', compact('owners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'owner_id' => ['required', 'exists:owners,id'],
            'property_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:20'],
            'property_type' => ['required', 'string', 'max:255'],
        ]);

        $property = Property::create($validated);

        Activity::log('created', $property, "Created property \"{$property->property_name}\"");

        return redirect()->route('properties.index')->with('status', 'Property created.');
    }

    public function edit(Property $property): View
    {
        $owners = Owner::orderBy('name')->get();

        return view('properties.edit', compact('property', 'owners'));
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $validated = $request->validate([
            'owner_id' => ['required', 'exists:owners,id'],
            'property_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:20'],
            'property_type' => ['required', 'string', 'max:255'],
        ]);

        $property->update($validated);

        Activity::log('updated', $property, "Updated property \"{$property->property_name}\"");

        return redirect()->route('properties.index')->with('status', 'Property updated.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        Activity::log('deleted', null, "Deleted property \"{$property->property_name}\"");

        $property->delete();

        return redirect()->route('properties.index')->with('status', 'Property deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:properties,id'],
        ]);

        $names = Property::whereIn('id', $validated['ids'])->pluck('property_name');
        Activity::log('deleted', null, 'Bulk deleted '.$names->count().' properties', ['names' => $names->all()]);

        Property::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('properties.index')->with('status', 'Properties deleted.');
    }
}
