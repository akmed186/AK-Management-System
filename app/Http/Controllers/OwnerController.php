<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Owner;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $owners = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('owners.index', compact('owners', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $owners = $this->filteredQuery($search)->get();

        $headers = ['Name', 'Company', 'Email', 'Phone', 'Properties'];
        $rows = $owners->map(fn (Owner $owner) => [
            $owner->name,
            $owner->company_name ?: '—',
            $owner->email,
            $owner->phone ?: '—',
            $owner->properties_count,
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('owners.pdf', 'Owners', $headers, $rows)
            : Exporter::csv('owners.csv', $headers, $rows);
    }

    private function filteredQuery(string $search)
    {
        return Owner::withCount('properties')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->latest();
    }

    public function create(): View
    {
        return view('owners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:owners,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_identification_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $owner = Owner::create($validated);

        Activity::log('created', $owner, "Created owner \"{$owner->name}\"");

        return redirect()->route('owners.index')->with('status', 'Owner created.');
    }

    public function edit(Owner $owner): View
    {
        return view('owners.edit', compact('owner'));
    }

    public function update(Request $request, Owner $owner): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:owners,email,'.$owner->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_identification_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $owner->update($validated);

        Activity::log('updated', $owner, "Updated owner \"{$owner->name}\"");

        return redirect()->route('owners.index')->with('status', 'Owner updated.');
    }

    public function destroy(Owner $owner): RedirectResponse
    {
        Activity::log('deleted', null, "Deleted owner \"{$owner->name}\"");

        $owner->delete();

        return redirect()->route('owners.index')->with('status', 'Owner deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:owners,id'],
        ]);

        $names = Owner::whereIn('id', $validated['ids'])->pluck('name');
        Activity::log('deleted', null, 'Bulk deleted '.$names->count().' owners', ['names' => $names->all()]);

        Owner::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('owners.index')->with('status', 'Owners deleted.');
    }
}
