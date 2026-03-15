@extends('admin.layouts.app')

@section('title', __('roles.modal.edit_title', ['name' => ucfirst(str_replace('_', ' ', $role->name))]))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('common.breadcrumb_dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">{{ __('sidebar.roles') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('roles.modal.edit_title', ['name' => ucfirst(str_replace('_', ' ', $role->name))]) }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-pencil-square me-2"></i>{{ __('roles.modal.edit_title', ['name' => ucfirst(str_replace('_', ' ', $role->name))]) }}
            </h5>
        </div>

        <form action="{{ route('admin.roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="roleName" class="form-label">{{ __('roles.modal.name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="roleName" name="name" value="{{ old('name', $role->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('roles.columns.created_at') }}</label>
                        <input type="text" class="form-control" disabled value="{{ $role->created_at->format('M d, Y H:i') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="roleDescription" class="form-label">{{ __('roles.modal.description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="roleDescription" name="description" rows="3" placeholder="{{ __('roles.modal.description_placeholder') }}">{{ old('description', $role->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold fs-5">
                        <i class="bi bi-shield-check me-2"></i>{{ __('roles.modal.assign_permissions') }}
                    </label>
                    <p class="text-muted small mb-3">{{ __('admin.roles.grouped_permissions_help') }}</p>

                    <div class="row">
                        @forelse($allPermissions as $category => $group)
                            <div class="col-md-6 mb-4">
                                <div class="card border-1 h-100">
                                    <div class="card-header bg-light border-bottom">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input permission-category-check" type="checkbox" id="category_{{ $category }}" data-category="{{ $category }}">
                                            <label class="form-check-label fw-bold text-uppercase" for="category_{{ $category }}">
                                                <i class="bi bi-folder-check me-2"></i>{{ $group['label'] }}
                                            </label>
                                            <span class="badge bg-secondary ms-2">{{ count($group['permissions']) }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @foreach($group['permissions'] as $permissionItem)
                                            @php($permission = $permissionItem['model'])
                                            <div class="form-check mb-2">
                                                <input class="form-check-input permission-check category-{{ $category }}" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}" {{ in_array($permission->id, $rolePermissionIds) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="permission_{{ $permission->id }}">
                                                    <span class="badge bg-info text-dark me-1">{{ strtoupper(substr($permissionItem['action'], 0, 1)) }}</span>
                                                    {{ $permissionItem['label'] }}
                                                    <div class="text-muted small">{{ $permission->name }}</div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    {{ __('permissions.none_available') }}
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="alert alert-light border mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <strong>{{ __('admin.roles.total_permissions_assigned') }}</strong>
                                <span class="badge bg-success" id="permissionCount">{{ count($rolePermissionIds) }}</span>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllBtn">{{ __('admin.roles.clear_all') }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light d-flex justify-content-between">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('admin.roles.back_to_roles') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>{{ __('admin.roles.update_role') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryCheckboxes = document.querySelectorAll('.permission-category-check');
    const permissionCheckboxes = document.querySelectorAll('.permission-check');
    const clearAllBtn = document.getElementById('clearAllBtn');
    const permissionCountBadge = document.getElementById('permissionCount');

    categoryCheckboxes.forEach(categoryCheckbox => {
        categoryCheckbox.addEventListener('change', function() {
            const category = this.getAttribute('data-category');
            const categoryPermissions = document.querySelectorAll(`.category-${category}`);

            categoryPermissions.forEach(permissionCheckbox => {
                permissionCheckbox.checked = categoryCheckbox.checked;
            });

            updatePermissionCount();
        });
    });

    permissionCheckboxes.forEach(permissionCheckbox => {
        permissionCheckbox.addEventListener('change', function() {
            updateCategoryCheckboxes();
            updatePermissionCount();
        });
    });

    function updateCategoryCheckboxes() {
        categoryCheckboxes.forEach(categoryCheckbox => {
            const category = categoryCheckbox.getAttribute('data-category');
            const categoryPermissions = document.querySelectorAll(`.category-${category}`);
            const checkedCount = Array.from(categoryPermissions).filter(cb => cb.checked).length;

            categoryCheckbox.checked = checkedCount === categoryPermissions.length && categoryPermissions.length > 0;
            categoryCheckbox.indeterminate = checkedCount > 0 && checkedCount < categoryPermissions.length;
        });
    }

    function updatePermissionCount() {
        const checkedCount = document.querySelectorAll('.permission-check:checked').length;
        permissionCountBadge.textContent = checkedCount;
        permissionCountBadge.className = 'badge ' + (checkedCount > 0 ? 'bg-success' : 'bg-secondary');
    }

    clearAllBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm(@json(__('admin.roles.confirm_clear_permissions')))) {
            permissionCheckboxes.forEach(cb => cb.checked = false);
            categoryCheckboxes.forEach(cb => cb.checked = false);
            updatePermissionCount();
        }
    });

    updateCategoryCheckboxes();
});
</script>
@endpush
@endsection
