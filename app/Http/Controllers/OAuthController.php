<?php

namespace App\Http\Controllers;

use App\Http\Grants\ClientCredentialsGrant;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;

class OAuthController extends Controller
{
    protected $authServer;

    public function __construct(AuthorizationServer $authServer)
    {
        $this->authServer = $authServer;
    }

    public function issueToken(ServerRequestInterface $request)
    {
        // Enable the custom grant type
        $this->authServer->enableGrantType(
            $this->app->make(ClientCredentialsGrant::class),
            new \DateInterval('PT1H') // Access token TTL
        );

        return $this->authServer->respondToAccessTokenRequest($request, new \League\OAuth2\Server\ResponseTypes\JsonResponse());
    }
}
