<?php

declare(strict_types=1);

use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
$artisan = escapeshellarg(PHP_BINARY) . ' artisan route:list --path=api --except-vendor --json';
$json = shell_exec('cd ' . escapeshellarg($root) . ' && ' . $artisan);

if (! is_string($json) || trim($json) === '') {
    throw new RuntimeException('Unable to read Laravel API routes.');
}

$routes = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

function normalizeMethod(string $method): string
{
    return match ($method) {
        'GETHEAD' => 'GET',
        'PUTPATCH' => 'PUT',
        default => $method,
    };
}

function jsonBody(array $payload): array
{
    return [
        'mode' => 'raw',
        'raw' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        'options' => [
            'raw' => ['language' => 'json'],
        ],
    ];
}

function formDataBody(array $fields): array
{
    return [
        'mode' => 'formdata',
        'formdata' => $fields,
    ];
}

function authHeader(string $tokenVariable): array
{
    return [
        'key' => 'Authorization',
        'value' => 'Bearer {{' . $tokenVariable . '}}',
        'type' => 'text',
    ];
}

function contentTypeHeader(): array
{
    return [
        'key' => 'Content-Type',
        'value' => 'application/json',
        'type' => 'text',
    ];
}

function buildUrl(string $uri): array
{
    $raw = '{{base_url}}/' . replaceUriParameters($uri);
    $parts = array_values(array_filter(explode('/', trim(replaceUriParameters($uri), '/'))));

    return [
        'raw' => $raw,
        'host' => ['{{base_url}}'],
        'path' => $parts,
    ];
}

function replaceUriParameters(string $uri): string
{
    $segments = explode('/', $uri);

    foreach ($segments as $index => $segment) {
        if (! preg_match('/^{(.+)}$/', $segment, $matches)) {
            continue;
        }

        $parameter = $matches[1];
        $previous = $segments[$index - 1] ?? '';
        $segments[$index] = '{{' . mapParameterToVariable($parameter, $previous, $uri) . '}}';
    }

    return implode('/', $segments);
}

function mapParameterToVariable(string $parameter, string $previousSegment, string $uri): string
{
    $normalized = trim($parameter);

    $bySegment = [
        'appointments' => 'appointment_id',
        'audit-logs' => 'audit_log_id',
        'holidays' => 'holiday_id',
        'invoice-items' => 'invoice_item_id',
        'invoices' => 'invoice_id',
        'medical-files' => 'medical_file_id',
        'notifications' => str_contains($uri, '/patient/') ? 'patient_notification_id' : 'admin_notification_id',
        'patients' => 'patient_id',
        'payments' => 'payment_id',
        'prescription-items' => 'prescription_item_id',
        'prescriptions' => 'prescription_id',
        'promotions' => 'promotion_id',
        'treatment-plan-items' => 'treatment_plan_item_id',
        'treatment-plans' => 'treatment_plan_id',
        'visits' => 'visit_id',
        'waiting-list' => 'waiting_list_request_id',
        'working-hours' => 'working_hour_id',
        'device-tokens' => 'device_token_id',
    ];

    if ($normalized === 'reportType') {
        return 'report_type';
    }

    if ($normalized === 'waitingListRequestId') {
        return 'waiting_list_request_id';
    }

    if (isset($bySegment[$previousSegment])) {
        return $bySegment[$previousSegment];
    }

    return Str::snake($normalized);
}

function routeKey(string $method, string $uri): string
{
    return normalizeMethod($method) . ' ' . $uri;
}

