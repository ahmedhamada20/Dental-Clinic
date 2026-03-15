<?php

namespace App\Providers;

use App\Http\ViewComposers\SidebarComposer;
use App\Support\SpecialtyModule;
use App\Support\SpecialtyModuleRegistry;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SpecialtyModuleRegistry::class, function () {
            $registry = new SpecialtyModuleRegistry();

            $registry->register(new SpecialtyModule(
                key: 'dental',
                name: 'Dental',
                description: 'Dental-specific charts and longitudinal tooth records.',
                specialtySlugs: ['dental'],
                enabled: (bool) config('specialty-modules.dental.enabled', true),
                adminRoutesPath: app_path('Modules/Dental/Routes/admin.php'),
                navigation: [],
            ));

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Render paginator links with Bootstrap 5 markup across the app.
        Paginator::useBootstrapFive();

        $registry = $this->app->make(SpecialtyModuleRegistry::class);

        View::composer('admin.*', function ($view) use ($registry): void {
            $view->with('specialtyModules', $registry->all());
        });

        // Inject today's visits into the sidebar partial only.
        View::composer('admin.partials.sidebar', SidebarComposer::class);

        foreach ($registry->all() as $module) {
            if ($module->adminRoutesPath && File::exists($module->adminRoutesPath)) {
                require $module->adminRoutesPath;
            }
        }
    }
}
