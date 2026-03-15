<?php

use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    app()['cache']->forget('spatie.permission.cache');

    $this->admin = User::query()->create([
        'first_name' => 'Locale',
        'last_name' => 'Admin',
        'full_name' => 'Locale Admin',
        'email' => 'locale-admin@example.com',
        'phone' => '5558001',
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);

    $permissions = [
        'dashboard.view',
        'patients.view',
        'appointments.view',
        'service-categories.view',
        'service-categories.manage',
        'settings.view',
        'roles.view',
        'audit-logs.view',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $this->admin->givePermissionTo($permissions);
});

it('switches language globally and persists locale in session', function () {
    $this->actingAs($this->admin)
        ->get(route('language.switch', ['language' => 'ar']))
        ->assertRedirect()
        ->assertSessionHas('locale', 'ar');

    $this->actingAs($this->admin)
        ->withSession(['locale' => 'ar'])
        ->get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee('dir="rtl"', false);
});

it('renders rtl for arabic and ltr for english on admin layout', function () {
    $this->actingAs($this->admin)
        ->withSession(['locale' => 'ar'])
        ->get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee('dir="rtl"', false);

    $this->actingAs($this->admin)
        ->withSession(['locale' => 'en'])
        ->get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee('dir="ltr"', false);
});

it('loads major admin pages successfully', function () {
    $this->actingAs($this->admin)->get(route('admin.dashboard.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.patients.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.appointments.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.service-categories.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.settings.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.audit-logs.index'))->assertOk();
});

it('supports create update and delete for service categories', function () {
    $specialty = MedicalSpecialty::query()->create([
        'name' => 'General Dentistry',
        'description' => 'General care',
        'is_active' => true,
    ]);

    $storeResponse = $this->actingAs($this->admin)
        ->post(route('admin.service-categories.store'), [
            'medical_specialty_id' => $specialty->id,
            'name_ar' => 'الخدمات العامة',
            'name_en' => 'General Services',
            'is_active' => 1,
            'sort_order' => 10,
        ]);

    $storeResponse->assertRedirect(route('admin.service-categories.index'));

    $category = ServiceCategory::query()->where('name_en', 'General Services')->firstOrFail();

    $updateResponse = $this->actingAs($this->admin)
        ->put(route('admin.service-categories.update', $category), [
            'medical_specialty_id' => $specialty->id,
            'name_ar' => 'خدمات عامة محدثة',
            'name_en' => 'General Services Updated',
            'is_active' => 1,
            'sort_order' => 20,
        ]);

    $updateResponse->assertRedirect(route('admin.service-categories.index'));

    expect($category->fresh()->name_en)->toBe('General Services Updated');

    $deleteResponse = $this->actingAs($this->admin)
        ->delete(route('admin.service-categories.destroy', $category));

    $deleteResponse->assertRedirect(route('admin.service-categories.index'));

    expect(ServiceCategory::query()->whereKey($category->id)->exists())->toBeFalse();
});

it('enforces role permissions for restricted pages', function () {
    $restrictedUser = User::query()->create([
        'first_name' => 'No',
        'last_name' => 'Permission',
        'full_name' => 'No Permission',
        'email' => 'no-permission@example.com',
        'phone' => '5558002',
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);

    $this->actingAs($restrictedUser)
        ->get(route('admin.roles.index'))
        ->assertForbidden();
});

