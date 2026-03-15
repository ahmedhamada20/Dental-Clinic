<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('User CRUD Operations', function () {

    describe('Index', function () {
        it('displays users index page', function () {
            User::factory(3)->create();

            $response = $this->get(route('admin.users.index'));

            $response->assertStatus(200)
                ->assertViewIs('admin.users.index')
                ->assertViewHas('users')
                ->assertViewHas('specialties');
        });

        it('can search users by name', function () {
            User::factory()->create(['first_name' => 'Ahmed']);
            User::factory()->create(['first_name' => 'Mohamed']);

            $response = $this->get(route('admin.users.index', ['search' => 'Ahmed']));

            $response->assertStatus(200)
                ->assertViewHas('users');
        });

        it('can filter users by status', function () {
            User::factory()->create(['status' => 'active']);
            User::factory()->create(['status' => 'inactive']);

            $response = $this->get(route('admin.users.index', ['status' => 'active']));

            $response->assertStatus(200)
                ->assertViewHas('users');
        });
    });

    describe('Create', function () {
        it('displays user create form', function () {
            $response = $this->get(route('admin.users.create'));

            $response->assertStatus(200)
                ->assertViewIs('admin.users.create')
                ->assertViewHas('statuses')
                ->assertViewHas('userTypes')
                ->assertViewHas('specialties');
        });
    });

    describe('Store', function () {
        it('creates a new user with valid data', function () {
            $data = [
                'first_name' => 'Dr.',
                'last_name' => 'Smith',
                'email' => 'doctor@clinic.com',
                'phone' => '01012345678',
                'user_type' => 'admin',
                'status' => 'active',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ];

            $response = $this->post(route('admin.users.store'), $data);

            $response->assertRedirect(route('admin.users.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('users', [
                'first_name' => 'Dr.',
                'last_name' => 'Smith',
                'email' => 'doctor@clinic.com',
                'phone' => '01012345678',
            ]);
        });

        it('rejects user creation with invalid email', function () {
            $data = [
                'first_name' => 'Dr.',
                'last_name' => 'Smith',
                'email' => 'invalid-email',
                'phone' => '01012345678',
                'user_type' => 'admin',
                'status' => 'active',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ];

            $response = $this->post(route('admin.users.store'), $data);

            $response->assertSessionHasErrors('email');
        });

        it('rejects user creation with non-matching passwords', function () {
            $data = [
                'first_name' => 'Dr.',
                'last_name' => 'Smith',
                'email' => 'doctor@clinic.com',
                'phone' => '01012345678',
                'user_type' => 'admin',
                'status' => 'active',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'DifferentPass123!',
            ];

            $response = $this->post(route('admin.users.store'), $data);

            $response->assertSessionHasErrors('password');
        });

        it('rejects user creation with missing required fields', function () {
            $data = [
                'first_name' => 'Dr.',
                // Missing other required fields
            ];

            $response = $this->post(route('admin.users.store'), $data);

            $response->assertSessionHasErrors(['last_name', 'email', 'phone', 'user_type', 'status', 'password']);
        });

        it('rejects duplicate email', function () {
            User::factory()->create(['email' => 'existing@clinic.com']);

            $data = [
                'first_name' => 'Dr.',
                'last_name' => 'Smith',
                'email' => 'existing@clinic.com',
                'phone' => '01012345678',
                'user_type' => 'admin',
                'status' => 'active',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ];

            $response = $this->post(route('admin.users.store'), $data);

            $response->assertSessionHasErrors('email');
        });
    });

    describe('Edit', function () {
        it('displays user edit form', function () {
            $user = User::factory()->create();

            $response = $this->get(route('admin.users.edit', $user));

            $response->assertStatus(200)
                ->assertViewIs('admin.users.edit')
                ->assertViewHas('user')
                ->assertViewHas('statuses');
        });
    });

    describe('Update', function () {
        it('updates user with valid data', function () {
            $user = User::factory()->create();

            $data = [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'email' => 'updated@clinic.com',
                'phone' => '01098765432',
                'user_type' => 'receptionist',
                'status' => 'active',
            ];

            $response = $this->put(route('admin.users.update', $user), $data);

            $response->assertRedirect(route('admin.users.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'first_name' => 'Updated',
                'phone' => '01098765432',
                'email' => 'updated@clinic.com',
            ]);
        });

        it('persists user changes to database', function () {
            $user = User::factory()->create([
                'first_name' => 'Original',
                'phone' => '01012345678',
            ]);

            $this->put(route('admin.users.update', $user), [
                'first_name' => 'Changed',
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => '01098765432',
                'user_type' => $user->user_type->value,
                'status' => 'active',
            ]);

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'first_name' => 'Changed',
                'phone' => '01098765432',
            ]);
        });

        it('rejects update with invalid email', function () {
            $user = User::factory()->create();

            $data = [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'email' => 'invalid-email',
                'phone' => '01098765432',
                'user_type' => 'admin',
                'status' => 'active',
            ];

            $response = $this->put(route('admin.users.update', $user), $data);

            $response->assertSessionHasErrors('email');
        });
    });

    describe('Delete', function () {
        it('deletes user record', function () {
            $user = User::factory()->create();
            $userId = $user->id;

            $response = $this->delete(route('admin.users.destroy', $user));

            $response->assertRedirect(route('admin.users.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseMissing('users', [
                'id' => $userId,
            ]);
        });

        it('completely removes user from database', function () {
            $user = User::factory()->create();

            $this->delete(route('admin.users.destroy', $user));

            $this->assertDatabaseCount('users', 0);
        });
    });
});

