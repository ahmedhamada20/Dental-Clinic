<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('keeps core tables free from dental-only assumptions', function () {
    $coreTables = [
        'patients',
        'users',
        'medical_specialties',
        'services',
        'appointments',
        'visits',
        'visit_notes',
        'invoices',
        'payments',
    ];

    $forbiddenColumnPatterns = [
        '/^tooth(_|$)/i',
        '/odontogram/i',
        '/^jaw(_|$)/i',
        '/quadrant/i',
        '/dental_/i',
    ];

    foreach ($coreTables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Expected table [{$table}] to exist.");

        $columns = Schema::getColumnListing($table);

        foreach ($columns as $column) {
            foreach ($forbiddenColumnPatterns as $pattern) {
                expect((bool) preg_match($pattern, $column))
                    ->toBeFalse("Core table [{$table}] contains specialty-specific column [{$column}].");
            }
        }
    }
});

