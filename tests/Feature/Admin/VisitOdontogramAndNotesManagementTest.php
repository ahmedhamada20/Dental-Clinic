<?php

use App\Models\Medical\OdontogramHistory;
use App\Models\Medical\OdontogramTooth;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use App\Models\Visit\VisitNote;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    foreach ([
        'admin.dashboard.index',
        'admin.appointments.index',
        'admin.waiting-list.index',
        'admin.patients.index',
        'admin.visits.index',
        'admin.service-categories.index',
        'admin.services.index',
        'admin.treatment-plans.index',
        'admin.prescriptions.index',
        'admin.billing.index',
        'admin.promotions.index',
        'admin.reports.index',
        'admin.settings.index',
        'admin.users.index',
        'admin.roles.index',
        'admin.notifications.index',
    ] as $index => $name) {
        Route::get('/__phase6-test/'.($index + 1), fn () => 'ok')->name($name);
    }

    $this->admin = User::query()->create([
        'first_name' => 'Admin',
        'last_name' => 'User',
        'full_name' => 'Admin User',
        'email' => 'phase6-admin@example.com',
        'phone' => '500100100',
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);

    $this->dentist = User::query()->create([
        'first_name' => 'Dina',
        'last_name' => 'Dentist',
        'full_name' => 'Dina Dentist',
        'email' => 'phase6-dentist@example.com',
        'phone' => '500100101',
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'status' => 'active',
    ]);

    $this->patient = Patient::query()->create([
        'patient_code' => 'PAT-6001',
        'first_name' => 'Layla',
        'last_name' => 'Smile',
        'full_name' => 'Layla Smile',
        'phone' => '500200200',
        'email' => 'layla@example.com',
        'password' => Hash::make('secret123'),
        'gender' => 'female',
        'date_of_birth' => '1995-08-10',
        'age' => 30,
        'address' => '123 Dental Street',
        'city' => 'Riyadh',
        'status' => 'active',
        'registered_from' => 'dashboard',
    ]);

    $this->visit = Visit::query()->create([
        'visit_no' => 'VIS-PHASE6-1001',
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->dentist->id,
        'checked_in_by' => $this->admin->id,
        'visit_date' => now()->toDateString(),
        'status' => 'completed',
        'chief_complaint' => 'Pain in upper molar',
        'diagnosis' => 'Caries suspected',
        'clinical_notes' => 'Initial chairside assessment completed',
        'internal_notes' => 'Monitor recovery response',
    ]);
});

it('renders the visit details page with integrated odontogram and notes sections', function () {
    VisitNote::query()->create([
        'visit_id' => $this->visit->id,
        'note_type' => 'clinical',
        'note' => 'General clinical observation',
        'created_by' => $this->admin->id,
    ]);

    OdontogramTooth::query()->create([
        'patient_id' => $this->patient->id,
        'tooth_number' => '16',
        'status' => 'healthy',
        'surface' => 'occlusal',
        'notes' => 'Baseline record',
        'visit_id' => $this->visit->id,
        'last_updated_by' => $this->admin->id,
    ]);

    OdontogramHistory::query()->create([
        'patient_id' => $this->patient->id,
        'tooth_number' => '16',
        'old_status' => null,
        'new_status' => 'healthy',
        'surface' => 'occlusal',
        'notes' => 'Baseline record',
        'visit_id' => $this->visit->id,
        'changed_by' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.visits.show', $this->visit));

    $response->assertOk()
        ->assertSee('Visit Details')
        ->assertSee('Odontogram')
        ->assertSee('Visit Notes')
        ->assertSee('Tooth-Specific Notes &amp; History', false)
        ->assertSee('Current Odontogram')
        ->assertSee('General clinical observation')
        ->assertSee('Baseline record');
});

it('stores a tooth specific visit note for the current visit', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.visits.notes.store', $this->visit), [
        'note_type' => 'clinical',
        'tooth_number' => '26',
        'note' => 'Tooth 26 is tender on percussion.',
    ]);

    $response->assertRedirect(route('admin.visits.show', ['visit' => $this->visit->id, 'tab' => 'notes']));

    $note = VisitNote::query()->where('visit_id', $this->visit->id)->first();

    expect($note)->not->toBeNull()
        ->and($note->tooth_number)->toBe('26')
        ->and($note->note_type)->toBe('clinical')
        ->and($note->note)->toBe('Tooth 26 is tender on percussion.');
});

it('creates an odontogram record and matching history entry for the visit', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.visits.odontogram.store', $this->visit), [
        'tooth_number' => '11',
        'status' => 'healthy',
        'surface' => 'mesial',
        'notes' => 'Enamel intact after polishing.',
    ]);

    $response->assertRedirect(route('admin.visits.show', ['visit' => $this->visit->id, 'tab' => 'odontogram']));

    $tooth = OdontogramTooth::query()
        ->where('patient_id', $this->patient->id)
        ->where('tooth_number', '11')
        ->first();

    $history = OdontogramHistory::query()
        ->where('patient_id', $this->patient->id)
        ->where('tooth_number', '11')
        ->latest('id')
        ->first();

    expect($tooth)->not->toBeNull()
        ->and($tooth->visit_id)->toBe($this->visit->id)
        ->and((string) $tooth->surface)->toBe('mesial')
        ->and((string) $tooth->notes)->toBe('Enamel intact after polishing.')
        ->and($history)->not->toBeNull()
        ->and($history->visit_id)->toBe($this->visit->id)
        ->and($history->new_status)->toBe('healthy');
});

it('shows the dedicated odontogram history page and supports tooth filtering', function () {
    OdontogramHistory::query()->create([
        'patient_id' => $this->patient->id,
        'tooth_number' => '21',
        'old_status' => 'healthy',
        'new_status' => 'crown',
        'surface' => 'distal',
        'notes' => 'Crown fitted successfully.',
        'visit_id' => $this->visit->id,
        'changed_by' => $this->admin->id,
    ]);

    OdontogramHistory::query()->create([
        'patient_id' => $this->patient->id,
        'tooth_number' => '31',
        'old_status' => 'healthy',
        'new_status' => 'implant',
        'surface' => 'lingual',
        'notes' => 'Implant review.',
        'visit_id' => $this->visit->id,
        'changed_by' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.visits.odontogram-history.index', [
        'visit' => $this->visit->id,
        'tooth_number' => '21',
    ]));

    $response->assertOk()
        ->assertSee('Odontogram History')
        ->assertSee('21')
        ->assertSee('Crown fitted successfully.')
        ->assertDontSee('Implant review.');
});

