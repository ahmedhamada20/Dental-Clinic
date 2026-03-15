<?php

return [
    'title' => 'Patients',
    'create_title' => 'Create Patient',
    'edit_title' => 'Edit Patient',
    'show_title' => 'Patient Details',

    'sections' => [
        'patient_profile' => 'Patient Profile Details',
        'patient_profile_description' => 'Register a patient and capture profile, history, contacts, and initial files in one workflow.',
        'personal_information' => 'Personal Information',
        'patient_profile_details' => 'Patient Profile Details',
        'profile_additional_info' => 'Additional Profile Information',
        'medical_history' => 'Medical History',
        'emergency_contacts' => 'Emergency Contacts',
        'medical_files' => 'Medical Files',
        'not_available' => 'Not Available',
    ],

    'columns' => [
        'id' => '#',
        'patient_code' => 'Patient Code',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'full_name' => 'Full Name',
        'phone' => 'Phone',
        'email' => 'Email',
        'status' => 'Status',
        'actions' => 'Actions',
    ],

    'fields' => [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'phone' => 'Phone',
        'alternate_phone' => 'Alternate Phone',
        'email' => 'Email',
        'gender' => 'Gender',
        'date_of_birth' => 'Date of Birth',
        'city' => 'City',
        'status' => 'Status',
        'password' => 'Password',
        'password_confirmation' => 'Confirm Password',
        'occupation' => 'Occupation',
        'marital_status' => 'Marital Status',
        'preferred_language' => 'Preferred Language',
        'blood_group' => 'Blood Group',
        'allergies' => 'Allergies',
        'chronic_diseases' => 'Chronic Diseases',
        'current_medications' => 'Current Medications',
        'dental_history' => 'Dental History',
        'important_alerts' => 'Important Alerts',
        'contact_name' => 'Contact Name',
        'contact_relation' => 'Relation',
        'contact_phone' => 'Phone',
        'contact_notes' => 'Notes',
    ],

    'placeholders' => [
        'search' => 'Search by name, code, phone, or email',
    ],

    'filters' => [
        'all_statuses' => 'All',
    ],

    'actions' => [
        'create' => 'Create Patient Record',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'view' => 'View',
        'cancel' => 'Cancel',
        'back_to_patients' => 'Back to Patients',
        'save_changes' => 'Save Changes',
        'add_emergency_contact' => 'Add Emergency Contact',
        'add_medical_file' => 'Add Medical File',
    ],

    'messages' => [
        'created' => 'Patient created successfully.',
        'updated' => 'Patient updated successfully.',
        'deleted' => 'Patient deleted successfully.',
        'not_found' => 'Patient not found.',
        'empty' => 'No patients found.',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    'genders' => [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
    ],
];

