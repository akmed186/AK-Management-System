<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $role = $request->string('role')->trim()->toString();

        $users = $this->filteredQuery($search, $status, $role)
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'search', 'status', 'role'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $roleFilter = $request->string('role')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $users = $this->filteredQuery($search, $status, $roleFilter)->get();

        $headers = ['Name', 'Email', 'Role', 'Status'];
        $rows = $users->map(fn (User $user) => [
            $user->name,
            $user->email,
            $user->roles->map(fn ($role) => Str::headline($role->name))->join(', ') ?: '—',
            $user->is_active ? 'Active' : 'Inactive',
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('users.pdf', 'Users', $headers, $rows)
            : Exporter::csv('users.csv', $headers, $rows);
    }

    private function filteredQuery(string $search, string $status, string $roleFilter = '')
    {
        return User::with('roles')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('roles', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($roleFilter === 'unassigned', fn ($query) => $query->doesntHave('roles'))
            ->latest();
    }

    public function show(User $user): View
    {
        $activities = $user->causedActivities()->paginate(15);

        return view('users.show', compact('user', 'activities'));
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        $user->syncRoles([$validated['role']]);

        Activity::log('updated', $user, "Updated user \"{$user->name}\" (role: ".Str::headline($validated['role']).')');

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function activate(User $user): RedirectResponse
    {
        $user->update(['is_active' => true]);

        Activity::log('activated', $user, "Activated user \"{$user->name}\"");

        return redirect()->route('users.index')->with('status', 'User activated.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('status', "You can't deactivate your own account.");
        }

        $user->update(['is_active' => false]);

        Activity::log('deactivated', $user, "Deactivated user \"{$user->name}\"");

        return redirect()->route('users.index')->with('status', 'User deactivated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('status', "You can't delete your own account.");
        }

        $name = $user->name;

        // Every record this user touched (activity logs, expenses they
        // recorded, maintenance requests assigned to them, tenant profile
        // link, complaint comments) is set up to survive their deletion with
        // just the reference nulled out — see the FKs on those tables.
        $user->syncRoles([]);
        $user->delete();

        Activity::log('deleted', null, "Deleted user \"{$name}\"");

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }

    public function bulkDeactivate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        $ids = collect($validated['ids'])->reject(fn ($id) => $id === auth()->id());
        $names = User::whereIn('id', $ids)->pluck('name');
        User::whereIn('id', $ids)->update(['is_active' => false]);

        Activity::log('deactivated', null, 'Bulk deactivated '.$names->count().' users', ['names' => $names->all()]);

        return redirect()->route('users.index')->with('status', 'Users deactivated.');
    }
}
