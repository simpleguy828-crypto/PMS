<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPositionController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('admin.positions', [
            'positions' => Position::withCount('users')->orderBy('name')->get(),
            'moduleOptions' => User::moduleOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:positions,name'],
            'module_access' => ['nullable', 'array'],
            'module_access.*' => ['string', Rule::in(array_keys(User::moduleOptions()))],
        ]);

        Position::create([
            'name' => $validated['name'],
            'module_access' => strcasecmp($validated['name'], 'Admin') === 0
                ? array_keys(User::moduleOptions())
                : array_values(array_unique($validated['module_access'] ?? [])),
        ]);

        return back()->with('message', 'Position created successfully.');
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('positions', 'name')->ignore($position->id)],
            'module_access' => ['nullable', 'array'],
            'module_access.*' => ['string', Rule::in(array_keys(User::moduleOptions()))],
        ]);

        $moduleAccess = strcasecmp($validated['name'], 'Admin') === 0
            ? array_keys(User::moduleOptions())
            : array_values(array_unique($validated['module_access'] ?? []));
        $assignedUsers = User::where('position', $position->name)->get();
        $otherAdminExists = User::where('position', '!=', $position->name)->get()->contains(fn (User $user) => $user->isAdmin());
        if ($position->hasModuleAccess('account-manager') && ! in_array('account-manager', $moduleAccess, true)
            && $assignedUsers->contains(fn (User $user) => $user->isAdmin()) && ! $otherAdminExists) {
            return back()->withErrors(['position' => 'Keep Account Manager access on at least one assigned position.'])->withInput();
        }

        $previousName = $position->name;

        $position->update(['name' => $validated['name'], 'module_access' => $moduleAccess]);
        User::where('position', $previousName)->update(['position' => $position->name]);

        return back()->with('message', 'Position updated successfully.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        if (User::where('position', $position->name)->exists()) {
            return back()->withErrors(['position' => 'Reassign users before deleting this position.']);
        }

        $position->delete();

        return back()->with('message', 'Position deleted successfully.');
    }
}
