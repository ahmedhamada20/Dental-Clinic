#!/usr/bin/env python3
import os
import re
from pathlib import Path
from collections import defaultdict

# Define existing routes from the web.php file
EXISTING_ROUTES = {
    'admin.patients.index', 'admin.patients.create', 'admin.patients.store',
    'admin.patients.show', 'admin.patients.edit', 'admin.patients.update',
    'admin.patients.destroy', 'admin.patients.medical-history.store',
    'admin.patients.emergency-contacts.store', 'admin.patients.emergency-contacts.update',
    'admin.patients.emergency-contacts.destroy', 'admin.patients.medical-files.store',
    'admin.patients.medical-files.destroy',
    'admin.appointments.index', 'admin.appointments.create', 'admin.appointments.store',
    'admin.appointments.show', 'admin.appointments.edit', 'admin.appointments.update',
    'admin.appointments.destroy',
    'admin.waiting-list.index', 'admin.waiting-list.notify', 'admin.waiting-list.convert',
    'admin.waiting-list.cancel', 'admin.waiting-list.destroy',
    'admin.visits.index', 'admin.visits.show', 'admin.visits.start', 'admin.visits.complete',
    'admin.visits.cancel', 'admin.visits.notes.store', 'admin.visits.notes.update',
    'admin.visits.notes.destroy',
    'admin.specialties.index', 'admin.specialties.create', 'admin.specialties.store',
    'admin.specialties.edit', 'admin.specialties.update', 'admin.specialties.activate',
    'admin.specialties.deactivate',
    'admin.service-categories.index', 'admin.service-categories.create',
    'admin.service-categories.store', 'admin.service-categories.edit',
    'admin.service-categories.update', 'admin.service-categories.destroy',
    'admin.service-categories.activate', 'admin.service-categories.deactivate',
    'admin.services.index', 'admin.services.create', 'admin.services.store',
    'admin.services.show', 'admin.services.edit', 'admin.services.update',
    'admin.services.destroy', 'admin.services.activate', 'admin.services.deactivate',
    'admin.treatment-plans.index', 'admin.treatment-plans.show',
    'admin.prescriptions.index', 'admin.prescriptions.show',
    'admin.billing.index', 'admin.billing.invoices.index', 'admin.billing.invoices.create',
    'admin.billing.invoices.store', 'admin.billing.invoices.show', 'admin.billing.invoices.edit',
    'admin.billing.invoices.update', 'admin.billing.invoices.destroy', 'admin.billing.invoices.items.store',
    'admin.billing.invoices.items.destroy', 'admin.billing.invoices.finalize', 'admin.billing.invoices.cancel',
    'admin.billing.invoices.print', 'admin.billing.payments.index', 'admin.billing.payments.show',
    'admin.billing.payments.store', 'admin.billing.payments.destroy',
    'admin.promotions.index', 'admin.promotions.create', 'admin.promotions.store',
    'admin.promotions.show', 'admin.promotions.edit', 'admin.promotions.update',
    'admin.promotions.destroy', 'admin.promotions.activate', 'admin.promotions.deactivate',
    'admin.notifications.index', 'admin.notifications.create', 'admin.notifications.store',
    'admin.notifications.show', 'admin.notifications.send-appointment-reminders',
    'admin.notifications.send-billing-reminders', 'admin.notifications.send-waiting-list',
    'admin.reports.index', 'admin.reports.export-pdf', 'admin.reports.export-excel',
    'admin.reports.print', 'admin.settings.index', 'admin.settings.update',
    'admin.users.index', 'admin.users.create', 'admin.users.store',
    'admin.users.edit', 'admin.users.update', 'admin.users.destroy',
    'admin.roles.index', 'admin.roles.create', 'admin.roles.store', 'admin.roles.show',
    'admin.roles.edit', 'admin.roles.update', 'admin.roles.destroy',
    'admin.audit-logs.index', 'admin.audit-logs.show',
    'admin.dashboard.index', 'profile.edit', 'profile.update', 'profile.destroy',
    'login', 'register', 'dashboard', 'welcome'
}

CRUD_ACTIONS = ['create', 'edit', 'delete', 'destroy', 'remove']
LAYOUT_PATTERNS = ['@extends', '@layout', 'x-app-layout', 'x-guest-layout']
SECTION_PATTERNS = ['@section', '@yield']
TRANSLATION_PATTERNS = ['__\(', '@lang\(']
BUTTON_PATTERNS = ['btn', 'button', 'submit', 'action']

