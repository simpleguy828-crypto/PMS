@props([
    'id',
    'wireModel',
    'selectedValue' => null,
    'options' => [],
    'placeholder' => 'Select an option',
    'surface' => 'neutral',
])

@php
    $selectedOption = collect($options)->first(fn ($option) => (string) $option['value'] === (string) $selectedValue);
@endphp

@php
    $surfaceClass = $surface === 'white' ? 'bg-white' : 'bg-neutral-secondary-medium';
@endphp

<div class="relative" x-data>
    <button id="{{ $id }}-button"
            x-ref="trigger"
            data-dropdown-toggle="{{ $id }}-menu"
            aria-haspopup="listbox"
            aria-labelledby="{{ $id }}-label"
            class="inline-flex w-full items-center justify-between gap-3 rounded-base border border-default-medium {{ $surfaceClass }} px-4 py-2.5 text-left text-sm font-medium text-heading shadow-xs hover:bg-neutral-tertiary-medium focus:outline-none focus:ring-4 focus:ring-brand-medium"
            type="button">
        <span>{{ $selectedOption['label'] ?? $placeholder }}</span>
        <svg class="h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
        </svg>
    </button>

    <div id="{{ $id }}-menu" class="absolute left-0 top-full z-50 mt-1 hidden w-full rounded-base border border-default-medium bg-white shadow-lg">
        <ul class="max-h-64 overflow-y-auto p-2 text-sm font-medium text-body" aria-labelledby="{{ $id }}-button" role="listbox">
            @foreach($options as $option)
                <li wire:key="{{ $id }}-option-{{ $loop->index }}">
                    <button type="button"
                            role="option"
                            aria-selected="{{ (string) $option['value'] === (string) $selectedValue ? 'true' : 'false' }}"
                            wire:click="$set('{{ $wireModel }}', '{{ addslashes((string) $option['value']) }}')"
                            x-on:click="$refs.trigger.click()"
                            class="inline-flex w-full items-center rounded p-2 text-left hover:bg-neutral-tertiary-medium hover:text-heading">
                        {{ $option['label'] }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>
</div>
