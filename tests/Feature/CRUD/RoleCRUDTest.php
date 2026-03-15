<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['user_type' => 'admin']);

    $permissions = [
        'roles.view',
        'roles.create',
        'roles.edit',
        'roles.delete',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $this->user->givePermissionTo($permissions);
    $this->actingAs($this->user);
});

describe('Role CRUD Operations', function () {

    describe('Index', function () {
        it('displays roles index page', function () {
            Role::create(['name' => 'role-one', 'guard_name' => 'web']);
            Role::create(['name' => 'role-two', 'guard_name' => 'web']);
            Role::create(['name' => 'role-three', 'guard_name' => 'web']);

            $response = $this->get(route('admin.roles.index'));

            $response->assertStatus(200)
                ->assertViewIs('admin.roles.index')
                ->assertViewHas('roles')
                ->assertViewHas('allPermissions');
        });

        it('can search roles by name', function () {
            Role::create(['name' => 'doctor', 'guard_name' => 'web', 'description' => 'A dentist']);
            Role::create(['name' => 'receptionist', 'guard_name' => 'web', 'description' => 'Reception staff']);

            $response = $this->get(route('admin.roles.index', ['search' => 'doctor']));

            $response->assertStatus(200)
                ->assertViewHas('roles');
        });

        it('can search roles by description', function () {
            Role::create(['name' => 'doctor', 'guard_name' => 'web', 'description' => 'A dentist']);
            Role::create(['name' => 'receptionist', 'guard_name' => 'web', 'description' => 'Reception staff']);

            $response = $this->get(route('admin.roles.index', ['search' => 'dentist']));

            $response->assertStatus(200)
                ->assertViewHas('roles');
        });
    });

    describe('Store', function () {
        it('creates a new role with valid data', function () {
            Permission::create(['name' => 'manage-users', 'guard_name' => 'web']);
            Permission::create(['name' => 'view-reports', 'guard_name' => 'web']);

            $data = [
                'name' => 'Manager',
                'description' => 'A manager role',
                'permissions' => [],
            ];

            $response = $this->post(route('admin.roles.store'), $data);

            $response->assertRedirect(route('admin.roles.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('roles', [
                'name' => 'manager',
                'description' => 'A manager role',
            ]);
        });

        it('creates role with assigned permissions', function () {
            $perm1 = Permission::create(['name' => 'manage-users', 'guard_name' => 'web']);
            $perm2 = Permission::create(['name' => 'view-reports', 'guard_name' => 'web']);

            $data = [
                'name' => 'Manager',
                'description' => 'A manager role',
                'permissions' => [$perm1->id, $perm2->id],
            ];

            $response = $this->post(route('admin.roles.store'), $data);

            $response->assertRedirect(route('admin.roles.index'));

            $role = Role::where('name', 'manager')->first();
            expect($role->permissions()->count())->toBe(2);
        });

        it('rejects role creation with missing name', function () {
            $data = [
                'description' => 'A manager role',
                'permissions' => [],
            ];

            $response = $this->post(route('admin.roles.store'), $data);

            $response->assertSessionHasErrors('name');
        });

        it('rejects duplicate role name', function () {
            Role::create(['name' => 'manager', 'guard_name' => 'web']);

            $data = [
                'name' => 'Manager',
                'description' => 'Another manager role',
                'permissions' => [],
            ];

            $response = $this->post(route('admin.roles.store'), $data);

            $response->assertSessionHasErrors('name');
        });

        it('rejects invalid permission ids', function () {
            $data = [
                'name' => 'Manager',
                'description' => 'A manager role',
                'permissions' => [9999],
            ];

            $response = $this->post(route('admin.roles.store'), $data);

            $response->assertSessionHasErrors('permissions.0');
        });
    });

    describe('Edit', function () {
        it('displays role edit form', function () {
            $role = Role::create(['name' => 'doctor', 'guard_name' => 'web', 'description' => 'A dentist']);

            $response = $this->get(route('admin.roles.edit', $role));

            $response->assertStatus(200)
                ->assertViewIs('admin.roles.edit')
                ->assertViewHas('role')
                ->assertViewHas('allPermissions')
                ->assertViewHas('rolePermissionIds');
        });
    });

    describe('Update', function () {
        it('updates role with valid data', function () {
            $role = Role::create(['name' => 'doctor', 'guard_name' => 'web', 'description' => 'A dentist']);

            $data = [
                'name' => 'Senior Doctor',
                'description' => 'A senior dentist',
                'permissions' => [],
            ];

            $response = $this->put(route('admin.roles.update', $role), $data);

            $response->assertRedirect(route('admin.roles.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('roles', [
                'id' => $role->id,
                'description' => 'A senior dentist',
            ]);
        });

        it('persists role changes to database', function () {
            $role = Role::create(['name' => 'doctor', 'guard_name' => 'web', 'description' => 'A dentist']);

            $this->put(route('admin.roles.update', $role), [
                'name' => 'Senior Doctor',
                'description' => 'Updated description',
                'permissions' => [],
            ]);

            $this->assertDatabaseHas('roles', [
                'id' => $role->id,
                'description' => 'Updated description',
            ]);
        });

        it('prevents editing system roles', function () {
            $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

            $response = $this->put(route('admin.roles.update', $role), [
                'name' => 'Super Admin',
                'description' => 'Updated',
                'permissions' => [],
            ]);

            $response->assertRedirect(route('admin.roles.index'))
                ->assertSessionHas('error');
        });

        it('updates role permissions', function () {
            $perm1 = Permission::create(['name' => 'manage-users', 'guard_name' => 'web']);
            $perm2 = Permission::create(['name' => 'view-reports', 'guard_name' => 'web']);

            $role = Role::create(['name' => 'manager', 'guard_name' => 'web']);
            $role->syncPermissions([$perm1->name]);

            $response = $this->put(route('admin.roles.update', $role), [
                'name' => 'manager',
                'description' => 'Updated',
                'permissions' => [$perm2->id],
            ]);

            $role->refresh();
            expect($role->permissions()->count())->toBe(1);
            expect($role->hasPermissionTo('view-reports'))->toBeTrue();
        });
    });

    describe('Delete', function () {
        it('deletes role record', function () {
            $role = Role::create(['name' => 'doctor', 'guard_name' => 'web']);
            $roleId = $role->id;

            $response = $this->delete(route('admin.roles.destroy', $role));

            $response->assertRedirect(route('admin.roles.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseMissing('roles', [
                'id' => $roleId,
            ]);
        });

        it('removes role completely from database', function () {
            Role::create(['name' => 'doctor', 'guard_name' => 'web']);

            $role = Role::where('name', 'doctor')->first();
            $this->delete(route('admin.roles.destroy', $role));

            $this->assertDatabaseMissing('roles', [
                'name' => 'doctor',
            ]);
        });
    });
});

