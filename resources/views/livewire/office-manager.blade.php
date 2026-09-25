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
        'currentTab' => 'office-manager' // Highlight the Manage Offices tab since we're on this page
    ])
 @endcomponent
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-heading">Office Manager</h1>
        @component('components.add-button', [
          'wireClick' => 'createOffice',
          'text' => 'Add New Office'
        ])
        @endcomponent
    </div>

    <div class="mb-5 relative">
        <label for="search" class="block mb-2 text-sm font-medium text-heading sr-only">Search offices by name</label>
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
            </div>
            <input type="search"
                   id="search"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search offices by name..."
                   class="block w-full p-4 ps-10 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                   autocomplete="off">
        </div>
    </div>

    @component('components.table', [
        'headers' => [
            ['label' => 'Name', 'field' => 'name', 'sortable' => true],
            ['label' => 'Computers', 'field' => 'computers', 'sortable' => true],
            ['label' => 'Status', 'field' => 'status', 'sortable' => true],
            ['label' => 'Actions', 'field' => 'actions', 'sortable' => false]
        ],
        'rows' => $offices,
        'sortField' => $sortField,
        'sortAsc' => $sortAsc,
        'rowCallback' => function($office) {
            return [
                'name' => $office->name,
                'computers' => $office->computer_count,
                'status' => ucfirst($office->status),
                'actions' => '<button wire:click="editOffice(' . $office->id . ')" class="font-medium text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium rounded-base text-sm px-3 py-1.5 me-2 focus:outline-none">Edit</button> <button wire:click="deleteOffice(' . $office->id . ')" wire:confirm="Are you sure you want to delete this office?" class="font-medium text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 rounded-base text-sm px-3 py-1.5 focus:outline-none">Delete</button>'
            ];
        }
    ])
    @endcomponent

    @component('components.flowbite-drawer', ['title' => $editingOfficeId ? "Edit Office: {$this->name}" : 'Create New Office'])
        <form wire:submit.prevent="saveOffice">
            <div class="space-y-6">
                <div>
                    <label for="office-name" class="block mb-2 text-sm font-medium text-heading">Office Name</label>
                    <input type="text"
                           id="office-name"
                           wire:model="name"
                           class="block w-full p-3 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                           placeholder="Enter office name"
                           required>
                    @error('name') <span class="mt-1 text-sm text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="office-computer-count" class="block mb-2 text-sm font-medium text-heading">Number of Computers</label>
                    <input type="number"
                           id="office-computer-count"
                           wire:model="computer_count"
                           min="0"
                           class="block w-full p-3 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                           placeholder="Enter number of computers"
                           required>
                    @error('computer_count') <span class="mt-1 text-sm text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="office-status" class="block mb-2 text-sm font-medium text-heading">Status</label>
                    <select id="office-status"
                            wire:model="status"
                            class="block w-full p-3 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    @error('status') <span class="mt-1 text-sm text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-4">
                <button type="button"
                        wire:click="closeModal"
                        class="text-body bg-neutral-secondary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading focus:ring-4 focus:ring-neutral-tertiary shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">
                    Cancel
                </button>
                <button type="submit"
                        class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">
                    <span wire:loading.remove wire:target="saveOffice">{{ $editingOfficeId ? 'Update' : 'Create' }}</span>
                    <span wire:loading wire:target="saveOffice">Saving...</span>
                </button>
            </div>
        </form>
    @endcomponent

    @if(session()->has('message'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)"
             class="fixed top-4 right-4 z-50 flex items-center space-x-4 text-sm font-medium bg-emerald-50 text-emerald-800 rounded-lg px-4 py-3 shadow-lg"
             role="alert">
            {{ session('message') }}
        </div>
    @endif
</div>