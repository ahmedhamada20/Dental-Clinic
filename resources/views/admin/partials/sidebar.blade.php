<aside class="admin-sidebar" id="adminSidebar">
    @php
        $adminRoute = static function (string $name, array $parameters = []): string {
            if (! \Illuminate\Support\Facades\Route::has($name)) {
                return '#';
            }

            try {
                return route($name, $parameters);
            } catch (\Throwable) {
                return '#';
            }
        };

        $clinicName = \App\Models\Clinic\ClinicSetting::getValue(
            'clinic_name',
            config('app.name', 'Clinic System')
        );
    @endphp

        <!-- Sidebar Brand -->
    <div class="sidebar-brand">
        <i class="bi bi-hospital"></i>
        <h3>{{ $clinicName }}</h3>
    </div>

    <!-- Sidebar Menu -->
    <ul class="sidebar-menu">
        <!-- Main Menu -->
        <li class="sidebar-menu-label">{{ __('admin.sidebar.sections.main') }}</li>

        <!-- Dashboard -->
        @can('dashboard.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.dashboard.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-speedometer2"></i>
                    <span>{{ __('admin.sidebar.dashboard') }}</span>
                </a>
            </li>
        @endcan

        <!-- Patient Management Section -->
        <li class="sidebar-menu-label">{{ __('admin.sidebar.sections.patient_management') }}</li>

        <!-- Patients -->
        @can('patients.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.patients.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-people"></i>
                    <span>{{ __('admin.sidebar.patients') }}</span>
                </a>
            </li>
        @endcan

        <!-- Clinic Operations Section -->
        <li class="sidebar-menu-label">{{ __('admin.sidebar.sections.clinic_operations') }}</li>

        <!-- Appointments -->
        @can('appointments.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.appointments.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-calendar2-check"></i>
                    <span>{{ __('admin.sidebar.appointments') }}</span>
                </a>
            </li>
        @endcan

        <!-- Waiting List -->
        @can('waiting-list.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.waiting-list.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-hourglass-split"></i>
                    <span>{{ __('admin.sidebar.waiting_list') }}</span>
                </a>
            </li>
        @endcan

        <!-- Visits -->
        @can('visits.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.visits.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-person-check"></i>
                    <span>{{ __('admin.sidebar.visits') }}</span>
                </a>
            </li>
        @endcan

        <!-- Specialty Modules -->
        @if(($specialtyModules ?? collect())->isNotEmpty())
            @foreach($specialtyModules as $module)
                @foreach($module->navigation as $item)
                    @php
                        $routeName = $item['route'] ?? null;
                        $permission = $item['can'] ?? null;
                        $routeParameters = $item['parameters'] ?? [];
                    @endphp

                    @continue(! $routeName || ! \Illuminate\Support\Facades\Route::has($routeName))

                    @if(! $permission || auth()->user()?->can($permission))
                        @php
                            $specialtyRoute = $adminRoute($routeName, $routeParameters);
                        @endphp

                        @continue($specialtyRoute === '#')

                        <li class="sidebar-menu-item">
                            <a href="{{ $specialtyRoute }}" class="sidebar-menu-link">
                                <i class="{{ $item['icon'] ?? 'bi bi-puzzle' }}"></i>
                                <span>{{ __($item['label']) }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
            @endforeach
        @endif

        {{-- ── TODAY'S VISITS PANEL ─────────────────────────────────── --}}
        @can('visits.view')
        @php
            $todayVisits   = $sidebarTodayVisits ?? collect();
            $todayCount    = $todayVisits->count();
            $todayDate     = now()->toDateString();
            $todayCreateUrl = \Illuminate\Support\Facades\Route::has('admin.visits.create')
                ? route('admin.visits.create')
                : '#';
            $todayIndexUrl = \Illuminate\Support\Facades\Route::has('admin.visits.index')
                ? route('admin.visits.index', ['date' => $todayDate])
                : '#';
        @endphp

        <li class="sidebar-menu-item mt-2">
            <div
                class="sidebar-menu-link d-flex justify-content-between align-items-center"
                style="cursor:pointer; user-select:none;"
                data-bs-toggle="collapse"
                data-bs-target="#todayVisitsPanel"
                aria-expanded="{{ $todayCount > 0 ? 'true' : 'false' }}"
            >
                <span class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar2-check" style="width:24px;text-align:center;"></i>
                    <span>{{ app()->getLocale() === 'ar' ? 'زيارات اليوم' : "Today's Visits" }}</span>
                </span>
                <span class="badge rounded-pill bg-light text-dark" style="font-size:.7rem;">{{ $todayCount }}</span>
            </div>

            <div
                id="todayVisitsPanel"
                class="collapse {{ $todayCount > 0 ? 'show' : '' }}"
                style="background: rgba(0,0,0,.15); border-radius:0 0 6px 6px;"
            >
                {{-- Add Visit button --}}
                @can('visits.create')
                <div class="px-3 pt-2 pb-1">
                    <a href="{{ $todayCreateUrl }}"
                       class="btn btn-sm w-100"
                       style="background:rgba(255,255,255,.15);color:#fff;border:1px dashed rgba(255,255,255,.4);font-size:.78rem;">
                        <i class="bi bi-plus-circle me-1"></i>
                        {{ app()->getLocale() === 'ar' ? 'إضافة زيارة اليوم' : 'Add Today\'s Visit' }}
                    </a>
                </div>
                @endcan

                {{-- List of today's visits --}}
                @if($todayVisits->isEmpty())
                    <div class="px-3 py-2 text-center" style="color:rgba(255,255,255,.5);font-size:.78rem;">
                        {{ app()->getLocale() === 'ar' ? 'لا توجد زيارات اليوم' : 'No visits today' }}
                    </div>
                @else

                    {{-- View all --}}
                    <div class="px-3 pb-2 pt-1 text-center">
                        <a href="{{ $todayIndexUrl }}"
                           style="color:rgba(255,255,255,.6);font-size:.74rem;text-decoration:underline;">
                            {{ app()->getLocale() === 'ar' ? 'عرض الكل' : 'View all' }}
                            ({{ $todayCount }})
                        </a>
                    </div>
                @endif
            </div>
        </li>
        @endcan

        <!-- Medical Management Section -->
        <li class="sidebar-menu-label">{{ __('admin.sidebar.sections.medical_management') }}</li>

        <!-- Specialties -->
        @can('specialties.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.specialties.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-diagram-3"></i>
                    <span>{{ __('admin.sidebar.specialties') }}</span>
                </a>
            </li>
        @endcan

        <!-- Service Categories -->
        @can('service-categories.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.service-categories.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-grid"></i>
                    <span>{{ __('admin.sidebar.service_categories') }}</span>
                </a>
            </li>
        @endcan

        <!-- Services -->
        @can('services.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.services.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-briefcase"></i>
                    <span>{{ __('admin.sidebar.services') }}</span>
                </a>
            </li>
        @endcan

        <!-- Treatment Plans -->
        @can('treatment-plans.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.treatment-plans.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-file-medical"></i>
                    <span>{{ __('admin.sidebar.treatment_plans') }}</span>
                </a>
            </li>
        @endcan

        <!-- Prescriptions -->
        @can('prescriptions.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.prescriptions.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-capsule"></i>
                    <span>{{ __('admin.sidebar.prescriptions') }}</span>
                </a>
            </li>
        @endcan

        <!-- Financial Management Section -->
        <li class="sidebar-menu-label">{{ __('admin.sidebar.sections.financial') }}</li>

        <!-- Billing -->
        @can('billing.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.billing.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-credit-card"></i>
                    <span>{{ __('admin.sidebar.billing') }}</span>
                </a>
            </li>
        @endcan

        <!-- Promotions -->
        @can('promotions.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.promotions.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-tag"></i>
                    <span>{{ __('admin.sidebar.promotions') }}</span>
                </a>
            </li>
        @endcan

        <!-- Communication & Reports Section -->
        <li class="sidebar-menu-label">{{ __('admin.sidebar.sections.reports_communication') }}</li>

        <!-- Notifications -->
        @can('notifications.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.notifications.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-bell"></i>
                    <span>{{ __('admin.sidebar.notifications') }}</span>
                </a>
            </li>
        @endcan

        <!-- Reports -->
        @can('reports.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.reports.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-graph-up"></i>
                    <span>{{ __('admin.sidebar.reports') }}</span>
                </a>
            </li>
        @endcan

        <!-- System Management Section -->
        <li class="sidebar-menu-label">{{ __('admin.sidebar.sections.system') }}</li>

        <!-- Settings -->
        @can('settings.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.settings.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-gear"></i>
                    <span>{{ __('admin.sidebar.settings') }}</span>
                </a>
            </li>
        @endcan

        <!-- Users -->
        @can('users.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.users.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-person-badge"></i>
                    <span>{{ __('admin.sidebar.users') }}</span>
                </a>
            </li>
        @endcan

        <!-- Roles -->
        @can('roles.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.roles.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-shield-lock"></i>
                    <span>{{ __('admin.sidebar.roles') }}</span>
                </a>
            </li>
        @endcan

        <!-- Audit Logs -->
        @can('audit-logs.view')
            <li class="sidebar-menu-item">
                <a href="{{ $adminRoute('admin.audit-logs.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-journal-text"></i>
                    <span>{{ __('admin.sidebar.audit_logs') }}</span>
                </a>
            </li>
        @endcan
    </ul>
</aside>
