<div class="max-w-7xl mx-auto p-2">
       @component('components.navigation-tabs', [
            'tabs' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'dashboard',
                    'method' => 'navigateToDashboard',
                    'id' => 'dashboard'
                ],
                [
                    'label' => 'Conduct Preventive Maintenance',
                    'route' => 'office-selection',
                    'method' => 'navigateToOfficeSelection',
                    'id' => 'office-selection'
                ],
                [
                    'label' => 'Records',
                    'route' => 'pm-records-list',
                    'method' => 'navigateToRecords',
                    'id' => 'pm-records-list'
                ],
                [
                    'label' => 'Manage Offices',
                    'route' => 'office-manager',
                    'method' => 'navigateToOfficeManager',
                    'id' => 'office-manager'
                ],
                [
                    'label' => 'PM Schedule Manager',
                    'route' => 'pm-schedule-manager',
                    'method' => 'navigateToScheduleManager',
                    'id' => 'schedule-manager'
                ]
            ],
            'currentTab' => 'dashboard'
        ])
@endcomponent
    <div class="py-8">
        <!-- Analytics Section -->
        <div class="mb-8">
            <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Preventive maintenance</p>
                    <h2 class="text-2xl font-bold text-gray-900">Dashboard analytics</h2>
                </div>
                <p class="text-sm text-gray-500">Computer results follow the office and date filters. Office coverage follows the date period.</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="font-semibold text-gray-900">Analysis filters</h3>
                        <p class="text-sm text-gray-500">Choose an office for computer results and a date period for all analysis.</p>
                    </div>
                    <span class="hidden rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 sm:inline-block">Live</span>
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-1">
                        <label for="dashboard-office" class="mb-2 block text-sm font-medium text-gray-700">Office</label>
                        <select id="dashboard-office" wire:model.live="selectedOfficeId"
                                class="block w-full rounded-md border-0 py-3 pl-4 pr-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                            <option value="">All active offices</option>
                            @foreach($allActiveOffices as $office)
                                <option value="{{ $office->id }}">{{ $office->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="dashboard-start-date" class="mb-2 block text-sm font-medium text-gray-700">From</label>
                        <input id="dashboard-start-date" type="date" wire:model.live="customStartDate"
                               class="block w-full rounded-md border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                    </div>

                    <div>
                        <label for="dashboard-end-date" class="mb-2 block text-sm font-medium text-gray-700">To</label>
                        <input id="dashboard-end-date" type="date" wire:model.live="customEndDate"
                               class="block w-full rounded-md border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Primary computer metrics -->
        <div class="mb-8 grid gap-4 md:grid-cols-2">
            <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-6 shadow-sm">
                <p class="text-sm font-medium text-emerald-800">Computers checked</p>
                <p class="mt-2 text-4xl font-bold text-emerald-950">{{ $totalComputersChecked }}</p>
                <p class="mt-2 text-sm text-emerald-700">PM records in the selected period</p>
            </div>

            <div class="rounded-lg border border-rose-100 bg-rose-50 p-6 shadow-sm">
                <p class="text-sm font-medium text-rose-800">Damaged computers</p>
                <p class="mt-2 text-4xl font-bold text-rose-950">{{ $totalDamagedComputers }}</p>
                <p class="mt-2 text-sm text-rose-700">Computers with at least one defective item</p>
            </div>
        </div>

        <!-- Office coverage -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Office coverage</h3>
                    <p class="text-sm text-gray-500">Separate coverage tracking from computer condition results.</p>
                </div>
                <p class="text-sm text-gray-500">{{ $allActiveOffices->count() }} active offices total</p>
            </div>

            <div class="mb-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-md bg-gray-50 p-4">
                    <p class="text-sm font-medium text-gray-600">Offices checked</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $totalOfficesChecked }}</p>
                </div>
                <div class="rounded-md bg-gray-50 p-4">
                    <p class="text-sm font-medium text-gray-600">Offices not checked</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $totalOfficesNotChecked }}</p>
                </div>
            </div>

            @if(!empty($officesChecked))
                <div>
                    <h4 class="mb-2 text-sm font-semibold text-gray-700">Checked offices</h4>
                    <ul class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($allActiveOffices->whereIn('id', $officesChecked)->take(6) as $office)
                            <li class="flex items-center justify-between rounded-md border border-gray-100 bg-gray-50 px-3 py-2 text-sm">
                                <span class="truncate pr-3">{{ $office->name }}</span>
                                <span class="font-semibold text-emerald-600">Checked</span>
                            </li>
                        @endforeach
                    </ul>
                    @if(collect($officesChecked)->count() > 6)
                        <p class="mt-3 text-center text-sm text-gray-500">
                            And {{ collect($officesChecked)->count() - 6 }} more checked offices
                        </p>
                    @endif
                </div>
            @else
                <p class="rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-500">No offices have records in this period.</p>
            @endif
        </div>

</div>
</div>