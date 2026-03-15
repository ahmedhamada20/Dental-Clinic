<?php

use App\Models\Clinic\MedicalSpecialty;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Support\AdminFeatureTestHelpers;

uses(AdminFeatureTestHelpers::class);

beforeEach(function () {
    $this->seedAdminFeaturePermissions();
    $this->admin = $this->createAdminUser([
        'users.view',
        'users.create',
        'users.edit',
        'users.delete',
        'roles.view',
        'roles.create',
        'roles.edit',
        'roles.delete',
    ]);
});

it('covers users index create store edit update and destroy actions', function () {
    $specialty = MedicalSpecialty::query()->create([
        'name' => 'Surgery',
        'description' => 'Surgery cases',
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)->get(route('admin.users.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.users.create'))->assertOk();

    $phone = (string) random_int(8300000000, 8399999999);

    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'phone' => $phone,
            'user_type' => 'doctor',
            'specialty_id' => $specialty->id,
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('admin.users.index'));

    $user = User::query()->where('email', 'jane.doe@example.com')->firstOrFail();

    $this->actingAs($this->admin)->get(route('admin.users.edit', $user))->assertOk();

    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $user), [
            'first_name' => 'Jane Updated',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'phone' => $phone,
            'user_type' => 'doctor',
            'specialty_id' => $specialty->id,
            'status' => 'active',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($user->fresh()->first_name)->toBe('Jane Updated');

    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $user))
        ->assertRedirect(route('admin.users.index'));

    expect(User::query()->whereKey($user->id)->exists())->toBeFalse();
});

it('prevents deleting the currently authenticated user', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $this->admin))
        ->assertRedirect(route('admin.users.index'));

    expect(User::query()->whereKey($this->admin->id)->exists())->toBeTrue();
});

it('covers roles index and edit pages for authorized users', function () {
    $role = Role::query()->create([
        'name' => 'front_desk',
        'guard_name' => 'web',
        'description' => 'Front desk role',
    ]);

    $this->actingAs($this->admin)->get(route('admin.roles.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.roles.edit', $role))->assertOk();
});

it('prevents modifying protected system roles', function () {
    $systemRole = Role::query()->create([
        'name' => 'admin',
        'guard_name' => 'web',
        'description' => 'System role',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.roles.update', $systemRole), [
            'name' => 'admin updated',
            'description' => 'Should not change',
        ])
        ->assertRedirect(route('admin.roles.index'));

    $this->actingAs($this->admin)
        ->delete(route('admin.roles.destroy', $systemRole))
        ->assertRedirect(route('admin.roles.index'));

    expect(Role::query()->whereKey($systemRole->id)->exists())->toBeTrue();
});

