<?php

namespace App\Http\Middleware;

use Jenssegers\Agent\Agent;
use Closure;
use Illuminate\Support\Str;
use App\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class Decrypter extends Authenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        $arrayValues = $this->getArrayFromEnv('SIGNATURE');
        $arrayValuesHash = $this->getArrayFromEnv('SHA256');

        $signature = $request->header('signature');
        $hash = $request->header('hash256');
        $agent = new Agent();

        if(env('ENABLE_API_CHECK')) {
            $isValid = is_array($arrayValues) && in_array($signature, $arrayValues) && $agent->isAndroidOS();

            if (env('ENABLE_API_CHECK_HASH_256')) {
                $isValid = $isValid && is_array($arrayValuesHash) && in_array($hash, $arrayValuesHash);
            }

            if ($isValid) {
                return $next($request);
            } else {
                Log::warning('Unauthorized API access attempt', [
                    'signature' => $signature,
                    'hash' => $hash,
                    'isAndroid' => $agent->isAndroidOS(),
                ]);
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        } else {
            return $next($request);
        }
    }

    private function getArrayFromEnv($key)
    {
        $value = env($key);
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        Log::error("Failed to decode environment variable: $key");
        return [];
    }
}