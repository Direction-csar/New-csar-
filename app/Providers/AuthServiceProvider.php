<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Définir les gates pour les rôles
        Gate::define('admin', function ($user) {
            return $user->role && $user->role->name === 'admin';
        });

        Gate::define('dg', function ($user) {
            return $user->role && $user->role->name === 'dg';
        });

        Gate::define('responsable', function ($user) {
            return $user->role && $user->role->name === 'responsable';
        });

        Gate::define('agent', function ($user) {
            return $user->role && $user->role->name === 'agent';
        });

        Gate::define('ctc', function ($user) {
            return $user->role === 'ctc' || (isset($user->attributes['role']) && $user->attributes['role'] === 'ctc');
        });
    }
} 