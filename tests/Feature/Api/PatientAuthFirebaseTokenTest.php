<?php

use App\Models\Patient\Patient;

it('registers patient and stores firebase token when provided', function () {
    $response = $this->postJson('/api/v1/patient/register', [
        'first_name' => 'Ali',
        'last_name' => 'Hassan',
        'phone' => '01012345678',
        'email' => 'ali@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'gender' => 'male',
        'device_name' => 'Android Pixel',
        'device_type' => 'android',
        'firebase_token' => 'fcm_token_register_123',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.firebase_token_registered', true);
    expect($response->json('data.token'))->toBeString()->not->toBe('');

    $patientId = (int) $response->json('data.patient.id');

    $this->assertDatabaseHas('device_tokens', [
        'patient_id' => $patientId,
        'firebase_token' => 'fcm_token_register_123',
        'device_type' => 'android',
        'device_name' => 'Android Pixel',
        'is_active' => 1,
    ]);
});

it('logs in patient and stores firebase token when provided', function () {
    $patient = Patient::factory()->create([
        'phone' => '01112345678',
        'password' => bcrypt('secret123'),
        'status' => 'active',
    ]);

    $response = $this->postJson('/api/v1/patient/login', [
        'phone' => '01112345678',
        'password' => 'secret123',
        'device_name' => 'iPhone 14',
        'device_type' => 'ios',
        'firebase_token' => 'fcm_token_login_456',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.firebase_token_registered', true);
    expect($response->json('data.token'))->toBeString()->not->toBe('');

    $this->assertDatabaseHas('device_tokens', [
        'patient_id' => $patient->id,
        'firebase_token' => 'fcm_token_login_456',
        'device_type' => 'ios',
        'device_name' => 'iPhone 14',
        'is_active' => 1,
    ]);
});