$bodyTemplates = [
    'POST api/v1/patient/register' => jsonBody([
        'first_name' => '{{new_patient_first_name}}',
        'last_name' => '{{new_patient_last_name}}',
        'phone' => '{{new_patient_phone}}',
        'email' => '{{new_patient_email}}',
        'password' => '{{new_patient_password}}',
        'password_confirmation' => '{{new_patient_password}}',
        'gender' => 'male',
        'device_name' => 'Postman Desktop',
    ]),
    'POST api/v1/patient/login' => jsonBody([
        'phone' => '{{patient_phone}}',
        'password' => '{{patient_password}}',
        'device_name' => 'Postman Desktop',
    ]),
    'POST api/v1/patient/change-password' => jsonBody([
        'current_password' => '{{patient_password}}',
        'password' => '{{new_patient_password}}',
        'password_confirmation' => '{{new_patient_password}}',
    ]),
    'PUT api/v1/patient/profile' => jsonBody([
        'first_name' => 'Updated Patient',
        'last_name' => 'Profile',
        'phone' => '{{patient_phone}}',
        'email' => '{{new_patient_email}}',
        'gender' => 'male',
        'date_of_birth' => '1995-05-15',
        'address' => 'Updated Postman Address',
        'city' => 'Cairo',
        'occupation' => 'QA Engineer',
        'marital_status' => 'single',
        'blood_group' => 'O+',
    ]),
    'POST api/v1/patient/appointments' => jsonBody([
        'specialty_id' => '{{specialty_id}}',
        'doctor_id' => '{{doctor_id}}',
        'service_id' => '{{service_id}}',
        'appointment_date' => '{{appointment_date}}',
        'appointment_time' => '{{appointment_time}}',
        'notes' => 'Booked from Postman collection',
    ]),
    'POST api/v1/patient/appointments/{id}/cancel' => jsonBody([
        'cancellation_reason' => 'Patient requested cancellation from Postman',
    ]),
    'POST api/v1/patient/device-tokens' => jsonBody([
        'device_type' => 'web',
        'firebase_token' => 'postman-firebase-token-demo',
        'device_name' => 'Postman Desktop',
        'app_version' => '1.0.0',
    ]),
    'POST api/v1/patient/notifications/read-all' => jsonBody([]),
    'POST api/v1/patient/notifications/{id}/read' => jsonBody([]),
    'POST api/v1/patient/waiting-list' => jsonBody([
        'service_id' => '{{service_id}}',
        'preferred_date' => '{{appointment_date}}',
        'preferred_from_time' => '09:00',
        'preferred_to_time' => '12:00',
    ]),
    'POST api/v1/patient/waiting-list/{id}/claim-slot' => jsonBody([
        'waiting_list_request_id' => '{{waiting_list_request_id}}',
    ]),
    'POST api/v1/admin/login' => jsonBody([
        'email' => '{{admin_email}}',
        'password' => '{{admin_password}}',
        'device_name' => 'Postman Desktop',
    ]),
    'POST api/v1/admin/appointments' => jsonBody([
        'patient_id' => '{{patient_id}}',
        'specialty_id' => '{{specialty_id}}',
        'doctor_id' => '{{doctor_id}}',
        'service_id' => '{{service_id}}',
        'appointment_date' => '{{appointment_date}}',
        'appointment_time' => '{{appointment_time}}',
        'notes' => 'Admin-created appointment from Postman',
    ]),
    'PUT api/v1/admin/appointments/{id}' => jsonBody([
        'appointment_time' => '11:00',
        'notes' => 'Updated by Postman',
    ]),
    'POST api/v1/admin/appointments/{id}/cancel' => jsonBody([
        'cancellation_reason' => 'Cancelled from Postman collection',
        'notes' => 'Admin cancellation test',
    ]),
    'POST api/v1/admin/appointments/{id}/check-in' => jsonBody([
        'notes' => 'Checked in by Postman',
    ]),
    'POST api/v1/admin/appointments/{id}/confirm' => jsonBody([
        'notes' => 'Confirmed by Postman',
    ]),
    'POST api/v1/admin/appointments/{id}/mark-no-show' => jsonBody([]),
    'POST api/v1/admin/holidays' => jsonBody([
        'name' => 'Postman Holiday',
        'date' => '{{appointment_date}}',
        'description' => 'Created from Postman collection',
    ]),
    'PUT api/v1/admin/holidays/{id}' => jsonBody([
        'name' => 'Updated Postman Holiday',
        'date' => '{{appointment_date}}',
        'description' => 'Updated from Postman collection',
    ]),
    'DELETE api/v1/admin/invoice-items/{id}' => jsonBody([
        'reason' => 'Cleanup from Postman collection',
    ]),
    'POST api/v1/admin/invoices' => jsonBody([
        'patient_id' => '{{patient_id}}',
        'visit_id' => '{{visit_id}}',
        'promotion_id' => '{{promotion_id}}',
        'notes' => 'Invoice created from Postman',
    ]),
    'POST api/v1/admin/invoices/{id}/cancel' => jsonBody([
        'reason' => 'Cancelled from Postman collection',
    ]),
    'POST api/v1/admin/invoices/{id}/finalize' => jsonBody([
        'notes' => 'Finalized from Postman collection',
    ]),
    'POST api/v1/admin/invoices/{id}/items' => jsonBody([
        'service_id' => '{{service_id}}',
        'item_type' => 'service',
        'item_name_ar' => 'خدمة بوستمان',
        'item_name_en' => 'Postman Service Item',
        'description' => 'Added from Postman collection',
        'quantity' => 1,
        'unit_price' => 150,
        'discount_amount' => 0,
        'tooth_number' => '11',
    ]),
    'POST api/v1/admin/invoices/{id}/payments' => jsonBody([
        'payments' => [[
            'payment_method' => 'cash',
            'amount' => 50,
            'payment_date' => '{{to_date}}',
            'reference_no' => 'POSTMAN-PAYMENT',
            'notes' => 'Recorded from Postman collection',
        ]],
    ]),
    'PUT api/v1/admin/invoices/{invoice}' => jsonBody([
        'patient_id' => '{{patient_id}}',
        'visit_id' => '{{visit_id}}',
        'promotion_id' => '{{promotion_id}}',
        'notes' => 'Updated invoice from Postman',
        'discount_type' => 'fixed',
        'discount_value' => 25,
    ]),
    'POST api/v1/admin/notifications/send-announcement' => jsonBody([
        'title' => 'Postman Announcement',
        'body' => 'Announcement sent from Postman collection',
        'channels' => ['database'],
    ]),
    'POST api/v1/admin/notifications/send-appointment-reminders' => jsonBody([]),
    'POST api/v1/admin/notifications/send-billing-reminders' => jsonBody([]),
    'POST api/v1/admin/notifications/send-bulk' => jsonBody([
        'title' => 'Bulk Notification from Postman',
        'body' => 'Bulk notification body',
        'type' => 'announcement',
        'channels' => ['database'],
        'audience' => 'patient_ids',
        'patient_ids' => ['{{patient_id}}'],
    ]),
    'POST api/v1/admin/notifications/waiting-list/{waitingListRequestId}/notify' => jsonBody([]),
    'POST api/v1/admin/notifications/{id}/read' => jsonBody([]),
    'POST api/v1/admin/patients' => jsonBody([
        'first_name' => '{{new_patient_first_name}}',
        'last_name' => '{{new_patient_last_name}}',
        'email' => '{{new_patient_email}}',
        'phone' => '{{new_patient_phone}}',
        'gender' => 'male',
        'date_of_birth' => '1995-05-15',
        'address' => 'Created from Postman collection',
        'city' => 'Cairo',
        'status' => 'active',
    ]),
    'POST api/v1/admin/patients/{id}/medical-files' => formDataBody([
        ['key' => 'file', 'type' => 'file', 'src' => '{{sample_file_path}}'],
        ['key' => 'file_category', 'type' => 'text', 'value' => 'xray'],
        ['key' => 'visit_id', 'type' => 'text', 'value' => '{{visit_id}}'],
        ['key' => 'title', 'type' => 'text', 'value' => 'Postman Uploaded File'],
        ['key' => 'notes', 'type' => 'text', 'value' => 'Uploaded through Postman collection'],
        ['key' => 'is_visible_to_patient', 'type' => 'text', 'value' => 'true'],
    ]),
    'POST api/v1/admin/patients/{id}/notify' => jsonBody([
        'title' => 'Manual Notification',
        'body' => 'Sent manually from Postman collection',
        'channel' => 'database',
    ]),
    'POST api/v1/admin/patients/{id}/odontogram/teeth' => jsonBody([
        'tooth_number' => 11,
        'status' => 'filled',
        'surface' => 'occlusal',
        'notes' => 'Updated from Postman',
        'visit_id' => '{{visit_id}}',
    ]),
    'POST api/v1/admin/patients/{id}/treatment-plans' => jsonBody([
        'title' => 'Postman Treatment Plan',
        'description' => 'Created from Postman collection',
        'estimated_total' => 500,
        'status' => 'draft',
        'start_date' => '{{to_date}}',
        'end_date' => '{{appointment_date}}',
        'visit_id' => '{{visit_id}}',
    ]),
    'PUT api/v1/admin/patients/{patient}' => jsonBody([
        'first_name' => 'Updated',
        'last_name' => 'Patient',
        'email' => '{{new_patient_email}}',
        'phone' => '{{new_patient_phone}}',
        'gender' => 'male',
        'date_of_birth' => '1995-05-15',
        'address' => 'Updated from Postman collection',
        'city' => 'Giza',
        'status' => 'active',
    ]),
    'POST api/v1/admin/patients/{patient}/emergency-contacts' => jsonBody([
        'name' => 'Emergency Contact',
        'relation' => 'Brother',
        'phone' => '01011112222',
        'notes' => 'Added from Postman collection',
    ]),
    'PUT api/v1/admin/patients/{patient}/medical-history' => jsonBody([
        'allergies' => 'None',
        'chronic_diseases' => 'None',
        'current_medications' => 'Vitamin D',
        'medical_notes' => 'Updated from Postman collection',
        'dental_history' => 'Routine checkups',
        'important_alerts' => 'No alerts',
    ]),
    'DELETE api/v1/admin/payments/{payment}' => jsonBody([
        'reason' => 'Deleted from Postman collection',
    ]),
    'PUT api/v1/admin/prescription-items/{id}' => jsonBody([
        'medicine_name' => 'Ibuprofen',
        'dosage' => '400mg',
        'frequency' => 'Twice daily',
        'duration' => '5 days',
        'instructions' => 'Take after meals',
    ]),
    'PUT api/v1/admin/prescriptions/{id}' => jsonBody([
        'notes' => 'Updated prescription notes from Postman',
    ]),
    'POST api/v1/admin/prescriptions/{id}/items' => jsonBody([
        'medicine_name' => 'Amoxicillin',
        'dosage' => '500mg',
        'frequency' => 'Three times daily',
        'duration' => '7 days',
        'instructions' => 'Complete the full course',
    ]),
    'POST api/v1/admin/promotions' => jsonBody([
        'title_ar' => 'عرض بوستمان',
        'title_en' => 'Postman Offer',
        'code' => '{{new_promotion_code}}',
        'promotion_type' => 'fixed',
        'value' => 25,
        'applies_once' => true,
        'starts_at' => '{{to_date}}',
        'ends_at' => '{{appointment_date}}',
        'is_active' => true,
        'notes' => 'Created from Postman collection',
    ]),
    'POST api/v1/admin/promotions/{id}/toggle-status' => jsonBody([]),
    'PUT api/v1/admin/promotions/{promotion}' => jsonBody([
        'title_en' => 'Updated Postman Offer',
        'notes' => 'Updated from Postman collection',
        'value' => 30,
    ]),
    'PUT api/v1/admin/settings/clinic' => jsonBody([
        'clinic_name' => 'Dental Clinic System',
        'phone' => '+20 100 000 0000',
        'email' => 'clinic@example.com',
        'address' => '123 Clinic Street, Cairo',
        'timezone' => 'Africa/Cairo',
    ]),
    'PUT api/v1/admin/settings/working-days' => jsonBody([
        'days' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'],
    ]),
    'POST api/v1/admin/settings/working-hours' => jsonBody([
        'day' => 'monday',
        'start_time' => '09:00',
        'end_time' => '17:00',
        'is_active' => true,
    ]),
    'PUT api/v1/admin/settings/working-hours/{id}' => jsonBody([
        'day' => 'monday',
        'start_time' => '10:00',
        'end_time' => '18:00',
        'is_active' => true,
    ]),
    'POST api/v1/admin/tickets/{id}/call' => jsonBody([]),
    'POST api/v1/admin/tickets/{id}/finish' => jsonBody([]),
    'POST api/v1/admin/tickets/{id}/start' => jsonBody([]),
    'PUT api/v1/admin/treatment-plan-items/{id}' => jsonBody([
        'title' => 'Updated Treatment Item',
        'description' => 'Updated from Postman collection',
        'session_no' => 2,
        'estimated_cost' => 250,
        'status' => 'planned',
        'planned_date' => '{{appointment_date}}',
    ]),
    'POST api/v1/admin/treatment-plan-items/{id}/complete' => jsonBody([
        'completed_visit_id' => '{{visit_id}}',
    ]),
    'PUT api/v1/admin/treatment-plans/{id}' => jsonBody([
        'title' => 'Updated Treatment Plan',
        'description' => 'Updated via Postman',
        'estimated_total' => 750,
        'status' => 'approved',
        'start_date' => '{{to_date}}',
        'end_date' => '{{appointment_date}}',
    ]),
    'POST api/v1/admin/treatment-plans/{id}/change-status' => jsonBody([
        'status' => 'approved',
    ]),
    'POST api/v1/admin/treatment-plans/{id}/items' => jsonBody([
        'service_id' => '{{service_id}}',
        'tooth_number' => 11,
        'title' => 'Treatment Plan Item',
        'description' => 'Added from Postman collection',
        'session_no' => 1,
        'estimated_cost' => 200,
        'status' => 'planned',
        'planned_date' => '{{appointment_date}}',
    ]),
    'PUT api/v1/admin/visit-notes/{id}' => jsonBody([
        'note_type' => 'clinical',
        'note' => 'Updated visit note from Postman collection',
    ]),
    'POST api/v1/admin/visits/{id}/complete' => jsonBody([
        'diagnosis' => 'Completed through Postman smoke test',
        'clinical_notes' => 'Clinical notes updated from Postman',
        'internal_notes' => 'Internal notes updated from Postman',
    ]),
    'POST api/v1/admin/visits/{id}/notes' => jsonBody([
        'note_type' => 'clinical',
        'note' => 'Visit note added from Postman collection',
    ]),
    'POST api/v1/admin/visits/{id}/prescriptions' => jsonBody([
        'notes' => 'Prescription created from Postman collection',
    ]),
    'POST api/v1/admin/visits/{id}/start' => jsonBody([
        'clinical_notes' => 'Visit started from Postman collection',
    ]),
];

