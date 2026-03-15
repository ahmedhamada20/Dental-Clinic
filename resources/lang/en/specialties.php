<?php

return [
    'title' => 'Specialties',
    'create_title' => 'Create Specialty',
    'edit_title' => 'Edit Specialty',
    'show_title' => 'Specialty Details',
    'list' => 'Specialty List',
    'create' => 'Create Specialty',
    'edit' => 'Edit Specialty',
    'details' => 'Specialty Details',
    'name' => 'Specialty Name',
    'code' => 'Specialty Code',
    'description' => 'Description',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'created' => 'Specialty created successfully.',
    'updated' => 'Specialty updated successfully.',
    'activated' => 'Specialty activated successfully.',
    'deactivated' => 'Specialty deactivated successfully.',
    'no_specialties' => 'No specialties found.',
    'empty_state' => 'No specialties available.',
    'view' => 'View Specialty',
    'edit_specialty' => 'Edit Specialty',
    'delete_specialty' => 'Delete Specialty',
    'doctors_count' => 'Doctors',
    'services_count' => 'Services',
    'appointments_count' => 'Appointments',
    'filter_by_status' => 'Filter by Status',
    'search_placeholder' => 'Search specialties...',

    'columns' => [
        'id' => '#',
        'name' => 'Name',
        'description' => 'Description',
        'doctors' => 'Doctors',
        'categories' => 'Categories',
        'status' => 'Status',
        'actions' => 'Actions',
    ],

    'fields' => [
        'name' => 'Specialty Name',
        'description' => 'Description',
        'icon' => 'Icon (Optional)',
        'is_active' => 'Active',
    ],

    'placeholders' => [
        'search' => 'Search by name or description',
        'icon' => 'bi bi-heart-pulse',
        'select_specialty' => 'Select specialty',
    ],

    'filters' => [
        'all_statuses' => 'All statuses',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    'actions' => [
        'create' => 'Create Specialty',
        'edit' => 'Edit',
        'view_doctors' => 'View Doctors',
        'delete' => 'Delete',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'new' => 'New Specialty',
        'add_doctor' => 'Assign Doctor to Specialty',
        'back_to_list' => 'Back to Specialties',
        'filter' => 'Filter',
        'search' => 'Search',
        'cancel' => 'Cancel',
    ],

    'messages' => [
        'created' => 'Specialty created successfully.',
        'updated' => 'Specialty updated successfully.',
        'deleted' => 'Specialty deleted successfully.',
        'activated' => 'Specialty activated successfully.',
        'deactivated' => 'Specialty deactivated successfully.',
        'not_found' => 'Specialty not found.',
        'empty' => 'No specialties found.',
        'doctor_already_in_specialty' => 'The selected doctor is already assigned to this specialty.',
    ],

    'doctors' => [
        'list_title' => 'Doctors in This Specialty',
        'assign_title' => 'Assign Doctor to This Specialty',
        'select_doctor' => 'Select Doctor',
        'select_doctor_placeholder' => 'Choose a doctor',
        'current_specialty' => 'Current specialty: :name',
        'no_available_doctors' => 'No doctors are available for assignment right now.',
        'empty' => 'No doctors are assigned to this specialty yet.',
        'columns' => [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'status' => 'Status',
        ],
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
];

