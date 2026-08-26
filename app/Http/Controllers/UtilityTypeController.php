<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\UtilityType;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UtilityTypeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $utilityTypes = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('utility-types.index', compact('utilityTypes', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $utilityTypes = $this->filteredQuery($search)->get();

        $headers = ['Name', 'Unit of Measure', 'Rate / Unit', 'Bills'];
        $rows = $utilityTypes->map(fn (UtilityType $utilityType) => [
            $utilityType->utility_name,
            $utilityType->unit_of_measure,
            number_format($utilityType->rate_per_unit, 4),
            $utilityType->utility_bills_count,
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('utility-types.pdf', 'Utility Types', $headers, $rows)
            : Exporter::csv('utility-types.csv', $headers, $rows);
    }

    private function filteredQuery(string $search)
    {
        return UtilityType::withCount('utilityBills')
            ->when($search, function ($query, $search) {
                $query->where('utility_name', 'like', "%{$search}%");
            })
            ->orderBy('utility_name');
    }

    public function create(): View
    {
        return view('utility-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'utility_name' => ['required', 'string', 'max:255', 'unique:utility_types,utility_name'],
            'unit_of_measure' => ['required', 'string', 'max:255'],
            'rate_per_unit' => ['required', 'numeric', 'min:0'],
        ]);

        $utilityType = UtilityType::create($validated);

        Activity::log('created', $utilityType, "Created utility type \"{$utilityType->utility_name}\"");

        return redirect()->route('utility-types.index')->with('status', 'Utility type created.');
    }

    public function edit(UtilityType $utilityType): View
    {
        return view('utility-types.edit', compact('utilityType'));
    }

    public function update(Request $request, UtilityType $utilityType): RedirectResponse
    {
        $validated = $request->validate([
            'utility_name' => ['required', 'string', 'max:255', 'unique:utility_types,utility_name,'.$utilityType->id],
            'unit_of_measure' => ['required', 'string', 'max:255'],
            'rate_per_unit' => ['required', 'numeric', 'min:0'],
        ]);

        $utilityType->update($validated);

        Activity::log('updated', $utilityType, "Updated utility type \"{$utilityType->utility_name}\"");

        return redirect()->route('utility-types.index')->with('status', 'Utility type updated.');
    }

    public function destroy(UtilityType $utilityType): RedirectResponse
    {
        Activity::log('deleted', null, "Deleted utility type \"{$utilityType->utility_name}\"");

        $utilityType->delete();

        return redirect()->route('utility-types.index')->with('status', 'Utility type deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:utility_types,id'],
        ]);

        Activity::log('deleted', null, 'Bulk deleted '.count($validated['ids']).' utility types');

        UtilityType::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('utility-types.index')->with('status', 'Utility types deleted.');
    }
}
