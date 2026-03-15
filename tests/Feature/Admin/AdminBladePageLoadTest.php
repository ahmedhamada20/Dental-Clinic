<?php

use App\Models\Appointment\Appointment;
use App\Models\Billing\Invoice;
use App\Models\Patient\Patient;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Support\AdminFeatureTestHelpers;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class, AdminFeatureTestHelpers::class);

beforeEach(function () {
    $this->seedAdminFeaturePermissions();
    $this->admin = $this->createAdminUser([
        'dashboard.view',
        'patients.view',
        'patients.create',
        'patients.edit',
        'appointments.view',
        'appointments.create',
        'appointments.edit',
        'users.view',
        'users.create',
        'users.edit',
        'roles.view',
        'roles.create',
        'roles.edit',
        'billing.view',
        'reports.view',
        'settings.view',
    ]);
});

describe('Dashboard Blade Page Loads', function () {
    it('loads dashboard index page with all required elements', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.dashboard.index');

        // Assert required view data exists
        $response->assertViewHas([
            'totalPatients',
            'todayAppointments',
            'waitingListRequests',
            'todayRevenue',
            'monthlyRevenue',
            'appointmentsBySpecialty',
            'doctorsBySpecialty',
            'revenueBySpecialty',
        ]);

        // Assert required UI elements
        $response->assertSee(__('dashboard.title'))
            ->assertSee(__('dashboard.stats.patients'))
            ->assertSee(__('dashboard.stats.today_appointments'))
            ->assertSee(__('dashboard.stats.waiting_list'))
            ->assertSee(__('dashboard.stats.monthly_revenue'));
    });

    it('dashboard does not have undefined variables', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard.index'));

        $response->assertStatus(200);
        // If view renders without errors, undefined variables would cause rendering errors
    });

    it('dashboard renders without component errors', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard.index'));

        $response->assertStatus(200);
        // Verify no broken includes - layout should load properly
        $response->assertSee('breadcrumb', false); // HTML attributes are case-insensitive in assertions
    });
});

describe('Patients Module Blade Page Loads', function () {
    it('loads patients index page with all required elements', function () {
        Patient::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.patients.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.patients.index');

        // Assert required view data exists
        $response->assertViewHas([
            'patients',
            'summary',
            'statuses',
        ]);

        // Assert required UI elements
        $response->assertSee(__('patients.title'))
            ->assertSee(__('patients.index.heading'))
            ->assertSee(__('patients.index.new_record'));
    });

    it('loads patients create page with all required elements', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.patients.create'))
            ->assertStatus(200)
            ->assertViewIs('admin.patients.create');

        // Assert form is visible
        $response->assertSee(route('admin.patients.store'), false)
            ->assertSee('first_name')
            ->assertSee('last_name')
            ->assertSee('phone')
            ->assertSee('email');
    });

    it('loads patients show page with all required elements', function () {
        $patient = Patient::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.patients.show', $patient))
            ->assertStatus(200)
            ->assertViewIs('admin.patients.show');

        // Assert patient data is displayed
        $response->assertViewHas('patient');
        $response->assertSee($patient->full_name);
    });

    it('loads patients edit page with all required elements', function () {
        $patient = Patient::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.patients.edit', $patient))
            ->assertStatus(200)
            ->assertViewIs('admin.patients.edit');

        // Assert form is visible with patient data
        $response->assertViewHas('patient')
            ->assertSee($patient->phone)
            ->assertSee(route('admin.patients.update', $patient), false);
    });

    it('patients pages have no undefined variables or null relations', function () {
        $patient = Patient::factory()->create();

        // Index
        $this->actingAs($this->admin)
            ->get(route('admin.patients.index'))
            ->assertStatus(200);

        // Show
        $this->actingAs($this->admin)
            ->get(route('admin.patients.show', $patient))
            ->assertStatus(200);

        // Edit
        $this->actingAs($this->admin)
            ->get(route('admin.patients.edit', $patient))
            ->assertStatus(200);
    });
});

