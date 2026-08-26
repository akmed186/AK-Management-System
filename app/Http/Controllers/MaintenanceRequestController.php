<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Complaint;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Notifications\ComplaintStatusUpdated;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MaintenanceRequestController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $maintenanceRequests = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('maintenance-requests.index', compact('maintenanceRequests', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $maintenanceRequests = $this->filteredQuery($search)->get();

        $headers = ['Property', 'Room', 'Assigned To', 'Cost', 'Status'];
        $rows = $maintenanceRequests->map(fn (MaintenanceRequest $request) => [
            $request->property->property_name,
            $request->room?->room_number ?? '—',
            $request->assignedTo?->name ?? 'Unassigned',
            $request->cost !== null ? number_format($request->cost, 2) : '—',
            Str::headline($request->status),
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('maintenance-requests.pdf', 'Maintenance Requests', $headers, $rows)
            : Exporter::csv('maintenance-requests.csv', $headers, $rows);
    }

    private function filteredQuery(string $search)
    {
        return MaintenanceRequest::with(['property', 'room', 'assignedTo'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('status', 'like', "%{$search}%")
                        ->orWhereHas('property', function ($query) use ($search) {
                            $query->where('property_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest();
    }

    public function create(): View
    {
        return view('maintenance-requests.create', [
            'properties' => Property::orderBy('property_name')->get(),
            'rooms' => Room::with('property')->orderBy('room_number')->get(),
            'complaints' => Complaint::orderBy('title')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'complaint_id' => ['nullable', 'exists:complaints,id'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:scheduled,in_progress,completed'],
            'scheduled_date' => ['nullable', 'date'],
            'completed_date' => ['nullable', 'date'],
        ]);

        $maintenanceRequest = MaintenanceRequest::create($validated);

        Activity::log('created', $maintenanceRequest, 'Created maintenance request for "'.$maintenanceRequest->property->property_name.'"');

        return redirect()->route('maintenance-requests.index')->with('status', 'Maintenance request created.');
    }

    public function edit(MaintenanceRequest $maintenanceRequest): View
    {
        return view('maintenance-requests.edit', [
            'maintenanceRequest' => $maintenanceRequest,
            'properties' => Property::orderBy('property_name')->get(),
            'rooms' => Room::with('property')->orderBy('room_number')->get(),
            'complaints' => Complaint::orderBy('title')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'complaint_id' => ['nullable', 'exists:complaints,id'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:scheduled,in_progress,completed'],
            'scheduled_date' => ['nullable', 'date'],
            'completed_date' => ['nullable', 'date'],
        ]);

        $maintenanceRequest->update($validated);

        Activity::log('updated', $maintenanceRequest, 'Updated maintenance request for "'.$maintenanceRequest->property->property_name.'"');

        // Mirror of the sync in ComplaintController@update: once every work
        // order tied to a complaint is done, the complaint itself should
        // flip to resolved instead of sitting open with nothing left to do.
        if ($maintenanceRequest->wasChanged('status') && $maintenanceRequest->status === 'completed' && $maintenanceRequest->complaint) {
            $complaint = $maintenanceRequest->complaint;
            $stillOpen = $complaint->maintenanceRequests()->where('status', '!=', 'completed')->exists();

            if (! $stillOpen && ! in_array($complaint->status, ['resolved', 'closed'])) {
                $complaint->update(['status' => 'resolved']);

                if ($complaint->tenant->user) {
                    try {
                        $complaint->tenant->user->notify(new ComplaintStatusUpdated($complaint));
                    } catch (\Throwable $e) {
                        Log::warning('Failed to notify tenant of complaint status change', ['complaint_id' => $complaint->id, 'error' => $e->getMessage()]);
                    }
                }
            }
        }

        return redirect()->route('maintenance-requests.index')->with('status', 'Maintenance request updated.');
    }

    public function destroy(MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        Activity::log('deleted', null, 'Deleted maintenance request for "'.$maintenanceRequest->property->property_name.'"');

        $maintenanceRequest->delete();

        return redirect()->route('maintenance-requests.index')->with('status', 'Maintenance request deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:maintenance_requests,id'],
        ]);

        Activity::log('deleted', null, 'Bulk deleted '.count($validated['ids']).' maintenance requests');

        MaintenanceRequest::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('maintenance-requests.index')->with('status', 'Maintenance requests deleted.');
    }
}
