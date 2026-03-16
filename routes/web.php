<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmergencyContactController;
use App\Http\Controllers\Admin\MedicalSpecialtyController;
use App\Http\Controllers\Admin\MedicalFileController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\PatientMedicalHistoryController;
use App\Http\Controllers\Admin\PrescriptionController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TreatmentPlanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitController;
use App\Http\Controllers\Admin\VisitNoteController;
use App\Http\Controllers\Admin\WaitingListController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LanguageController;
use App\Modules\Audit\Controllers\Admin\AuditLogController;
use App\Modules\Billing\Controllers\InvoiceController as BillingInvoiceController;
use App\Modules\Billing\Controllers\PaymentController as BillingPaymentController;
use Illuminate\Support\Facades\Route;

// ========== LANGUAGE SWITCHING ==========
Route::get('/lang/{language}', [LanguageController::class, 'switch'])->name('language.switch');

Route::get('/', function () {
    return view('welcome');
});




Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'can:dashboard.view'])
    ->name('admin.dashboard.index');

Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant'])->group(function () {
    Route::get('/patients', [PatientController::class, 'index'])
        ->middleware('can:patients.view')
        ->name('admin.patients.index');
    Route::get('/patients/create', [PatientController::class, 'create'])
        ->middleware('can:patients.create')
        ->name('admin.patients.create');
    Route::post('/patients', [PatientController::class, 'store'])
        ->middleware('can:patients.create')
        ->name('admin.patients.store');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])
        ->middleware('can:patients.view')
        ->name('admin.patients.show');
    Route::get('/patients/{patient}/edit', [PatientController::class, 'edit'])
        ->middleware('can:patients.edit')
        ->name('admin.patients.edit');
    Route::put('/patients/{patient}', [PatientController::class, 'update'])
        ->middleware('can:patients.edit')
        ->name('admin.patients.update');
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])
        ->middleware('can:patients.delete')
        ->name('admin.patients.destroy');

    Route::post('/patients/{patient}/medical-history', [PatientMedicalHistoryController::class, 'store'])
        ->middleware('can:patients.manage-medical-history')
        ->name('admin.patients.medical-history.store');
    Route::post('/patients/{patient}/emergency-contacts', [EmergencyContactController::class, 'store'])
        ->middleware('can:patients.edit')
        ->name('admin.patients.emergency-contacts.store');
    Route::put('/patients/{patient}/emergency-contacts/{contact}', [EmergencyContactController::class, 'update'])
        ->middleware('can:patients.edit')
        ->name('admin.patients.emergency-contacts.update');
    Route::delete('/patients/{patient}/emergency-contacts/{contact}', [EmergencyContactController::class, 'destroy'])
        ->middleware('can:patients.edit')
        ->name('admin.patients.emergency-contacts.destroy');
    Route::post('/patients/{patient}/medical-files', [MedicalFileController::class, 'store'])
        ->middleware('can:patients.manage-medical-history')
        ->name('admin.patients.medical-files.store');
    Route::delete('/patients/{patient}/medical-files/{file}', [MedicalFileController::class, 'destroy'])
        ->middleware('can:patients.manage-medical-history')
        ->name('admin.patients.medical-files.destroy');
});

Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/appointments/form-options', [AppointmentController::class, 'formOptions'])
        ->middleware('can:appointments.view')
        ->name('appointments.form-options');
    Route::get('/appointments', [AppointmentController::class, 'index'])
        ->middleware('can:appointments.view')
        ->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])
        ->middleware('can:appointments.create')
        ->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])
        ->middleware('can:appointments.create')
        ->name('appointments.store');
    Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
        ->middleware('can:appointments.edit')
        ->name('appointments.status.update');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])
        ->middleware('can:appointments.view')
        ->name('appointments.show');
    Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])
        ->middleware('can:appointments.edit')
        ->name('appointments.edit');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])
        ->middleware('can:appointments.edit')
        ->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])
        ->middleware('can:appointments.delete')
        ->name('appointments.destroy');
});


Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant'])->group(function () {
    Route::get('/waiting-list', [WaitingListController::class, 'index'])
        ->middleware('can:waiting-list.view')
        ->name('admin.waiting-list.index');
    Route::get('/waiting-list/create', [WaitingListController::class, 'create'])
        ->middleware('can:appointments.create')
        ->name('admin.waiting-list.create');
    Route::post('/waiting-list', [WaitingListController::class, 'store'])
        ->middleware('can:appointments.create')
        ->name('admin.waiting-list.store');
    Route::get('/waiting-list/{waitingListRequest}', [WaitingListController::class, 'show'])
        ->middleware('can:waiting-list.view')
        ->name('admin.waiting-list.show');
    Route::post('/waiting-list/{waitingListRequest}/notify', [WaitingListController::class, 'notify'])
        ->middleware('can:appointments.edit')
        ->name('admin.waiting-list.notify');
    Route::post('/waiting-list/{waitingListRequest}/convert', [WaitingListController::class, 'convert'])
        ->middleware('can:appointments.edit')
        ->name('admin.waiting-list.convert');
    Route::post('/waiting-list/{waitingListRequest}/cancel', [WaitingListController::class, 'cancel'])
        ->middleware('can:appointments.edit')
        ->name('admin.waiting-list.cancel');
    Route::delete('/waiting-list/{waitingListRequest}', [WaitingListController::class, 'destroy'])
        ->middleware('can:appointments.edit')
        ->name('admin.waiting-list.destroy');
});

Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant'])->group(function () {
    Route::get('/visits', [VisitController::class, 'index'])->middleware('can:visits.view')->name('admin.visits.index');
    Route::get('/visits/create', [VisitController::class, 'create'])->middleware('can:visits.create')->name('admin.visits.create');
    Route::post('/visits', [VisitController::class, 'store'])->middleware('can:visits.create')->name('admin.visits.store');
    Route::get('/visits/queue/today', [VisitController::class, 'queue'])->middleware('can:visits.view')->name('admin.visits.queue');
    Route::post('/visits/queue/{ticket}/call', [VisitController::class, 'callFromQueue'])->middleware('can:visits.check-in')->name('admin.visits.call-from-queue');
    Route::get('/visits/{visit}', [VisitController::class, 'show'])->middleware('can:visits.view')->name('admin.visits.show');
    Route::get('/visits/{visit}/edit', [VisitController::class, 'edit'])->middleware('can:visits.edit')->name('admin.visits.edit');
    Route::put('/visits/{visit}', [VisitController::class, 'update'])->middleware('can:visits.edit')->name('admin.visits.update');
    Route::delete('/visits/{visit}', [VisitController::class, 'destroy'])->middleware('can:visits.edit')->name('admin.visits.destroy');
    Route::post('/visits/{visit}/start', [VisitController::class, 'start'])->middleware('can:visits.complete')->name('admin.visits.start');
    Route::post('/visits/{visit}/complete', [VisitController::class, 'complete'])->middleware('can:visits.complete')->name('admin.visits.complete');
    Route::post('/visits/{visit}/cancel', [VisitController::class, 'cancel'])->middleware('can:visits.edit')->name('admin.visits.cancel');
    Route::post('/visits/{visit}/notes', [VisitNoteController::class, 'store'])->middleware('can:visits.notes')->name('admin.visits.notes.store');
    Route::put('/visits/{visit}/notes/{visitNote}', [VisitNoteController::class, 'update'])->middleware('can:visits.notes')->name('admin.visits.notes.update');
    Route::delete('/visits/{visit}/notes/{visitNote}', [VisitNoteController::class, 'destroy'])->middleware('can:visits.notes')->name('admin.visits.notes.destroy');
});

