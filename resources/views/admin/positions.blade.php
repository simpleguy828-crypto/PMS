<x-layouts.app>
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Position Manager</h1>
                <p class="mt-1 text-sm text-slate-600">Assign module access to positions. Accounts inherit their position permissions; Admin always has full access.</p>
            </div>
            @component('components.add-button', ['drawerId' => 'create-position-drawer', 'text' => 'Create position'])
            @endcomponent
        </div>

        @if (session('message'))
            <div class="mb-5 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('message') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-5 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <section>
            @component('components.table')
                @slot('slotHeader')
                    <th scope="col" class="px-6 py-3">Position</th>
                    <th scope="col" class="px-6 py-3">Module permissions</th>
                    <th scope="col" class="px-6 py-3">Accounts</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                @endslot
                @slot('slotBody')
                    @forelse($positions as $position)
                        <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                            <td class="px-6 py-4 font-semibold text-heading">{{ $position->name }}</td>
                            <td class="max-w-xl px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($position->module_access ?? [] as $module)
                                        <span class="rounded bg-neutral-secondary-medium px-2 py-1 text-xs text-body">{{ $moduleOptions[$module] ?? $module }}</span>
                                    @endforeach
                                    @if(empty($position->module_access))<span class="text-body">No module access</span>@endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-body">{{ $position->users_count }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <button type="button" data-drawer-target="edit-position-{{ $position->id }}" data-drawer-show="edit-position-{{ $position->id }}" data-drawer-placement="right" aria-controls="edit-position-{{ $position->id }}" class="font-medium text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium rounded-base text-sm px-3 py-1.5 me-2 focus:outline-none">Edit</button>
                                <form method="POST" action="{{ route('admin.positions.destroy', $position) }}" class="ml-3 inline" onsubmit="return confirm('Delete this position?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 rounded-base text-sm px-3 py-1.5 focus:outline-none">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-body">No positions created.</td></tr>
                    @endforelse
                @endslot
            @endcomponent
        </section>

        @component('components.flowbite-drawer', ['title' => 'Create position', 'id' => 'create-position-drawer', 'livewire' => false])
                <p class="mb-4 text-sm text-body">Choose which modules this position can access.</p>
                <form method="POST" action="{{ route('admin.positions.store') }}" class="space-y-5">
                    @csrf
                    <label class="block mb-2 text-sm font-medium text-heading">Position name<input required name="name" value="{{ old('name') }}" class="mt-2 block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs"></label>
                    <fieldset>
                        <legend class="mb-2 text-sm font-medium text-heading">Module access</legend>
                        <div class="grid gap-2 rounded-base border border-default-medium bg-white p-3 sm:grid-cols-2">
                            @foreach($moduleOptions as $key => $label)
                                <label class="inline-flex items-center gap-2 text-sm text-body"><input type="checkbox" name="module_access[]" value="{{ $key }}" class="rounded border-default-medium text-brand focus:ring-brand">{{ $label }}</label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div class="flex justify-end pt-2"><button type="submit" class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">Create position</button></div>
                </form>
        @endcomponent

        @foreach($positions as $position)
            @component('components.flowbite-drawer', ['title' => 'Edit position', 'id' => 'edit-position-' . $position->id, 'livewire' => false])
                    <p class="mb-4 text-sm text-body">Changes apply to all accounts assigned to this position.</p>
                    <form method="POST" action="{{ route('admin.positions.update', $position) }}" class="space-y-5">
                        @csrf
                        @method('PUT')
                        <label class="block mb-2 text-sm font-medium text-heading">Position name<input required name="name" value="{{ $position->name }}" class="mt-2 block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs"></label>
                        <fieldset>
                            <legend class="mb-2 text-sm font-medium text-heading">Module access</legend>
                            <div class="grid gap-2 rounded-base border border-default-medium bg-white p-3 sm:grid-cols-2">
                                @foreach($moduleOptions as $key => $label)
                                    <label class="inline-flex items-center gap-2 text-sm text-body"><input type="checkbox" name="module_access[]" value="{{ $key }}" {{ in_array($key, $position->module_access ?? [], true) ? 'checked' : '' }} class="rounded border-default-medium text-brand focus:ring-brand">{{ $label }}</label>
                                @endforeach
                            </div>
                        </fieldset>
                        <div class="flex justify-end pt-2"><button type="submit" class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">Save position</button></div>
                    </form>
            @endcomponent
        @endforeach
    </div>
</x-layouts.app>
