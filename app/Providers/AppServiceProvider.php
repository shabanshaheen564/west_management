<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        try {
            if (Schema::hasTable('settings')) {
                $settings = \App\Models\Setting::where('is_public', true)->get();
                foreach ($settings as $s) {
                    config(["settings.{$s->key}" => $s->value]);
                }
            }
        } catch (\Exception $e) {}

        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) return true;
        });
    }
}