<?php

namespace App\Http\Middleware;

use App\Domain\Contracts\JwtManagerInterface;
use App\Domain\Repositories\UserRepositoryInterface; // Dùng Interface
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class JwtAuthMiddleware
{
    public function __construct(
        protected JwtManagerInterface $jwtManager,
        protected UserRepositoryInterface $userRepository
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $payload = $this->jwtManager->parseToken($token);

        if (! $payload) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = User::find($payload->id);

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        Auth::login($user);

        return $next($request);
    }
}
