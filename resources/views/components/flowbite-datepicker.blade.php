@props([
    'id',
    'type' => 'date',
    'value' => '',
    'placeholder' => 'Select date',
    'dateFormat' => 'yyyy-mm-dd',
])

<div class="relative">
    <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3">
        <svg class="h-4 w-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z"/>
        </svg>
    </div>
    <input id="{{ $id }}"
           @if($type === 'date')
               type="text"
               datepicker
               datepicker-autohide
               datepicker-format="{{ $dateFormat }}"
               x-init="window.initFlowbiteDatepicker($el)"
           @else
               type="month"
           @endif
           value="{{ $value }}"
           placeholder="{{ $placeholder }}"
           {{ $attributes->merge(['class' => 'block w-full rounded-base border border-default-medium bg-neutral-secondary-medium py-2.5 pe-3 ps-9 text-sm text-heading shadow-xs placeholder:text-body focus:border-brand focus:ring-brand']) }}>
</div>