def analyze_blade_files():
    views_dir = Path('resources/views')
    blade_files = list(views_dir.rglob('*.blade.php'))

    report = []
    issues = defaultdict(list)

    for blade_file in sorted(blade_files):
        rel_path = str(blade_file.relative_to(views_dir))

        try:
            with open(blade_file, 'r', encoding='utf-8') as f:
                content = f.read()
        except Exception as e:
            report.append({
                'file': rel_path,
                'status': 'ERROR',
                'size': 0,
                'issues': [f'Cannot read file: {str(e)}']
            })
            continue

        size = len(content)
        file_issues = []
        status = 'OK'

        # Check 1: Empty or almost empty files
        if size < 50:
            status = 'Empty'
            file_issues.append('File is empty or has minimal content')

        # Check 2: Missing layouts/sections (for content files)
        is_layout = 'layouts' in rel_path
        is_component = 'components' in rel_path
        is_partial = 'partials' in rel_path or '_' in blade_file.name

        if not is_layout and not is_component and not is_partial and size > 50:
            has_layout = any(pattern in content for pattern in LAYOUT_PATTERNS)
            if not has_layout:
                status = 'Broken' if status == 'OK' else status
                file_issues.append('Missing layout or extends statement')

        # Check 3: Extract route references
        route_refs = re.findall(r"route\(['\"]([^'\"]+)", content)
        broken_routes = []
        for route_ref in route_refs:
            if route_ref not in EXISTING_ROUTES:
                broken_routes.append(route_ref)

        if broken_routes:
            status = 'Broken' if status == 'OK' else status
            file_issues.append(f'References non-existent routes: {", ".join(broken_routes[:3])}')

        # Check 4: Missing CRUD action buttons
        if 'index' in rel_path and status != 'Empty':
            has_create_btn = 'admin.create' in content or 'admin.store' in content or 'New ' in content
            if not has_create_btn:
                file_issues.append('Missing create/new button in index')

            has_actions = any(action in content for action in ['edit', 'delete', 'show', 'destroy'])
            if not has_actions:
                file_issues.append('Missing edit/delete/show buttons in table/list')

        if 'create' in rel_path or 'edit' in rel_path:
            has_submit = 'submit' in content.lower() or 'save' in content.lower()
            if not has_submit:
                file_issues.append('Missing submit/save button in form')

        # Check 5: Missing translation support
        if size > 100 and not is_layout and not is_component:
            text_patterns = re.findall(r"[>]([A-Z][^<]*[a-z][^<]*)<", content)
            has_translations = any(pattern in content for pattern in TRANSLATION_PATTERNS)

            if not has_translations and len(text_patterns) > 2:
                file_issues.append('May be missing translation support (hard-coded text detected)')

        if file_issues:
            status = 'Broken' if status == 'OK' else status

        report_entry = {
            'file': rel_path,
            'status': status,
            'size': size,
            'issues': file_issues,
            'route_refs': route_refs[:5]  # First 5 route refs
        }

        report.append(report_entry)
        if file_issues:
            issues[status].extend([(rel_path, issue) for issue in file_issues])

    return report, issues

def print_report(report, issues):
    print("=" * 120)
    print("BLADE FILE ANALYSIS REPORT")
    print("=" * 120)

    # Summary
    ok_count = len([r for r in report if r['status'] == 'OK'])
    empty_count = len([r for r in report if r['status'] == 'Empty'])
    broken_count = len([r for r in report if r['status'] == 'Broken'])
    error_count = len([r for r in report if r['status'] == 'ERROR'])

    print(f"\nSUMMARY:")
    print(f"  Total Files: {len(report)}")
    print(f"  OK: {ok_count}")
    print(f"  Empty: {empty_count}")
    print(f"  Broken: {broken_count}")
    print(f"  Errors: {error_count}")

    print("\n" + "=" * 120)
    print("BROKEN FILES (Issues Found)")
    print("=" * 120)

    broken_files = [r for r in report if r['status'] == 'Broken']
    for entry in broken_files:
        print(f"\n📋 File: resources/views/{entry['file']}")
        print(f"   Status: {entry['status']} | Size: {entry['size']} bytes")
        print(f"   Issues:")
        for issue in entry['issues']:
            print(f"     ❌ {issue}")
        if entry['route_refs']:
            print(f"   Route References: {', '.join(entry['route_refs'][:3])}")

    print("\n" + "=" * 120)
    print("EMPTY FILES")
    print("=" * 120)

    empty_files = [r for r in report if r['status'] == 'Empty']
    for entry in empty_files:
        print(f"\n📋 File: resources/views/{entry['file']}")
        print(f"   Status: {entry['status']} | Size: {entry['size']} bytes")
        print(f"   Issues:")
        for issue in entry['issues']:
            print(f"     ⚠️  {issue}")

    print("\n" + "=" * 120)
    print("DETAILED RECOMMENDATIONS")
    print("=" * 120)

    for status in ['Empty', 'Broken']:
        if issues[status]:
            print(f"\n{status.upper()} FILES:")
            issue_counts = defaultdict(int)
            for file_path, issue in issues[status]:
                issue_counts[issue] += 1

            for issue, count in sorted(issue_counts.items(), key=lambda x: x[1], reverse=True):
                print(f"  • {issue} ({count} files)")

    print("\n" + "=" * 120)
    print("OK FILES (No issues detected)")
    print("=" * 120)

    ok_files = [r for r in report if r['status'] == 'OK']
    if ok_files:
        for entry in ok_files[:10]:  # Show first 10
            print(f"  ✅ resources/views/{entry['file']} ({entry['size']} bytes)")
        if len(ok_files) > 10:
            print(f"  ... and {len(ok_files) - 10} more OK files")

    return broken_files, empty_files, ok_files

if __name__ == '__main__':
    report, issues = analyze_blade_files()
    broken, empty, ok = print_report(report, issues)

