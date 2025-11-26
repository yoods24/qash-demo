<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Order;
use App\Observers\OrderObserver;
use Livewire\Livewire;
use App\Filament\Widgets\SalesChartWidget;

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
        Paginator::useBootstrapFive(); // or useBootstrapFour();

        // Register model observers
        Order::observe(OrderObserver::class);

        // Allow rendering Filament widget outside Filament panels
        Livewire::component('sales-chart-widget', SalesChartWidget::class);
    }
}