$notes = [
    'POST api/v1/admin/login' => 'This route is registered under the admin auth middleware in `routes/api.php`, so it requires an existing admin Sanctum token and is not a usable public login endpoint in the current codebase.',
];

function requestName(string $method, string $uri): string
{
    return normalizeMethod($method) . ' ' . str_replace('api/v1/', '', $uri);
}

function describeRequest(string $method, string $uri): string
{
    $auth = str_contains($uri, '/admin/')
        ? 'Requires `admin_token`.'
        : ((str_contains($uri, '/patient/') && ! in_array($uri, ['api/v1/patient/login', 'api/v1/patient/register', 'api/v1/patient/services', 'api/v1/patient/services/{service}'], true))
            ? 'Requires `patient_token`.'
            : 'Public endpoint.');

    return trim($auth . ' ' . ($GLOBALS['notes'][routeKey($method, $uri)] ?? ''));
}

function buildHeaders(string $method, string $uri, ?array $body): array
{
    $headers = [];

    if (str_contains($uri, '/admin/')) {
        $headers[] = authHeader('admin_token');
    } elseif (str_contains($uri, '/patient/') && ! in_array($uri, ['api/v1/patient/login', 'api/v1/patient/register', 'api/v1/patient/services', 'api/v1/patient/services/{service}'], true)) {
        $headers[] = authHeader('patient_token');
    }

    if ($body !== null && ($body['mode'] ?? null) === 'raw') {
        $headers[] = contentTypeHeader();
    }

    return $headers;
}

