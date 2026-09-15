<?php

namespace App\Http\Grants;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\RequestEvent;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\ClientCredentialsGrant as LeagueClientCredentialsGrant;

class ClientCredentialsGrant extends LeagueClientCredentialsGrant
{
    public function __construct()
    {
        $this->setRefreshTokenTTL(new \DateInterval('P1M')); // Set the refresh token TTL if needed
    }

    public function respondToAccessTokenRequest(ServerRequestInterface $request
    , ResponseTypeInterface $responseType, \DateInterval $accessTokenTTL)
    {
        // Add custom logic here if needed
        
        return parent::respondToAccessTokenRequest($request, $responseType, $accessTokenTTL);
    }
}
