<?php

namespace App\Support\Authorization;

use App\Models\Appointment\Appointment;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Builder;

class SpecialtyDataScope
{
    public static function isBypassed(?User $user): bool
    {
        return $user?->hasRole('admin') ?? false;
    }

    public static function specialtyId(?User $user): ?int
    {
        return $user?->specialty_id ? (int) $user->specialty_id : null;
    }

    public static function applyToAppointments(Builder $query, ?User $user): Builder
    {
       return $query;
    }

    public static function applyToVisits(Builder $query, ?User $user): Builder
    {
        return $query;
    }

    public static function applyToPatients(Builder $query, ?User $user): Builder
    {
        if (self::isBypassed($user)) {
            return $query;
        }
        return $query;
    }




    public static function applyToInvoices(Builder $query, ?User $user): Builder
    {
        if (self::isBypassed($user)) {
            return $query;
        }

        $specialtyId = self::specialtyId($user);

        if (! $specialtyId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $invoiceQuery) use ($specialtyId): void {
            $invoiceQuery
                ->whereHas('visit.doctor', fn (Builder $doctorQuery) => $doctorQuery->where('specialty_id', $specialtyId))
                ->orWhereHas('patient.appointments', fn (Builder $appointmentQuery) => $appointmentQuery->where('specialty_id', $specialtyId));
        });
    }

    public static function applyToPayments(Builder $query, ?User $user): Builder
    {
        if (self::isBypassed($user)) {
            return $query;
        }

        $specialtyId = self::specialtyId($user);

        if (! $specialtyId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('invoice', function (Builder $invoiceQuery) use ($user): void {
            self::applyToInvoices($invoiceQuery, $user);
        });
    }

    public static function canAccessAppointment(Appointment $appointment, ?User $user): bool
    {
        return self::applyToAppointments(Appointment::query(), $user)
            ->whereKey($appointment->getKey())
            ->exists();
    }

    public static function canAccessVisit(Visit $visit, ?User $user): bool
    {
        return self::applyToVisits(Visit::query(), $user)
            ->whereKey($visit->getKey())
            ->exists();
    }

    public static function canAccessPatient(Patient $patient, ?User $user): bool
    {
        return self::applyToPatients(Patient::query(), $user)
            ->whereKey($patient->getKey())
            ->exists();
    }

    public static function canAccessInvoice(Invoice $invoice, ?User $user): bool
    {
        return self::applyToInvoices(Invoice::query(), $user)
            ->whereKey($invoice->getKey())
            ->exists();
    }

    public static function canAccessPayment(Payment $payment, ?User $user): bool
    {
        return self::applyToPayments(Payment::query(), $user)
            ->whereKey($payment->getKey())
            ->exists();
    }
}

