<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $query = Role::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $roles = $query->with(['permissions'])->withCount('permissions')->latest()->paginate(15);
        $allPermissions = $this->getGroupedPermissions();

        return view('admin.roles.index', compact('roles', 'allPermissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
            ],
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::create([
            'name' => strtolower(str_replace(' ', '_', $validated['name'])),
            'guard_name' => 'web',
            'description' => $validated['description'] ?? null,
        ]);

        $permissionNames = Permission::whereIn('id', $validated['permissions'] ?? [])->pluck('name')->values()->all();

        if ($permissionNames !== []) {
            $role->syncPermissions($permissionNames);
        }

        $this->auditLogService->log('roles', 'create', $role, null, [
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $permissionNames,
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('admin.messages.roles.created'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $allPermissions = $this->getGroupedPermissions();
        $rolePermissionIds = $role->permissions()->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'allPermissions', 'rolePermissionIds'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing of system roles
        if (in_array($role->name, ['admin', 'super_admin'])) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', __('admin.messages.roles.system_modify_forbidden'));
        }

        $before = [
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $role->permissions()->pluck('name')->sort()->values()->all(),
        ];

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role->id)
            ],
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->update([
            'name' => strtolower(str_replace(' ', '_', $validated['name'])),
            'description' => $validated['description'] ?? null,
        ]);

        $permissionNames = Permission::whereIn('id', $validated['permissions'] ?? [])->pluck('name')->values()->all();
        $role->syncPermissions($permissionNames);

        $this->auditLogService->log('roles', 'update', $role, $before, [
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $permissionNames,
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('admin.messages.roles.updated'));
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        try {
            // Prevent deletion of system roles
            if (in_array($role->name, ['admin', 'super_admin'])) {
                return redirect()
                    ->route('admin.roles.index')
                    ->with('error', __('admin.messages.roles.system_delete_forbidden'));
            }

            // Check if role is assigned to users
            if ($role->users()->count() > 0) {
                return redirect()
                    ->route('admin.roles.index')
                    ->with('error', __('admin.messages.roles.in_use_delete_forbidden'));
            }

            $before = [
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions()->pluck('name')->sort()->values()->all(),
            ];

            $role->delete();

            $this->auditLogService->log('roles', 'delete', Role::class, $before, null);

            return redirect()
                ->route('admin.roles.index')
                ->with('success', __('admin.messages.roles.deleted'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', __('admin.messages.roles.delete_failed', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Get permissions grouped by category.
     */
    private function getGroupedPermissions(): array
    {
        $permissions = Permission::query()->orderBy('name')->get();
        $grouped = [];

        foreach ($permissions as $permission) {
            [$module, $action] = $this->parsePermissionName($permission->name);

            $grouped[$module]['label'] ??= ucfirst(str_replace(['-', '.'], ' ', $module));
            $grouped[$module]['permissions'][] = [
                'model' => $permission,
                'action' => $action,
                'label' => ucfirst(str_replace(['-', '_'], ' ', $action)),
            ];
        }

        ksort($grouped);

        return $grouped;
    }

    private function parsePermissionName(string $permission): array
    {
        if (str_contains($permission, '.')) {
            [$module, $action] = array_pad(explode('.', $permission, 2), 2, 'access');
            return [$module, $action];
        }

        if (str_contains($permission, '_')) {
            $segments = explode('_', $permission, 2);
            return [$segments[1] ?? $segments[0], $segments[0]];
        }

        if (str_contains($permission, '-')) {
            $segments = explode('-', $permission, 2);
            return [$segments[1] ?? $segments[0], $segments[0]];
        }

        return ['misc', $permission];
    }
}
