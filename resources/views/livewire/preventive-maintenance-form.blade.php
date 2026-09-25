<div class="max-w-7xl mx-auto p-4">

    @if($isEditMode)
        <div class="mb-4 p-3 bg-amber-50 border border-amber-400 text-amber-800 rounded">
            Editing Record #{{ $editingRecordId }}
        </div>
    @endif

    <!-- Header Box -->
    <div class="border border-gray-600 mb-4 flex">
        <div class="w-1/2 p-4 flex items-center justify-center">
            <img src="{{ asset('images/gvcf-logo.jpg') }}" alt="Green Valley College Foundation Logo" class="h-32 w-32 object-contain">
        </div>
        <div class="w-1/2 p-4 flex flex-col justify-center">
            <div class="text-center text-sm font-bold w-full">
                GREEN VALLEY COLLEGE FOUNDATION, INC.
            </div>
            <div class="text-center text-xs w-full">
                Km.2, General Santos Drive, Koronadal City
            </div>
            <div class="text-center text-2xl font-bold w-full mt-2">
                PREVENTIVE MAINTENANCE FORM
            </div>
        </div>
    </div>

    <!-- Info Section: Name, Position, Date Started, Department -->
    <div class="grid gap-6 mb-6 md:grid-cols-2">
        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name:</label>
            <input type="text"
                   id="name"
                   wire:model="name"
                   class="block w-full rounded-md border-0 py-3 pl-4 pr-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"
                   placeholder="Enter name"
                   required>
        </div>

        <!-- Position -->
        <div>
            <label for="position" class="block text-sm font-medium text-gray-700 mb-2">Position:</label>
            <input type="text"
                   id="position"
                   wire:model="position"
                   class="block w-full rounded-md border-0 py-3 pl-4 pr-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"
                   placeholder="Position"
                   required>
        </div>
        <!-- Department (Auto-filled from Office Selection) -->
        <div>
            <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department:</label>
            <div class="block w-full rounded-md border-0 py-3 pl-4 pr-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm" readonly>
                    {{ $selectedDepartmentInfo }}
            </div>
        </div>
        <!-- Date Started -->
        <div>
            <label for="date_started" class="block text-sm font-medium text-gray-700 mb-2">Date Started:</label>
            <input type="date"
                   id="date_started"
                   wire:model="date_started"
                   class="block w-full rounded-md border-0 py-3 pl-4 pr-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"
                   placeholder="Date Started"
                   required>
        </div>

    </div>

    <!-- Checklist: Flat Table -->
    <div class="space-y-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wiser">
                        Task
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wiser">
                        Status
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wiser">
                        Date Completed
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wiser">
                        Remarks
                    </th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grouped = [];
                    foreach ($checklistItems as $item) {
                        $grouped[$item['section']][] = $item;
                    }
                @endphp

                @foreach($grouped as $section => $items)
                    <tr class="bg-gray-50">
                        <td colspan="4" class="px-4 py-2 text-left font-medium text-gray-900">
                            {{ $section }}
                        </td>
                    </tr>
                    @foreach($items as $item)
                        @php
                            $itemId = $item['id'];
                            $status = $this->itemStatus[$itemId] ?? null;
                            $remarks = $itemRemarks[$itemId] ?? '';
                            $dateCompleted = $this->itemDateCompleted[$itemId] ?? '';
                        @endphp
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="px-4 py-2 text-left text-sm font-medium text-gray-900">
                                {{ $item['task_name'] }}
                                @if($item['finding_label'])
                                    <br>
                                    <span class="block text-xs text-gray-500">{{ $item['finding_label'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-left">
                                <select wire:model.live="itemStatus.{{ $itemId }}"
                                        class="block w-full rounded-md border-0 py-2 pl-3 pr-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                                    <option value="">Select Status</option>
                                    <option value="good">Good</option>
                                    <option value="defective">Defective</option>
                                    <option value="na">N/A</option>
                                    <option value="needs_attention">Needs Attention</option>
                                </select>
                            </td>
                            <td class="px-4 py-2 text-left">
                                <input type="date"
                                       wire:model.live="itemDateCompleted.{{ $itemId }}"
                                       class="block w-full rounded-md border-0 py-2 pl-3 pr-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                            </td>
                            <td class="px-4 py-2 text-left">
                                <input type="text"
                                       wire:model.live="itemRemarks.{{ $itemId }}"
                                       class="block w-full rounded-md border-0 py-3 pl-4 pr-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"
                                       placeholder="Enter remarks if any">
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Note Box -->
    <div class="bg-gray-50 p-4 border border-gray-600 mb-4">
        <div class="font-bold mb-2">NOTE:</div>
        <div>
            It is your responsibility to back-up the data, information or other files stored on your computer disk and/or drives. And the MIS shall not be responsible under any circumstance for any loss or corruption of data and/or software or hardware or any other part as well as CD's/DVDs, and other equipment's.
        </div>
    </div>

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="mt-4 p-3 bg-red-50 border border-red-400 text-red-700 rounded">
            <div class="font-bold mb-1">Please fix the following:</div>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('message'))
        <div class="mt-4 p-3 bg-green-50 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mt-4 p-3 bg-red-50 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Footer -->
    <div class="mt-6 text-right text-xs text-gray-500">
        FM-MIS-008-02 Dated 11 June 2026
    </div>

    <!-- Save Button Container -->
    <div class="mt-6">
        @if($isEditMode)
            {{-- Edit mode: single Update button, then a link back to the records list --}}
            @if(!session('message'))
                <button wire:click="save"
                        class="w-full px-6 py-3 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-800 rounded-lg disabled:opacity-50"
                        @disabled(!$formReady)>
                    Update Record
                </button>
            @else
                <button wire:click="backToRecordsList"
                        class="w-full px-6 py-3 text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 rounded-lg">
                    Back to Records List
                </button>
            @endif
        @elseif(!$isSubmitted)
            <button wire:click="save"
                    class="w-full px-6 py-3 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-800 rounded-lg disabled:opacity-50"
                    @disabled(!$formReady)>
                Save
            </button>
        @else
            <div class="flex space-x-3">
                <button wire:click="conductAgain"
                        class="flex-1 px-6 py-3 text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-800 rounded-lg">
                    Conduct Again
                </button>
                <button wire:click="backToOfficeSelection"
                        class="flex-1 px-6 py-3 text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 rounded-lg">
                    Back to Office Selection
                </button>
            </div>
        @endif
    </div>
</div>