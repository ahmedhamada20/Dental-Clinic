## 🛠️ SPECIFIC CODE FIXES - READY TO IMPLEMENT

This document contains exact code snippets for the most critical fixes needed to support multi-specialty operations.

---

## FIX #1: Remove DENTIST Filter from AppointmentController

**File:** `app/Http/Controllers/Admin/AppointmentController.php`

**Location:** `buildFormData()` method (around line 270)

**CURRENT CODE:**
```php
private function buildFormData(Request $request): array
{
    $selectedSpecialtyId = $request->integer('specialty_id', 0) ?: null;
    
    return [
        'patients' => Patient::query()->orderBy('full_name')->get(),
        'statuses' => AppointmentStatus::cases(),
        'specialties' => MedicalSpecialty::query()->where('is_active', true)->orderBy('name')->get(),
        'doctors' => User::where('user_type', UserType::DENTIST->value) // ❌ HARDCODED
            ->orderBy('full_name')
            ->get(),
        // ... rest of method
    ];
}
```

**FIXED CODE:**
```php
private function buildFormData(Request $request): array
{
    $selectedSpecialtyId = $request->integer('specialty_id', 0) ?: null;
    
    return [
        'patients' => Patient::query()->orderBy('full_name')->get(),
        'statuses' => AppointmentStatus::cases(),
        'specialties' => MedicalSpecialty::query()->where('is_active', true)->orderBy('name')->get(),
        'doctors' => User::query() // ✅ FIXED: Removed hardcoded filter
            ->when($selectedSpecialtyId, fn ($q) => 
                $q->where('specialty_id', $selectedSpecialtyId)
            )
            ->whereIn('user_type', [UserType::DOCTOR->value, UserType::DENTIST->value])
            ->orderBy('full_name')
            ->get(),
        // ... rest of method
    ];
}
```

**Impact:** Doctors assigned to any specialty will now appear in appointments.

---

## FIX #2: Remove DENTIST Filter from VisitController

**File:** `app/Http/Controllers/Admin/VisitController.php`

**Location:** `create()` and `edit()` methods (around lines 85-95)

**CURRENT CODE:**
```php
public function create(): View
{
    return view('admin.visits.create', [
        'patients' => Patient::query()->orderBy('full_name')->get(),
        'doctors' => User::query()->where('user_type', UserType::DENTIST->value)
                        ->orderBy('full_name')->get(), // ❌ HARDCODED
        'appointments' => Appointment::query()->latest('appointment_date')->limit(100)->get(),
    ]);
}

public function edit(Visit $visit): View
{
    return view('admin.visits.edit', [
        'visit' => $visit,
        'patients' => Patient::query()->orderBy('full_name')->get(),
        'doctors' => User::query()->where('user_type', UserType::DENTIST->value)
                        ->orderBy('full_name')->get(), // ❌ HARDCODED
        'appointments' => Appointment::query()->latest('appointment_date')->limit(100)->get(),
    ]);
}
```

**FIXED CODE:**
```php
public function create(): View
{
    return view('admin.visits.create', [
        'patients' => Patient::query()->orderBy('full_name')->get(),
        'doctors' => User::query() // ✅ FIXED: Removed hardcoded filter
                        ->whereIn('user_type', [UserType::DOCTOR->value, UserType::DENTIST->value])
                        ->orderBy('full_name')->get(),
        'appointments' => Appointment::query()->latest('appointment_date')->limit(100)->get(),
    ]);
}

public function edit(Visit $visit): View
{
    return view('admin.visits.edit', [
        'visit' => $visit,
        'patients' => Patient::query()->orderBy('full_name')->get(),
        'doctors' => User::query() // ✅ FIXED: Removed hardcoded filter
                        ->whereIn('user_type', [UserType::DOCTOR->value, UserType::DENTIST->value])
                        ->orderBy('full_name')->get(),
        'appointments' => Appointment::query()->latest('appointment_date')->limit(100)->get(),
    ]);
}
```

**Impact:** Visits can now be assigned to any doctor type, not just dentists.

---

## FIX #3: Fix UserController Specialty Validation

**File:** `app/Http/Controllers/Admin/UserController.php`

**Location:** `store()` method validation (around line 81-86)

**CURRENT CODE:**
```php
$validated = $request->validate([
    'first_name' => 'required|string|max:255',
    'last_name' => 'required|string|max:255',
    'email' => 'required|email|max:255|unique:users,email',
    'phone' => 'required|string|max:20|unique:users,phone',
    'user_type' => ['required', Rule::enum(UserType::class)],
    'specialty_id' => [
        Rule::requiredIf(fn () => $request->input('user_type') === UserType::DENTIST->value), // ❌ HARDCODED
        'nullable',
        Rule::exists('medical_specialties', 'id')->where(fn ($query) => $query->where('is_active', true)),
    ],
    'status' => ['required', Rule::enum(UserStatus::class)],
    'password' => 'required|string|min:8|confirmed',
]);
```

