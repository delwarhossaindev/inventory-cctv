<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Older MySQL/InnoDB row formats cap index keys at 1000 bytes;
        // with utf8mb4 a 255-char unique column would exceed that.
        Schema::defaultStringLength(191);

        // @money(1234.5) => "৳ 1,234.50"
        Blade::directive('money', function ($expression) {
            return "<?php echo '৳ ' . number_format((float) ($expression), 2); ?>";
        });

        // Render pagination with Bootstrap 5 markup (the app uses Bootstrap, not Tailwind).
        Paginator::useBootstrapFive();

        // Share business settings with all views.
        View::composer('*', function ($view) {
            static $settings;
            if ($settings === null && Schema::hasTable('settings')) {
                $settings = Setting::getAll();
            }
            $view->with('appSettings', $settings ?? []);
        });
    }
}
