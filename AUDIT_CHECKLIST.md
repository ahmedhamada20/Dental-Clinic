## ✅ AUDIT FINDINGS - QUICK CHECKLIST

### CRITICAL ISSUES (Block Multi-Specialty)

- [ ] **Hardcoded DENTIST filter in AppointmentController.buildFormData()**
  - File: `app/Http/Controllers/Admin/AppointmentController.php` ~Line 270
  - Current: `User::where('user_type', UserType::DENTIST->value)`
  - Fix: Use specialty-based filtering instead

- [ ] **Hardcoded DENTIST filter in AppointmentController._form.blade.php**
  - File: `resources/views/admin/appointments/_form.blade.php` ~Line 70
  - Current: Same filter applied in view
  - Fix: Dynamic doctor selection based on specialty

- [ ] **Hardcoded DENTIST filter in VisitController**
  - File: `app/Http/Controllers/Admin/VisitController.php` (create, edit methods)
  - Current: `User::where('user_type', UserType::DENTIST->value)`
  - Fix: Remove or make specialty-aware

- [ ] **Visit Model missing Specialty relationship**
  - File: `app/Models/Visit/Visit.php`
  - Missing: specialty_id column, BelongsTo relationship
  - Fix: Add migration + model relationship

- [ ] **Treatment Plans Routes MISSING**
  - File: `routes/web.php`
  - Missing: .create, .edit, .store, .update, .destroy routes
  - Status: TreatmentPlanController has methods but routes don't exist

- [ ] **UserController specialty validation hardcoded to DENTIST**
  - File: `app/Http/Controllers/Admin/UserController.php` ~Line 81
  - Current: `Rule::requiredIf(fn () => $request->input('user_type') === UserType::DENTIST->value)`
  - Fix: Allow specialty for all doctor types

---

### HIGH PRIORITY ISSUES (Translations & Gaps)

- [ ] **Specialties module hardcoded English**
  - Files:
    - `resources/views/admin/specialties/index.blade.php`
    - `resources/views/admin/specialties/create.blade.php`
    - `resources/views/admin/specialties/edit.blade.php`
  - Missing: `resources/lang/en/specialties.php` and `resources/lang/ar/specialties.php`
  - Hardcoded: "Specialties", "Dashboard", "Create", "Edit", "Filter", "Active", "Inactive"

- [ ] **Patients module hardcoded English**
  - Files:
    - `resources/views/admin/patients/_form.blade.php`
    - `resources/views/admin/patients/create.blade.php`
  - Missing: Comprehensive `resources/lang/en/patients.php`
  - Hardcoded: Form labels, card headers, placeholder text

- [ ] **Service Categories module hardcoded English**
  - Files:
    - `resources/views/admin/service-categories/create.blade.php`
    - `resources/views/admin/service-categories/edit.blade.php`
  - Missing: `resources/lang/en/service_categories.php`
  - Hardcoded: Form labels, breadcrumb text

- [ ] **Waiting List missing edit route**
  - File: `routes/web.php`
  - Missing: `admin.waiting-list.edit` route
  - Missing: Controller edit() method implementation

- [ ] **User form missing status field**
  - File: `resources/views/admin/users/create.blade.php`
  - Issue: Controller validates 'status' but form doesn't have select
  - Fix: Add status dropdown to form

---

### MEDIUM PRIORITY ISSUES (Quality & Consistency)

- [ ] **Appointments form doctor filtering incomplete**
  - File: `resources/views/admin/appointments/_form.blade.php` ~Line 70
  - Issue: Doctor list doesn't show specialty assignment
  - Fix: Add data attributes or modify display format

- [ ] **PatientController file categories hardcoded**
  - File: `app/Http/Controllers/Admin/PatientController.php` ~Line 18-23
  - Constant: `DASHBOARD_FILE_CATEGORIES`
  - Fix: Make configurable per specialty

- [ ] **Enum labels missing translation methods**
  - Files: Various enum classes in `app/Enums/`
  - Issue: Some enums might not have label() methods
  - Fix: Verify all have proper translation support