function buildRequestItem(string $method, string $uri, ?array $body = null, array $events = [], ?string $name = null): array
{
    return [
        'name' => $name ?? requestName($method, $uri),
        'request' => [
            'method' => normalizeMethod($method),
            'header' => buildHeaders($method, $uri, $body),
            'body' => $body,
            'url' => buildUrl($uri),
            'description' => describeRequest($method, $uri),
        ],
        'event' => $events,
        'response' => [],
    ];
}

function testEvent(array $expectedStatuses = [200, 201], array $captures = []): array
{
    $expected = json_encode($expectedStatuses);
    $script = [
        'pm.test("Expected HTTP status", function () {',
        '    pm.expect(' . $expected . ').to.include(pm.response.code);',
        '});',
        'let json = null;',
        'try { json = pm.response.json(); } catch (error) {}',
        'if (json && Object.prototype.hasOwnProperty.call(json, "success")) {',
        '    pm.test("API response shape includes success", function () {',
        '        pm.expect(json.success).to.not.equal(undefined);',
        '    });',
        '}',
    ];

    foreach ($captures as $variable => $path) {
        $parts = explode('.', $path);
        $walker = 'json';
        foreach ($parts as $part) {
            $walker .= '?.' . $part;
        }
        $script[] = 'if (' . $walker . ' !== undefined && ' . $walker . ' !== null) {';
        $script[] = '    pm.environment.set(' . json_encode($variable) . ', String(' . $walker . '));';
        $script[] = '}';
    }

    return [[
        'listen' => 'test',
        'script' => [
            'type' => 'text/javascript',
            'exec' => $script,
        ],
    ]];
}