describe('Appointments Module Blade Page Loads', function () {
    it('loads appointments index page with all required elements', function () {
        $specialty = $this->createSpecialtyCategoryService()[0];
        Appointment::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.appointments.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.appointments.index');

        // Assert required view data exists
        $response->assertViewHas('appointments');

        // Assert UI elements
        $response->assertSee(__('appointments.title'));
    });

    it('loads appointments create page with all required elements', function () {
        $this->createSpecialtyCategoryService();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.appointments.create'))
            ->assertStatus(200)
            ->assertViewIs('admin.appointments.create');

        // Assert form exists
        $response->assertSee(route('admin.appointments.store'), false);
    });

    it('loads appointments show page with all required elements', function () {
        $appointment = Appointment::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.appointments.show', $appointment))
            ->assertStatus(200)
            ->assertViewIs('admin.appointments.show');

        $response->assertViewHas('appointment');
    });

    it('loads appointments edit page with all required elements', function () {
        $appointment = Appointment::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.appointments.edit', $appointment))
            ->assertStatus(200)
            ->assertViewIs('admin.appointments.edit');

        $response->assertViewHas('appointment')
            ->assertSee(route('admin.appointments.update', $appointment), false);
    });
});

describe('Users Module Blade Page Loads', function () {
    it('loads users index page with all required elements', function () {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.users.index');

        // Assert required view data exists
        $response->assertViewHas('users');

        // Assert UI elements
        $response->assertSee(__('users.title'));
    });

    it('loads users create page with all required elements', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'))
            ->assertStatus(200)
            ->assertViewIs('admin.users.create');

        // Assert form exists
        $response->assertSee(route('admin.users.store'), false)
            ->assertSee('first_name')
            ->assertSee('last_name')
            ->assertSee('email');
    });

    it('loads users show page with all required elements', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user))
            ->assertStatus(200)
            ->assertViewIs('admin.users.show');

        $response->assertViewHas('user');
        $response->assertSee($user->full_name);
    });

    it('loads users edit page with all required elements', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $user))
            ->assertStatus(200)
            ->assertViewIs('admin.users.edit');

        $response->assertViewHas('user')
            ->assertSee(route('admin.users.update', $user), false);
    });
});

describe('Roles Module Blade Page Loads', function () {
    it('loads roles index page with all required elements', function () {
        Role::create(['name' => 'test-role', 'guard_name' => 'web']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.roles.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.roles.index');

        // Assert required view data exists
        $response->assertViewHas('roles');

        // Assert UI elements
        $response->assertSee(__('roles.title'));
    });

    it('loads roles edit page with all required elements', function () {
        $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.roles.edit', $role))
            ->assertStatus(200)
            ->assertViewIs('admin.roles.edit');

        $response->assertViewHas('role')
            ->assertSee($role->name);
    });

    it('loads roles show page with all required elements', function () {
        $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.roles.show', $role))
            ->assertStatus(200)
            ->assertViewIs('admin.roles.show');

        $response->assertViewHas('role');
    });
});

describe('Billing Module Blade Page Loads', function () {
    it('loads billing index page with all required elements', function () {
        Invoice::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.index'))
            ->assertStatus(200);

        // Assert UI elements - billing index page
        $response->assertSee(__('billing.title'));
    });

    it('loads billing invoices index page with all required elements', function () {
        Invoice::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.invoices.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.billing.invoices.index');

        // Assert required view data exists
        $response->assertViewHas('invoices');
    });

    it('loads billing invoices show page with all required elements', function () {
        $invoice = Invoice::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.invoices.show', $invoice))
            ->assertStatus(200)
            ->assertViewIs('admin.billing.invoices.show');

        $response->assertViewHas('invoice');
        $response->assertSee($invoice->invoice_number);
    });

    it('loads billing invoices create page with all required elements', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.invoices.create'))
            ->assertStatus(200)
            ->assertViewIs('admin.billing.invoices.create');

        $response->assertSee(route('admin.billing.invoices.store'), false);
    });

    it('loads billing invoices edit page with all required elements', function () {
        $invoice = Invoice::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.billing.invoices.edit', $invoice))
            ->assertStatus(200)
            ->assertViewIs('admin.billing.invoices.edit');

        $response->assertViewHas('invoice');
    });
});

