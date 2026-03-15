<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ __('admin.layout.meta_description') }}">
    <meta name="theme-color" content="#667eea">
    <title>@yield('title', __('admin.layout.default_title')) - {{ config('app.name', 'Dental Clinic') }}</title>

    <!-- ========== STYLESHEETS ========== -->

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Google Fonts - Support for Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <style>
        /* ========== CSS VARIABLES ========== */
        :root {
            --sidebar-width: 260px;
            --topbar-height: 70px;
            --primary-color: #667eea;
            --primary-dark: #5568d3;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
        }

        /* ========== GLOBAL STYLES ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        html[dir="rtl"] {
            direction: rtl;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Roboto, 'Cairo', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #212529;
            line-height: 1.5;
        }

        html[lang^="ar"] body {
            font-family: 'Cairo', 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* ========== LAYOUT STRUCTURE ========== */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Positioning */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            overflow-y: auto;
            position: fixed;
            height: 100vh;
            top: 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        html[dir="ltr"] .admin-sidebar {
            left: 0;
        }

        html[dir="rtl"] .admin-sidebar {
            right: 0;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Content Area */
        .admin-content {
            margin-top: var(--topbar-height);
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        html[dir="ltr"] .admin-content {
            margin-left: var(--sidebar-width);
        }

        html[dir="rtl"] .admin-content {
            margin-right: var(--sidebar-width);
        }

        /* ========== TOPBAR STYLES ========== */
        .admin-topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            height: var(--topbar-height);
            background: white;
            border-bottom: 1px solid var(--border-color);
            z-index: 999;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }

        html[dir="ltr"] .admin-topbar {
            margin-left: var(--sidebar-width);
        }

        html[dir="rtl"] .admin-topbar {
            margin-right: var(--sidebar-width);
        }

        .topbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 100%;
            padding: 0 30px;
            gap: 20px;
        }

        html[dir="rtl"] .topbar-content {
            flex-direction: row-reverse;
        }

        /* Sidebar Toggle Button */
        .sidebar-toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.5rem;
            color: #212529;
            padding: 5px 10px;
            transition: color 0.3s ease;
        }

        .sidebar-toggle:hover {
            color: var(--primary-color);
        }

        /* Search Bar */
        .topbar-search {
            flex: 1;
            max-width: 400px;
        }

        .topbar-search input {
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 8px 15px;
            width: 100%;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .topbar-search input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        /* User Menu */
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        html[dir="rtl"] .topbar-user {
            flex-direction: row-reverse;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        html[dir="rtl"] .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #212529;
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--secondary-color);
        }

        /* Language & Settings Dropdowns */
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        html[dir="rtl"] .topbar-actions {
            flex-direction: row-reverse;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #6c757d;
            padding: 5px 10px;
            transition: color 0.3s ease;
        }

        .action-btn:hover {
            color: var(--primary-color);
        }

        .dropdown-menu {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-width: 180px;
        }

        .dropdown-item {
            padding: 10px 16px;
            transition: background-color 0.2s ease;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: var(--light-bg);
            color: var(--primary-color);
        }

        .dropdown-divider {
            margin: 5px 0;
        }

        /* ========== SIDEBAR STYLES ========== */
        .sidebar-brand {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        html[dir="rtl"] .sidebar-brand {
            flex-direction: row-reverse;
        }

        .sidebar-brand i {
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        .sidebar-brand h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .sidebar-brand small {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        /* Menu */
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu-item {
            margin: 0;
        }

        .sidebar-menu-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        html[dir="rtl"] .sidebar-menu-link {
            border-left: none;
            border-right: 3px solid transparent;
        }

        .sidebar-menu-link i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-menu-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
            border-left-color: white;
        }

        html[dir="rtl"] .sidebar-menu-link.active {
            border-left-color: transparent;
            border-right-color: white;
        }

        .sidebar-menu-divider {
            margin: 15px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu-label {
            padding: 10px 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            letter-spacing: 0.5px;
        }

        /* ========== MAIN CONTENT ========== */
        .admin-main {
            padding: 30px;
            flex: 1;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        html[dir="rtl"] .page-header {
            flex-direction: row-reverse;
        }

        .page-title {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
        }

        /* ========== BREADCRUMB ========== */
        .breadcrumb-section {
            margin-bottom: 20px;
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-item.active {
            color: var(--secondary-color);
        }

        /* ========== ALERTS & MESSAGES ========== */
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideInDown 0.3s ease;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert i {
            flex-shrink: 0;
            font-size: 1.2rem;
            margin-top: 2px;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border-left: 4px solid #198754;
        }

        html[dir="rtl"] .alert-success {
            border-left: none;
            border-right: 4px solid #198754;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
            border-left: 4px solid #dc3545;
        }

        html[dir="rtl"] .alert-danger {
            border-left: none;
            border-right: 4px solid #dc3545;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #664d03;
            border-left: 4px solid #ffc107;
        }

        html[dir="rtl"] .alert-warning {
            border-left: none;
            border-right: 4px solid #ffc107;
        }

        .alert-info {
            background-color: #cfe2ff;
            color: #084298;
            border-left: 4px solid #0dcaf0;
        }

        html[dir="rtl"] .alert-info {
            border-left: none;
            border-right: 4px solid #0dcaf0;
        }

        .alert-dismissible .btn-close {
            padding: 0.5rem;
        }

        /* ========== FORM VALIDATION ========== */
        .invalid-feedback,
        .valid-feedback {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }

        .invalid-feedback {
            color: var(--danger-color);
        }

        .valid-feedback {
            color: var(--success-color);
        }

        .form-control.is-invalid,
        .form-select.is-invalid,
        .form-check-input.is-invalid {
            border-color: var(--danger-color);
        }

        .form-control.is-invalid:focus,
        .form-select.is-invalid:focus {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }

        /* ========== CARDS ========== */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
            border-radius: 8px 8px 0 0;
            padding: 20px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        /* ========== BUTTONS ========== */
        .btn {
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* ========== DATATABLES ========== */
        .dataTables_wrapper {
            margin-bottom: 20px;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 0.9rem;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.active {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
        }

        /* ========== FOOTER ========== */
        .admin-footer {
            background: white;
            border-top: 1px solid var(--border-color);
            padding: 20px 30px;
            text-align: center;
            color: var(--secondary-color);
            font-size: 0.875rem;
            margin-top: auto;
        }

        html[dir="rtl"] .admin-footer {
            text-align: right;
        }

        /* ========== UTILITIES ========== */
        .text-muted {
            color: var(--secondary-color) !important;
        }

        .font-weight-bold {
            font-weight: 700 !important;
        }

        .gap-3 {
            gap: 1rem !important;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.25em;
        }

        /* ========== RESPONSIVE DESIGN ========== */
        @media (max-width: 768px) {
            :root {
                --topbar-height: 70px;
            }

            .admin-sidebar {
                width: 260px;
                transform: translateX(-260px);
            }

            html[dir="rtl"] .admin-sidebar {
                transform: translateX(260px);
            }

            .admin-sidebar.active {
                transform: translateX(0);
            }

            .admin-content {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .admin-topbar {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .admin-main {
                padding: 20px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .topbar-content {
                padding: 0 15px;
            }

            .topbar-search {
                display: none;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            html[dir="rtl"] .page-header {
                flex-direction: column;
                align-items: flex-end;
            }

            .sidebar-brand h3 {
                font-size: 1rem;
            }

            .user-info {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .topbar-content {
                gap: 10px;
            }

            .admin-main {
                padding: 15px;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .page-header {
                margin-bottom: 20px;
            }

            .alert {
                padding: 12px 15px;
                font-size: 0.9rem;
            }

            .card {
                margin-bottom: 15px;
            }

            .card-body {
                padding: 15px;
            }

            .btn {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
        }

        /* ========== SIDEBAR TODAY VISITS PANEL ========== */
        #todayVisitsPanel ul::-webkit-scrollbar {
            width: 4px;
        }
        #todayVisitsPanel ul::-webkit-scrollbar-track {
            background: transparent;
        }
        #todayVisitsPanel ul::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.25);
            border-radius: 4px;
        }
        #todayVisitsPanel ul::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,.45);
        }

        /* ========== PRINT STYLES ========== */
        @media print {
            .admin-sidebar,
            .admin-topbar,
            .admin-footer {
                display: none;
            }

            .admin-content {
                margin: 0;
            }

            .admin-main {
                padding: 0;
            }

            .alert {
                display: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="admin-wrapper {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <!-- Sidebar Navigation -->
        @include('admin.partials.sidebar')

        <!-- Main Content Area -->
        <div class="admin-content">
            <!-- Top Navigation Bar -->
            @include('admin.partials.topbar')

            <!-- Page Content -->
            <div class="admin-main">
                <!-- Breadcrumb Navigation -->
                @if (View::hasSection('breadcrumb'))
                    <nav class="breadcrumb-section" aria-label="{{ __('admin.layout.breadcrumb_label') }}">
                        @yield('breadcrumb')
                    </nav>
                @endif

                <!-- Page Header -->
                <div class="page-header">
                    <h1 class="page-title">@yield('title', __('admin.sidebar.dashboard'))</h1>
                </div>

                <!-- Flash Messages & Alerts -->
                <div id="alerts-container">
                    {{-- Success Message --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <strong>{{ __('admin.common.success') }}</strong>
                                {{ session('success') }}
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
                        </div>
                    @endif

                    {{-- Error Message --}}
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <div>
                                <strong>{{ __('admin.common.error') }}</strong>
                                {{ session('error') }}
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
                        </div>
                    @endif

                    {{-- Warning Message --}}
                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>
                                <strong>{{ __('admin.common.warning') }}</strong>
                                {{ session('warning') }}
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
                        </div>
                    @endif

                    {{-- Info Message --}}
                    @if (session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="bi bi-info-circle-fill"></i>
                            <div>
                                <strong>{{ __('admin.common.information') }}</strong>
                                {{ session('info') }}
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
                        </div>
                    @endif

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <div>
                                <strong>{{ __('admin.common.validation_errors') }}</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
                        </div>
                    @endif
                </div>

                <!-- Main Page Content -->
                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="admin-footer">
                <div class="container-fluid">
                    <p class="mb-0">
                        &copy; {{ date('Y') }} {{ config('app.name', 'Dental Clinic System') }} -
                        {{ __('admin.layout.all_rights_reserved') }}
                    </p>
                </div>
            </footer>
        </div>
    </div>

    <!-- ========== SCRIPTS ========== -->

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Locale-specific UI alignment fixes when using default Bootstrap build in RTL */
        .rtl .dropdown-menu-end {
            right: auto;
            left: 0;
        }

        .rtl .me-1 { margin-right: 0 !important; margin-left: .25rem !important; }
        .rtl .me-2 { margin-right: 0 !important; margin-left: .5rem !important; }
        .rtl .me-3 { margin-right: 0 !important; margin-left: 1rem !important; }
        .rtl .ms-1 { margin-left: 0 !important; margin-right: .25rem !important; }
        .rtl .ms-2 { margin-left: 0 !important; margin-right: .5rem !important; }
        .rtl .ms-3 { margin-left: 0 !important; margin-right: 1rem !important; }

        .rtl .table td.text-end,
        .rtl .table th.text-end {
            text-align: left !important;
        }

        .rtl .input-group .form-control,
        .rtl .input-group .form-select,
        .rtl .form-control,
        .rtl .form-select,
        .rtl textarea {
            text-align: right;
        }

        .rtl .modal-header .btn-close {
            margin: 0;
            margin-right: auto;
        }

        .rtl .pagination {
            direction: ltr;
        }

        .rtl .text-start {
            text-align: right !important;
        }

        .rtl .text-end {
            text-align: left !important;
        }

        .rtl .btn-group > .btn + .btn,
        .rtl .btn-group-vertical > .btn + .btn {
            margin-left: 0;
            margin-right: -1px;
        }
    </style>

    <!-- Core Application Scripts -->
    <script>
        const i18n = {
            success: @json(__('admin.common.success')),
            error: @json(__('admin.common.error')),
            warning: @json(__('admin.common.warning')),
        };

        document.addEventListener('DOMContentLoaded', function() {
            'use strict';

            // ========== SIDEBAR TOGGLE ==========
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const adminSidebar = document.querySelector('.admin-sidebar');

            if (sidebarToggle && adminSidebar) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    adminSidebar.classList.toggle('active');
                });

                // Close sidebar when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.sidebar-toggle') && !e.target.closest('.admin-sidebar')) {
                        adminSidebar.classList.remove('active');
                    }
                });
            }

            // ========== ACTIVE MENU ITEM ==========
            const currentPath = window.location.pathname;
            const menuLinks = document.querySelectorAll('.sidebar-menu-link');

            menuLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && (currentPath === href || currentPath.includes(href))) {
                    link.classList.add('active');
                }
            });

            // ========== AUTO-DISMISS ALERTS ==========
            const alerts = document.querySelectorAll('#alerts-container .alert');
            alerts.forEach(alert => {
                // Auto-dismiss after 5 seconds (but allow manual close)
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // ========== CSRF TOKEN FOR AJAX ==========
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ========== FORM VALIDATION ==========
            // Add Bootstrap validation styling
            const forms = document.querySelectorAll('form[novalidate]');
            forms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    if (!this.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    this.classList.add('was-validated');
                });
            });

            // ========== TOOLTIP & POPOVER INITIALIZATION ==========
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // ========== CONFIRM DIALOGS ==========
            // Add data-confirm attribute to links/buttons for confirmation
            document.querySelectorAll('[data-confirm]').forEach(el => {
                el.addEventListener('click', function(e) {
                    if (!confirm(this.getAttribute('data-confirm'))) {
                        e.preventDefault();
                    }
                });
            });
        });

        // ========== UTILITY FUNCTIONS ==========

        /**
         * Show success notification
         */
        function showSuccess(message) {
            const alertHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>
                        <strong>${i18n.success}</strong>
                        ${message}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            insertAlert(alertHTML);
        }

        /**
         * Show error notification
         */
        function showError(message) {
            const alertHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div>
                        <strong>${i18n.error}</strong>
                        ${message}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            insertAlert(alertHTML);
        }

        /**
         * Show warning notification
         */
        function showWarning(message) {
            const alertHTML = `
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>${i18n.warning}</strong>
                        ${message}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            insertAlert(alertHTML);
        }

        /**
         * Insert alert into the page
         */
        function insertAlert(alertHTML) {
            const container = document.getElementById('alerts-container');
            if (container) {
                container.insertAdjacentHTML('beforeend', alertHTML);
                // Auto-dismiss after 5 seconds
                const alert = container.lastElementChild;
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            }
        }
    </script>

    @stack('scripts')
</body>
</html>

