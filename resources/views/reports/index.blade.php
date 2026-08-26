<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Reports') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                A detailed breakdown of occupancy, finances, tenants, and maintenance — export any section as CSV or PDF.
            </p>
        </div>
    </x-slot>

    @php
        $leaseStatusColors = [
            'active' => 'bg-emerald-500', 'expired' => 'bg-gray-400', 'terminated' => 'bg-red-500', 'unassigned' => 'bg-amber-500',
        ];
        $leaseStatusText = [
            'active' => 'text-emerald-700 dark:text-emerald-400', 'expired' => 'text-gray-600 dark:text-gray-400',
            'terminated' => 'text-red-700 dark:text-red-400', 'unassigned' => 'text-amber-700 dark:text-amber-400',
        ];
        $maintenanceStatusColors = ['scheduled' => 'bg-gray-400', 'in_progress' => 'bg-amber-500', 'completed' => 'bg-emerald-500'];
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-status :status="session('status')" />

            <!-- Top-level summary -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <div class="text-2xl font-semibold text-emerald-600 dark:text-emerald-400">GH₵ {{ number_format($stats['income'], 2) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Income in Range</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <div class="text-2xl font-semibold text-red-600 dark:text-red-400">GH₵ {{ number_format($stats['expenses'], 2) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Expenses in Range</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <div @class([
                        'text-2xl font-semibold',
                        'text-gray-900 dark:text-gray-100' => $stats['net'] >= 0,
                        'text-red-600 dark:text-red-400' => $stats['net'] < 0,
                    ])>GH₵ {{ number_format($stats['net'], 2) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Net in Range</div>
                </div>
            </div>

            <!-- Date filter -->
            <div class="flex flex-wrap items-end justify-between gap-4">
                <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="date_from" class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">From</label>
                        <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}"
                            class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="date_to" class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">To</label>
                        <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}"
                            class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                        Filter
                    </button>
                    @if ($dateFrom || $dateTo)
                        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
                    @endif
                    <x-input-error :messages="$errors->get('date_to')" class="basis-full" />
                </form>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 -mt-3">
                The date range applies to the Financial, Tenant, and Maintenance sections below. Occupancy always reflects current room status, and the trend chart always covers the trailing {{ count($financial['trend']) }} months regardless of the filter.
            </p>

            <!-- Occupancy -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">{{ $reports['occupancy']['name'] }}</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $reports['occupancy']['description'] }}</p>
                    </div>
                    <x-export-menu route="reports.export" :params="['type' => 'occupancy']" />
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                        <div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $occupancy['total'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Total Rooms</div>
                        </div>
                        <div>
                            <div class="text-xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $occupancy['occupied'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Occupied</div>
                        </div>
                        <div>
                            <div class="text-xl font-semibold text-gray-500 dark:text-gray-400">{{ $occupancy['vacant'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Vacant</div>
                        </div>
                        <div>
                            <div class="text-xl font-semibold text-amber-600 dark:text-amber-400">{{ $occupancy['maintenance'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Maintenance</div>
                        </div>
                        <div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $occupancy['rate'] }}%</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Occupancy Rate</div>
                        </div>
                    </div>

                    @if ($occupancy['total'] > 0)
                        <div class="flex h-2.5 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700">
                            <div class="bg-emerald-500" style="width: {{ $occupancy['occupied'] / $occupancy['total'] * 100 }}%"></div>
                            <div class="bg-gray-400 dark:bg-gray-500" style="width: {{ $occupancy['vacant'] / $occupancy['total'] * 100 }}%"></div>
                            <div class="bg-amber-500" style="width: {{ $occupancy['maintenance'] / $occupancy['total'] * 100 }}%"></div>
                        </div>
                    @endif

                    @if ($occupancy['by_property']->isNotEmpty())
                        <div class="border border-gray-100 dark:border-gray-700 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Property</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Occupied</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vacant</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Maintenance</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($occupancy['by_property'] as $property)
                                        <tr>
                                            <td class="px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100">{{ $property['name'] }}</td>
                                            <td class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400">{{ $property['occupied'] }} / {{ $property['total'] }}</td>
                                            <td class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400">{{ $property['vacant'] }}</td>
                                            <td class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400">{{ $property['maintenance'] }}</td>
                                            <td class="px-4 py-2.5 text-sm">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-16 h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                                        <div class="h-full bg-indigo-500" style="width: {{ $property['rate'] }}%"></div>
                                                    </div>
                                                    <span class="text-gray-500 dark:text-gray-400">{{ $property['rate'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Financial -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">{{ $reports['financial']['name'] }}</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $reports['financial']['description'] }}</p>
                    </div>
                    <x-export-menu route="reports.export" :params="['type' => 'financial']" />
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">Last {{ count($financial['trend']) }} Months — Income vs. Expenses</h4>
                        <div class="flex items-end gap-4 h-36">
                            @foreach ($financial['trend'] as $month)
                                <div class="flex-1 flex flex-col items-center justify-end h-full gap-1">
                                    <div class="w-full flex items-end justify-center gap-1 flex-1">
                                        <div class="w-1/2 max-w-[18px] rounded-t bg-emerald-500" style="height: {{ $month['income'] > 0 ? max($month['income'] / $financial['trend_max'] * 100, 2) : 0 }}%" title="Income: GH₵ {{ number_format($month['income'], 2) }}"></div>
                                        <div class="w-1/2 max-w-[18px] rounded-t bg-red-400" style="height: {{ $month['expenses'] > 0 ? max($month['expenses'] / $financial['trend_max'] * 100, 2) : 0 }}%" title="Expenses: GH₵ {{ number_format($month['expenses'], 2) }}"></div>
                                    </div>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ $month['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Income</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400"></span> Expenses</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">Income by Method</h4>
                            @forelse ($financial['by_method'] as $method)
                                <div class="flex items-center justify-between py-1.5 text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ $method->payment_method }}</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">GH₵ {{ number_format($method->total, 2) }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 dark:text-gray-500">No income recorded in this range.</p>
                            @endforelse
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">Expenses by Category</h4>
                            @forelse ($financial['by_category'] as $category)
                                <div class="flex items-center justify-between py-1.5 text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ $category->category }}</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">GH₵ {{ number_format($category->total, 2) }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 dark:text-gray-500">No expenses recorded in this range.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Tenants -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">{{ $reports['tenants']['name'] }}</h3>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $reports['tenants']['description'] }}</p>
                        </div>
                        <x-export-menu route="reports.export" :params="['type' => 'tenants']" />
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $tenantSummary['total'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Total Tenants</div>
                            </div>
                            <div>
                                <div class="text-xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $tenantSummary['with_unit'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">With a Unit</div>
                            </div>
                            <div>
                                <div class="text-xl font-semibold text-amber-600 dark:text-amber-400">{{ $tenantSummary['unassigned'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Unassigned</div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">By Lease Status</h4>
                            <div class="space-y-2">
                                @forelse ($tenantSummary['by_lease_status'] as $status => $count)
                                    <div class="flex items-center gap-3">
                                        <span class="w-24 shrink-0 text-sm {{ $leaseStatusText[$status] ?? 'text-gray-600 dark:text-gray-400' }}">{{ Str::headline($status) }}</span>
                                        <div class="flex-1 h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-full {{ $leaseStatusColors[$status] ?? 'bg-gray-400' }}" style="width: {{ $tenantSummary['total'] > 0 ? $count / $tenantSummary['total'] * 100 : 0 }}%"></div>
                                        </div>
                                        <span class="w-6 text-right text-sm text-gray-500 dark:text-gray-400">{{ $count }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400 dark:text-gray-500">No tenants match this range.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">{{ $reports['maintenance']['name'] }}</h3>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $reports['maintenance']['description'] }}</p>
                        </div>
                        <x-export-menu route="reports.export" :params="['type' => 'maintenance']" />
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $maintenanceSummary['total'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Total Requests</div>
                            </div>
                            <div>
                                <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">GH₵ {{ number_format($maintenanceSummary['total_cost'], 2) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Total Cost</div>
                            </div>
                            <div>
                                <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">GH₵ {{ number_format($maintenanceSummary['avg_cost'], 2) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Avg Cost</div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">By Status</h4>
                            <div class="space-y-2">
                                @forelse (['scheduled', 'in_progress', 'completed'] as $status)
                                    @php $count = $maintenanceSummary['by_status'][$status] ?? 0; @endphp
                                    <div class="flex items-center gap-3">
                                        <span class="w-24 shrink-0 text-sm text-gray-600 dark:text-gray-400">{{ Str::headline($status) }}</span>
                                        <div class="flex-1 h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-full {{ $maintenanceStatusColors[$status] }}" style="width: {{ $maintenanceSummary['total'] > 0 ? $count / $maintenanceSummary['total'] * 100 : 0 }}%"></div>
                                        </div>
                                        <span class="w-6 text-right text-sm text-gray-500 dark:text-gray-400">{{ $count }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400 dark:text-gray-500">No maintenance requests in this range.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
