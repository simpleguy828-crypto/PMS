<div class="max-w-7xl mx-auto p-2">
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
              @php $draft = $draftsByOffice->get($office->id); @endphp
              <div class="border border-gray-300 rounded-lg overflow-hidden hover:shadow-md transition-shadow bg-white">
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

                    <div class="mt-5 border-t border-gray-200 pt-4">
                        @if($draft)
                            <p class="mb-3 text-sm text-amber-800">
                                Draft for {{ $draft->requested_by_name }} · last saved {{ $draft->updated_at->format('M d, Y g:i A') }}
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="resumeDraft({{ $draft->id }})"
                                        class="rounded-md bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    Resume Draft
                                </button>
                                <button type="button" wire:click="selectOffice({{ $office->id }})"
                                        class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400">
                                    Start New PM
                                </button>
                            </div>
                        @else
                            <button type="button" wire:click="selectOffice({{ $office->id }})"
                                    class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                Start Preventive Maintenance
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>