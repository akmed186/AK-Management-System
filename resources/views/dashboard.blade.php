<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Welcome back, {{ auth()->user()->name }} — here's what's happening today, {{ now()->format('F j, Y') }}.
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-status :status="session('status')" />

            @if (count($stats))
                @php
                    $colorClasses = [
                        'blue' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                        'violet' => 'bg-violet-50 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',
                        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
                        'indigo' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
                        'sky' => 'bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400',
                        'red' => 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                        'orange' => 'bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
                        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-700/50 dark:text-slate-300',
                        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
                    ];

                    $accentClasses = [
                        'blue' => 'bg-blue-500', 'violet' => 'bg-violet-500', 'emerald' => 'bg-emerald-500',
                        'amber' => 'bg-amber-500', 'indigo' => 'bg-indigo-500', 'sky' => 'bg-sky-500',
                        'red' => 'bg-red-500', 'orange' => 'bg-orange-500', 'slate' => 'bg-slate-400', 'rose' => 'bg-rose-500',
                    ];

                    $buildingIcon = 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21';
                    $roomsIcon = 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z';
                    $tenantsIcon = 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z';
                    $currencyIcon = 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
                    $ownersIcon = 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z';
                    $rentalsIcon = 'M10.5 21h6a1.5 1.5 0 0 0 1.5-1.5V6.621a1.5 1.5 0 0 0-.44-1.06L14.44 2.44A1.5 1.5 0 0 0 13.378 2H6a1.5 1.5 0 0 0-1.5 1.5v16.5a1.5 1.5 0 0 0 1.5 1.5Zm-1.5-9h6m-6 3h6';
                    $complaintsIcon = 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z';
                    $maintenanceIcon = 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z';
                    $utilityBillsIcon = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z';
                    $usersIcon = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z';
                    $unassignedIcon = 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z';

                    $roomsTotal = $stats['rooms'] ?? 0;
                    $occupancyRate = $roomsTotal > 0 ? round(($stats['rooms_occupied'] ?? 0) / $roomsTotal * 100) : null;

                    $revenueChange = $stats['revenue_change'] ?? null;
                    $revenueBadge = null;
                    if ($revenueChange !== null) {
                        $revenueBadge = [
                            'text' => ($revenueChange > 0 ? '+' : '').$revenueChange.'% vs last month',
                            'class' => $revenueChange > 0
                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                                : ($revenueChange < 0
                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'),
                        ];
                    }

                    $statCards = collect([
                        isset($stats['properties']) ? [
                            'label' => 'Properties', 'value' => $stats['properties'], 'route' => 'properties.index',
                            'color' => 'blue', 'icon' => $buildingIcon,
                        ] : null,
                        isset($stats['rooms']) ? [
                            'label' => 'Occupancy Rate', 'value' => $occupancyRate !== null ? $occupancyRate.'%' : '—',
                            'subtitle' => $roomsTotal > 0 ? ($stats['rooms_occupied'] ?? 0)." of {$roomsTotal} rooms occupied" : 'No rooms yet',
                            'route' => 'rooms.index', 'color' => 'violet', 'icon' => $roomsIcon,
                        ] : null,
                        isset($stats['tenants']) ? [
                            'label' => 'Tenants', 'value' => $stats['tenants'], 'route' => 'tenants.index',
                            'color' => 'emerald', 'icon' => $tenantsIcon,
                        ] : null,
                        isset($stats['revenue_this_month']) ? [
                            'label' => 'Revenue This Month', 'value' => '$'.number_format($stats['revenue_this_month'], 2),
                            'badge' => $revenueBadge, 'route' => 'payments.index', 'color' => 'amber', 'icon' => $currencyIcon,
                        ] : null,
                        isset($stats['owners']) ? ['label' => 'Owners', 'value' => $stats['owners'], 'route' => 'owners.index', 'color' => 'indigo', 'icon' => $ownersIcon] : null,
                        isset($stats['active_rentals']) ? ['label' => 'Active Rentals', 'value' => $stats['active_rentals'], 'route' => 'rentals.index', 'color' => 'sky', 'icon' => $rentalsIcon] : null,
                        isset($stats['open_complaints']) ? ['label' => 'Open Complaints', 'value' => $stats['open_complaints'], 'route' => 'complaints.index', 'color' => 'red', 'icon' => $complaintsIcon] : null,
                        isset($stats['pending_maintenance']) ? ['label' => 'Pending Maintenance', 'value' => $stats['pending_maintenance'], 'route' => 'maintenance-requests.index', 'color' => 'orange', 'icon' => $maintenanceIcon] : null,
                        isset($stats['unpaid_bills']) ? ['label' => 'Unpaid Bills', 'value' => $stats['unpaid_bills'], 'route' => 'utility-bills.index', 'color' => 'rose', 'icon' => $utilityBillsIcon] : null,
                        isset($stats['users']) ? ['label' => 'System Users', 'value' => $stats['users'], 'route' => 'users.index', 'color' => 'slate', 'icon' => $usersIcon] : null,
                        ! empty($stats['unassigned_users']) ? [
                            'label' => 'Unassigned Users', 'value' => $stats['unassigned_users'], 'route' => 'users.index',
                            'params' => ['role' => 'unassigned'], 'color' => 'red', 'icon' => $unassignedIcon,
                            'subtitle' => 'Registered, no role yet',
                        ] : null,
                    ])->filter()->values();

                    $showRoomStatus = isset($stats['rooms']);
                    $showQuickActions = auth()->user()->canAny(['create properties', 'create owners', 'create tenants', 'create payments', 'manage users']);
                @endphp

                <!-- Stat cards (uniform size) -->
                @if ($statCards->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        @foreach ($statCards as $card)
                            <a href="{{ route($card['route'], $card['params'] ?? []) }}"
                                class="group relative flex flex-col overflow-hidden bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-4 hover:shadow-md hover:-translate-y-0.5 transition">
                                <span class="absolute inset-x-0 top-0 h-1 {{ $accentClasses[$card['color']] }}"></span>
                                <div class="flex items-start justify-between gap-2">
                                    <div class="w-8 h-8 rounded-md flex items-center justify-center {{ $colorClasses[$card['color']] }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                                        </svg>
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium whitespace-nowrap {{ ! empty($card['badge']) ? $card['badge']['class'] : 'invisible' }}">
                                        {{ ! empty($card['badge']) ? $card['badge']['text'] : '—' }}
                                    </span>
                                </div>
                                <div class="mt-2 text-xl font-bold text-gray-900 dark:text-gray-100">{{ $card['value'] }}</div>
                                <div class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300">{{ $card['label'] }}</div>
                                <div class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500 min-h-[0.9rem]">{{ $card['subtitle'] ?? '' }}</div>
                            </a>
                        @endforeach
                    </div>
                @endif

                <!-- Leases expiring soon -->
                @if (! is_null($expiringRentals))
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">Leases Expiring Soon</h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Active leases ending within the next 30 days — reach out to these tenants about renewal.</p>
                            </div>
                            <a href="{{ route('rentals.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline shrink-0">View all rentals</a>
                        </div>

                        @if ($expiringRentals->isNotEmpty())
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tenant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Unit</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lease Ends</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Days Left</th>
                                        <th class="px-6 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($expiringRentals as $rental)
                                        @php $daysLeft = now()->startOfDay()->diffInDays($rental->end_date, false); @endphp
                                        <tr>
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $rental->tenant->full_name }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $rental->room->property->property_name }} — {{ $rental->room->room_number }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $rental->end_date->format('M j, Y') }}</td>
                                            <td class="px-6 py-4 text-sm">
                                                <span @class([
                                                    'px-2 py-1 rounded-full text-xs font-medium',
                                                    'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' => $daysLeft <= 7,
                                                    'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' => $daysLeft > 7,
                                                ])>
                                                    {{ $daysLeft }} {{ Str::plural('day', $daysLeft) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm">
                                                @can('edit rentals')
                                                    <a href="{{ route('rentals.edit', $rental) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Manage lease</a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No leases expiring in the next 30 days.
                            </div>
                        @endif
                    </div>
                @endif

                @if (! is_null($unassignedUsers) && $unassignedUsers->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-red-200 dark:border-red-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-red-100 dark:border-red-800/50 bg-red-50/50 dark:bg-red-900/10 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">New Users Awaiting a Role</h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Registered accounts with no role assigned yet — they can't do anything until you give them one.</p>
                            </div>
                            <a href="{{ route('users.index', ['role' => 'unassigned']) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline shrink-0">View all</a>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Registered</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($unassignedUsers as $unassignedUser)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $unassignedUser->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $unassignedUser->email }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $unassignedUser->created_at->diffForHumans() }}</td>
                                        <td class="px-6 py-4 text-right text-sm">
                                            <a href="{{ route('users.edit', $unassignedUser) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Assign role</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if ($showRoomStatus || $showQuickActions)
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                        <!-- Room status breakdown -->
                        @if ($showRoomStatus)
                            @php $statusTotal = max($stats['rooms_occupied'] + $stats['rooms_vacant'] + $stats['rooms_maintenance'], 1); @endphp
                            <div class="{{ $showQuickActions ? 'lg:col-span-2' : 'lg:col-span-3' }} bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">Room Status</h3>

                                <div class="mt-4 flex h-2.5 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700">
                                    <div class="bg-emerald-500" style="width: {{ $stats['rooms_occupied'] / $statusTotal * 100 }}%"></div>
                                    <div class="bg-gray-400 dark:bg-gray-500" style="width: {{ $stats['rooms_vacant'] / $statusTotal * 100 }}%"></div>
                                    <div class="bg-amber-500" style="width: {{ $stats['rooms_maintenance'] / $statusTotal * 100 }}%"></div>
                                </div>

                                <div class="mt-5 grid grid-cols-3 gap-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        <span class="text-gray-600 dark:text-gray-400">Occupied</span>
                                        <span class="ml-auto font-medium text-gray-900 dark:text-gray-100">{{ $stats['rooms_occupied'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-gray-400 dark:bg-gray-500"></span>
                                        <span class="text-gray-600 dark:text-gray-400">Vacant</span>
                                        <span class="ml-auto font-medium text-gray-900 dark:text-gray-100">{{ $stats['rooms_vacant'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                        <span class="text-gray-600 dark:text-gray-400">Maintenance</span>
                                        <span class="ml-auto font-medium text-gray-900 dark:text-gray-100">{{ $stats['rooms_maintenance'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Quick actions -->
                        @if ($showQuickActions)
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">Quick Actions</h3>
                                <div class="mt-4 space-y-2">
                                    @can('create properties')
                                        <a href="{{ route('properties.create') }}" class="flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/40 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Add Property
                                            <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    @endcan
                                    @can('create owners')
                                        <a href="{{ route('owners.create') }}" class="flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/40 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Add Owner
                                            <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    @endcan
                                    @can('create tenants')
                                        <a href="{{ route('tenants.create') }}" class="flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/40 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Add Tenant
                                            <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    @endcan
                                    @can('create payments')
                                        <a href="{{ route('payments.create') }}" class="flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/40 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Record Payment
                                            <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    @endcan
                                    @can('manage users')
                                        <a href="{{ route('users.index') }}" class="flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/40 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Manage Users
                                            <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Recently added properties -->
                @if (! is_null($recentProperties))
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">Recently Added Properties</h3>
                            <a href="{{ route('properties.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View all</a>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Property</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Owner</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rooms</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($recentProperties as $property)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $property->property_name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $property->owner->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $property->property_type }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $property->rooms_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">No properties yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center">
                    <div class="mx-auto w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">You're logged in!</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage your account details from your profile page.
                    </p>
                    <a href="{{ route('profile.edit') }}" class="mt-5 inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-medium hover:bg-gray-700 dark:hover:bg-white">
                        Go to Profile
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
