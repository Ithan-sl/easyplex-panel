<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Route;
use App\Http\Grants\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;


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

        Passport::tokensExpireIn(Carbon::now()->addDays(7));

        Passport::refreshTokensExpireIn(Carbon::now()->addDays(7));

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Verifiy Your Email')
                ->line('Click the button below to verify your email address.')
                ->action('Verify', $url);
        });

        $this->registerCustomGrants();
    }


    protected function registerCustomGrants()
{
    $this->app->singleton(ClientCredentialsGrant::class, function ($app) {
        return new ClientCredentialsGrant();
    });

    // You can register other grants if needed
}
}
