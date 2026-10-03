@props([
    'paginator',
    'perPage',
    'perPageProperty' => 'perPage',
    'perPageOptions' => [10, 25, 50, 100],
    'itemLabel' => 'items',
])

@php
    $currentPage = $paginator->currentPage();
    $lastPage = max(1, $paginator->lastPage());
    $visiblePages = $paginator->getUrlRange(max(1, $currentPage - 2), min($lastPage, $currentPage + 2));
@endphp

<div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-body">
        Showing {{ $paginator->total() === 0 ? 0 : $paginator->firstItem() }}
        to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} {{ $itemLabel }}
    </p>

    <nav aria-label="Page navigation" class="flex flex-wrap items-center gap-3">
        <ul class="flex -space-x-px text-sm">
            <li>
                <button type="button" wire:click="previousPage" @disabled($currentPage <= 1)
                        class="flex h-9 items-center justify-center rounded-s-base border border-default-medium bg-neutral-secondary-medium px-3 text-body shadow-xs hover:bg-neutral-tertiary-medium hover:text-heading focus:outline-none disabled:cursor-not-allowed disabled:opacity-50">
                    Previous
                </button>
            </li>
            @if($currentPage > 3)
                <li><span class="flex h-9 w-9 items-center justify-center border border-default-medium bg-neutral-secondary-medium text-body">...</span></li>
            @endif
            @foreach($visiblePages as $page => $url)
                <li>
                    <button type="button" wire:click="gotoPage({{ $page }})"
                            @if($page === $currentPage) aria-current="page" @endif
                            class="flex h-9 w-9 items-center justify-center border border-default-medium text-sm focus:outline-none {{ $page === $currentPage ? 'bg-neutral-tertiary-medium font-medium text-fg-brand' : 'bg-neutral-secondary-medium text-body hover:bg-neutral-tertiary-medium hover:text-heading' }}">
                        {{ $page }}
                    </button>
                </li>
            @endforeach
            @if($currentPage < $lastPage - 2)
                <li><span class="flex h-9 w-9 items-center justify-center border border-default-medium bg-neutral-secondary-medium text-body">...</span></li>
            @endif
            <li>
                <button type="button" wire:click="nextPage" @disabled($currentPage >= $lastPage)
                        class="flex h-9 items-center justify-center rounded-e-base border border-default-medium bg-neutral-secondary-medium px-3 text-body shadow-xs hover:bg-neutral-tertiary-medium hover:text-heading focus:outline-none disabled:cursor-not-allowed disabled:opacity-50">
                    Next
                </button>
            </li>
        </ul>

        <label class="sr-only" for="{{ $perPageProperty }}-pagination">Items per page</label>
        <select id="{{ $perPageProperty }}-pagination" wire:model.live="{{ $perPageProperty }}"
                class="block w-32 rounded-base border border-default-medium bg-neutral-secondary-medium px-3 py-2.5 text-sm leading-4 text-heading shadow-xs focus:border-brand focus:ring-brand">
            @foreach($perPageOptions as $option)
                <option value="{{ $option }}" @selected((int) $perPage === (int) $option)>{{ $option }} per page</option>
            @endforeach
        </select>
    </nav>
</div>
