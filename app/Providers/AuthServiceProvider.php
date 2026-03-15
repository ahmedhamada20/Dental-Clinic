<?php

namespace App\Providers;

use App\Models\Appointment\Appointment;
use App\Models\Appointment\WaitingListRequest;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Promotion;
use App\Models\Medical\MedicalFile;
use App\Models\Medical\OdontogramHistory;
use App\Models\Medical\OdontogramTooth;
use App\Models\Medical\Prescription;
use App\Models\Medical\TreatmentPlan;
use App\Models\Patient\Patient;
use App\Models\Visit\Visit;
use App\Models\Visit\VisitNote;
use App\Policies\AppointmentPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\MedicalFilePolicy;
use App\Policies\OdontogramPolicy;
use App\Policies\PatientPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PrescriptionPolicy;
use App\Policies\PromotionPolicy;
use App\Policies\TreatmentPlanPolicy;
use App\Policies\VisitNotePolicy;
use App\Policies\VisitPolicy;
use App\Policies\WaitingListRequestPolicy;
use App\Support\Authorization\PermissionAuthorizer;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Patient Management
        Patient::class => PatientPolicy::class,

        // Appointment Management
        Appointment::class => AppointmentPolicy::class,
        WaitingListRequest::class => WaitingListRequestPolicy::class,

        // Visit Management
        Visit::class => VisitPolicy::class,
        VisitNote::class => VisitNotePolicy::class,

        // Medical Records
        TreatmentPlan::class => TreatmentPlanPolicy::class,
        Prescription::class => PrescriptionPolicy::class,
        MedicalFile::class => MedicalFilePolicy::class,
        OdontogramTooth::class => OdontogramPolicy::class,
        OdontogramHistory::class => OdontogramPolicy::class,

        // Billing
        Invoice::class => InvoicePolicy::class,
        Payment::class => PaymentPolicy::class,
        Promotion::class => PromotionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            $userType = $user->user_type?->value ?? $user->user_type;
            if (in_array((string) $userType, ['admin', 'super_admin'], true)) {
                return true;
            }

            return PermissionAuthorizer::check($user, $ability) ? true : null;
        });
    }
}
