<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment\Appointment;
use App\Models\Billing\Invoice;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use App\Support\Authorization\SpecialtyDataScope;

$doctor = User::query()
    ->where('user_type', App\Enums\UserType::DOCTOR->value)
    ->whereNotNull('specialty_id')
    ->first();

if (! $doctor) {
    echo "No doctor with specialty found.\n";
    exit(0);
}

echo "User: {$doctor->email} | specialty_id={$doctor->specialty_id}\n";

$totals = [
    'patients_total' => Patient::query()->count(),
    'appointments_total' => Appointment::query()->count(),
    'visits_total' => Visit::query()->count(),
    'invoices_total' => Invoice::query()->count(),
];

$scoped = [
    'patients_scoped' => SpecialtyDataScope::applyToPatients(Patient::query(), $doctor)->count(),
    'appointments_scoped' => SpecialtyDataScope::applyToAppointments(Appointment::query(), $doctor)->count(),
    'visits_scoped' => SpecialtyDataScope::applyToVisits(Visit::query(), $doctor)->count(),
    'invoices_scoped' => SpecialtyDataScope::applyToInvoices(Invoice::query(), $doctor)->count(),
];

foreach ($totals as $k => $v) {
    echo $k . ': ' . $v . PHP_EOL;
}

foreach ($scoped as $k => $v) {
    echo $k . ': ' . $v . PHP_EOL;
}

echo "Done\n";

