<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Support\Exporter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = isset($validated['date_from']) ? Carbon::parse($validated['date_from'])->startOfDay() : null;
        $dateTo = isset($validated['date_to']) ? Carbon::parse($validated['date_to'])->endOfDay() : null;

        $activities = $this->filteredQuery($search, $dateFrom, $dateTo)
            ->paginate(20)
            ->withQueryString();

        return view('activity-logs.index', [
            'activities' => $activities,
            'search' => $search,
            'dateFrom' => $validated['date_from'] ?? '',
            'dateTo' => $validated['date_to'] ?? '',
        ]);
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = isset($validated['date_from']) ? Carbon::parse($validated['date_from'])->startOfDay() : null;
        $dateTo = isset($validated['date_to']) ? Carbon::parse($validated['date_to'])->endOfDay() : null;

        $activities = $this->filteredQuery($search, $dateFrom, $dateTo)->get();

        $headers = ['User', 'Action', 'Description', 'Date'];
        $rows = $activities->map(fn (Activity $activity) => [
            $activity->causer->name ?? 'System',
            Str::headline($activity->action),
            $activity->description,
            $activity->created_at->format('M j, Y g:ia'),
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('activity-logs.pdf', 'Activity Logs', $headers, $rows)
            : Exporter::csv('activity-logs.csv', $headers, $rows);
    }

    private function filteredQuery(string $search, ?Carbon $dateFrom, ?Carbon $dateTo)
    {
        return Activity::with('causer')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhereHas('causer', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($dateFrom, fn ($query) => $query->where('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->where('created_at', '<=', $dateTo))
            ->latest();
    }
}
