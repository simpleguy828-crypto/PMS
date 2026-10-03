<x-layouts.app>
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">{{ $user->position ?: 'No position assigned' }}</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">My Profile</h1>
            </div>
            <a href="{{ route('dashboard') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Back to Dashboard</a>
        </div>

        @if (session('message'))
            <div class="mb-5 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('message') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-md border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="profile-name" class="mb-1 block text-sm font-medium text-gray-700">Full name</label>
                        <input id="profile-name" name="name" type="text" required value="{{ old('name', $user->name) }}" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label for="profile-email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                        <input id="profile-email" name="email" type="email" required value="{{ old('email', $user->email) }}" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label for="profile-department" class="mb-1 block text-sm font-medium text-gray-700">Department</label>
                        <input id="profile-department" name="department" type="text" value="{{ old('department', $user->department) }}" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <div>
                        <span class="mb-1 block text-sm font-medium text-gray-700">Position</span>
                        <p class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700">{{ $user->position ?: 'Not assigned' }}</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <h2 class="mb-3 text-sm font-semibold text-gray-900">Change password</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="profile-password" class="mb-1 block text-sm font-medium text-gray-700">New password</label>
                            <input id="profile-password" name="password" type="password" autocomplete="new-password" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        </div>
                        <div>
                            <label for="profile-password-confirmation" class="mb-1 block text-sm font-medium text-gray-700">Confirm new password</label>
                            <input id="profile-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-5">
                    <p class="text-sm text-gray-500">Position: <span class="font-medium text-gray-700">{{ $user->position ?: 'Not assigned' }}</span></p>
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Save profile</button>
                </div>
            </form>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-5">
                @if($user->isAdmin())
                    <a href="{{ route('admin.accounts') }}" class="text-sm font-medium text-indigo-700 hover:underline">Account Manager</a>
                @else
                    <span></span>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Logout</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
