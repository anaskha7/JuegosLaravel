<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Bearer token requerido.'], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::query()->where('api_token', $token)->first();

        if (! $user) {
            return response()->json(['message' => 'Token no válido.'], Response::HTTP_UNAUTHORIZED);
        }

        auth()->setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