**FIXED CODE:**
```php
$validated = $request->validate([
    'first_name' => 'required|string|max:255',
    'last_name' => 'required|string|max:255',
    'email' => 'required|email|max:255|unique:users,email',
    'phone' => 'required|string|max:20|unique:users,phone',
    'user_type' => ['required', Rule::enum(UserType::class)],
    'specialty_id' => [
        // ✅ FIXED: Allow specialty for any doctor type
        Rule::requiredIf(fn () => in_array(
            $request->input('user_type'), 
            [UserType::DOCTOR->value, UserType::DENTIST->value]
        )),
        'nullable',
        Rule::exists('medical_specialties', 'id')->where(fn ($query) => $query->where('is_active', true)),
    ],
    'status' => ['required', Rule::enum(UserStatus::class)],
    'password' => 'required|string|min:8|confirmed',
]);
```

**Also in `update()` method** - Apply same fix around line 145.

**Impact:** All doctor types can now have specialties assigned.

---

## FIX #4: Add Visit → Specialty Relationship

### Step 1: Create Migration

**File:** `database/migrations/2026_03_12_XXXXXX_add_specialty_to_visits_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->unsignedBigInteger('specialty_id')->nullable()->after('doctor_id');
            $table->index('specialty_id', 'visits_specialty_id_index');
            $table->foreign('specialty_id', 'visits_specialty_id_foreign')
                ->references('id')
                ->on('medical_specialties')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign('visits_specialty_id_foreign');
            $table->dropIndex('visits_specialty_id_index');
            $table->dropColumn('specialty_id');
        });
    }
};
```

### Step 2: Update Visit Model

**File:** `app/Models/Visit/Visit.php`

Add to fillable array:
```php
protected $fillable = [
    'visit_no',
    'appointment_id',
    'patient_id',
    'doctor_id',
    'specialty_id', // ✅ ADD THIS
    'checked_in_by',
    // ... rest of fillable
];
```

Add relationship method:
```php
/**
 * The medical specialty this visit belongs to.
 */
public function specialty(): BelongsTo
{
    return $this->belongsTo(MedicalSpecialty::class);
}
```

### Step 3: Update VisitController

**File:** `app/Http/Controllers/Admin/VisitController.php`

In `store()` method:
```php
public function store(Request $request): RedirectResponse
{
    $validated = $this->validateVisit($request);
    
    // ✅ Add specialty_id from selected doctor or appointment
    $doctor = User::find($validated['doctor_id']);
    $validated['specialty_id'] = $doctor->specialty_id;
    
    $visit = Visit::query()->create($validated);

    return redirect()
        ->route('admin.visits.show', $visit)
        ->with('success', __('admin.messages.visits.created'));
}
```

**Impact:** All visits now track which specialty they belong to.

---

## FIX #5: Add Treatment Plans CRUD Routes

**File:** `routes/web.php`

**CURRENT:** Only has index and show routes

**ADD THESE ROUTES:**
```php
Route::middleware(['auth', 'can:treatment-plans.view'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/treatment-plans', [TreatmentPlanController::class, 'index'])
            ->middleware('can:treatment-plans.view')
            ->name('treatment-plans.index');
        
        // ✅ ADD CREATE
        Route::get('/treatment-plans/create', [TreatmentPlanController::class, 'create'])
            ->middleware('can:treatment-plans.manage')
            ->name('treatment-plans.create');
        
        // ✅ ADD STORE
        Route::post('/treatment-plans', [TreatmentPlanController::class, 'store'])
            ->middleware('can:treatment-plans.manage')
            ->name('treatment-plans.store');
        
        Route::get('/treatment-plans/{treatmentPlan}', [TreatmentPlanController::class, 'show'])
            ->middleware('can:treatment-plans.view')
            ->name('treatment-plans.show');
        
        // ✅ ADD EDIT
        Route::get('/treatment-plans/{treatmentPlan}/edit', [TreatmentPlanController::class, 'edit'])
            ->middleware('can:treatment-plans.manage')
            ->name('treatment-plans.edit');
        
        // ✅ ADD UPDATE
        Route::put('/treatment-plans/{treatmentPlan}', [TreatmentPlanController::class, 'update'])
            ->middleware('can:treatment-plans.manage')
            ->name('treatment-plans.update');
        
        // ✅ ADD DELETE
        Route::delete('/treatment-plans/{treatmentPlan}', [TreatmentPlanController::class, 'destroy'])
            ->middleware('can:treatment-plans.manage')
            ->name('treatment-plans.destroy');
    });
```

**Impact:** Treatment Plans now have full CRUD operations.

---

## FIX #6: Add Missing Status Field to User Create Form

**File:** `resources/views/admin/users/create.blade.php`

**LOCATION:** Around line 55 (after user_type select)

**ADD THIS:**
```blade
<div class="col-md-4">
    <label class="form-label">{{ __('common.status') }}</label>
    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
        @foreach ($statuses as $status)
            <option value="{{ $status->value }}" @selected(old('status', 'active') === $status->value)>
                {{ ucfirst($status->value) }}
            </option>
        @endforeach
    </select>
    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
```

**Impact:** Users form now matches controller validation.

---

