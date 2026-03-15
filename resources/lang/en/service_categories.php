<?php

return [
    'title' => 'Service Categories',
    'create_title' => 'Create Service Category',
    'edit_title' => 'Edit Service Category',

    'columns' => [
        'id' => '#',
        'medical_specialty_id' => 'Specialty',
        'name' => 'Name',
        'services' => 'Services',
        'status' => 'Status',
        'sort_order' => 'Sort Order',
        'actions' => 'Actions',
    ],

    'fields' => [
        'medical_specialty_id' => 'Specialty',
        'name_ar' => 'Name (Arabic)',
        'name_en' => 'Name (English)',
        'sort_order' => 'Sort Order',
        'is_active' => 'Active',
    ],

    'placeholders' => [
        'search' => 'Search by name',
        'select_specialty' => 'Select specialty',
    ],

    'filters' => [
        'all' => 'All',
    ],

    'actions' => [
        'create' => 'Create Category',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'new' => 'New Category',
        'filter' => 'Filter',
        'search' => 'Search',
        'cancel' => 'Cancel',
    ],

    'messages' => [
        'created' => 'Service category created successfully.',
        'updated' => 'Service category updated successfully.',
        'deleted' => 'Service category deleted successfully.',
        'activated' => 'Service category activated successfully.',
        'deactivated' => 'Service category deactivated successfully.',
        'cannot_delete_with_services' => 'Cannot delete category with services. Please remove services first.',
        'not_found' => 'Service category not found.',
        'empty' => 'No service categories found.',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
];

