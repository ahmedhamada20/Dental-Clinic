#!/usr/bin/env python3
"""
Translation Audit Script for Dental Clinic System
Scans all admin blade files and checks translation keys against ar/ and en/ lang files.
"""

import os
import re
import json
import subprocess
from pathlib import Path
from collections import defaultdict

BASE_DIR = Path(__file__).parent
VIEWS_DIR = BASE_DIR / "resources" / "views" / "admin"
LANG_DIR = BASE_DIR / "resources" / "lang"
AR_DIR = LANG_DIR / "ar"
EN_DIR = LANG_DIR / "en"
REPORT_FILE = BASE_DIR / "TRANSLATION_AUDIT_REPORT.md"

# ── 1. Parse PHP lang files ────────────────────────────────────────────────────

def parse_php_file(php_path: Path) -> dict:
    """Load a PHP file that returns an array and return it as a flat dict."""
    try:
        result = subprocess.run(
            ["php", "-r", f"echo json_encode(include('{str(php_path).replace(chr(92), '/')}'));"],
            capture_output=True, text=True, timeout=10
        )
        if result.returncode == 0 and result.stdout.strip():
            return json.loads(result.stdout)
    except Exception:
        pass
    return {}


def flatten(d: dict, prefix: str = "") -> set:
    """Flatten a nested dict into dot-notation keys."""
    keys = set()
    for k, v in d.items():
        full_key = f"{prefix}.{k}" if prefix else k
        if isinstance(v, dict):
            keys |= flatten(v, full_key)
        else:
            keys.add(full_key)
    return keys


def load_locale(lang_dir: Path) -> dict:
    """Load all PHP lang files in a directory into a dict keyed by file stem."""
    locale = {}
    for php_file in lang_dir.glob("*.php"):
        data = parse_php_file(php_file)
        locale[php_file.stem] = flatten(data)
    return locale


# ── 2. Extract translation keys from blade files ──────────────────────────────

# Matches __('key'), __("key"), trans('key'), @lang('key')
TRANS_PATTERN = re.compile(
    r"""(?:__|\btrans\b|@lang)\s*\(\s*['"]([^'"]+)['"]\s*[,)]"""
)
# Detect dynamic keys (variables or concatenations)
DYNAMIC_PATTERN = re.compile(
    r"""(?:__|\btrans\b|@lang)\s*\(\s*(?!\s*['"])"""
)


def scan_blade_file(blade_path: Path):
    """Return list of (key, line_number) tuples found in a blade file."""
    static_keys = []
    dynamic_count = 0
    with open(blade_path, encoding="utf-8", errors="ignore") as f:
        for lineno, line in enumerate(f, 1):
            for match in TRANS_PATTERN.finditer(line):
                static_keys.append((match.group(1), lineno))
            if DYNAMIC_PATTERN.search(line):
                dynamic_count += 1
    return static_keys, dynamic_count


# ── 3. Cross-reference ────────────────────────────────────────────────────────

def check_key(key: str, ar_locale: dict, en_locale: dict):
    """
    Returns (ar_missing: bool, en_missing: bool, file_stem: str, sub_key: str)
    """
    parts = key.split(".", 1)
    if len(parts) < 2:
        return True, True, parts[0], ""

    file_stem, sub_key = parts[0], parts[1]

    ar_keys = ar_locale.get(file_stem, None)
    en_keys = en_locale.get(file_stem, None)

    ar_missing = ar_keys is None or sub_key not in ar_keys
    en_missing = en_keys is None or sub_key not in en_keys

    return ar_missing, en_missing, file_stem, sub_key


# ── 4. Main audit ─────────────────────────────────────────────────────────────

