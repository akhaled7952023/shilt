<?php

namespace App\Providers;

use App\Models\Delegate;
use App\Models\DelegateMonthlyEntry;
use App\Models\MonthlyPeriod;
use App\Models\SupportTicket;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use App\Policies\DelegateMonthlyEntryPolicy;
use App\Policies\DelegatePolicy;
use App\Policies\MonthlyPeriodPolicy;
use App\Policies\SupportTicketPolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\VehiclePolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrap();

        foreach (config('permissions_en') as $permission => $value) {
            Gate::define($permission, function ($auth) use ($permission) {
                return $auth->hasAccess($permission);
            });
        }

        Gate::define('super-admin', fn ($user) => $user->isSuperAdmin());

        // Unread support notification count injected into the dashboard header
        View::composer('layouts.dashboard._header', function ($view) {
            $count = 0;
            if (auth()->check()) {
                try {
                    $count = \Illuminate\Support\Facades\DB::table('notifications')
                        ->where('recipient_type', 'admin')
                        ->where('recipient_id', auth()->id())
                        ->where('channel', 'portal')
                        ->whereNull('read_at')
                        ->count();
                } catch (\Throwable) {}
            }
            $view->with('unreadSupportCount', $count);
        });

        Gate::policy(Delegate::class, DelegatePolicy::class);
        Gate::policy(Vehicle::class, VehiclePolicy::class);
        Gate::policy(MonthlyPeriod::class, MonthlyPeriodPolicy::class);
        Gate::policy(DelegateMonthlyEntry::class, DelegateMonthlyEntryPolicy::class);
        Gate::policy(SystemSetting::class, SystemSettingPolicy::class);
        Gate::policy(SupportTicket::class, SupportTicketPolicy::class);
    }
}