function actorFolder(string $uri): string
{
    return str_contains($uri, '/admin/') ? 'Admin APIs' : 'Patient APIs';
}

function moduleFolder(string $uri): string
{
    $path = explode('/', trim(str_replace('api/v1/', '', $uri), '/'));
    $actor = $path[0] ?? 'misc';
    $first = $path[1] ?? 'misc';

    if ($actor === 'patient') {
        return match ($first) {
            'login', 'register', 'logout', 'me', 'change-password', 'profile' => 'Auth',
            'services' => 'Services',
            'appointments', 'waiting-list' => 'Appointments',
            'invoices', 'payments' => 'Billing',
            'notifications', 'device-tokens' => 'Notifications',
            'medical-files', 'prescriptions', 'treatment-plans' => 'Medical',
            default => Str::headline($first),
        };
    }

    return match ($first) {
        'me', 'login', 'logout' => 'Auth',
        'dashboard' => 'Dashboard',
        'settings', 'holidays' => 'Settings',
        'reports' => 'Reports',
        'audit-logs' => 'Audit',
        default => Str::headline($first),
    };
}

$grouped = [];
foreach ($routes as $route) {
    $method = normalizeMethod($route['method']);
    $uri = $route['uri'];
    $key = $method . ' ' . $uri;
    $actor = actorFolder($uri);
    $module = moduleFolder($uri);

    $grouped[$actor][$module][] = buildRequestItem($method, $uri, $bodyTemplates[$key] ?? null);
}

