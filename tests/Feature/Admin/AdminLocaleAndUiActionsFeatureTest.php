<?php

use App\Models\Patient\Patient;
use Tests\Support\AdminFeatureTestHelpers;

uses(AdminFeatureTestHelpers::class);

beforeEach(function () {
    $this->seedAdminFeaturePermissions();
    $this->admin = $this->createAdminUser([
        'dashboard.view',
        'patients.view',
        'patients.create',
        'patients.edit',
        'appointments.view',
        'service-categories.view',
        'roles.view',
    ]);
});

it('switches locale globally and renders rtl ltr markers on admin pages', function () {
    $this->actingAs($this->admin)
        ->get(route('language.switch', ['language' => 'ar']))
        ->assertRedirect()
        ->assertSessionHas('locale', 'ar');

    $this->actingAs($this->admin)
        ->withSession(['locale' => 'ar'])
        ->get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee('class="rtl"', false);

    $this->actingAs($this->admin)
        ->withSession(['locale' => 'en'])
        ->get(route('admin.patients.index'))
        ->assertOk()
        ->assertSee('dir="ltr"', false)
        ->assertSee('class="ltr"', false);
});

it('supports filter search and pagination actions on patient index', function () {
    for ($i = 1; $i <= 40; $i++) {
        $this->createPatient([
            'first_name' => $i === 1 ? 'Filterable' : "Ui{$i}",
            'full_name' => $i === 1 ? 'Filterable Patient' : "Ui{$i} Patient",
            'status' => $i % 2 === 0 ? 'active' : 'inactive',
        ]);
    }

    $response = $this->actingAs($this->admin)
        ->get(route('admin.patients.index', [
            'search' => 'Ui',
            'page' => 1,
        ]));

    $response->assertOk()
        ->assertSee('Ui')
        ->assertSee('page=2', false);
});

it('renders add edit save update cancel and back actions in major views', function () {
    $patient = Patient::query()->create([
        'patient_code' => 'PAT-UITEST',
        'first_name' => 'Back',
        'last_name' => 'Test',
        'full_name' => 'Back Test',
        'phone' => (string) random_int(8400000000, 8499999999),
        'email' => 'back-test@example.com',
        'password' => bcrypt('patient12345'),
        'gender' => 'female',
        'date_of_birth' => now()->subYears(22)->toDateString(),
        'age' => 22,
        'status' => 'active',
        'registered_from' => 'dashboard',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.patients.index'))
        ->assertOk()
        ->assertSee(route('admin.patients.create'), false)
        ->assertSee(route('admin.patients.edit', $patient), false)
        ->assertSee(route('admin.patients.destroy', $patient), false);

    $this->actingAs($this->admin)
        ->get(route('admin.patients.create'))
        ->assertOk()
        ->assertSee(route('admin.patients.store'), false)
        ->assertSee(route('admin.patients.index'), false);

    $this->actingAs($this->admin)
        ->get(route('admin.patients.edit', $patient))
        ->assertOk()
        ->assertSee(route('admin.patients.update', $patient), false)
        ->assertSee(route('admin.patients.show', $patient), false);
});

