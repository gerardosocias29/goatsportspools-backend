<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Http;

class VerifyJwtFromJwks
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        if (count(explode('.', $token)) !== 3) {
            return response()->json(['error' => 'Malformed token'], 400);
        }

        try {
            // Fetch the JWKS
            $jwksUrl = env('JWKS_URL');
            $response = Http::get($jwksUrl);
            if ($response->failed()) {
                throw new Exception('Failed to fetch JWKS');
            }

            $jwks = $response->json();
            $decodedToken = JWT::decode($token, JWK::parseKeySet($jwks));

            // Attach user info to the request
            $request->attributes->add(['user' => $decodedToken]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Token validation failed: ' . $e->getMessage()], 401);
        }

        return $next($request);
    }
}