// ── Phase 4: Service Categories ──────────────────────────────────────────────
Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant', 'can:specialties.view'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/specialties', [MedicalSpecialtyController::class, 'index'])->middleware('can:specialties.view')->name('specialties.index');
    Route::get('/specialties/create', [MedicalSpecialtyController::class, 'create'])->middleware('can:specialties.manage')->name('specialties.create');
    Route::post('/specialties', [MedicalSpecialtyController::class, 'store'])->middleware('can:specialties.manage')->name('specialties.store');
    Route::get('/specialties/{specialty}', [MedicalSpecialtyController::class, 'show'])->middleware('can:specialties.view')->name('specialties.show');
    Route::post('/specialties/{specialty}/doctors', [MedicalSpecialtyController::class, 'attachDoctor'])->middleware('can:specialties.manage')->name('specialties.doctors.attach');
    Route::get('/specialties/{specialty}/edit', [MedicalSpecialtyController::class, 'edit'])->middleware('can:specialties.manage')->name('specialties.edit');
    Route::put('/specialties/{specialty}', [MedicalSpecialtyController::class, 'update'])->middleware('can:specialties.manage')->name('specialties.update');
    Route::patch('/specialties/{specialty}/activate', [MedicalSpecialtyController::class, 'activate'])->middleware('can:specialties.manage')->name('specialties.activate');
    Route::patch('/specialties/{specialty}/deactivate', [MedicalSpecialtyController::class, 'deactivate'])->middleware('can:specialties.manage')->name('specialties.deactivate');
});

Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant', 'can:service-categories.view'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/service-categories', [ServiceCategoryController::class, 'index'])->middleware('can:service-categories.view')->name('service-categories.index');
    Route::get('/service-categories/create', [ServiceCategoryController::class, 'create'])->middleware('can:service-categories.manage')->name('service-categories.create');
    Route::post('/service-categories', [ServiceCategoryController::class, 'store'])->middleware('can:service-categories.manage')->name('service-categories.store');
    Route::get('/service-categories/{serviceCategory}/edit', [ServiceCategoryController::class, 'edit'])->middleware('can:service-categories.manage')->name('service-categories.edit');
    Route::put('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'update'])->middleware('can:service-categories.manage')->name('service-categories.update');
    Route::delete('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'destroy'])->middleware('can:service-categories.manage')->name('service-categories.destroy');
    Route::patch('/service-categories/{serviceCategory}/activate', [ServiceCategoryController::class, 'activate'])->middleware('can:service-categories.manage')->name('service-categories.activate');
    Route::patch('/service-categories/{serviceCategory}/deactivate', [ServiceCategoryController::class, 'deactivate'])->middleware('can:service-categories.manage')->name('service-categories.deactivate');
});

