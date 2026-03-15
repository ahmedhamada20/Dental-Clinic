# Core Tables Generic Policy

These core tables are shared across all medical specialties and must stay specialty-agnostic:

- `patients`
- `users`
- `medical_specialties`
- `services`
- `appointments`
- `visits`
- `visit_notes`
- `invoices`
- `payments`

## Rules

1. Do not add dental-only columns to core tables (examples: `tooth_number`, `odontogram_*`, `jaw_*`, `quadrant_*`).
2. Keep specialty-specific clinical structures in module/feature tables linked by foreign keys.
3. Use `medical_specialties` + relations (users/services/appointments) for specialty segmentation, not specialty-specific core columns.
4. If a new field is required by one specialty only, implement it in extension tables instead of modifying core tables.

## Extension Pattern

- Keep common workflow data in core tables (`appointments`, `visits`, `visit_notes`).
- Add specialty data to dedicated tables (for example, orthodontic or dermatology-specific records) with `visit_id`/`patient_id` references.
- Keep billing entities (`invoices`, `payments`) generic; attribute specialty through linked services/appointments.

