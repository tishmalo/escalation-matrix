<?php

namespace Tishmalo\EscalationMatrix;

use Illuminate\Support\ServiceProvider;
use Tishmalo\EscalationMatrix\Services\EscalationService;
use Tishmalo\EscalationMatrix\Services\SmsService;

class EscalationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/escalation.php', 'escalation'
        );

        // Register default SmsService
        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService();
        });

        // Register EscalationService
        $this->app->singleton(EscalationService::class, function ($app) {
            return new EscalationService(
                $app->make(\Tishmalo\EscalationMatrix\Contracts\SupportTicketDriver::class),
                $app->make(SmsService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'escalation-matrix');

        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publish Config
        $this->publishes([
            __DIR__ . '/../config/escalation.php' => config_path('escalation.php'),
        ], 'escalation-config');

        // Publish Views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/escalation-matrix'),
        ], 'escalation-views');
    }
}
