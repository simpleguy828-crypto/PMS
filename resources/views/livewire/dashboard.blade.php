<div class="max-w-7xl mx-auto p-2">
    <div class="py-8">
        <!-- Analytics Section -->
        <div class="mb-8">
            <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Preventive maintenance</p>
                    <h2 class="text-2xl font-bold text-gray-900">Dashboard analytics</h2>
                </div>
                <div class="flex flex-col gap-2 sm:items-end">
                    <p class="text-sm text-gray-500">Computer results follow the office and date filters. Office coverage follows the date period.</p>
                    <a href="{{ route('pm-summary.pdf', ['start_date' => $customStartDate, 'end_date' => $customEndDate, 'office_id' => $selectedOfficeId]) }}"
                       class="inline-flex items-center rounded-base bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-strong focus:outline-none focus:ring-4 focus:ring-brand-medium">
                        Generate PDF Summary
                    </a>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="font-semibold text-gray-900">Analysis filters</h3>
                        <p class="text-sm text-gray-500">Choose an office for computer results and a date period for all analysis.</p>
                    </div>
                    <span class="hidden rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 sm:inline-block">Live</span>
                </div>

                @php
                    $dashboardOfficeOptions = array_merge(
                        [['label' => 'All active offices', 'value' => '']],
                        $allActiveOffices->map(fn ($office) => ['label' => $office->name, 'value' => $office->id])->all()
                    );
                @endphp
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-1">
                        <label for="dashboard-office" class="mb-2 block text-sm font-medium text-gray-700">Office</label>
                        <x-flowbite-dropdown id="dashboard-office"
                                             wire-model="selectedOfficeId"
                                             :selected-value="$selectedOfficeId"
                                             :options="$dashboardOfficeOptions"
                                             placeholder="All active offices" />
                    </div>

                    <div>
                        <label for="dashboard-start-date" class="mb-2 block text-sm font-medium text-gray-700">From</label>
                        <x-flowbite-datepicker id="dashboard-start-date" :value="$customStartDate" wire:model.live="customStartDate" />
                    </div>

                    <div>
                        <label for="dashboard-end-date" class="mb-2 block text-sm font-medium text-gray-700">To</label>
                        <x-flowbite-datepicker id="dashboard-end-date" :value="$customEndDate" wire:model.live="customEndDate" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Primary computer metrics -->
        <div class="mb-8 grid gap-4 md:grid-cols-3">
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

            <div class="relative {{ $showIssueBreakdown ? 'z-20' : '' }} rounded-lg border border-amber-200 bg-amber-50 p-6 shadow-sm">
                <p class="text-sm font-medium text-amber-900">Total issues found</p>
                <p class="mt-2 text-4xl font-bold text-amber-950">{{ $totalIssuesFound }}</p>
                <p class="mt-2 text-sm text-amber-800">Defective findings across computers</p>
                <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2">
                    <button type="button"
                            wire:click="toggleIssueBreakdown"
                            aria-controls="issue-breakdown"
                            aria-expanded="{{ $showIssueBreakdown ? 'true' : 'false' }}"
                            class="text-sm font-semibold text-amber-950 underline underline-offset-2 hover:text-amber-700">
                        {{ $showIssueBreakdown ? 'Hide breakdown' : 'View breakdown' }}
                    </button>
                    <button type="button"
                            wire:click="openIssueBreakdownModal"
                            class="text-sm font-semibold text-amber-950 underline underline-offset-2 hover:text-amber-700">
                        Expand
                    </button>
                </div>

                @if($showIssueBreakdown)
                    <div id="issue-breakdown" class="absolute inset-x-0 top-full z-30 mt-2 max-h-80 overflow-y-auto overscroll-contain rounded-lg border border-gray-200 bg-white p-4 shadow-xl">
                        @include('livewire.issue-breakdown-content', ['issueBreakdown' => $issueBreakdown])
                    </div>
                @endif
            </div>
        </div>

        @if($showIssueBreakdownModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center bg-gray-950/60 p-4"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="issue-breakdown-title"
                 wire:click="closeIssueBreakdownModal"
                 @keydown.escape.window="$wire.call('closeIssueBreakdownModal')">
                <div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white shadow-2xl"
                     wire:click.stop>
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                        <h3 id="issue-breakdown-title" class="text-lg font-semibold text-gray-900">Issue breakdown</h3>
                        <button type="button"
                                wire:click="closeIssueBreakdownModal"
                                class="text-sm font-semibold text-gray-700 underline underline-offset-2 hover:text-gray-950">
                            Close
                        </button>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-5">
                        @include('livewire.issue-breakdown-content', ['issueBreakdown' => $issueBreakdown])
                    </div>
                </div>
            </div>
        @endif

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