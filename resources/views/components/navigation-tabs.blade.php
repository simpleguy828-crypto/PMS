@php
    $user = auth()->user();
    $routeName = request()->route()?->getName();
    $tabs = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'module' => 'dashboard', 'active' => ['dashboard']],
        ['label' => 'Conduct PMS', 'route' => 'office-selection', 'module' => 'office-selection', 'active' => ['office-selection', 'preventive-maintenance-form', 'pm-form.edit']],
        ['label' => 'Records', 'route' => 'pm-records-list', 'module' => 'pm-records-list', 'active' => ['pm-records-list']],
        ['label' => 'Manage Offices', 'route' => 'office-manager', 'module' => 'office-manager', 'active' => ['office-manager']],
        ['label' => 'PM Schedule Manager', 'route' => 'pm-schedule-manager', 'module' => 'pm-schedule-manager', 'active' => ['pm-schedule-manager']],
    ];

    if ($user?->hasModuleAccess('account-manager')) {
        $tabs[] = ['label' => 'Account Manager', 'route' => 'admin.accounts', 'module' => 'account-manager', 'active' => ['admin.accounts']];
    }

    $tabs[] = ['label' => 'Position Manager', 'route' => 'admin.positions', 'module' => 'position-manager', 'active' => ['admin.positions']];
    $tabs[] = ['label' => 'Profile', 'route' => 'profile', 'profile' => true, 'active' => ['profile']];
    $tabs = array_values(array_filter($tabs, fn ($tab) => isset($tab['profile']) || $user?->hasModuleAccess($tab['module'])));
@endphp

<div class="py-2">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <nav class="hidden min-w-0 flex-1 flex-wrap items-center text-sm font-medium md:flex" aria-label="Main navigation">
            @foreach($tabs as $tab)
                @php($isActive = in_array($routeName, $tab['active'], true))
                <a href="{{ route($tab['route']) }}"
                   @if($isActive) aria-current="page" @endif
                   class="inline-flex min-h-[52px] flex-1 items-center justify-center border-b-2 px-3 py-3 text-center {{ $isActive ? 'border-indigo-500 text-indigo-600' : 'border-gray-200 text-gray-600 hover:border-gray-300 hover:text-gray-900' }} focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center justify-between gap-3 md:hidden">
            <label for="main-navigation" class="sr-only">Navigate to section</label>
            <select id="main-navigation" onchange="if (this.value) window.location.href = this.value" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800">
                @foreach($tabs as $tab)
                    <option value="{{ route($tab['route']) }}" {{ in_array($routeName, $tab['active'], true) ? 'selected' : '' }}>{{ $tab['label'] }}</option>
                @endforeach
            </select>
            <span class="shrink-0 text-right text-xs text-gray-500">{{ $user?->name }}</span>
        </div>

        @if($user)
            <div class="hidden shrink-0 pb-2 text-right leading-tight md:block">
                <span class="block text-xs font-medium uppercase text-gray-500">{{ $user->position ?: 'No position' }}</span>
                <span class="block text-sm font-semibold text-gray-800">{{ $user->name }}</span>
            </div>
        @endif
    </div>
</div>