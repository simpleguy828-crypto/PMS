<div id="{{ $id ?? 'flowbite-drawer' }}"
     x-data="{ open: $wire.entangle('open') }"
     tabindex="-1"
     :aria-hidden="!open"
     class="overflow-y-auto overflow-x-hidden fixed right-0 top-0 z-50 flex w-96 max-w-full h-screen flex-col p-4 transition-transform duration-300 ease-in-out"
     :class="{ 'hidden translate-x-full': !open, 'translate-x-0': open }"
     @keydown.escape.window="open = false">

    <div class="relative w-full h-full max-w-full">
        <div class="fixed inset-0 -z-10 bg-gray-900/50 backdrop-blur-sm"
             wire:click="closeModal"></div>

        <div class="relative flex h-full w-full max-w-full flex-col overflow-hidden bg-neutral-primary-soft shadow-xs rounded-base">
            <div class="p-4 h-full overflow-y-auto">
                <div class="border-b border-default pb-4 mb-5 flex items-center">
                    <h5 id="{{ $id ?? 'flowbite-drawer' }}-title" class="inline-flex items-center text-lg font-medium text-heading">
                        {{ $icon ?? '' }}
                        {{ $title ?? 'Drawer' }}
                    </h5>
                    <button type="button"
                            wire:click="closeModal"
                            class="text-body bg-transparent hover:text-heading hover:bg-neutral-tertiary rounded-base w-9 h-9 absolute top-2.5 end-2.5 flex items-center justify-center">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                        </svg>
                        <span class="sr-only">Close menu</span>
                    </button>
                </div>

                <div class="text-sm text-body space-y-3">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>