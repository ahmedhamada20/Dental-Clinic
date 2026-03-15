# Admin Feature Test Coverage

## Added test files

- `tests/Feature/Admin/AdminAccessControlFeatureTest.php`
- `tests/Feature/Admin/AdminPatientsCrudFeatureTest.php`
- `tests/Feature/Admin/AdminAppointmentsCrudFeatureTest.php`
- `tests/Feature/Admin/AdminCatalogCrudFeatureTest.php`
- `tests/Feature/Admin/AdminUsersRolesCrudFeatureTest.php`
- `tests/Feature/Admin/AdminLocaleAndUiActionsFeatureTest.php`

## Test support and seeding

- `tests/Support/AdminFeatureTestHelpers.php`
- `database/seeders/Test/AdminFeaturePermissionSeeder.php`

## Covered route matrix

### Access control and major page load

- `admin.dashboard.index`
- `admin.patients.index`
- `admin.appointments.index`
- `admin.waiting-list.index`
- `admin.visits.index`
- `admin.specialties.index`
- `admin.service-categories.index`
- `admin.services.index`
- `admin.reports.index`
- `admin.settings.index`
- `admin.users.index`
- `admin.roles.index`
- `admin.audit-logs.index`
- Guest redirect checks also include `admin.billing.index`

### Patients module

- `admin.patients.index`
- `admin.patients.create`
- `admin.patients.store`
- `admin.patients.show`
- `admin.patients.edit`
- `admin.patients.update`
- `admin.patients.destroy`
- Index query actions: search/filter/pagination params

### Appointments module

- `admin.appointments.index`
- `admin.appointments.create`
- `admin.appointments.show`
- `admin.appointments.edit`
- Index query actions: status/date/specialty filters + pagination

### Catalog module (specialties/categories/services)

- `admin.specialties.index`
- `admin.specialties.create`
- `admin.specialties.store`
- `admin.specialties.edit`
- `admin.specialties.update`
- `admin.specialties.activate`
- `admin.specialties.deactivate`
- `admin.service-categories.index`
- `admin.service-categories.create`
- `admin.service-categories.store`
- `admin.service-categories.edit`
- `admin.service-categories.update`
- `admin.service-categories.destroy`
- `admin.services.index`
- `admin.services.create`
- `admin.services.store`
- `admin.services.show`
- `admin.services.edit`
- `admin.services.update`
- `admin.services.destroy`

### Users and roles

- `admin.users.index`
- `admin.users.create`
- `admin.users.store`
- `admin.users.edit`
- `admin.users.update`
- `admin.users.destroy`
- `admin.roles.index`
- `admin.roles.edit`
- Protected role guards (system-role update/delete deny)

### Locale and UI action coverage

- `language.switch`
- RTL/LTR assertions across admin pages (`dir` and locale classes)
- Presence checks for add/edit/delete/save/update/cancel/back actions in patient views
- Patient index search/filter/pagination behavior

## Known uncovered or intentionally deferred items

- Billing page success assertion on authorized access is not in the pass matrix because `admin.billing.index` currently has a view rendering issue (`htmlspecialchars()` array input) outside this test patch.
- Appointment mutating actions (`store`, `update`, `destroy`) are not asserted in this suite due current audit-log DB constraint issue (`actor_type` check in SQLite during logging).
- Waiting-list and visits create/update/delete actions are not yet covered end-to-end.
- Billing invoices/payments CRUD actions are not yet covered.
- Notifications, promotions, treatment plans, prescriptions, and report export endpoints are only partially represented via top-level access checks.

## Last run summary

- Command set: new admin suite only (`tests/Feature/Admin/*FeatureTest.php` files added in this patch)
- Result: **44 passed** with **149 assertions**
- Output snapshot stored at: `storage/logs/admin_feature_full_output.txt`

