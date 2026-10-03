<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-6">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-slate-900">Admin Portal</h1>
                <p class="mt-2 text-sm text-slate-600">Manage user access and all PM modules.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                </div>

                <div class="flex items-center justify-between text-sm text-slate-600">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        Remember me
                    </label>
                    <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:underline">User login</a>
                </div>

                <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Sign In as Admin
                </button>
            </form>
        </div>
    </div>
</body>
</html>