// ── Phase 4: Services ─────────────────────────────────────────────────────────
Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant', 'can:services.view'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/services', [ServiceController::class, 'index'])->middleware('can:services.view')->name('services.index');
    Route::get('/services/create', [ServiceController::class, 'create'])->middleware('can:services.manage')->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->middleware('can:services.manage')->name('services.store');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->middleware('can:services.view')->name('services.show');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->middleware('can:services.manage')->name('services.edit');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->middleware('can:services.manage')->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->middleware('can:services.manage')->name('services.destroy');
    Route::patch('/services/{service}/activate', [ServiceController::class, 'activate'])->middleware('can:services.manage')->name('services.activate');
    Route::patch('/services/{service}/deactivate', [ServiceController::class, 'deactivate'])->middleware('can:services.manage')->name('services.deactivate');
});

Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant', 'can:treatment-plans.view'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/treatment-plans', [TreatmentPlanController::class, 'index'])->middleware('can:treatment-plans.view')->name('treatment-plans.index');
        Route::get('/treatment-plans/{treatmentPlan}', [TreatmentPlanController::class, 'show'])->middleware('can:treatment-plans.view')->name('treatment-plans.show');
    });


Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/prescriptions', [PrescriptionController::class, 'index'])->middleware('can:prescriptions.view')->name('prescriptions.index');
    Route::get('/prescriptions/create', [PrescriptionController::class, 'create'])->middleware('can:prescriptions.create')->name('prescriptions.create');
    Route::post('/prescriptions', [PrescriptionController::class, 'store'])->middleware('can:prescriptions.create')->name('prescriptions.store');
    Route::get('/prescriptions/patients/{patient}/visits', [PrescriptionController::class, 'visitsByPatient'])->middleware('can:prescriptions.view')->name('prescriptions.patients.visits');
    Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'show'])->middleware('can:prescriptions.view')->name('prescriptions.show');
    Route::get('/prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->middleware('can:prescriptions.edit')->name('prescriptions.edit');
    Route::put('/prescriptions/{prescription}', [PrescriptionController::class, 'update'])->middleware('can:prescriptions.edit')->name('prescriptions.update');
    Route::get('/prescriptions/{prescription}/print', [PrescriptionController::class, 'print'])->middleware('can:prescriptions.view')->name('prescriptions.print');
    Route::get('/patients/{patient}/prescriptions/{prescription}', [PrescriptionController::class, 'showForPatient'])->middleware('can:prescriptions.view')->name('patients.prescriptions.show');
    Route::get('/patients/{patient}/prescriptions/{prescription}/print', [PrescriptionController::class, 'printForPatient'])->middleware('can:prescriptions.view')->name('patients.prescriptions.print');
    Route::get('/patients/{patient}/prescriptions/print-all', [\App\Http\Controllers\Admin\PrescriptionController::class, 'printAllForPatient'])
        ->middleware('can:prescriptions.view')
        ->name('patients.prescriptions.printAll');
});

Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->middleware('can:billing.view')->name('admin.billing.index');
    Route::get('/billing/invoices', [BillingInvoiceController::class, 'index'])->middleware('can:invoices.view')->name('admin.billing.invoices.index');
    Route::get('/billing/patients/{patient}/visits', [BillingInvoiceController::class, 'visitsByPatient'])
        ->middleware('can:invoices.view')
        ->name('admin.billing.patients.visits');
    Route::get('/billing/invoices/create', [BillingInvoiceController::class, 'create'])->middleware('can:invoices.create')->name('admin.billing.invoices.create');
    Route::post('/billing/invoices', [BillingInvoiceController::class, 'store'])->middleware('can:invoices.create')->name('admin.billing.invoices.store');
    Route::get('/billing/invoices/{invoice}', [BillingInvoiceController::class, 'show'])->middleware('can:invoices.view')->name('admin.billing.invoices.show');
    Route::get('/billing/invoices/{invoice}/edit', [BillingInvoiceController::class, 'edit'])->middleware('can:invoices.edit')->name('admin.billing.invoices.edit');
    Route::put('/billing/invoices/{invoice}', [BillingInvoiceController::class, 'update'])->middleware('can:invoices.edit')->name('admin.billing.invoices.update');
    Route::delete('/billing/invoices/{invoice}', [BillingInvoiceController::class, 'destroy'])->middleware('can:invoices.delete')->name('admin.billing.invoices.destroy');
    Route::post('/billing/invoices/{invoice}/items', [BillingInvoiceController::class, 'addItem'])->middleware('can:invoices.edit')->name('admin.billing.invoices.items.store');
    Route::delete('/billing/invoices/{invoice}/items/{item}', [BillingInvoiceController::class, 'deleteItem'])->middleware('can:invoices.delete')->name('admin.billing.invoices.items.destroy');
    Route::post('/billing/invoices/{invoice}/finalize', [BillingInvoiceController::class, 'finalize'])->middleware('can:invoices.edit')->name('admin.billing.invoices.finalize');
    Route::post('/billing/invoices/{invoice}/cancel', [BillingInvoiceController::class, 'cancel'])->middleware('can:invoices.edit')->name('admin.billing.invoices.cancel');
    Route::get('/billing/invoices/{invoice}/print', [BillingInvoiceController::class, 'print'])->middleware('can:invoices.view')->name('admin.billing.invoices.print');

    Route::get('/billing/payments', [BillingPaymentController::class, 'index'])->middleware('can:payments.view')->name('admin.billing.payments.index');
    Route::get('/billing/payments/{payment}', [BillingPaymentController::class, 'show'])->middleware('can:payments.view')->name('admin.billing.payments.show');
    Route::post('/billing/invoices/{invoice}/payments', [BillingPaymentController::class, 'store'])->middleware('can:payments.create')->name('admin.billing.payments.store');
    Route::delete('/billing/payments/{payment}', [BillingPaymentController::class, 'destroy'])->middleware('can:payments.delete')->name('admin.billing.payments.destroy');
});



