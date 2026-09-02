<?php

namespace App\Providers;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        foreach (['created', 'updated', 'deleted'] as $event) {
            Event::listen("eloquent.{$event}: *", function (string $eventName, array $payload) use ($event): void {
                $model = $payload[0] ?? null;

                if ($model instanceof Model) {
                    app(ActivityLogger::class)->logModelEvent($event, $model);
                }
            });
        }

        Event::listen(Login::class, function (Login $event): void {
            app(ActivityLogger::class)->log('logged_in', $event->user);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            app(ActivityLogger::class)->log('logged_out', $event->user);
        });
    }
}
