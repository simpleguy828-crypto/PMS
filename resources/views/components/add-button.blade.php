{{--
  Usage:
    @component('components.add-button', [
        'wireClick' => 'createOffice',
        'text' => 'Add New Office',
    ])
    @endcomponent
--}}
<button type="button"
        wire:click="{{ $wireClick }}"
        class="inline-flex items-center justify-center text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">
    <svg class="h-4 w-4 me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    {{ $text }}
</button>