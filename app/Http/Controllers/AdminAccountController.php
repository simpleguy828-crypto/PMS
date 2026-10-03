<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function index(): View
    {
        return view('admin.accounts', [
            'users' => User::orderBy('name')->get(),
            'positions' => Position::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'position' => ['required', 'string', 'exists:positions,name'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'position' => $validated['position'],
            'department' => $validated['department'] ?? null,
        ]);

        return back()->with('message', 'Account created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'position' => ['required', 'string', 'exists:positions,name'],
            'department' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $targetPosition = Position::where('name', $validated['position'])->firstOrFail();
        $otherAdminExists = User::where('id', '!=', $user->id)->get()->contains(fn (User $otherUser) => $otherUser->isAdmin());
        if ($user->isAdmin() && ! $targetPosition->hasModuleAccess('account-manager') && ! $otherAdminExists) {
            return back()->withErrors(['position' => 'Assign Account Manager access to another position before changing the last administrator.'])->withInput();
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'position' => $validated['position'],
            'department' => $validated['department'] ?? null,
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        return back()->with('message', 'Account updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['account' => 'You cannot delete your own account.']);
        }

        if ($user->isAdmin() && ! User::where('id', '!=', $user->id)->get()->contains(fn (User $otherUser) => $otherUser->isAdmin())) {
            return back()->withErrors(['account' => 'The last administrator account cannot be deleted.']);
        }

        DB::transaction(function () use ($user) {
            DB::table('pm_records')
                ->where('conducted_by', $user->id)
                ->update(['conducted_by' => null]);

            $user->delete();
        });

        return back()->with('message', 'Account deleted. Existing PM records were retained.');
    }
}
