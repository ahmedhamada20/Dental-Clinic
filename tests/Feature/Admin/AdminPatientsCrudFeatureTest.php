<?php

use App\Models\Patient\Patient;
use Tests\Support\AdminFeatureTestHelpers;

uses(AdminFeatureTestHelpers::class);

beforeEach(function () {
    $this->seedAdminFeaturePermissions();
    $this->admin = $this->createAdminUser([
        'patients.view',
        'patients.create',
        'patients.edit',
        'patients.delete',
        'patients.manage-medical-history',
    ]);
});

it('covers patients index create store show edit update and destroy actions', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.patients.index'))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('admin.patients.create'))
        ->assertOk()
        ->assertSee(route('admin.patients.index'), false);

    $phone = (string) random_int(8100000000, 8199999999);

    $storePayload = [
        'patient_code' => 'PAT-TEST01',
        'first_name' => 'Mina',
        'last_name' => 'Hassan',
        'phone' => $phone,
        'email' => 'mina.hassan@example.com',
        'gender' => 'male',
        'date_of_birth' => now()->subYears(30)->toDateString(),
        'address' => 'Test address',
        'city' => 'Giza',
        'status' => 'active',
        'password' => 'patient12345',
        'password_confirmation' => 'patient12345',
    ];

    $this->actingAs($this->admin)
        ->post(route('admin.patients.store'), $storePayload)
        ->assertRedirect();

    $patient = Patient::query()->where('phone', $phone)->firstOrFail();

    $this->actingAs($this->admin)
        ->get(route('admin.patients.show', $patient))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('admin.patients.edit', $patient))
        ->assertOk()
        ->assertSee(route('admin.patients.show', $patient), false);

    $updatePayload = [
        'patient_code' => 'PAT-TEST01',
        'first_name' => 'Mina Updated',
        'last_name' => 'Hassan',
        'phone' => $phone,
        'email' => 'mina.hassan@example.com',
        'gender' => 'male',
        'date_of_birth' => now()->subYears(30)->toDateString(),
        'address' => 'Updated address',
        'city' => 'Cairo',
        'status' => 'active',
    ];

    $this->actingAs($this->admin)
        ->put(route('admin.patients.update', $patient), $updatePayload)
        ->assertRedirect(route('admin.patients.show', $patient));

    expect($patient->fresh()->first_name)->toBe('Mina Updated');

    $this->actingAs($this->admin)
        ->delete(route('admin.patients.destroy', $patient))
        ->assertRedirect(route('admin.patients.index'));

    expect(Patient::query()->whereKey($patient->id)->exists())->toBeFalse();
});

it('supports patients filter search and pagination actions', function () {
    for ($i = 1; $i <= 18; $i++) {
        $this->createPatient([
            'first_name' => $i === 1 ? 'Searchable' : "Patient{$i}",
            'full_name' => $i === 1 ? 'Searchable Person' : "Patient{$i} Person",
            'status' => $i % 2 === 0 ? 'active' : 'inactive',
        ]);
    }

    $this->actingAs($this->admin)
        ->get(route('admin.patients.index', [
            'search' => 'Searchable',
            'status' => 'inactive',
            'page' => 1,
        ]))
        ->assertOk()
        ->assertSee('Searchable');

    $this->actingAs($this->admin)
        ->get(route('admin.patients.index', ['page' => 1]))
        ->assertOk()
        ->assertSee('page=2', false);
});

