# Blade/UI Action Audit Checklist

Date: 2026-03-12
Scope: `resources/views/**`, named routes used in Blade, controller `view()` references, forms/links/buttons/dropdown/modal/table/sidebar/navbar/breadcrumb actions.

| Module | Page/View | Route | Button/Action | Status | Issue | Fix applied |
|---|---|---|---|---|---|---|
| Global Layout | `resources/views/admin/partials/sidebar.blade.php` | Multiple `admin.*` links | Sidebar navigation links | PASS | All route names used in sidebar resolve via route registry. | No code change required. |
| Global Layout | `resources/views/admin/partials/topbar.blade.php` | N/A (dropdown placeholders) | Notification/message dropdown links | FIXED | Dead links used `href="#"` (non-functional actions). | Replaced anchor placeholders with non-clickable `dropdown-item-text` and explicit disabled text for "view all messages". |
| Auth | `resources/views/auth/*.blade.php` | `password.*`, `login`, `register` | Reset/login/register forms | PASS | No missing routes; CSRF present for non-GET forms. | No code change required. |
| Appointments | `resources/views/admin/appointments/*.blade.php` | `admin.appointments.*` | Index/create/show/edit + CRUD buttons | PASS (static wiring) | Route references and form actions are valid. | No code change required. |
| Patients | `resources/views/admin/patients/*.blade.php` | `admin.patients.*` | Index/create/show/edit + medical/emergency actions | PASS (static wiring) | Route references and form actions are valid. | No code change required. |
| Visits | `resources/views/admin/visits/*.blade.php` | `admin.visits.*`, `admin.visits.notes.*` | Show/edit/start/complete/cancel/note actions | PASS (static wiring) | Named route usage valid; forms include CSRF and proper methods. | No code change required. |
| Waiting List | `resources/views/admin/waiting-list/*.blade.php` | `admin.waiting-list.*` | Notify/convert/cancel/delete actions | PASS (static wiring) | Named route usage valid; form wiring present. | No code change required. |
| Billing | `resources/views/admin/billing/**/*.blade.php` | `admin.billing.*` | Invoices/payments actions (create/update/destroy/finalize/cancel/print/export) | PASS (static wiring) | Named route usage valid; form wiring present. | No code change required. |
| Services | `resources/views/admin/services/*.blade.php` | `admin.services.*` | CRUD + activate/deactivate actions | PASS (static wiring) | Named route usage valid; form wiring present. | No code change required. |
| Service Categories | `resources/views/admin/service-categories/*.blade.php` | `admin.service-categories.*` | CRUD + activate/deactivate actions | PASS (static wiring) | Named route usage valid; form wiring present. | No code change required. |
| Specialties | `resources/views/admin/specialties/*.blade.php` | `admin.specialties.*` | CRUD + activate/deactivate actions | PASS (static wiring) | Named route usage valid; form wiring present. | No code change required. |
| Notifications | `resources/views/admin/notifications/*.blade.php` | `admin.notifications.*` | Create/send/show actions | PASS (static wiring) | Named route usage valid; form wiring present. | No code change required. |
| Reports | `resources/views/admin/reports/*.blade.php` | `admin.reports.*` | Filter/reset/export/print actions | PASS (static wiring) | Named route usage valid; form wiring present. | No code change required. |
| Settings | `resources/views/admin/settings/index.blade.php` | `admin.settings.*` | Save/update actions | PASS (static wiring) | Named route usage valid; form wiring present. | No code change required. |
| Users & Roles | `resources/views/admin/users/*.blade.php`, `resources/views/admin/roles/*.blade.php` | `admin.users.*`, `admin.roles.*` | CRUD actions and permission controls | PASS (static wiring) | Named route usage valid; form wiring present. | No code change required. |
| Audit Logs | `resources/views/admin/audit-logs/*.blade.php` | `admin.audit-logs.*` | Index/show links | PASS (static wiring) | Named route usage valid. | No code change required. |
| Controllers -> Views | `app/Http/Controllers/**`, `app/Modules/**` | N/A | `view('...')` references | PASS | No missing Blade templates detected for string-literal `view()` calls. | No code change required. |
| Runtime Coverage | Admin feature tests | Multiple | Index/create/show/edit/store/update/delete runtime behavior | PARTIAL | Existing admin test suite currently has many unrelated failures (permissions/seed constraints/data integrity), limiting full runtime confirmation for every action in this run. | Kept changes scoped to deterministic UI wiring fixes; captured gaps for follow-up remediation. |

## Notes

- Static route/include/view audit found no actionable missing route names/includes/views after this pass.
- One static false positive can appear for `route('token')` pattern in password reset views when scanning `$request->route('token')`; this is not a named-route bug.
- No non-GET form missing `@csrf` detected in Blade templates.

## Files changed in this audit

- `resources/views/admin/partials/topbar.blade.php`

