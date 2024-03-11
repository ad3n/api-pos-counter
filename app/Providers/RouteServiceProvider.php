<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $userApiNamespace = 'App\Http\Controllers\API\v2';

    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $adminApiNamespace = 'App\Http\Controllers\API\admin';

    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $frontNamespace = 'App\Http\Controllers\Web';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            $this->mapUserApiRoutes();

            $this->mapAdminApiRoutes();

            $this->mapTenantApiRoutes();
            // Route::middleware('api')
            //     ->prefix('api')
            //     ->group(base_path('routes/api.php'));

            // Route::middleware('web')
            //     ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::domain(env('DOMAIN_WEB'))
            ->middleware('web')
            ->namespace($this->frontNamespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapUserApiRoutes()
    {
        Route::domain(env('DOMAIN_API'))
            ->prefix('api')
            ->middleware('api')
            ->namespace($this->userApiNamespace)
            ->group(base_path('routes/user.php'));
    }

    /**
     * Define the "admin API" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapAdminApiRoutes()
    {
        Route::domain(env('DOMAIN_API'))
            ->prefix('admin')
            ->middleware('api')
            ->namespace($this->adminApiNamespace)
            ->group(base_path('routes/admin.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapTenantApiRoutes()
    {
        Route::domain(env('DOMAIN_TENANT'))
            ->prefix('emp')
            ->middleware('api')
            ->group(base_path('routes/employee.php'));
    }
}
