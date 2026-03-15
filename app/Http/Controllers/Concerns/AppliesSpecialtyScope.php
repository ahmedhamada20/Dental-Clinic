<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Appointment\Appointment;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use App\Support\Authorization\SpecialtyDataScope;
use Illuminate\Database\Eloquent\Builder;

trait AppliesSpecialtyScope
{
    protected function scopeAppointments(Builder $query): Builder
    {
        return SpecialtyDataScope::applyToAppointments($query, auth()->user());
    }

    protected function scopeVisits(Builder $query): Builder
    {
        return SpecialtyDataScope::applyToVisits($query, auth()->user());
    }

    protected function scopePatients(Builder $query): Builder
    {
        return SpecialtyDataScope::applyToPatients($query, auth()->user());
    }

    protected function scopeInvoices(Builder $query): Builder
    {
        return SpecialtyDataScope::applyToInvoices($query, auth()->user());
    }

    protected function scopePayments(Builder $query): Builder
    {
        return SpecialtyDataScope::applyToPayments($query, auth()->user());
    }

    protected function currentSpecialtyId(): ?int
    {
        return SpecialtyDataScope::specialtyId(auth()->user());
    }

    protected function isSpecialtyScopeBypassed(): bool
    {
        return SpecialtyDataScope::isBypassed(auth()->user());
    }

    protected function ensureCanAccessAppointment(Appointment $appointment): void
    {
        abort_unless(SpecialtyDataScope::canAccessAppointment($appointment, auth()->user()), 404);
    }

    protected function ensureCanAccessVisit(Visit $visit): void
    {
        abort_unless(SpecialtyDataScope::canAccessVisit($visit, auth()->user()), 404);
    }

    protected function ensureCanAccessPatient(Patient $patient): void
    {
        abort_unless(SpecialtyDataScope::canAccessPatient($patient, auth()->user()), 404);
    }

    protected function ensureCanAccessInvoice(Invoice $invoice): void
    {
        abort_unless(SpecialtyDataScope::canAccessInvoice($invoice, auth()->user()), 404);
    }

    protected function ensureCanAccessPayment(Payment $payment): void
    {
        abort_unless(SpecialtyDataScope::canAccessPayment($payment, auth()->user()), 404);
    }

    protected function scopeUsersBySpecialty(Builder $query): Builder
    {
        if ($this->isSpecialtyScopeBypassed()) {
            return $query;
        }

        $specialtyId = $this->currentSpecialtyId();

        return $specialtyId
            ? $query->where('specialty_id', $specialtyId)
            : $query->whereRaw('1 = 0');
    }

    protected function ensureUserBelongsToCurrentSpecialty(User $user): void
    {
        if ($this->isSpecialtyScopeBypassed()) {
            return;
        }

        abort_unless($user->specialty_id && (int) $user->specialty_id === $this->currentSpecialtyId(), 404);
    }
}

