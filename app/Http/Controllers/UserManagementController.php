<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Only account managers and approvers can manage users
        if (!$user->canManageBills()) {
            abort(403, 'Unauthorized to manage users.');
        }

        $query = User::query();

        // Filter by user type
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('UserManagement/Index', [
            'users' => $users,
            'filters' => $request->only(['user_type', 'is_active', 'search']),
            'canCreate' => $user->isApprover(), // Only approvers can create users
        ]);
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->isApprover()) {
            abort(403, 'Only approvers can create users.');
        }

        return Inertia::render('UserManagement/Create');
    }

    /**
     * Store a newly created user in storage
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isApprover()) {
            abort(403, 'Only approvers can create users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:account_manager,approver',
            'is_active' => 'boolean',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('user-management.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $userManagement)
    {
        $user = Auth::user();
        
        if (!$user->canManageBills()) {
            abort(403, 'Unauthorized to view user details.');
        }

        $userManagement->load(['createdBills', 'approvedBills']);

        return Inertia::render('UserManagement/Show', [
            'user' => $userManagement,
            'canEdit' => $user->isApprover(),
        ]);
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $userManagement)
    {
        $user = Auth::user();
        
        if (!$user->isApprover()) {
            abort(403, 'Only approvers can edit users.');
        }

        return Inertia::render('UserManagement/Edit', [
            'user' => $userManagement,
        ]);
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, User $userManagement)
    {
        $user = Auth::user();
        
        if (!$user->isApprover()) {
            abort(403, 'Only approvers can edit users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userManagement->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'user_type' => 'required|in:account_manager,approver',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'user_type' => $validated['user_type'],
            'is_active' => $validated['is_active'] ?? true,
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $userManagement->update($updateData);

        return redirect()->route('user-management.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(User $userManagement)
    {
        $user = Auth::user();
        
        if (!$user->isApprover()) {
            abort(403, 'Only approvers can delete users.');
        }

        // Prevent self-deletion
        if ($userManagement->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Check if user has created or approved bills
        $billsCount = $userManagement->createdBills()->count() + $userManagement->approvedBills()->count();
        if ($billsCount > 0) {
            return back()->with('error', 'Cannot delete user who has created or approved bills.');
        }

        $userManagement->delete();

        return redirect()->route('user-management.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $userManagement)
    {
        $user = Auth::user();
        
        if (!$user->isApprover()) {
            abort(403, 'Only approvers can change user status.');
        }

        // Prevent self-deactivation
        if ($userManagement->id === $user->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $userManagement->update([
            'is_active' => !$userManagement->is_active
        ]);

        $status = $userManagement->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully.");
    }
}
