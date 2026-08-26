@php
    $isTenant = auth()->user()->hasRole('tenant');
    $dashboardRoute = $isTenant ? 'portal.dashboard' : 'dashboard';

    $links = [
        ['route' => $dashboardRoute, 'label' => 'Dashboard', 'icon' => 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
    ];

    // Tenants get a small, fixed nav into their own read-only portal instead
    // of the permission-driven admin menu below.
    $tenantGroups = [
        'My Account' => [
            ['route' => 'portal.payments', 'label' => 'My Payments', 'icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ['route' => 'portal.bills', 'label' => 'My Bills', 'icon' => 'M3.75 13.5 10.5 3v6.75h9L12.75 21v-6.75h-9Z'],
            ['route' => 'portal.complaints.index', 'label' => 'My Complaints', 'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'],
            ['route' => 'portal.maintenance-requests', 'label' => 'Maintenance Requests', 'icon' => 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z'],
        ],
    ];

    // Each menu lists the permission(s) that unlock it. A user who lacks every
    // permission for a menu never sees the link and can't reach the route.
    $adminGroups = $isTenant ? [] : [
        'Property Management' => [
            ['route' => 'owners.index', 'label' => 'Owners', 'permission' => ['view owners', 'create owners', 'edit owners', 'delete owners'], 'icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'],
            ['route' => 'properties.index', 'label' => 'Properties', 'permission' => ['view properties', 'create properties', 'edit properties', 'delete properties'], 'icon' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'],
            ['route' => 'rooms.index', 'label' => 'Rooms', 'permission' => ['view rooms', 'create rooms', 'edit rooms', 'delete rooms'], 'icon' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
            ['route' => 'tenants.index', 'label' => 'Tenants', 'permission' => ['view tenants', 'create tenants', 'edit tenants', 'delete tenants'], 'icon' => 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
            ['route' => 'rentals.index', 'label' => 'Rentals', 'permission' => ['view rentals', 'create rentals', 'edit rentals', 'delete rentals'], 'icon' => 'M10.5 21h6a1.5 1.5 0 0 0 1.5-1.5V6.621a1.5 1.5 0 0 0-.44-1.06L14.44 2.44A1.5 1.5 0 0 0 13.378 2H6a1.5 1.5 0 0 0-1.5 1.5v16.5a1.5 1.5 0 0 0 1.5 1.5Zm-1.5-9h6m-6 3h6'],
        ],
        'Financials' => [
            ['route' => 'payments.index', 'label' => 'Payments', 'permission' => ['view payments', 'create payments', 'edit payments', 'delete payments'], 'icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ['route' => 'expenses.index', 'label' => 'Expenses', 'permission' => ['view expenses', 'create expenses', 'edit expenses', 'delete expenses'], 'icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
            ['route' => 'utility-types.index', 'label' => 'Utility Types', 'permission' => ['view utilities', 'create utilities', 'edit utilities', 'delete utilities'], 'icon' => 'M3.75 13.5 10.5 3v6.75h9L12.75 21v-6.75h-9Z'],
            ['route' => 'utility-bills.index', 'label' => 'Utility Bills', 'permission' => ['view utilities', 'create utilities', 'edit utilities', 'delete utilities'], 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
        ],
        'Maintenance' => [
            ['route' => 'complaints.index', 'label' => 'Complaints', 'permission' => ['view complaints', 'create complaints', 'edit complaints', 'delete complaints'], 'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'],
            ['route' => 'complaint-comments.index', 'label' => 'Complaint Comments', 'permission' => ['view complaints', 'create complaints', 'edit complaints', 'delete complaints'], 'icon' => 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z'],
            ['route' => 'maintenance-requests.index', 'label' => 'Maintenance Requests', 'permission' => ['view maintenance requests', 'create maintenance requests', 'edit maintenance requests', 'delete maintenance requests'], 'icon' => 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z'],
        ],
        'System' => [
            ['route' => 'users.index', 'label' => 'Users', 'permission' => 'manage users', 'icon' => 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z'],
            ['route' => 'roles.index', 'label' => 'Roles', 'permission' => 'manage roles', 'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
            ['route' => 'permissions.index', 'label' => 'Permission Matrix', 'permission' => 'manage roles', 'icon' => 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z'],
            ['route' => 'reports.index', 'label' => 'Reports', 'permission' => 'view reports', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
            ['route' => 'activity-logs.index', 'label' => 'Activity Logs', 'permission' => 'view activity logs', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ['route' => 'settings.index', 'label' => 'Settings', 'permission' => 'manage settings', 'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.127.332-.184.582-.496.644-.87l.214-1.281Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
        ],
    ];

    $canSeeLink = function ($link) {
        $permissions = (array) ($link['permission'] ?? []);

        return empty($permissions) || auth()->user()->canAny($permissions);
    };

    $visibleGroups = collect($isTenant ? $tenantGroups : $adminGroups)
        ->map(fn ($groupLinks) => array_values(array_filter($groupLinks, $canSeeLink)))
        ->filter(fn ($groupLinks) => count($groupLinks) > 0);

    // Small red count badge on the Users link, so an unassigned registrant
    // is visible from anywhere in the app, not just the dashboard.
    $unassignedUserCount = ! $isTenant && auth()->user()->can('manage users')
        ? \App\Models\User::doesntHave('roles')->count()
        : 0;
@endphp

<aside
    x-data="{ open: false }"
    class="lg:w-64 lg:flex lg:flex-col lg:fixed lg:inset-y-0"
>
    <!-- Mobile top bar -->
    <div class="lg:hidden flex items-center justify-between bg-gradient-to-b from-slate-900 to-slate-800 border-b border-slate-700/50 px-4 py-3">
        <a href="{{ route($dashboardRoute) }}" class="flex items-center">
            <x-application-logo class="h-8 w-auto fill-current text-white" />
        </a>
        <button @click="open = ! open" class="p-2 rounded-md text-slate-300 hover:bg-slate-700/50">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ 'hidden': open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ 'hidden': ! open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Sidebar body -->
    <div
        :class="{ 'block': open, 'hidden': ! open }"
        class="hidden lg:flex lg:flex-1 lg:flex-col bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 border-r border-slate-700/50 h-full lg:h-screen overflow-y-auto shadow-xl"
    >
        <div class="hidden lg:flex items-center gap-2.5 px-6 py-5">
            <a href="{{ route($dashboardRoute) }}" class="flex items-center gap-2.5">
                <x-application-logo class="h-8 w-auto fill-current text-white" />
                <span class="text-white font-semibold tracking-wide">{{ config('app.name', 'Laravel') }}</span>
            </a>
        </div>

        <nav class="flex-1 px-3 space-y-0.5">
            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition
                        {{ request()->routeIs(explode('.', $link['route'])[0].'*')
                            ? 'bg-indigo-600 text-white shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                    </svg>
                    {{ $link['label'] }}
                </a>
            @endforeach

            @foreach ($visibleGroups as $groupLabel => $groupLinks)
                <p class="px-3 pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    {{ $groupLabel }}
                </p>

                @foreach ($groupLinks as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition
                            {{ request()->routeIs(explode('.', $link['route'])[0].'*')
                                ? 'bg-indigo-600 text-white shadow-sm'
                                : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                        </svg>
                        <span class="truncate">{{ $link['label'] }}</span>
                        @if ($link['route'] === 'users.index' && $unassignedUserCount > 0)
                            <span class="ml-auto inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-xs font-semibold bg-red-500 text-white">
                                {{ $unassignedUserCount }}
                            </span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="px-3 pb-4 mt-auto border-t border-slate-700/50 pt-4">
            <div class="px-3 mb-2">
                <div class="text-sm font-medium text-slate-100">{{ auth()->user()->name }}</div>
                <div class="text-xs text-slate-400">{{ auth()->user()->email }}</div>
                @if ($role = auth()->user()->roles->first())
                    <span class="inline-block mt-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-300">
                        {{ Str::headline($role->name) }}
                    </span>
                @endif
            </div>

            <a
                href="{{ route('profile.edit') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:bg-slate-800/70 hover:text-white"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Profile
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-red-400 hover:bg-red-500/10 hover:text-red-300"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                    </svg>
                    Log Out
                </button>
            </form>
        </div>
    </div>
</aside>
