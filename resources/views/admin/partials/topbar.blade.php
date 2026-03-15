@php
    $routeOrHash = static fn (string $name, array $parameters = []) =>
        \Illuminate\Support\Facades\Route::has($name) ? route($name, $parameters) : '#';
@endphp

<nav class="admin-topbar">
    <div class="topbar-content">
        <!-- Left Section -->
        <div class="d-flex align-items-center gap-3">
            <!-- Sidebar Toggle (Mobile) -->
            <button class="btn btn-link sidebar-toggle d-md-none p-0" type="button" aria-label="{{ __('admin.topbar.toggle_sidebar') }}">
                <i class="bi bi-list fs-5" style="color: #6c757d;"></i>
            </button>

            <!-- Search Bar -->
            <div class="topbar-search d-none d-lg-block">
                <input
                    type="text"
                    class="form-control"
                    placeholder="{{ __('common.search_placeholder') }}"
                    id="globalSearch"
                >
            </div>
        </div>

        <!-- Right Section -->
        <div class="topbar-user">
            <!-- Notifications -->
            <div class="dropdown">
                <button
                    class="btn btn-link position-relative p-0"
                    type="button"
                    id="notificationsDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    title="{{ __('admin.sidebar.notifications') }}"
                >
                    <i class="bi bi-bell-fill fs-5" style="color: #6c757d;"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                        3
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="width: 350px;">
                    <li><h6 class="dropdown-header">{{ __('admin.sidebar.notifications') }}</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <div class="dropdown-item-text" style="white-space: normal;">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('admin.topbar.new_appointment_request') }}</span>
                                <small class="text-muted">5m</small>
                            </div>
                            <small class="text-muted">{{ __('admin.topbar.new_appointment_request_details') }}</small>
                        </div>
                    </li>
                    <li>
                        <div class="dropdown-item-text" style="white-space: normal;">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('admin.topbar.invoice_payment_received') }}</span>
                                <small class="text-muted">1h</small>
                            </div>
                            <small class="text-muted">{{ __('admin.topbar.invoice_payment_received_details') }}</small>
                        </div>
                    </li>
                    <li>
                        <div class="dropdown-item-text" style="white-space: normal;">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('admin.topbar.appointment_reminder') }}</span>
                                <small class="text-muted">2h</small>
                            </div>
                            <small class="text-muted">{{ __('admin.topbar.appointment_reminder_details') }}</small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a href="{{ $routeOrHash('admin.notifications.index') }}" class="dropdown-item text-center">{{ __('admin.topbar.view_all_notifications') }}</a></li>
                </ul>
            </div>

            <!-- Messages -->
            <div class="dropdown">
                <button
                    class="btn btn-link position-relative p-0"
                    type="button"
                    id="messagesDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    title="{{ __('admin.topbar.messages') }}"
                >
                    <i class="bi bi-chat-left-dots-fill fs-5" style="color: #6c757d;"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size: 0.65rem;">
                        2
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="messagesDropdown" style="width: 350px;">
                    <li><h6 class="dropdown-header">{{ __('admin.topbar.messages') }}</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <div class="dropdown-item-text" style="white-space: normal;">
                            <div class="d-flex gap-2">
                                <div class="user-avatar" style="width: 30px; height: 30px; font-size: 0.75rem;">MD</div>
                                <div style="flex: 1;">
                                    <strong>Dr. Mohammad</strong>
                                    <br>
                                    <small class="text-muted">{{ __('common.placeholder_message') }}</small>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="dropdown-item-text" style="white-space: normal;">
                            <div class="d-flex gap-2">
                                <div class="user-avatar" style="width: 30px; height: 30px; font-size: 0.75rem;">SA</div>
                                <div style="flex: 1;">
                                    <strong>Sarah Ahmed</strong>
                                    <br>
                                    <small class="text-muted">{{ __('common.placeholder_message') }}</small>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><span class="dropdown-item-text text-center text-muted">{{ __('admin.topbar.view_all_messages') }}</span></li>
                </ul>
            </div>

            <!-- Language Selector -->
            <div class="dropdown">
                <button
                    class="btn btn-link p-0"
                    type="button"
                    id="languageDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    title="{{ __('admin.language.label') }}"
                >
                    <i class="bi bi-globe fs-5" style="color: #6c757d;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                    <li>
                        <a href="{{ $routeOrHash('language.switch', ['language' => 'en']) }}" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                            <span>{{ __('admin.language.english') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $routeOrHash('language.switch', ['language' => 'ar']) }}" class="dropdown-item {{ app()->getLocale() === 'ar' ? 'active' : '' }}">
                            <span>{{ __('admin.language.arabic') }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- User Profile Dropdown -->
            <div class="dropdown ms-3">
                <button
                    class="btn btn-link p-0 d-flex align-items-center gap-2"
                    type="button"
                    id="userDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <span class="d-none d-lg-inline text-dark" style="font-size: 0.9rem;">
                        {{ auth()->user()->name }}
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><h6 class="dropdown-header">{{ auth()->user()->name }}</h6></li>
                    <li><small class="dropdown-header text-muted">{{ auth()->user()->email }}</small></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a href="{{ $routeOrHash('profile.edit') }}" class="dropdown-item">
                            <i class="bi bi-person me-2"></i>{{ __('admin.topbar.profile') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ $routeOrHash('admin.settings.index') }}" class="dropdown-item">
                            <i class="bi bi-gear me-2"></i>{{ __('admin.sidebar.settings') }}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ $routeOrHash('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item" style="border: none; background: none; cursor: pointer; width: 100%; text-align: start;">
                                <i class="bi bi-box-arrow-right me-2"></i>{{ __('admin.topbar.logout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

