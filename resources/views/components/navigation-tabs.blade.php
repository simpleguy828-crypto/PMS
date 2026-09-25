{{-- Reusable Navigation Tabs Component --}}
{{-- Usage: @livewire('navigation-tabs', ['tabs' => $tabs, 'currentTab' => $currentTab]) --}}

<div>
    <!-- Mobile Navigation -->
    <div class="sm:hidden">
        <label for="nav-select" class="sr-only">Select navigation</label>
        <select id="nav-select"
                wire:change="navigateToSection($event.target.value)"
                class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body">
            <option value="">Select Section</option>
            @foreach($tabs as $tab)
                <option value="{{ $tab['route'] }}" {{ $currentTab === $tab['id'] ? 'selected' : '' }}>{{ $tab['label'] }}</option>
            @endforeach
        </select>
    </div>

    <!-- Desktop Navigation Tabs -->
    <div class="hidden sm:flex flex-wrap -mb-px text-sm font-medium w-full">
        @foreach($tabs as $tab)
            <div
                wire:click.prevent="navigateToSection('{{ $tab['route'] }}')"
                class="flex-1 inline-block p-4 border-b-2 {{ $currentTab === $tab['id'] ? 'border-indigo-500' : 'border-gray-200' }} rounded-t-lg {{ $currentTab === $tab['id'] ? 'text-indigo-600' : 'text-gray-500' }} hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer"
            >
                {{ $tab['label'] }}
            </div>
        @endforeach
    </div>
</div>