<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Rental;
use App\Models\RentPayment;
use App\Notifications\PaymentRecorded;
use App\Support\Exporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RentPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $payments = $this->filteredQuery($search)
            ->paginate(10)
            ->withQueryString();

        return view('payments.index', compact('payments', 'search'));
    }

    public function export(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $format = $request->string('format')->toString() ?: 'csv';

        $payments = $this->filteredQuery($search)->get();

        $headers = ['Tenant', 'Room', 'Amount', 'Date', 'Method', 'Status'];
        $rows = $payments->map(fn (RentPayment $payment) => [
            $payment->rental->tenant->full_name,
            "{$payment->rental->room->property->property_name} — {$payment->rental->room->room_number}",
            number_format($payment->amount_paid, 2),
            $payment->payment_date->format('M j, Y'),
            $payment->payment_method,
            ucfirst($payment->status),
        ]);

        return $format === 'pdf'
            ? Exporter::pdf('payments.pdf', 'Payments', $headers, $rows)
            : Exporter::csv('payments.csv', $headers, $rows);
    }

    private function filteredQuery(string $search)
    {
        return RentPayment::with('rental.tenant', 'rental.room.property')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('payment_method', 'like', "%{$search}%")
                        ->orWhere('transaction_reference', 'like', "%{$search}%")
                        ->orWhereHas('rental.tenant', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('payment_date');
    }

    public function create(): View
    {
        $rentals = Rental::with('tenant', 'room.property')->orderByDesc('id')->get();

        return view('payments.create', compact('rentals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rental_id' => ['required', 'exists:rentals,id'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:completed,pending,failed'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $payment = RentPayment::create($validated);

        Activity::log('created', $payment, 'Recorded payment of $'.number_format($payment->amount_paid, 2));

        if ($payment->rental->tenant->user) {
            try {
                $payment->rental->tenant->user->notify(new PaymentRecorded($payment));
            } catch (\Throwable $e) {
                Log::warning('Failed to notify tenant of recorded payment', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('payments.index')->with('status', 'Payment recorded.');
    }

    public function edit(RentPayment $payment): View
    {
        $rentals = Rental::with('tenant', 'room.property')->orderByDesc('id')->get();

        return view('payments.edit', compact('payment', 'rentals'));
    }

    public function update(Request $request, RentPayment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'rental_id' => ['required', 'exists:rentals,id'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:completed,pending,failed'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $payment->update($validated);

        Activity::log('updated', $payment, 'Updated payment of $'.number_format($payment->amount_paid, 2));

        return redirect()->route('payments.index')->with('status', 'Payment updated.');
    }

    public function destroy(RentPayment $payment): RedirectResponse
    {
        Activity::log('deleted', null, 'Deleted payment of $'.number_format($payment->amount_paid, 2));

        $payment->delete();

        return redirect()->route('payments.index')->with('status', 'Payment deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:rent_payments,id'],
        ]);

        Activity::log('deleted', null, 'Bulk deleted '.count($validated['ids']).' payments');

        RentPayment::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('payments.index')->with('status', 'Payments deleted.');
    }
}
