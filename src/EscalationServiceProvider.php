<?php

namespace Tishmalo\EscalationMatrix;

use Illuminate\Support\ServiceProvider;
use Tishmalo\EscalationMatrix\Contracts\SupportTicketDriver;
use Tishmalo\EscalationMatrix\Drivers\LocalTicketDriver;
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

        // Register default LocalTicketDriver if no custom driver is bound
        $this->app->bind(SupportTicketDriver::class, function ($app) {
            return new LocalTicketDriver();
        });

        // Register default SmsService
        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService();
        });

        // Register EscalationService
        $this->app->singleton(EscalationService::class, function ($app) {
            return new EscalationService(
                $app->make(SupportTicketDriver::class),
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

        // Register automatic exception handling
        $this->registerExceptionHandler();
    }

    /**
     * Register automatic exception handling with Laravel's exception handler
     */
    protected function registerExceptionHandler(): void
    {
        if (!config('escalation.auto_report', true)) {
            return;
        }

        $this->app->booted(function () {
            $handler = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class);

            if (method_exists($handler, 'reportable')) {
                $handler->reportable(function (\Throwable $e) {
                    try {
                        app(EscalationService::class)->handle($e);
                    } catch (\Throwable $escalationException) {
                        // Silently fail to prevent breaking the app
                        \Log::error('Escalation Matrix failed to handle exception', [
                            'original_exception' => $e->getMessage(),
                            'escalation_error' => $escalationException->getMessage(),
                        ]);
                    }
                });
            }
        });
    }
}
