<div class="max-w-7xl mx-auto p-2">
    <!-- Navigation Tabs Component -->
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
            ],
        ],
        'currentTab' => 'office-selection' // Highlight the Office Selection tab since we're on this page
    ])
     @endcomponent

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-center text-gray-800">
            Select Office for Preventive Maintenance
        </h1>
        <p class="text-center text-gray-600 mt-2">
            Choose an office to begin the preventive maintenance process
        </p>
    </div>

    <!-- Office Grid -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($offices as $office)
            <!-- Office Card -->
            <div class="border border-gray-300 rounded-lg overflow-hidden hover:shadow-md transition-shadow cursor-pointer"
                 wire:click.prevent="selectOffice({{ $office->id }})">
                <div class="p-6">
                    <!-- Office Info -->
                    <div class="mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">{{ $office->name }}</h2>
                    </div>

                    <!-- Statistics -->
                    <div class="text-sm text-gray-500">
                        <div class="mb-3">
                            <p class="font-medium">Last PM:</p>
                            <p class="">
                                @if($office->pmRecords && $office->pmRecords->isNotEmpty())
                                    {{ $office->pmRecords->max('date_started')->format('M d, Y') }}
                                @else
                                    No PM conducted yet
                                @endif
                            </p>
                        </div>

                        @php
                            $totalComputers = $office->computer_count;
                            $checkedComputers = $office->pmRecords ? $office->pmRecords->count() : 0;
                        @endphp

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="font-medium">Total Computers:</p>
                                <p class="text-lg font-semibold text-gray-800">{{ $totalComputers }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Computers Checked:</p>
                                <p class="text-lg font-semibold {{ $checkedComputers >= $totalComputers ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $checkedComputers }} / {{ $totalComputers }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>