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
            ]
        ],
        'currentTab' => 'pm-records-list' // Highlight the Records tab since we're on this page
    ])
    @endcomponent
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">PM Records List</h2>

        @if(session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('message') }}
            </div>
        @endif

        <div class="mb-4">
            <input type="text"
                   wire:model="search"
                   placeholder="Search records..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus-ring-indigo-500">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Office</th>
                        <th class="px-4 py-2">Position</th>
                        <th class="px-4 py-2">Date Started</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($records->isEmpty())
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-center">No records found.</td>
                        </tr>
                    @else
                        @foreach($records as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $record->id }}</td>
                                <td class="px-4 py-2">{{ $record->requested_by_name }}</td>
                                <td class="px-4 py-2">{{ $record->office->name }}</td>
                                <td class="px-4 py-2">{{ $record->position }}</td>
                                <td class="px-4 py-2">{{ $record->date_started->format('M d, Y') }}</td>
                                <td class="px-4 py-2 text-center space-x-2">
                                    <a href="{{ route('pm-record.pdf', ['id' => $record->id]) }}"
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-900">
                                        PDF
                                    </a>
                                                                        <a href="{{ route('pm-form.edit', ['record' => $record->id]) }}"
                                       target="_blank"
                                       class="text-amber-600 hover:text-amber-900 ml-2">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        @if($records->isNotEmpty())
            <div class="mt-4 flex items-center justify-between">
                <span class="text-sm text-gray-600">
                    Showing {{ $records->firstItem() }} to {{ $records->lastItem() }} of {{ $records->total() }} records
                </span>
                <div>
                    <span class="relative inline-block mr-2">
                        <select wire:model="perPage"
                                class="block appearance-none w-20 pl-1 pr-3 py-1 border border-gray-300 rounded-md shadow-sm text-gray-700 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </span>
                    <span class="text-sm text-gray-600">per page</span>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <button wire:click.prevent="previousPage"
                            class="disabled:opacity-50"
                            @disabled($records->currentPage() <= 1)>
                        ← Previous
                    </button>

                    <span class="px-3 py-1 text-xs border rounded-md">
                        Page {{ $records->currentPage() }} of {{ $records->lastPage() }}
                    </span>

                    <button wire:click.prevent="nextPage"
                            class="disabled:opacity-50"
                            @disabled($records->currentPage() >= $records->lastPage())>
                        Next →
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>