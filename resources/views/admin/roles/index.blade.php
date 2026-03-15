@extends('admin.layouts.app')

@section('title', __('roles.title'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('common.breadcrumb_dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('sidebar.roles') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('admin.roles.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control" placeholder="{{ __('roles.search_placeholder') }}" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> {{ __('common.search') }}
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    @can('roles.create')
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                            <i class="bi bi-plus-circle me-2"></i>{{ __('roles.create_new') }}
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 10%;">{{ __('roles.columns.id') }}</th>
                        <th style="width: 30%;">{{ __('roles.columns.name') }}</th>
                        <th style="width: 25%;">{{ __('roles.columns.permissions') }}</th>
                        <th style="width: 20%;">{{ __('roles.columns.created_at') }}</th>
                        <th style="width: 15%;" class="text-center">{{ __('roles.columns.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td><span class="badge bg-primary">{{ $role->id }}</span></td>
                            <td>
                                <strong>{{ ucfirst(str_replace('_', ' ', $role->name)) }}</strong>
                                @if($role->description)
                                    <br>
                                    <small class="text-muted">{{ $role->description }}</small>
                                @endif
                            </td>
                            <td>
                                @if($role->permissions_count > 0)
                                    <span class="badge bg-info">{{ __('roles.permissions_count', ['count' => $role->permissions_count]) }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('roles.no_permissions') }}</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $role->created_at->format('M d, Y H:i') }}</small></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    @can('roles.edit')
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="{{ __('common.edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                    @can('roles.delete')
                                    @if(!in_array($role->name, ['admin', 'super_admin']))
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ __('roles.confirm_delete') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('common.delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">{{ __('roles.empty') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="card-footer bg-light">
                {{ $roles->links() }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createRoleModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>{{ __('roles.modal.create_title') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="roleName" class="form-label">{{ __('roles.modal.name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="roleName" name="name" placeholder="{{ __('roles.modal.name_placeholder') }}" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="roleDescription" class="form-label">{{ __('roles.modal.description') }}</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="roleDescription" name="description" rows="2" placeholder="{{ __('roles.modal.description_placeholder') }}">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('roles.modal.assign_permissions') }}</label>
                        <div class="row">
                            @forelse($allPermissions as $category => $group)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-light h-100">
                                        <div class="card-header bg-light">
                                            <div class="form-check">
                                                <input class="form-check-input permission-category-check" type="checkbox" id="category_{{ $category }}" data-category="{{ $category }}">
                                                <label class="form-check-label fw-bold" for="category_{{ $category }}">{{ $group['label'] }}</label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @foreach($group['permissions'] as $permissionItem)
                                                @php($permission = $permissionItem['model'])
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input permission-check category-{{ $category }}" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}" {{ old('permissions') && in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="permission_{{ $permission->id }}">
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
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>{{ __('roles.create_new') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($roles as $role)
    <div class="modal fade" id="editRoleModal_{{ $role->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Role: {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editRoleName_{{ $role->id }}" class="form-label">Role Name</label>
                            <input type="text" class="form-control" id="editRoleName_{{ $role->id }}" name="name" value="{{ $role->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="editRoleDescription_{{ $role->id }}" class="form-label">Description</label>
                            <textarea class="form-control" id="editRoleDescription_{{ $role->id }}" name="description" rows="2">{{ $role->description }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="row">
                                @foreach($allPermissions as $category => $group)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-light h-100">
                                            <div class="card-header bg-light">
                                                <div class="form-check">
                                                    <input class="form-check-input permission-category-check" type="checkbox" id="editCategory_{{ $category }}_{{ $role->id }}" data-category="{{ $category }}">
                                                    <label class="form-check-label fw-bold" for="editCategory_{{ $category }}_{{ $role->id }}">{{ $group['label'] }}</label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                @foreach($group['permissions'] as $permissionItem)
                                                    @php($permission = $permissionItem['model'])
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input permission-check category-{{ $category }}" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="editPermission_{{ $permission->id }}_{{ $role->id }}" {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="editPermission_{{ $permission->id }}_{{ $role->id }}">
                                                            {{ $permissionItem['label'] }}
                                                            <div class="text-muted small">{{ $permission->name }}</div>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>{{ __('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryCheckboxes = document.querySelectorAll('.permission-category-check');

    categoryCheckboxes.forEach(categoryCheckbox => {
        categoryCheckbox.addEventListener('change', function() {
            const category = this.getAttribute('data-category');
            const permissionCheckboxes = document.querySelectorAll(`.category-${category}`);

            permissionCheckboxes.forEach(permissionCheckbox => {
                permissionCheckbox.checked = categoryCheckbox.checked;
            });
        });
    });

    const permissionCheckboxes = document.querySelectorAll('.permission-check');

    permissionCheckboxes.forEach(permissionCheckbox => {
        permissionCheckbox.addEventListener('change', function() {
            updateCategoryCheckboxes();
        });
    });

    function updateCategoryCheckboxes() {
        const categoryCheckboxes = document.querySelectorAll('.permission-category-check');

        categoryCheckboxes.forEach(categoryCheckbox => {
            const category = categoryCheckbox.getAttribute('data-category');
            const permissionCheckboxes = document.querySelectorAll(`.category-${category}`);
            const checkedCount = Array.from(permissionCheckboxes).filter(cb => cb.checked).length;

            categoryCheckbox.checked = checkedCount === permissionCheckboxes.length && permissionCheckboxes.length > 0;
            categoryCheckbox.indeterminate = checkedCount > 0 && checkedCount < permissionCheckboxes.length;
        });
    }

    updateCategoryCheckboxes();
});
</script>
@endpush
@endsection