def main():
    print("Loading language files...")
    ar_locale = load_locale(AR_DIR)
    en_locale = load_locale(EN_DIR)

    print(f"  AR files loaded: {list(ar_locale.keys())}")
    print(f"  EN files loaded: {list(en_locale.keys())}")

    blade_files = sorted(VIEWS_DIR.rglob("*.blade.php"))
    print(f"\nScanning {len(blade_files)} blade files...\n")

    # Results structure: {blade_rel_path: {"keys": [(key, line, ar_miss, en_miss)], "dynamic": int}}
    results = {}
    summary = {
        "total_keys": 0,
        "ok": 0,
        "missing_ar": 0,
        "missing_en": 0,
        "missing_both": 0,
        "dynamic": 0,
        "unknown_file": 0,
    }

    missing_ar_by_file = defaultdict(set)   # lang file stem -> set of sub_keys
    missing_en_by_file = defaultdict(set)

    for blade in blade_files:
        rel = blade.relative_to(BASE_DIR)
        static_keys, dynamic_count = scan_blade_file(blade)
        summary["dynamic"] += dynamic_count

        file_results = []
        for key, lineno in static_keys:
            summary["total_keys"] += 1
            ar_miss, en_miss, file_stem, sub_key = check_key(key, ar_locale, en_locale)

            if not ar_miss and not en_miss:
                summary["ok"] += 1
            elif ar_miss and en_miss:
                summary["missing_both"] += 1
            elif ar_miss:
                summary["missing_ar"] += 1
                missing_ar_by_file[file_stem].add(sub_key)
            elif en_miss:
                summary["missing_en"] += 1
                missing_en_by_file[file_stem].add(sub_key)

            if ar_miss or en_miss:
                file_results.append((key, lineno, ar_miss, en_miss, file_stem, sub_key))

        if file_results or dynamic_count > 0:
            results[str(rel)] = {"keys": file_results, "dynamic": dynamic_count}

    # ── 5. Write report ───────────────────────────────────────────────────────
    print("Writing report...")
    with open(REPORT_FILE, "w", encoding="utf-8") as rpt:
        rpt.write("# Translation Audit Report\n\n")
        rpt.write(f"**Date:** {os.popen('date /t').read().strip()}  \n")
        rpt.write(f"**Blade files scanned:** {len(blade_files)}  \n")
        rpt.write(f"**Total static translation keys found:** {summary['total_keys']}  \n\n")

        # Summary table
        rpt.write("## Summary\n\n")
        rpt.write("| Status | Count |\n")
        rpt.write("|--------|-------|\n")
        rpt.write(f"| ✅ Present in both AR & EN | {summary['ok']} |\n")
        rpt.write(f"| ⚠️ Missing in AR only | {summary['missing_ar']} |\n")
        rpt.write(f"| ⚠️ Missing in EN only | {summary['missing_en']} |\n")
        rpt.write(f"| ❌ Missing in BOTH | {summary['missing_both']} |\n")
        rpt.write(f"| 🔄 Dynamic keys (unverifiable) | {summary['dynamic']} |\n\n")

        # Missing files
        ar_files = set(ar_locale.keys())
        en_files = set(en_locale.keys())
        all_files = ar_files | en_files
        rpt.write("## Lang File Coverage\n\n")
        rpt.write("| File | AR exists | EN exists |\n")
        rpt.write("|------|-----------|----------|\n")
        for f in sorted(all_files):
            ar_x = "✅" if f in ar_files else "❌ **MISSING**"
            en_x = "✅" if f in en_files else "❌ **MISSING**"
            rpt.write(f"| {f}.php | {ar_x} | {en_x} |\n")
        rpt.write("\n")

        # Missing keys grouped by blade file
        if results:
            rpt.write("## Missing Keys by Blade File\n\n")
            for blade_rel, data in sorted(results.items()):
                if data["keys"]:
                    rpt.write(f"### `{blade_rel}`\n\n")
                    rpt.write("| Line | Key | Missing in |\n")
                    rpt.write("|------|-----|------------|\n")
                    for key, lineno, ar_miss, en_miss, _, _ in sorted(data["keys"], key=lambda x: x[1]):
                        missing_in = []
                        if ar_miss:
                            missing_in.append("🇸🇦 AR")
                        if en_miss:
                            missing_in.append("🇬🇧 EN")
                        rpt.write(f"| {lineno} | `{key}` | {', '.join(missing_in)} |\n")
                    rpt.write("\n")
                if data["dynamic"] > 0:
                    rpt.write(f"> ⚡ **{data['dynamic']} dynamic key(s)** found in this file (cannot be statically verified)\n\n")
        else:
            rpt.write("## ✅ No Missing Keys Found!\n\n")
            rpt.write("All translation keys found in blade files exist in both AR and EN lang files.\n\n")

        # Missing keys grouped by lang file (for easy fixing)
        rpt.write("## Keys to Add — Grouped by Lang File\n\n")

        all_missing_files = set(missing_ar_by_file.keys()) | set(missing_en_by_file.keys())
        if not all_missing_files:
            rpt.write("✅ Nothing to add!\n\n")
        else:
            for stem in sorted(all_missing_files):
                ar_missing = missing_ar_by_file.get(stem, set())
                en_missing = missing_en_by_file.get(stem, set())
                all_for_stem = ar_missing | en_missing
                rpt.write(f"### `{stem}.php`\n\n")
                rpt.write("| Key | Needs AR | Needs EN |\n")
                rpt.write("|-----|----------|----------|\n")
                for k in sorted(all_for_stem):
                    needs_ar = "❌" if k in ar_missing else "✅"
                    needs_en = "❌" if k in en_missing else "✅"
                    rpt.write(f"| `{k}` | {needs_ar} | {needs_en} |\n")
                rpt.write("\n")

        rpt.write("---\n_Generated by `audit_translations.py`_\n")

    print(f"\nReport written to: {REPORT_FILE}")
    print(f"\n{'='*60}")
    print(f"SUMMARY:")
    print(f"  Blade files scanned : {len(blade_files)}")
    print(f"  Total keys          : {summary['total_keys']}")
    print(f"  OK (both locales)   : {summary['ok']}")
    print(f"  Missing AR          : {summary['missing_ar']}")
    print(f"  Missing EN          : {summary['missing_en']}")
    print(f"  Missing BOTH        : {summary['missing_both']}")
    print(f"  Dynamic (unverif.)  : {summary['dynamic']}")
    print(f"{'='*60}\n")


if __name__ == "__main__":
    main()

