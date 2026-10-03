<div class="bg-neutral-primary-soft shadow-xs rounded-base border border-default">
    @if(isset($searchModel) || isset($filterModel))
        <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            @if(isset($searchModel))
                <label for="{{ $searchInputId ?? 'table-search' }}" class="sr-only">{{ $searchLabel ?? 'Search table' }}</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                    </div>
                    <input type="search"
                           id="{{ $searchInputId ?? 'table-search' }}"
                           wire:model.live.debounce.300ms="{{ $searchModel }}"
                           placeholder="{{ $searchPlaceholder ?? 'Search...' }}"
                           class="block w-full p-3 ps-10 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                           autocomplete="off">
                </div>
            @endif

            @if(isset($filterModel) && isset($filterOptions))
                <div class="w-full shrink-0 sm:w-64">
                    <label for="{{ $filterId ?? 'table-filter' }}-button" class="sr-only">{{ $filterLabel ?? 'Filter table' }}</label>
                    <x-flowbite-dropdown :id="$filterId ?? 'table-filter'"
                                         :wire-model="$filterModel"
                                         :selected-value="$filterValue ?? ''"
                                         :options="$filterOptions"
                                         :placeholder="$filterPlaceholder ?? 'Filter by'" />
                </div>
            @endif
        </div>
    @endif

    <div class="relative {{ ($overflowVisible ?? false) ? 'overflow-visible' : 'overflow-x-auto' }}">
    <table class="w-full text-sm text-left rtl:text-right text-body divide-y divide-default">
        <thead class="text-xs font-medium text-heading bg-neutral-secondary-medium">
            <tr>
                @if(isset($headers) && is_array($headers))
                    @foreach($headers as $header)
                        @php
                            $label = is_array($header) && isset($header['label']) ? $header['label'] : (is_string($header) ? $header : 'Column');
                            $sortable = isset($header['sortable']) && $header['sortable'];
                            $field = is_array($header) && isset($header['field']) ? $header['field'] : null;
                            $canSort = $sortable && $field && isset($sortField);
                        @endphp
                        <th scope="col" class="px-6 py-3 text-left" @if($canSort && $sortField === $field) aria-sort="{{ $sortAsc ? 'ascending' : 'descending' }}" @endif>
                            @if($canSort)
                                <button type="button" wire:click="sortBy('{{ $field }}')" class="inline-flex items-center gap-1.5 text-left font-medium hover:text-brand focus:outline-none focus:underline">
                                    {{ $label }}
                                    @if($sortField === $field)
                                        <span aria-hidden="true">{{ $sortAsc ? '↑' : '↓' }}</span>
                                    @endif
                                </button>
                            @else
                                {{ $label }}
                            @endif
                        </th>
                    @endforeach
                @elseif(isset($slotHeader))
                    {{ $slotHeader }}
                @else
                    @slot('header')
                    @endslot
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-default">
            @if(isset($rows) && is_iterable($rows))
                @php
                    $displayRows = [];
                    if(isset($rowCallback) && is_callable($rowCallback)) {
                        foreach($rows as $row) {
                            $displayRows[] = call_user_func($rowCallback, $row);
                        }
                    } else {
                        $displayRows = $rows;
                    }
                @endphp
                @foreach($displayRows as $row)
                    @php
                        $rowData = is_array($row) ? $row : (is_object($row) ? get_object_vars($row) : [$row]);
                    @endphp
                    <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                        @foreach($rowData as $cell)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-body">
                                {!! is_array($cell) || is_object($cell) ? json_encode($cell) : $cell !!}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @elseif(isset($slotBody))
                {!! $slotBody !!}
            @else
                @slot('body')
                @endslot
            @endif
        </tbody>
    </table>
    </div>
</div>