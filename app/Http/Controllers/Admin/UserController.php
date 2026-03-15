<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query()->with('specialty');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // User type filter
        if ($request->has('user_type') && $request->user_type) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->integer('specialty_id'));
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $specialties = MedicalSpecialty::query()->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'specialties'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $statuses = UserStatus::cases();
        $userTypes = UserType::cases();
        $specialties = MedicalSpecialty::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.users.create', compact('statuses', 'userTypes', 'specialties'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'user_type' => ['required', Rule::enum(UserType::class)],
            'specialty_id' => [

                Rule::exists('medical_specialties', 'id')->where(fn($query) => $query->where('is_active', true)),
            ],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create full name
        $validated['full_name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        if ($roleName = $this->roleNameForUserType($user->user_type)) {
            $user->syncRoles([$roleName]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $statuses = UserStatus::cases();
        $userTypes = UserType::cases();
        $specialties = MedicalSpecialty::query()
            ->where('is_active', true)
            ->get();

        return view('admin.users.edit', compact('user', 'statuses', 'userTypes', 'specialties'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id)
            ],
            'user_type' => ['required', Rule::enum(UserType::class)],
            'specialty_id' => [
            'nullable',
                Rule::exists('medical_specialties', 'id')->where(fn($query) => $query->where('is_active', true)),
            ],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update full name
        $validated['full_name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);


        // Hash password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($roleName = $this->roleNameForUserType($user->user_type)) {
            $user->syncRoles([$roleName]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Prevent deletion of the current user
            if ($user->id === auth()->id()) {
                return redirect()
                    ->route('admin.users.index')
                    ->with('error', 'You cannot delete your own user account.');
            }

            $user->delete();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Unable to delete user. They may have related records.');
        }
    }

    private function roleNameForUserType(UserType|string $userType): ?string
    {
        $typeValue = $userType instanceof UserType ? $userType->value : (string) $userType;

        return match ($typeValue) {
            UserType::ADMIN->value => 'admin',
            UserType::DOCTOR->value => 'doctor',
            UserType::RECEPTIONIST->value => 'receptionist',
            UserType::ASSISTANT->value => 'assistant',
            default => null,
        };
    }
}
