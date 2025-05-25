<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define Gate untuk Pulse
        Gate::define('viewPulse', function ($user) {
            // Laravel 12 style - menggunakan enum atau attribute
            return match ($user->role) {
                'ADMIN', 'SUPERADMIN' => true,
                default => false,
            };

            // Atau menggunakan attribute/property
            // return $user->is_admin || $user->hasRole('admin');
        });

        // Apply filter ke Pulse
        Pulse::filter(function ($request) {
            return $request->user()?->can('viewPulse') ?? false;
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Registering the macro for the hasValidSignature method
        Request::macro('hasValidSignature', function ($absolute = true) {
            // Skip signature validation for the 'livewire/upload-file' route
            if ('livewire/upload-file' == request()->path()) {
                return true;
            }
            // Use the default signature validation
            return URL::hasValidSignature($this, $absolute);
        });
    }
}
