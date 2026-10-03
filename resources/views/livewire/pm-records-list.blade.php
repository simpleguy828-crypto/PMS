<div class="max-w-7xl mx-auto p-2">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">PM Records List</h2>

        @if(session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('message') }}
            </div>
        @endif

        @component('components.table', [
            'searchModel' => 'search',
            'searchInputId' => 'pm-record-search',
            'searchLabel' => 'Search PM records',
            'searchPlaceholder' => 'Search records...',
            'filterModel' => 'sortOrder',
            'filterId' => 'pm-record-sort',
            'filterLabel' => 'Sort PM records by date and time',
            'filterPlaceholder' => 'Sort by date and time',
            'filterOptions' => $sortOptions,
            'filterValue' => $sortOrder,
            'headers' => [
                ['label' => 'Name', 'field' => 'requested_by_name', 'sortable' => true],
                ['label' => 'Office', 'field' => 'office', 'sortable' => true],
                ['label' => 'Position', 'field' => 'position', 'sortable' => true],
                ['label' => 'Conducted By', 'sortable' => false],
                ['label' => 'Date Started', 'field' => 'date_started', 'sortable' => true],
                ['label' => 'RAM / Storage Specs', 'sortable' => false],
                ['label' => 'Actions', 'sortable' => false],
            ],
        ])
            @slot('slotBody')
                @forelse($records as $record)
                    <tr wire:key="pm-record-{{ $record->id }}" class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-body">{{ $record->requested_by_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-body">{{ $record->office->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-body">{{ $record->position }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-body">{{ $record->conductedBy?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-body">{{ $record->date_started->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm text-body">
                            @forelse($this->specsForRecord($record) as $spec)
                                <div>{{ $spec }}</div>
                            @empty
                                <span class="text-gray-400">-</span>
                            @endforelse
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-body">
                            <a href="{{ route('pm-record.pdf', ['id' => $record->id]) }}" target="_blank" class="font-medium text-brand hover:underline">PDF</a>
                            <a href="{{ route('pm-form.edit', ['record' => $record->id]) }}" target="_blank" class="ml-3 font-medium text-amber-700 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-body">No records found.</td></tr>
                @endforelse
            @endslot
        @endcomponent

        <x-pagination :paginator="$records" :per-page="$perPage" item-label="records" />
    </div>
</div>