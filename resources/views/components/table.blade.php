<div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
    <table class="w-full text-sm text-left rtl:text-right text-body divide-y divide-default">
        <thead class="text-xs font-medium text-heading bg-neutral-secondary-medium">
            <tr>
                @if(isset($headers) && is_array($headers))
                    @foreach($headers as $header)
                        @php
                            $label = is_array($header) && isset($header['label']) ? $header['label'] : (is_string($header) ? $header : 'Column');
                            $sortable = isset($header['sortable']) && $header['sortable'];
                        @endphp
                        <th scope="col" class="px-6 py-3 text-left {{ $sortable ? 'cursor-pointer' : '' }}">{{ $label }}</th>
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