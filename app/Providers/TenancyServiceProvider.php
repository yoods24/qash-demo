<?php

declare(strict_types=1);

namespace App\Providers;

use Livewire\Livewire;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;
use Illuminate\Support\Facades\URL;
use Stancl\JobPipeline\JobPipeline;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Controllers\FilePreviewController;
use Stancl\Tenancy\Controllers\TenantAssetsController;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

class TenancyServiceProvider extends ServiceProvider
{
    // By default, no namespace is used to support the callable array syntax.
    public static string $controllerNamespace = '';

    public function events()
    {
        return [
            // Tenant events
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                // Seed default navigation/action permissions for the new tenant (shared DB)
                function ($event) {
                    try {
                        if (function_exists('tenancy')) {
                            tenancy()->initialize($event->tenant);
                        }
                        (new \Database\Seeders\TenantNavigationPermissionsSeeder())->run();
                    } finally {
                        if (function_exists('tenancy')) {
                            tenancy()->end();
                        }
                    }
                },
            ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class => [
                // Shared-DB mode: nothing to drop at DB level.
            ],

            // Domain events
            Events\CreatingDomain::class => [],
            Events\DomainCreated::class => [],
            Events\SavingDomain::class => [],
            Events\DomainSaved::class => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class => [],

            // Database events
            Events\DatabaseCreated::class => [],
            Events\DatabaseMigrated::class => [],
            Events\DatabaseSeeded::class => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class => [],

            // Tenancy events
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],

            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],

            Events\BootstrappingTenancy::class => [],
            Events\TenancyBootstrapped::class => [],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],

            // Resource syncing
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],

            // Fired only when a synced resource is changed in a different DB than the origin DB (to avoid infinite loops)
            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

    public function register()
    {
        //
    }

    public function boot()
    {
        $this->bootEvents();
        $this->mapRoutes();

        $this->makeTenancyMiddlewareHighestPriority();
        TenantAssetsController::$tenancyMiddleware = InitializeTenancyByPath::class;


        // Provide default {tenant} param to route() when tenancy is active (path-based)
        Event::listen(Events\TenancyInitialized::class, function ($event) {
            $tenant = tenant();
            $id = $tenant?->id;

            if ($id) {
                URL::defaults(['tenant' => $id]);
            }

            View::share('currentTenant', $tenant);
        });

        Event::listen(Events\TenancyEnded::class, function () {
            URL::defaults([]);
            View::share('currentTenant', null);
        });

        // Configure RequestData identification
        InitializeTenancyByRequestData::$header = 'X-Tenant';      // optional, this is default
        InitializeTenancyByRequestData::$queryParameter = null;    // only use header

        // 👇 IMPORTANT: don't throw if tenant can't be identified (e.g. central app Livewire)
        InitializeTenancyByRequestData::$onFail = function ($exception, $request, $next) {
            // Just continue without tenancy instead of throwing
            return $next($request);
        };

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware([
                    'web',
                    InitializeTenancyByRequestData::class,
                ]);
        });

        // Livewire file preview endpoint
        // FilePreviewController::$middleware = [
        //     'web',
        //     'universal',
        //     InitializeTenancyByPath::class, // 👈 same here
        // ];
    }

    protected function bootEvents()
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes()
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            // Even higher priority than the initialization middleware
            Middleware\PreventAccessFromCentralDomains::class,

            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}
