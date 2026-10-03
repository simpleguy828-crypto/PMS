<div class="max-w-7xl mx-auto p-2">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-3xl font-bold text-heading">PM Schedule Manager</h1>
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label for="schedule-month" class="mb-1 block text-sm font-medium text-heading">Show month</label>
                  <x-flowbite-datepicker id="schedule-month" type="month" :value="$monthFilter" wire:model.live="monthFilter" />
            </div>
            <button type="button" wire:click="createSchedule"
                    class="rounded-base bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-strong focus:outline-none focus:ring-4 focus:ring-brand-medium">
                New Schedule
            </button>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-base bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
            {{ session('message') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse($schedules as $schedule)
            <details open class="overflow-hidden rounded-base border border-default-medium bg-white shadow-xs">
                <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-3 border-b border-default-medium px-4 py-4">
                    <span class="font-semibold text-heading">Schedule: {{ $schedule->scheduled_date->format('F j, Y') }}</span>
                    <span class="flex items-center gap-2" @click.stop>
                        <button type="button" wire:click="editSchedule({{ $schedule->id }})"
                                class="rounded-base border border-default-medium px-3 py-1.5 text-sm font-medium text-heading hover:bg-neutral-secondary-medium">Edit</button>
                        <button type="button" wire:click="deleteSchedule({{ $schedule->id }})"
                                wire:confirm="Delete this schedule and all its office assignments?"
                                class="rounded-base border border-red-300 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-50">Delete</button>
                    </span>
                    @if($schedule->notes)
                        <span class="w-full text-sm text-body">Notes: {{ $schedule->notes }}</span>
                    @endif
                </summary>

                @component('components.table', [
                    'searchModel' => 'officeSearch',
                    'searchInputId' => 'schedule-office-search-' . $schedule->id,
                    'searchLabel' => 'Search offices in this schedule',
                    'searchPlaceholder' => 'Search assigned offices...',
                    'filterModel' => 'officeSortOrder',
                    'filterId' => 'schedule-office-sort-' . $schedule->id,
                    'filterLabel' => 'Sort scheduled offices',
                    'filterPlaceholder' => 'Sort offices by name',
                    'filterOptions' => $officeSortOptions,
                    'filterValue' => $officeSortOrder,
                ])
                    @slot('slotHeader')
                        <th scope="col" class="px-6 py-3 text-left">Office</th>
                        <th scope="col" class="px-6 py-3 text-left">Scheduled Date</th>
                        <th scope="col" class="px-6 py-3 text-left">Status</th>
                        <th scope="col" class="px-6 py-3 text-left">Action</th>
                    @endslot
                    @slot('slotBody')
                        @forelse($schedule->scheduleOffices as $scheduleOffice)
                            @php
                                $completed = $scheduleOffice->completion_status === 'Completed';
                                $badgeClass = $completed
                                    ? 'bg-emerald-100 text-emerald-800'
                                    : ($scheduleOffice->completion_count > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700');
                            @endphp
                            <tr wire:key="schedule-office-{{ $scheduleOffice->id }}" class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                <td class="px-6 py-4 font-medium text-heading">{{ $scheduleOffice->office->name }}</td>
                                <td class="px-6 py-4 text-body">{{ $scheduleOffice->current_scheduled_date->format('M j, Y') }}</td>
                                <td class="px-6 py-4 text-body">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $badgeClass }}">
                                        {{ $scheduleOffice->completion_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-body">
                                    @unless($completed)
                                        <div class="flex items-center gap-3">
                                            <button type="button" wire:click="beginReschedule({{ $scheduleOffice->id }})" class="font-medium text-brand hover:underline">Reschedule</button>
                                            <button type="button" wire:click="removeOffice({{ $scheduleOffice->id }})" wire:confirm="Remove {{ $scheduleOffice->office->name }} from this schedule?" class="font-medium text-red-700 hover:underline">Remove</button>
                                        </div>
                                    @else
                                        <button type="button" wire:click="removeOffice({{ $scheduleOffice->id }})" wire:confirm="Remove {{ $scheduleOffice->office->name }} from this schedule?" class="font-medium text-red-700 hover:underline">Remove</button>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-body">No matching offices are assigned to this schedule.</td></tr>
                        @endforelse
                    @endslot
                @endcomponent
            </details>
        @empty
            <div class="border-y border-default-medium py-12 text-center text-body">
                No schedules found for this month.
            </div>
        @endforelse
    </div>

    @component('components.flowbite-drawer', ['title' => $drawerTitle])
        <form wire:submit.prevent="saveSchedule">
            @if($drawerMode === 'reschedule' && $reschedulingOffice)
                <div class="space-y-5">
                    <div>
                        <p class="text-sm font-medium text-heading">{{ $reschedulingOffice->office->name }}</p>
                        <p class="mt-1 text-sm text-body">Schedule: {{ $reschedulingOffice->schedule->scheduled_date->format('F j, Y') }}</p>
                        <p class="text-sm text-body">Current date: {{ $reschedulingOffice->current_scheduled_date->format('F j, Y') }}</p>
                    </div>
                    <div>
                        <label for="reschedule-date" class="mb-2 block text-sm font-medium text-heading">New Date</label>
                           <x-flowbite-datepicker id="reschedule-date" :value="$rescheduleDate" surface="white" wire:model="rescheduleDate" required />
                        @error('rescheduleDate') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
            @else
                <div class="space-y-5">
                    <div>
                        <label for="scheduled-date" class="mb-2 block text-sm font-medium text-heading">Scheduled Date</label>
                           <x-flowbite-datepicker id="scheduled-date" :value="$scheduledDate" surface="white" wire:model="scheduledDate" required />
                        @error('scheduledDate') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <fieldset>
                        <div class="mb-2 flex items-center justify-between">
                            <legend class="text-sm font-medium text-heading">Offices</legend>
                            <button type="button" wire:click="toggleSelectAll" class="text-sm font-medium text-brand hover:underline">Select All</button>
                        </div>
                        <div class="max-h-64 space-y-2 overflow-y-auto rounded-base border border-default-medium p-3">
                            @forelse($offices as $office)
                                <label wire:key="office-option-{{ $office->id }}" class="flex items-center gap-3 text-sm text-heading">
                                    <input type="checkbox" value="{{ $office->id }}" wire:model="selectedOfficeIds"
                                           class="h-4 w-4 rounded border-default-medium text-brand focus:ring-brand">
                                    <span>{{ $office->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-body">No active offices available.</p>
                            @endforelse
                        </div>
                        @error('selectedOfficeIds') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                    </fieldset>
                    <div>
                        <label for="schedule-notes" class="mb-2 block text-sm font-medium text-heading">Notes <span class="font-normal text-body">(optional)</span></label>
                        <textarea id="schedule-notes" wire:model="notes" rows="3"
                                  class="block w-full rounded-base border border-default-medium bg-white p-3 text-sm text-heading focus:border-brand focus:ring-brand"></textarea>
                        @error('notes') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" wire:click="closeModal"
                        class="rounded-base border border-default-medium px-4 py-2.5 text-sm font-medium text-heading hover:bg-neutral-secondary-medium">Cancel</button>
                <button type="submit"
                        class="rounded-base bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-strong focus:outline-none focus:ring-4 focus:ring-brand-medium">
                    <span wire:loading.remove wire:target="saveSchedule">{{ $drawerMode === 'reschedule' ? 'Reschedule' : ($drawerMode === 'edit' ? 'Save Changes' : 'Create Schedule') }}</span>
                    <span wire:loading wire:target="saveSchedule">Saving...</span>
                </button>
            </div>
        </form>
    @endcomponent
</div>
