<div class="max-w-5xl mx-auto p-3 sm:p-4 md:p-6">

    @if($isEditMode)
        <div class="mb-4 p-3 bg-amber-50 border border-amber-400 text-amber-800 rounded-lg text-sm">
            Editing Record #{{ $editingRecordId }}
        </div>
    @endif

    <!-- Header Box -->
    <div class="border border-gray-300 rounded-lg mb-6 overflow-hidden bg-white">
        <div class="flex flex-col sm:flex-row items-center gap-3 p-4">
            <img src="{{ asset('images/gvcf-logo.jpg') }}"
                 alt="Green Valley College Foundation Logo"
                 class="h-20 w-20 sm:h-24 sm:w-24 object-contain shrink-0">
            <div class="flex-1 text-center sm:text-left">
                <div class="text-sm font-bold text-gray-900">
                    GREEN VALLEY COLLEGE FOUNDATION, INC.
                </div>
                <div class="text-xs text-gray-500 mt-0.5">
                    Km.2, General Santos Drive, Koronadal City
                </div>
                <div class="text-lg sm:text-2xl font-bold text-gray-900 mt-2">
                    Preventive Maintenance Form
                </div>
            </div>
        </div>
    </div>

    <!-- Info Section: Name, Position, Department, Date Started -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
            <input type="text"
                   id="name"
                   wire:model="name"
                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 text-base sm:text-sm"
                   placeholder="Enter name"
                   required>
                 @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="position" class="block text-sm font-medium text-gray-700 mb-1.5">Position</label>
            <input type="text"
                   id="position"
                   wire:model="position"
                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 text-base sm:text-sm"
                   placeholder="Position"
                   required>
                 @error('position') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="department" class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
            <div id="department"
                 class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 bg-gray-50 text-base sm:text-sm">
                {{ $selectedDepartmentInfo }}
            </div>
        </div>

        <div>
            <label for="date_started" class="block text-sm font-medium text-gray-700 mb-1.5">Date Started</label>
                 <x-flowbite-datepicker id="date_started" :value="$date_started" wire:model="date_started" required />
                 @error('date_started') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <!-- Checklist -->
    <div class="mb-6">
        @php
            $grouped = [];
            foreach ($checklistItems as $item) {
                $grouped[$item['section']][] = $item;
            }
        @endphp

        @foreach($grouped as $section => $items)
            <div class="mb-5">
                <div class="md:hidden bg-gray-100 border border-gray-300 rounded-t-lg px-4 py-2 font-semibold text-gray-900 text-sm">
                    {{ $section }}:
                </div>

                <!-- Mobile: stacked cards -->
                <div class="md:hidden border border-t-0 border-gray-300 rounded-b-lg divide-y divide-gray-200 bg-white">
                    @foreach($items as $item)
                        @php $itemId = $item['id']; @endphp
                        <div class="p-4 space-y-3">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $item['task_name'] }}
                                @if($item['finding_label'])
                                    <span class="block text-xs font-normal text-gray-500 mt-0.5">{{ $item['finding_label'] }}</span>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                                <x-flowbite-dropdown id="pm-status-mobile-{{ $itemId }}"
                                                     wire-model="itemStatus.{{ $itemId }}"
                                                     :selected-value="$itemStatus[$itemId] ?? ''"
                                                     :options="$this->statusOptions($item)"
                                                     placeholder="Select Status" />
                                @error("itemStatus.$itemId") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Date Completed</label>
                                    <x-flowbite-datepicker id="pm-date-mobile-{{ $itemId }}"
                                                           :value="$itemDateCompleted[$itemId] ?? ''"
                                                           wire:model.live="itemDateCompleted.{{ $itemId }}" />
                                </div>
                                @unless($this->isKeyboardCleaningTask($item))
                                <div class="space-y-2">
                                    <label class="block text-xs font-medium text-gray-500">Remarks</label>
                                    @if($this->itemAllowsFreeformRemarks($item))
                                        <input type="text"
                                               wire:model.live="itemRemarks.{{ $itemId }}"
                                               class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 text-base sm:text-sm"
                                               placeholder="{{ $this->itemRemarkPlaceholder($item) }}">
                                    @endif
                                    <x-flowbite-dropdown id="pm-recommendation-mobile-{{ $itemId }}"
                                                         wire-model="itemRecommendations.{{ $itemId }}"
                                                         :selected-value="$itemRecommendations[$itemId] ?? ''"
                                                         :options="$this->recommendationOptions($item)"
                                                         placeholder="Select Remarks" />
                                    @error("itemRecommendations.$itemId") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                @endunless
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop: table -->
                <div class="hidden md:block border border-t-0 border-gray-300 rounded-b-lg overflow-visible bg-white">
                    @component('components.table', ['overflowVisible' => true])
                        @slot('slotHeader')
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500">Task</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-44">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-44">Date Completed</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-64">Remarks</th>
                        @endslot
                        @slot('slotBody')
                            <tr class="bg-white border-b border-gray-300">
                                <th colspan="4" scope="rowgroup" class="px-2 py-2 text-left text-sm font-semibold text-gray-900">
                                    {{ $section }}:
                                </th>
                            </tr>
                            @foreach($items as $item)
                                @php $itemId = $item['id']; @endphp
                                <tr class="bg-white border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900 align-top">
                                        {{ $item['task_name'] }}
                                        @if($item['finding_label'])
                                            <span class="block text-xs font-normal text-gray-500 mt-0.5">{{ $item['finding_label'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 align-top">
                                        <x-flowbite-dropdown id="pm-status-desktop-{{ $itemId }}"
                                                             wire-model="itemStatus.{{ $itemId }}"
                                                             :selected-value="$itemStatus[$itemId] ?? ''"
                                                             :options="$this->statusOptions($item)"
                                                             placeholder="Select Status" />
                                        @error("itemStatus.$itemId") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-2 align-top">
                                             <x-flowbite-datepicker id="pm-date-desktop-{{ $itemId }}"
                                                        :value="$itemDateCompleted[$itemId] ?? ''"
                                                        wire:model.live="itemDateCompleted.{{ $itemId }}" />
                                    </td>
                                    <td class="px-4 py-2 align-top">
                                        @unless($this->isKeyboardCleaningTask($item))
                                        <div class="space-y-2">
                                        @if($this->itemAllowsFreeformRemarks($item))
                                            <input type="text"
                                                   wire:model.live="itemRemarks.{{ $itemId }}"
                                                   class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 text-sm"
                                                   placeholder="{{ $this->itemRemarkPlaceholder($item) }}">
                                        @endif
                                        <x-flowbite-dropdown id="pm-recommendation-desktop-{{ $itemId }}"
                                                             wire-model="itemRecommendations.{{ $itemId }}"
                                                             :selected-value="$itemRecommendations[$itemId] ?? ''"
                                                             :options="$this->recommendationOptions($item)"
                                                             placeholder="Select Remarks" />
                                        @error("itemRecommendations.$itemId") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        @endslot
                    @endcomponent
                </div>
            </div>
        @endforeach
    </div>

    <!-- Note Box -->
    <div class="bg-gray-50 p-4 border border-gray-300 rounded-lg mb-6 text-sm">
        <div class="font-bold mb-1 text-gray-900">Note</div>
        <div class="text-gray-600">
            It is your responsibility to back-up the data, information or other files stored on your computer disk and/or drives. The MIS shall not be responsible under any circumstance for any loss or corruption of data and/or software, hardware, or any other part, as well as CDs/DVDs and other equipment.
        </div>
    </div>

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="mb-6 p-3 bg-red-50 border border-red-400 text-red-700 rounded-lg text-sm">
            <div class="font-bold mb-1">Please fix the following:</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('message'))
        <div class="mb-6 p-3 bg-green-50 border border-green-400 text-green-700 rounded-lg text-sm">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-3 bg-red-50 border border-red-400 text-red-700 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Save Button(s) -->
    <div class="mb-6">
        @if(!$isSubmitted && !($isEditMode && session('message')))
            <p class="mb-2 text-sm text-gray-600">A status is required for each checklist item. Remarks are optional.</p>
        @endif

        @if($isEditMode && $isSubmitted)
            @if(!session('message'))
                <button wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                        class="w-full px-6 py-3.5 sm:py-3 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-800 rounded-lg disabled:opacity-50"
                    type="button">
                    Update Record
                </button>
            @else
                <button wire:click="backToRecordsList"
                        class="w-full px-6 py-3.5 sm:py-3 text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 rounded-lg">
                    Back to Records List
                </button>
            @endif
        @elseif(!$isSubmitted)
            <div class="flex flex-col sm:flex-row gap-3">
                <button wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="flex-1 px-6 py-3.5 sm:py-3 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-800 rounded-lg disabled:opacity-50"
                        type="button">
                    Save
                </button>
                <button wire:click="saveAsDraft"
                        wire:loading.attr="disabled"
                        wire:target="saveAsDraft"
                        class="flex-1 px-6 py-3.5 sm:py-3 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-800 rounded-lg disabled:opacity-50"
                        type="button">
                    Save as Draft
                </button>
            </div>
        @else
            <div class="flex flex-col sm:flex-row gap-3">
                <button wire:click="conductAgain"
                        class="flex-1 px-6 py-3.5 sm:py-3 text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-800 rounded-lg">
                    Conduct Again
                </button>
                <button wire:click="requestOfficeSelectionConfirmation"
                        class="flex-1 px-6 py-3.5 sm:py-3 text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 rounded-lg">
                    Back to Office Selection
                </button>
            </div>
        @endif
    </div>

    <x-flowbite-modal
        id="pm-form-confirmation"
        :show="$confirmationAction === 'saved'"
        title="Record saved"
        message="Your preventive maintenance record has been saved successfully."
        confirm-text="Conduct Again"
        cancel-text="Close"
        confirm-action="confirmAction"
        cancel-action="cancelConfirmation"
        confirm-button-class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium" />

    <x-flowbite-modal
        id="pm-office-confirmation"
        :show="$confirmationAction === 'office-selection'"
        title="Return to office selection?"
        message="This will leave the current form without saving changes."
        confirm-text="Yes, return"
        cancel-text="Close"
        confirm-action="confirmAction"
        cancel-action="cancelConfirmation"
        confirm-button-class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium" />

    <!-- Footer -->
    <div class="text-right text-xs text-gray-400">
        FM-MIS-008-02 Dated 11 June 2026
    </div>
</div>