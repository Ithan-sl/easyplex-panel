<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTFactory;
use Carbon\Carbon;

class JWTMiddleware
{
    public function handle($request, Closure $next)
    {
        $customClaims = [
            'custom_words' => 'your_custom_words_here',
            'password' => 'your_password_here',
            // Add additional custom claims as needed
        ];

        $expiration = Carbon::now()->addHour();

        $token = JWTAuth::claims($customClaims)->setExpiresAt($expiration)->token();

        return response()->json(['token' => $token]);

    }
}
