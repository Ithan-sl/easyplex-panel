<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Passport;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use League\OAuth2\Server\AuthorizationServer;
use App\Grants\FacebookGrant;
use App\Grants\FacebookUserRepository;
use Illuminate\Support\Facades\Gate;
use App\Services\ContentQueryService;



class AppServiceProvider extends ServiceProvider
{



    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {



      //  env('DEBUGBAR_ENABLED', true);


        Builder::defaultStringLength(1000); // Update defaultStringLength
        Schema::defaultStringLength(191);
        //Model::preventLazyLoading();
        \Illuminate\Support\Facades\URL::forceScheme('https');
        error_reporting(0);

        \Illuminate\Database\Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            $conn = new \Illuminate\Database\PostgresConnection($connection, $database, $prefix, $config);
            $conn->setQueryGrammar(new \App\Database\Query\Grammars\PostgresGrammar());
            return $conn;
        });

        app(AuthorizationServer::class)->enableGrantType(
            $this->makeFacebookGrant(), Passport::tokensExpireIn()
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ContentQueryService::class, function ($app) {
            return new ContentQueryService();
        });
    }



    protected function makeFacebookGrant()
    {
        $grant = new FacebookGrant(
            $this->app->make(FacebookUserRepository::class),
            $this->app->make(RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }
}
