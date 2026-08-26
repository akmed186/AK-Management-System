<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $tenants = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('tenants.index', compact('tenants', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $tenants = $this->filteredQuery($search)->get();

        $headers = ['Name', 'Email', 'Phone', 'Current Unit'];
        $rows = $tenants->map(fn (Tenant $tenant) => [
            $tenant->full_name,
            $tenant->email ?: '—',
            $tenant->phone_number ?: '—',
            $tenant->currentRental
                ? "{$tenant->currentRental->room->property->property_name} — {$tenant->currentRental->room->room_number}"
                : 'Unassigned',
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('tenants.pdf', 'Tenants', $headers, $rows)
            : Exporter::csv('tenants.csv', $headers, $rows);
    }

    /**
     * Users eligible to be linked as this tenant's login account: must
     * already hold the "tenant" role (assigned via the Users page) and not
     * already be linked to a different tenant, plus whoever is currently
     * linked (so editing a tenant doesn't drop their existing link from the
     * list even if their role changed since).
     */
    private function linkableUsers(?Tenant $tenant = null)
    {
        return User::role('tenant')
            ->where(function ($query) use ($tenant) {
                $query->whereDoesntHave('tenant');

                if ($tenant?->user_id) {
                    $query->orWhere('id', $tenant->user_id);
                }
            })
            ->orderBy('name')
            ->get();
    }

    private function filteredQuery(string $search)
    {
        return Tenant::with('currentRental.room.property')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->latest();
    }

    public function create(): View
    {
        return view('tenants.create', [
            'users' => $this->linkableUsers(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'user_id' => ['required', 'exists:users,id', Rule::unique('tenants', 'user_id')],
        ]);

        $tenant = Tenant::create($validated);

        Activity::log('created', $tenant, "Created tenant \"{$tenant->full_name}\"");

        return redirect()->route('tenants.index')->with('status', 'Tenant created.');
    }

    public function edit(Tenant $tenant): View
    {
        return view('tenants.edit', [
            'tenant' => $tenant,
            'users' => $this->linkableUsers($tenant),
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('tenants', 'user_id')->ignore($tenant->id)],
        ]);

        $tenant->update($validated);

        Activity::log('updated', $tenant, "Updated tenant \"{$tenant->full_name}\"");

        return redirect()->route('tenants.index')->with('status', 'Tenant updated.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        Activity::log('deleted', null, "Deleted tenant \"{$tenant->full_name}\"");

        $tenant->delete();

        return redirect()->route('tenants.index')->with('status', 'Tenant deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:tenants,id'],
        ]);

        $names = Tenant::whereIn('id', $validated['ids'])->get()->map->full_name;
        Activity::log('deleted', null, 'Bulk deleted '.$names->count().' tenants', ['names' => $names->all()]);

        Tenant::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('tenants.index')->with('status', 'Tenants deleted.');
    }
}