// ── Phase 4: Promotions ───────────────────────────────────────────────────────
Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant', 'can:promotions.view'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/promotions', [PromotionController::class, 'index'])->middleware('can:promotions.view')->name('promotions.index');
    Route::get('/promotions/create', [PromotionController::class, 'create'])->middleware('can:promotions.manage')->name('promotions.create');
    Route::post('/promotions', [PromotionController::class, 'store'])->middleware('can:promotions.manage')->name('promotions.store');
    Route::get('/promotions/{promotion}', [PromotionController::class, 'show'])->middleware('can:promotions.view')->name('promotions.show');
    Route::get('/promotions/{promotion}/edit', [PromotionController::class, 'edit'])->middleware('can:promotions.manage')->name('promotions.edit');
    Route::put('/promotions/{promotion}', [PromotionController::class, 'update'])->middleware('can:promotions.manage')->name('promotions.update');
    Route::delete('/promotions/{promotion}', [PromotionController::class, 'destroy'])->middleware('can:promotions.manage')->name('promotions.destroy');
    Route::patch('/promotions/{promotion}/activate', [PromotionController::class, 'activate'])->middleware('can:promotions.manage')->name('promotions.activate');
    Route::patch('/promotions/{promotion}/deactivate', [PromotionController::class, 'deactivate'])->middleware('can:promotions.manage')->name('promotions.deactivate');
});

Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant', 'can:notifications.view'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->middleware('can:notifications.view')
        ->name('notifications.index');
    Route::get('/notifications/create', [NotificationController::class, 'create'])
        ->middleware('can:notifications.send')
        ->name('notifications.create');
    Route::post('/notifications', [NotificationController::class, 'store'])
        ->middleware('can:notifications.send')
        ->name('notifications.store');
    Route::get('/notifications/{id}', [NotificationController::class, 'show'])
        ->middleware('can:notifications.view')
        ->name('notifications.show');
    Route::post('/notifications/send-appointment-reminders', [NotificationController::class, 'sendAppointmentReminders'])
        ->middleware('can:notifications.send')
        ->name('notifications.send-appointment-reminders');
    Route::post('/notifications/send-billing-reminders', [NotificationController::class, 'sendBillingReminders'])
        ->middleware('can:notifications.send')
        ->name('notifications.send-billing-reminders');
    Route::post('/notifications/waiting-list/{waitingListRequestId}/notify', [NotificationController::class, 'sendWaitingListNotification'])
        ->middleware('can:notifications.send')
        ->name('notifications.send-waiting-list');
});
Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant', 'can:reports.view'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->middleware('can:reports.view')->name('reports.index');
    Route::post('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::post('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
});

Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant', 'can:settings.view'])->group(function () {
    Route::get('/settings', [SettingController::class, 'index'])->middleware('can:settings.view')->name('admin.settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->middleware('can:settings.edit')->name('admin.settings.update');
});
Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant', 'can:users.view'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->middleware('can:users.view')->name('admin.users.index');
    Route::get('/users/create', [UserController::class, 'create'])->middleware('can:users.create')->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->middleware('can:users.create')->name('admin.users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware('can:users.edit')->name('admin.users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('can:users.edit')->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('can:users.delete')->name('admin.users.destroy');
});


Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('can:roles.view')
        ->name('admin.roles.index');
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('can:roles.create')
        ->name('admin.roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('can:roles.edit')
        ->name('admin.roles.edit');
    Route::match(['put', 'patch'], '/roles/{role}', [RoleController::class, 'update'])
        ->middleware('can:roles.edit')
        ->name('admin.roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('can:roles.delete')
        ->name('admin.roles.destroy');
});

Route::middleware(['auth', 'clinic.role:admin,doctor,receptionist,assistant', 'can:audit-logs.view'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{id}', [AuditLogController::class, 'show'])->name('audit-logs.show');
});
