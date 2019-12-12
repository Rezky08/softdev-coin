<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();
        Passport::personalAccessTokensExpireIn(now()->addHours(24));
        Passport::tokensCan([
            'customer' => 'only can buy product in seller',
            'seller' => 'sell product from supplier to customer',
            'coin' => 'all user connected with it',
            'supplier' => 'sell product to seller'
        ]);
        //
    }
}
