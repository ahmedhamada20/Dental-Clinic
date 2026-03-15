<?php

return [
    'title' => 'Appointments',
    'new' => 'New Appointment',
    'empty' => 'No appointments found.',
    'pages' => [
        'create' => [
            'title' => 'Book Appointment',
            'subtitle' => 'Follow the guided flow: specialty, doctor, service, date/time, then confirm.',
            'back' => 'Back to Appointments',
        ],
        'edit' => [
            'title' => 'Edit Appointment #:number',
            'subtitle' => 'Update specialty, doctor, service, date/time, and status in the same guided flow.',
            'back' => 'Back to Details',
        ],
    ],
    'form' => [
        'steps' => [
            'specialty' => '1. Select Specialty',
            'doctor' => '2. Select Doctor',
            'service' => '3. Select Service',
            'datetime' => '4. Select Date/Time',
            'confirm' => '5. Confirm Booking',
        ],
        'fields' => [
            'patient' => 'Patient',
            'status' => 'Status',
            'specialty' => 'Step 1: Specialty',
            'doctor' => 'Step 2: Doctor',
            'service' => 'Step 3: Service',
            'appointment_date' => 'Step 4: Appointment Date',
            'appointment_time' => 'Step 4: Appointment Time',
            'notes' => 'Notes',
        ],
        'placeholders' => [
            'select_patient' => 'Select patient',
            'select_specialty' => 'Select specialty',
            'select_doctor' => 'Select doctor',
            'select_service' => 'Select service',
            'select_specialty_first' => 'Select a specialty first',
            'loading' => 'Loading...',
            'load_failed' => 'Failed to load',
        ],
        'help' => [
            'specialty_filters' => 'Choose a specialty first to filter available doctors and services.',
        ],
        'actions' => [
            'confirm_booking' => 'Step 5: Confirm Booking',
        ],
    ],
    'filters' => [
        'specialty' => 'Specialty',
    ],
    'columns' => [
        'patient' => 'Patient',
        'specialty' => 'Specialty',
        'doctor' => 'Doctor',
        'service' => 'Service',
        'time' => 'Time',
    ],
    'status' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'checked_in' => 'Checked In',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled_by_patient' => 'Cancelled by Patient',
        'cancelled_by_clinic' => 'Cancelled by Clinic',
        'cancelled'   => 'Cancelled',
        'scheduled'   => 'Scheduled',
        'no_show'     => 'No Show',
    ],
];

