<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        $query = User::with('company');

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by super admin
        if ($request->filled('is_super_admin')) {
            $query->where('is_super_admin', $request->is_super_admin);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20);
        $companies = Company::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'companies'));
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user)
    {
        $companies = Company::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'companies'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'required|in:owner,admin,member',
            'is_super_admin' => 'boolean',
            'password' => 'nullable|string|min:8',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company_id' => $validated['company_id'],
            'role' => $validated['role'],
            'is_super_admin' => $request->boolean('is_super_admin'),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    /**
     * Impersonate a user.
     */
    public function impersonate(User $user)
    {
        // Can't impersonate yourself
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot impersonate yourself.');
        }

        // Can't impersonate another super admin
        if ($user->is_super_admin) {
            return back()->with('error', 'You cannot impersonate another super admin.');
        }

        // Store original user ID in session
        session()->put('impersonator_id', Auth::id());
        session()->put('impersonator_name', Auth::user()->name);

        // Log in as the target user
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', "Now impersonating {$user->name}.");
    }

    /**
     * Stop impersonating and return to original user.
     */
    public function stopImpersonating()
    {
        $impersonatorId = session()->get('impersonator_id');

        if (!$impersonatorId) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not impersonating anyone.');
        }

        $originalUser = User::find($impersonatorId);

        if (!$originalUser) {
            return redirect()->route('dashboard')
                ->with('error', 'Original user not found.');
        }

        // Clear impersonation session
        session()->forget('impersonator_id');
        session()->forget('impersonator_name');

        // Log back in as original user
        Auth::login($originalUser);

        return redirect()->route('admin.users.index')
            ->with('success', 'Stopped impersonating.');
    }
}
