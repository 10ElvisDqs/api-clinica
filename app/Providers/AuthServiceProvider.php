<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Ingreso\Ingreso;
use App\Models\Egreso\Egreso;
use App\Models\Seguimiento\Seguimiento;
use App\Policies\IngresoPolicy;
use App\Policies\EgresoPolicy;
use App\Policies\SeguimientoPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Ingreso::class     => IngresoPolicy::class,
        Egreso::class      => EgresoPolicy::class,
        Seguimiento::class => SeguimientoPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function($user,$abilty) {
            return $user->hasRole("Super-Admin") ? true : null;
        });
    }
}
