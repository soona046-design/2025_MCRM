<?php

namespace App\Providers;

// [SLA 기능 비활성화 2026-06-22] use App\Events\SlaViolated;
// [SLA 기능 비활성화 2026-06-22] use App\Listeners\SendSlaNotification;
// [SLA 기능 비활성화 2026-06-22] use App\Events\SlaWarning;
// [SLA 기능 비활성화 2026-06-22] use App\Listeners\SendSlaWarningNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        // [SLA 기능 비활성화 2026-06-22]
        // \App\Events\SlaViolated::class => [
        //     \App\Listeners\SendSlaViolationNotification::class,
        // ],
        // \App\Events\SlaWarning::class => [
        //     \App\Listeners\SendSlaWarningNotification::class,
        // ],
        \App\Events\AppointmentReminderSent::class => [
            \App\Listeners\ProcessAppointmentReminder::class,
        ],
        \App\Events\RebookingSuggestionSent::class => [
            \App\Listeners\ProcessRebookingSuggestion::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