ksort($grouped);
foreach ($grouped as &$modules) {
    ksort($modules);
}
unset($modules);

$fullReferenceFolders = [];
foreach ($grouped as $actor => $modules) {
    $items = [];
    foreach ($modules as $module => $requests) {
        $items[] = ['name' => $module, 'item' => $requests];
    }
    $fullReferenceFolders[] = ['name' => $actor, 'item' => $items];
}

$smoke = [
    buildRequestItem('GET', 'api/v1/patient/services', null, testEvent([200]), 'Smoke - List patient services'),
    buildRequestItem('POST', 'api/v1/patient/login', $bodyTemplates['POST api/v1/patient/login'], testEvent([200], [
        'patient_token' => 'data.token',
        'patient_id' => 'data.patient.id',
    ]), 'Smoke - Patient login'),
    buildRequestItem('GET', 'api/v1/patient/me', null, testEvent([200]), 'Smoke - Patient me'),
    buildRequestItem('GET', 'api/v1/patient/profile', null, testEvent([200]), 'Smoke - Patient profile'),
    buildRequestItem('GET', 'api/v1/patient/appointments', null, testEvent([200]), 'Smoke - Patient appointments'),
    buildRequestItem('POST', 'api/v1/patient/waiting-list', $bodyTemplates['POST api/v1/patient/waiting-list'], testEvent([200, 201], [
        'waiting_list_request_id' => 'data.id',
    ]), 'Smoke - Create waiting list request'),
    buildRequestItem('GET', 'api/v1/patient/waiting-list', null, testEvent([200]), 'Smoke - List waiting list requests'),
    buildRequestItem('GET', 'api/v1/admin/dashboard/summary', null, testEvent([200]), 'Smoke - Admin dashboard summary'),
    buildRequestItem('GET', 'api/v1/admin/patients', null, testEvent([200]), 'Smoke - Admin list patients'),
    buildRequestItem('POST', 'api/v1/admin/patients', $bodyTemplates['POST api/v1/admin/patients'], testEvent([201], [
        'created_admin_patient_id' => 'data.id',
    ]), 'Smoke - Admin create patient'),
    buildRequestItem('GET', 'api/v1/admin/patients/{patient}', null, testEvent([200]), 'Smoke - Admin show created patient'),
    buildRequestItem('PUT', 'api/v1/admin/patients/{patient}', jsonBody([
        'first_name' => 'Smoke Updated',
        'last_name' => 'Patient',
        'email' => '{{new_patient_email}}',
        'phone' => '{{new_patient_phone}}',
        'gender' => 'male',
        'date_of_birth' => '1995-05-15',
        'address' => 'Smoke updated address',
        'city' => 'Giza',
        'status' => 'active',
    ]), testEvent([200]), 'Smoke - Admin update created patient'),
    buildRequestItem('POST', 'api/v1/admin/patients/{patient}/emergency-contacts', $bodyTemplates['POST api/v1/admin/patients/{patient}/emergency-contacts'], testEvent([200, 201]), 'Smoke - Admin add emergency contact'),
    buildRequestItem('GET', 'api/v1/admin/patients/{patient}/emergency-contacts', null, testEvent([200]), 'Smoke - Admin list emergency contacts'),
    buildRequestItem('PUT', 'api/v1/admin/patients/{patient}/medical-history', $bodyTemplates['PUT api/v1/admin/patients/{patient}/medical-history'], testEvent([200]), 'Smoke - Admin update medical history'),
    buildRequestItem('POST', 'api/v1/admin/promotions', $bodyTemplates['POST api/v1/admin/promotions'], testEvent([200, 201], [
        'created_promotion_id' => 'data.id',
    ]), 'Smoke - Admin create promotion'),
    buildRequestItem('GET', 'api/v1/admin/promotions/{promotion}', null, testEvent([200]), 'Smoke - Admin show promotion'),
    buildRequestItem('PUT', 'api/v1/admin/promotions/{promotion}', jsonBody([
        'title_en' => 'Smoke Updated Promotion',
        'notes' => 'Updated by smoke test',
        'value' => 35,
    ]), testEvent([200]), 'Smoke - Admin update promotion'),
    buildRequestItem('DELETE', 'api/v1/admin/promotions/{promotion}', null, testEvent([200, 204]), 'Smoke - Admin delete promotion'),
    buildRequestItem('DELETE', 'api/v1/admin/patients/{patient}', null, testEvent([200, 204]), 'Smoke - Admin delete created patient'),
];

