<x-layouts.app>
    <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Account Manager</h1>
            <p class="text-sm text-slate-600">Manage accounts and assign access through positions.</p>
        </div>
        @component('components.add-button', ['drawerId' => 'create-account-drawer', 'text' => 'Create account'])
        @endcomponent
    </div>

    @if (session('message'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
            {{ session('message') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="space-y-6">
        @component('components.flowbite-drawer', ['title' => 'Create account', 'id' => 'create-account-drawer', 'livewire' => false])
            <p class="mb-4 text-sm text-body">Access comes from the selected position.</p>
            <form method="POST" action="{{ route('admin.accounts.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-medium text-heading">Full name</label>
                    <input type="text" name="name" required class="block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-heading">Email</label>
                    <input type="email" name="email" required class="block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-heading">Position</label>
                    <select name="position" required class="block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs">
                        <option value="" selected disabled>Select a position</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->name }}">{{ $position->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-heading">Department</label>
                    <input type="text" name="department" class="block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-heading">Password</label>
                    <input type="password" name="password" required class="block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-heading">Confirm password</label>
                    <input type="password" name="password_confirmation" required class="block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs" />
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">Create account</button>
                </div>
            </form>
        @endcomponent

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xl font-semibold text-slate-900">User accounts</h2>
            @component('components.table')
                @slot('slotHeader')
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3">Email</th>
                    <th scope="col" class="px-6 py-3">Position and access</th>
                    <th scope="col" class="px-6 py-3">Department</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                @endslot
                @slot('slotBody')
                    @forelse($users as $account)
                        <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                            <td class="px-6 py-4 font-medium text-heading">{{ $account->name }}</td>
                            <td class="px-6 py-4 text-body">{{ $account->email }}</td>
                            <td class="px-6 py-4"><span class="font-medium text-heading">{{ $account->position ?: 'Not assigned' }}</span><span class="mt-1 block text-xs text-body">{{ count($account->assignedPosition?->module_access ?? []) }} modules</span></td>
                            <td class="px-6 py-4 text-body">{{ $account->department ?: '—' }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <button type="button" data-drawer-target="edit-account-{{ $account->id }}" data-drawer-show="edit-account-{{ $account->id }}" data-drawer-placement="right" aria-controls="edit-account-{{ $account->id }}" class="font-medium text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium rounded-base text-sm px-3 py-1.5 me-2 focus:outline-none">Edit</button>
                                @if($account->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.accounts.destroy', $account) }}" class="ml-3 inline" onsubmit="return confirm('Delete this account? Existing PM records will be retained.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 rounded-base text-sm px-3 py-1.5 focus:outline-none">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-body">No user accounts found.</td></tr>
                    @endforelse
                @endslot
            @endcomponent

            @foreach($users as $account)
                @component('components.flowbite-drawer', ['title' => 'Edit account', 'id' => 'edit-account-' . $account->id, 'livewire' => false])
                        <p class="mb-4 text-sm text-body">Account access is inherited from its assigned position.</p>
                        <form method="POST" action="{{ route('admin.accounts.update', $account) }}" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="block text-sm font-medium text-heading">Name<input required name="name" value="{{ $account->name }}" class="mt-2 block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs"></label>
                                <label class="block text-sm font-medium text-heading">Email<input required type="email" name="email" value="{{ $account->email }}" class="mt-2 block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs"></label>
                                <label class="block text-sm font-medium text-heading">Position
                                    <select required name="position" class="mt-2 block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs">
                                        @foreach($positions as $position)<option value="{{ $position->name }}" {{ $account->position === $position->name ? 'selected' : '' }}>{{ $position->name }}</option>@endforeach
                                    </select>
                                </label>
                                <label class="block text-sm font-medium text-heading sm:col-span-2">Department<input name="department" value="{{ $account->department }}" class="mt-2 block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs"></label>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="block text-sm font-medium text-heading">New password<input type="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password" class="mt-2 block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"></label>
                                <label class="block text-sm font-medium text-heading">Confirm password<input type="password" name="password_confirmation" autocomplete="new-password" class="mt-2 block w-full p-3 bg-white border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs"></label>
                            </div>
                            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                                <button type="button" data-drawer-hide="edit-account-{{ $account->id }}" aria-controls="edit-account-{{ $account->id }}" class="text-body bg-neutral-secondary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading focus:ring-4 focus:ring-neutral-tertiary shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">Cancel</button>
                                <button type="submit" class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">Save changes</button>
                            </div>
                        </form>
                @endcomponent
            @endforeach
        </section>

    </div>
</div>
</x-layouts.app>
