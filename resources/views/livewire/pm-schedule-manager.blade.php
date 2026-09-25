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
        'currentTab' => 'schedule-manager' // Highlight the PM Schedule Manager tab since we're on this page
    ])@endcomponent

    <!-- Schedule Manager Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-center text-gray-800">
            PM Schedule Manager
        </h1>
        <p class="center text-gray-600 mt-2">
            Manage preventive maintenance schedules for all offices
        </p>
    </div>

    <!-- Placeholder for Schedule List and Controls -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-center py-12">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">
                PM Schedule Manager
            </h2>
            <p class="text-gray-600 mb-6">
                Schedule list and controls will be implemented in Fix 5
            </p>
            
            <!-- New Schedule Button (will be functional in Fix 4 & 5) -->
            <button 
                wire:click="showNewScheduleModal"
                class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus-ring-offset-2 focus-ring-indigo-500">
                New Schedule
            </button>
        </div>
    </div>
</div>
