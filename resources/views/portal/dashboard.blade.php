<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('My Dashboard') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Welcome back, {{ $tenant->full_name }} — here's an overview of your account.
            </p>
        </div>
    </x-slot>

    @php
        $rental = $tenant->currentRental;
        $daysLeft = $rental?->end_date ? now()->startOfDay()->diffInDays($rental->end_date, false) : null;
    @endphp

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-status :status="session('status')" />

            @if ($rental)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">My Lease</h3>
                    </div>
                    <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-6">
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Property</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $rental->room->property->property_name }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Room</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $rental->room->room_number }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Monthly Rent</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">GH₵ {{ number_format($rental->monthly_rent, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Security Deposit</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $rental->security_deposit !== null ? 'GH₵ '.number_format($rental->security_deposit, 2) : '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lease Start</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $rental->start_date->format('M j, Y') }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lease End</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $rental->end_date?->format('M j, Y') ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Days Remaining</div>
                            <div class="mt-1">
                                @if ($daysLeft === null)
                                    <span class="text-sm text-gray-900 dark:text-gray-100">—</span>
                                @else
                                    <span @class([
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' => $daysLeft <= 7,
                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' => $daysLeft > 7 && $daysLeft <= 30,
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' => $daysLeft > 30,
                                    ])>
                                        {{ $daysLeft }} {{ Str::plural('day', $daysLeft) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lease Status</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ Str::headline($rental->lease_status) }}</div>
                        </div>
                    </div>

                    @if ($rental->room->amenities->isNotEmpty())
                        <div class="px-6 pb-6">
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">Room Amenities</div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($rental->room->amenities as $amenity)
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ $amenity->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6 text-sm text-gray-500 dark:text-gray-400">
                    You're not currently assigned to a room. Contact your property manager if you believe this is a mistake.
                </div>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <a href="{{ route('portal.payments') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-4 hover:shadow-md transition">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">My Payments</div>
                    <div class="mt-1 text-sm text-indigo-600 dark:text-indigo-400">View payment history &rarr;</div>
                </a>
                <a href="{{ route('portal.bills') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-4 hover:shadow-md transition">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Unpaid Bills</div>
                    <div class="mt-1 text-xl font-bold {{ $unpaidBills->isNotEmpty() ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-gray-100' }}">{{ $unpaidBills->count() }}</div>
                </a>
                <a href="{{ route('portal.complaints.index') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-4 hover:shadow-md transition">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Open Complaints</div>
                    <div class="mt-1 text-xl font-bold text-gray-900 dark:text-gray-100">{{ $openComplaints }}</div>
                </a>
                <a href="{{ route('portal.maintenance-requests') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-4 hover:shadow-md transition">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pending Maintenance</div>
                    <div class="mt-1 text-xl font-bold text-gray-900 dark:text-gray-100">{{ $pendingMaintenance }}</div>
                </a>
                <a href="{{ route('portal.complaints.create') }}" class="bg-indigo-600 rounded-lg shadow-sm p-4 hover:bg-indigo-700 transition">
                    <div class="text-xs font-medium text-indigo-100 uppercase">Need Something?</div>
                    <div class="mt-1 text-sm font-medium text-white">File a complaint / request &rarr;</div>
                </a>
            </div>

            @if ($unpaidBills->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">Unpaid Bills</h3>
                        <a href="{{ route('portal.bills') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View all</a>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Utility</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Due</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($unpaidBills as $bill)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $bill->utilityType->utility_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">GH₵ {{ number_format($bill->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span @class([
                                            'px-2 py-1 rounded-full text-xs font-medium',
                                            'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' => $bill->due_date->isPast(),
                                            'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' => ! $bill->due_date->isPast(),
                                        ])>
                                            {{ $bill->due_date->format('M j, Y') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">Recent Payments</h3>
                    <a href="{{ route('portal.payments') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View all</a>
                </div>

                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($recentPayments as $payment)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">GH₵ {{ number_format($payment->amount_paid, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->payment_date->format('M j, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->payment_method }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span @class([
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $payment->status === 'completed',
                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' => $payment->status === 'pending',
                                        'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' => $payment->status === 'failed',
                                    ])>
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">No payments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