$replaceSmokeUrl = function (array &$item, string $needle, string $replacement): void {
    $raw = $item['request']['url']['raw'];
    $item['request']['url']['raw'] = str_replace('{{' . $needle . '}}', '{{' . $replacement . '}}', $raw);
    $item['request']['url']['path'] = array_map(
        fn (string $segment) => $segment === '{{' . $needle . '}}' ? '{{' . $replacement . '}}' : $segment,
        $item['request']['url']['path']
    );
};

foreach ($smoke as &$item) {
    if (str_contains($item['request']['url']['raw'], '{{patient_id}}') && str_contains($item['name'], 'created patient')) {
        $replaceSmokeUrl($item, 'patient_id', 'created_admin_patient_id');
    }
    if (str_contains($item['request']['url']['raw'], '{{promotion_id}}') && str_contains($item['name'], 'promotion')) {
        $replaceSmokeUrl($item, 'promotion_id', 'created_promotion_id');
    }
}
unset($item);

$collection = [
    'info' => [
        '_postman_id' => (string) Str::uuid(),
        'name' => 'Dental Clinic API',
        'description' => "Generated from Laravel `routes/api.php` and the registered API route list.\n\n- `Verified Smoke Flow`: safe subset validated locally with Newman.\n- `Full Route Reference`: all API endpoints currently registered under `routes/api.php`.\n\nNote: `POST /api/v1/admin/login` is currently behind the admin auth middleware and not usable as a public login endpoint in the current codebase.",
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'item' => [
        ['name' => 'Verified Smoke Flow', 'item' => $smoke],
        ['name' => 'Full Route Reference', 'item' => $fullReferenceFolders],
    ],
    'event' => [[
        'listen' => 'prerequest',
        'script' => [
            'type' => 'text/javascript',
            'exec' => [
                'pm.variables.set("request_started_at", new Date().toISOString());',
            ],
        ],
    ]],
    'variable' => [
        ['key' => 'collection_generated_at', 'value' => date(DATE_ATOM)],
    ],
];

$output = $root . '/postman/Dental Clinic API.postman_collection.json';
file_put_contents($output, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Collection exported to {$output}" . PHP_EOL;