## FIX #7: Create Specialties Translation File

**File:** `resources/lang/en/specialties.php` (NEW)

```php
<?php

return [
    'title' => 'Medical Specialties',
    'create_title' => 'Create Specialty',
    'edit_title' => 'Edit Specialty',
    
    'columns' => [
        'name' => 'Specialty Name',
        'description' => 'Description',
        'doctors' => 'Doctors',
        'categories' => 'Service Categories',
        'status' => 'Status',
        'actions' => 'Actions',
    ],
    
    'fields' => [
        'name' => 'Specialty Name',
        'description' => 'Description',
        'icon' => 'Icon (optional)',
        'is_active' => 'Active',
    ],
    
    'actions' => [
        'create' => 'Create Specialty',
        'edit' => 'Edit Specialty',
        'delete' => 'Delete',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
    ],
    
    'messages' => [
        'created' => 'Specialty created successfully.',
        'updated' => 'Specialty updated successfully.',
        'deleted' => 'Specialty deleted successfully.',
        'activated' => 'Specialty activated successfully.',
        'deactivated' => 'Specialty deactivated successfully.',
        'not_found' => 'Specialty not found.',
    ],
    
    'placeholders' => [
        'search' => 'Search by name or description',
    ],
];
```

**Also create:** `resources/lang/ar/specialties.php` with Arabic translations

**Impact:** Specialties module fully localized.

---

## FIX #8: Update User Create/Edit JavaScript Toggle

**File:** `resources/views/admin/users/create.blade.php` and `edit.blade.php`

**CURRENT CODE (Line ~98):**
```javascript
const toggle = () => {
    wrapper.style.display = typeEl.value === 'dentist' ? '' : 'none'; // ❌ HARDCODED STRING
};
```

**FIXED CODE:**
```javascript
const toggle = () => {
    wrapper.style.display = typeEl.value === '{{ \App\Enums\UserType::DENTIST->value }}' ? '' : 
                           (typeEl.value === '{{ \App\Enums\UserType::DOCTOR->value }}' ? '' : 'none');
};
```

**Or better, use a data attribute approach:**
```blade
<select name="user_type" id="user_type" class="form-select @error('user_type') is-invalid @enderror" 
        data-doctor-types="{{ json_encode([UserType::DOCTOR->value, UserType::DENTIST->value]) }}" required>
```

```javascript
const toggle = () => {
    const doctorTypes = JSON.parse(typeEl.getAttribute('data-doctor-types'));
    wrapper.style.display = doctorTypes.includes(typeEl.value) ? '' : 'none';
};
```

**Impact:** No more hardcoded enum values in JavaScript.

---

## FIX #9: Update Appointments Form Doctor Display

**File:** `resources/views/admin/appointments/_form.blade.php`

**LOCATION:** Around line 70

**CURRENT CODE:**
```blade
@foreach ($doctors as $doctor)
    <option value="{{ $doctor->id }}" @selected((string) $selectedDoctorId === (string) $doctor->id)>
        {{ $doctor->display_name }}
    </option>
@endforeach
```

**FIXED CODE:**
```blade
@foreach ($doctors as $doctor)
    <option value="{{ $doctor->id }}" 
        data-specialty="{{ $doctor->specialty_id }}"
        @selected((string) $selectedDoctorId === (string) $doctor->id)>
        {{ $doctor->display_name }} 
        @if($doctor->specialty)
            ({{ $doctor->specialty->name }})
        @endif
    </option>
@endforeach
```

**Impact:** Users can see which specialty each doctor is assigned to.

---

## SUMMARY OF CHANGES

| Fix # | Priority | Files | Effort | Lines Changed |
|-------|----------|-------|--------|---------------|
| 1 | CRITICAL | AppointmentController.php | 15 min | ~5 lines |
| 2 | CRITICAL | VisitController.php | 15 min | ~4 lines |
| 3 | CRITICAL | UserController.php | 10 min | ~3 lines |
| 4 | CRITICAL | 3 files (migration, model, controller) | 45 min | ~15 lines |
| 5 | CRITICAL | routes/web.php | 30 min | ~20 lines |
| 6 | HIGH | users/create.blade.php | 10 min | ~7 lines |
| 7 | HIGH | 2 lang files (NEW) | 30 min | ~50 lines |
| 8 | HIGH | create/edit.blade.php | 15 min | ~5 lines |
| 9 | MEDIUM | appointments/_form.blade.php | 10 min | ~3 lines |

**Total Estimated Effort:** 3-4 hours for all critical fixes

---

## IMPLEMENTATION ORDER

1. **First:** Fixes 1, 2, 3 (Remove hardcoded filters - most impactful)
2. **Second:** Fix 4 (Add visit → specialty relationship)
3. **Third:** Fix 5 (Enable treatment plans CRUD)
4. **Fourth:** Fixes 6, 7, 8, 9 (Complete forms and translations)
5. **Finally:** Test all workflows and verify multi-specialty support

---

**Generated:** March 12, 2026  
**Status:** Ready for Implementation  
**Tested:** No (requires QA after implementation)

