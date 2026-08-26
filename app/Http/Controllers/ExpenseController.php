<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Expense;
use App\Models\Property;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $expenses = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('expenses.index', compact('expenses', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $expenses = $this->filteredQuery($search)->get();

        $headers = ['Property', 'Category', 'Amount', 'Date', 'Recorded By'];
        $rows = $expenses->map(fn (Expense $expense) => [
            $expense->property->property_name,
            $expense->category,
            number_format($expense->amount, 2),
            $expense->expense_date->format('M j, Y'),
            $expense->recordedBy?->name ?? '—',
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('expenses.pdf', 'Expenses', $headers, $rows)
            : Exporter::csv('expenses.csv', $headers, $rows);
    }

    private function filteredQuery(string $search)
    {
        return Expense::with(['property', 'recordedBy'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('property', function ($query) use ($search) {
                            $query->where('property_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('expense_date');
    }

    public function create(): View
    {
        $properties = Property::orderBy('property_name')->get();

        return view('expenses.create', compact('properties'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['recorded_by_user_id'] = auth()->id();

        $expense = Expense::create($validated);

        Activity::log('created', $expense, "Recorded expense \"{$expense->category}\"");

        return redirect()->route('expenses.index')->with('status', 'Expense recorded.');
    }

    public function edit(Expense $expense): View
    {
        $properties = Property::orderBy('property_name')->get();

        return view('expenses.edit', compact('expense', 'properties'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $expense->update($validated);

        Activity::log('updated', $expense, "Updated expense \"{$expense->category}\"");

        return redirect()->route('expenses.index')->with('status', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        Activity::log('deleted', null, "Deleted expense \"{$expense->category}\"");

        $expense->delete();

        return redirect()->route('expenses.index')->with('status', 'Expense deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:expenses,id'],
        ]);

        Activity::log('deleted', null, 'Bulk deleted '.count($validated['ids']).' expenses');

        Expense::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('expenses.index')->with('status', 'Expenses deleted.');
    }
}
