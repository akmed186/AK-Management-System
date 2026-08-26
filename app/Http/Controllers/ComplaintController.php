<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Complaint;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\Tenant;
use App\Notifications\ComplaintStatusUpdated;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $complaints = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('complaints.index', compact('complaints', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $complaints = $this->filteredQuery($search)->get();

        $headers = ['Title', 'Tenant', 'Room', 'Priority', 'Status', 'Comments'];
        $rows = $complaints->map(fn (Complaint $complaint) => [
            $complaint->title,
            $complaint->tenant->full_name,
            "{$complaint->room->property->property_name} — {$complaint->room->room_number}",
            ucfirst($complaint->priority),
            Str::headline($complaint->status),
            $complaint->comments_count,
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('complaints.pdf', 'Complaints', $headers, $rows)
            : Exporter::csv('complaints.csv', $headers, $rows);
    }

    private function filteredQuery(string $search)
    {
        return Complaint::with(['tenant', 'room.property'])->withCount('comments')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('tenant', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest();
    }

    public function create(): View
    {
        return view('complaints.create', [
            'tenants' => Tenant::orderBy('first_name')->get(),
            'rooms' => Room::with('property')->orderBy('room_number')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,emergency'],
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $complaint = Complaint::create($validated);

        Activity::log('created', $complaint, "Logged complaint \"{$complaint->title}\"");

        return redirect()->route('complaints.index')->with('status', 'Complaint logged.');
    }

    public function edit(Complaint $complaint): View
    {
        $complaint->load('comments.user');

        return view('complaints.edit', [
            'complaint' => $complaint,
            'tenants' => Tenant::orderBy('first_name')->get(),
            'rooms' => Room::with('property')->orderBy('room_number')->get(),
        ]);
    }

    public function update(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,emergency'],
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $complaint->update($validated);

        Activity::log('updated', $complaint, "Updated complaint \"{$complaint->title}\"");

        // A resolved/closed complaint shouldn't leave its maintenance work
        // orders looking stuck "in progress" — close those out too so the
        // tenant's view of the request isn't stale on one side.
        if ($complaint->wasChanged('status') && in_array($complaint->status, ['resolved', 'closed'])) {
            $complaint->maintenanceRequests()
                ->where('status', '!=', 'completed')
                ->get()
                ->each(fn (MaintenanceRequest $maintenanceRequest) => $maintenanceRequest->update([
                    'status' => 'completed',
                    'completed_date' => $maintenanceRequest->completed_date ?? now(),
                ]));
        }

        if ($complaint->wasChanged('status') && $complaint->tenant->user) {
            try {
                $complaint->tenant->user->notify(new ComplaintStatusUpdated($complaint));
            } catch (\Throwable $e) {
                Log::warning('Failed to notify tenant of complaint status change', ['complaint_id' => $complaint->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('complaints.index')->with('status', 'Complaint updated.');
    }

    public function destroy(Complaint $complaint): RedirectResponse
    {
        Activity::log('deleted', null, "Deleted complaint \"{$complaint->title}\"");

        $complaint->delete();

        return redirect()->route('complaints.index')->with('status', 'Complaint deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:complaints,id'],
        ]);

        Activity::log('deleted', null, 'Bulk deleted '.count($validated['ids']).' complaints');

        Complaint::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('complaints.index')->with('status', 'Complaints deleted.');
    }
}