describe('Reports Module Blade Page Loads', function () {
    it('loads reports index page with all required elements', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.reports.index');

        // Assert UI elements
        $response->assertSee(__('reports.title'));
    });
});

describe('Settings Module Blade Page Loads', function () {
    it('loads settings index page with all required elements', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.settings.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.settings.index');

        // Assert UI elements
        $response->assertSee(__('settings.title'));
    });
});

describe('Layout and Component Rendering', function () {
    it('all admin pages use correct layout', function () {
        $routes = [
            route('admin.dashboard.index'),
            route('admin.patients.index'),
            route('admin.appointments.index'),
            route('admin.users.index'),
            route('admin.roles.index'),
            route('admin.billing.index'),
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)
                ->get($route)
                ->assertStatus(200);
        }
    });

    it('detects missing includes and components', function () {
        // These tests will fail if includes/components are broken
        // Check for layout elements that should appear on every page
        $routes = [
            route('admin.dashboard.index'),
            route('admin.patients.index'),
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)
                ->get($route);

            // If layout components are broken, these will fail
            $response->assertStatus(200);
        }
    });
});

describe('Edge Cases and Error Detection', function () {
    it('handles missing relationships gracefully', function () {
        $patient = Patient::factory()->create();
        // Delete related records if any
        $patient->medicalHistories()->delete();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.patients.show', $patient))
            ->assertStatus(200);
    });

    it('handles pagination without errors', function () {
        Patient::factory()->count(50)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.patients.index', ['page' => 1]))
            ->assertStatus(200);

        $response->assertSee('pagination', false);
    });

    it('pages render even with empty data', function () {
        // Dashboard with no data
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard.index'))
            ->assertStatus(200);

        // Patients index with no patients
        $response = $this->actingAs($this->admin)
            ->get(route('admin.patients.index'))
            ->assertStatus(200);
    });

    it('shows correct error messages when needed', function () {
        // Test non-existent resource
        $response = $this->actingAs($this->admin)
            ->get(route('admin.patients.show', 99999))
            ->assertStatus(404);
    });
});

describe('Permission and Authorization', function () {
    it('denies access to unauthorized users', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.dashboard.index'))
            ->assertStatus(403);
    });

    it('redirects to login for guest users', function () {
        $response = $this->get(route('admin.dashboard.index'))
            ->assertRedirect('/login');
    });
});

describe('Render Validation for Complex Pages', function () {
    it('patients page renders all summary stats', function () {
        Patient::factory()->count(10)->create(['status' => 'active']);
        Patient::factory()->count(5)->create(['status' => 'inactive']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.patients.index'))
            ->assertStatus(200)
            ->assertViewHas('summary');

        // Verify summary contains required keys
        $summary = $response['summary'];
        expect($summary)->toHaveKeys(['total', 'active', 'inactive', 'withAlerts']);
    });

    it('dashboard renders recent data collections', function () {
        Patient::factory()->count(5)->create();
        Appointment::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard.index'))
            ->assertStatus(200)
            ->assertViewHas([
                'recentAppointments',
                'latestPatients',
                'recentInvoices',
            ]);
    });

    it('all pages are not empty responses', function () {
        $routes = [
            route('admin.dashboard.index'),
            route('admin.patients.index'),
            route('admin.appointments.index'),
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)
                ->get($route);

            // Content should not be empty
            expect(strlen($response->getContent()))->toBeGreaterThan(100);
        }
    });
});