- [ ] **Services module partial translation**
  - Files: `resources/views/admin/services/`
  - Issue: Mix of hardcoded and translated strings
  - Fix: Standardize to use translations consistently

---

### LOW PRIORITY ISSUES (Polish & Enhancement)

- [ ] **Specialties missing delete route**
  - File: `routes/web.php`
  - Status: Controller has destroy() but no route
  - Note: Only activate/deactivate currently available

- [ ] **Prescription standalone create missing**
  - File: `routes/web.php`
  - Status: Only created through visits, not standalone
  - Note: May be intentional design choice

- [ ] **Breadcrumb text hardcoded in various templates**
  - Files: Multiple Blade files
  - Example: "Dashboard", "Create", "Edit"
  - Fix: Extract to translation files for consistency

---

## 📊 TRANSLATION COVERAGE BY MODULE

### ✅ FULLY TRANSLATED (5)
- [ ] Appointments
- [ ] Visits
- [ ] Billing/Invoices
- [ ] Prescriptions
- [ ] Waiting List
- [ ] Roles
- [ ] Settings
- [ ] Audit Logs

### ⚠️ PARTIALLY TRANSLATED (3)
- [ ] Users
- [ ] Services
- [ ] Dashboard

### ❌ HARDCODED - NEEDS TRANSLATION FILES (4)
- [ ] Specialties → Need `specialties.php`
- [ ] Patients → Need improved `patients.php`
- [ ] Service Categories → Need `service_categories.php`
- [ ] Various common strings → Need `admin.php` expansion

---

## 🔧 AFFECTED FILES SUMMARY

### Controllers to Modify
- `app/Http/Controllers/Admin/AppointmentController.php`
- `app/Http/Controllers/Admin/VisitController.php`
- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Admin/PatientController.php`

### Models to Enhance
- `app/Models/Visit/Visit.php` - Add specialty relationship
- `app/Models/Clinic/MedicalSpecialty.php` - Verify relationships

### Views/Blades to Fix
- `resources/views/admin/appointments/_form.blade.php`
- `resources/views/admin/specialties/` (all files)
- `resources/views/admin/patients/` (form files)
- `resources/views/admin/service-categories/` (all files)
- `resources/views/admin/users/create.blade.php`
- `resources/views/admin/users/edit.blade.php`

### Language Files to Create/Update
- `resources/lang/en/specialties.php` (NEW)
- `resources/lang/ar/specialties.php` (NEW)
- `resources/lang/en/service_categories.php` (NEW)
- `resources/lang/ar/service_categories.php` (NEW)
- `resources/lang/en/patients.php` (EXPAND)
- `resources/lang/ar/patients.php` (EXPAND)

### Routes to Add
- `routes/web.php` - Add Treatment Plans CRUD routes
- `routes/web.php` - Add Waiting List edit route
- `routes/web.php` - Add Specialties delete route (optional)

### Migrations to Create
- New migration to add `specialty_id` to visits table
- New migration to add foreign key constraint

---

## 📈 EFFORT ESTIMATE SUMMARY

| Tier | Category | Estimated Hours | Files Affected |
|------|----------|-----------------|-----------------|
| 1 | CRITICAL | 6-8 hours | 8 files |
| 2 | HIGH | 4-6 hours | 6 files |
| 3 | MEDIUM | 3-4 hours | 5 files |
| 4 | LOW | 2-3 hours | 4 files |
| | **TOTAL** | **15-21 hours** | **~25 files** |

---

## 🎯 NEXT STEPS

### Immediate Actions (Week 1)
1. Address Tier 1 Critical Issues
2. Fix hardcoded DENTIST filters
3. Add Visit → Specialty relationship
4. Enable Treatment Plans CRUD

### Follow-up Actions (Week 2)
1. Complete all translation files
2. Implement missing routes
3. Add form field validations
4. Test multi-specialty workflows

### Polish & Testing (Week 3)
1. Verify all forms work with new data
2. Test appointment/visit creation with non-dentist doctors
3. Validate all translations in AR/EN
4. Performance testing and optimization

---

**Generated:** March 12, 2026  
**Status:** Ready for Implementation  
**Last Updated:** In concurrent audit report

