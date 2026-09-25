<div class="container mx-auto px-4 py-8">
    <!-- Navigation Tabs Component -->
    @component('components.navigation-tabs', [
        'tabs' => [
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
        'currentTab' => '' // No active tab since this is a report page and we're navigating away
    ])@endcomponent

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">Monthly Summary Report</h2>

        <div class="mb-6">
            <form wire:submit.prevent="generateReport" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="month">
                        Month
                    </label>
                    <select wire:model="month"
                            id="month"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus-ring-indigo-500">
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="year">
                        Year
                    </label>
                    <select wire:model="year"
                            id="year"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus-ring-offset-2 focus-ring-indigo-500">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg shadow-sm hover:bg-indigo-700 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus-ring-offset-2 focus-ring-indigo-500">
                    Generate Report
                </button>
            </form>
        </div>

        @if(session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('message') }}
            </div>
        @endif
    </div>
</div>