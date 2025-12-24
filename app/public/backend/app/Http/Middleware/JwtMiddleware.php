<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\JwtService;
use App\Models\User;

class JwtMiddleware
{
    private JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Token is missing'
            ], 401);
        }

        $decoded = $this->jwtService->verifyToken($token);

        if (!$decoded) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid or expired token'
            ], 401);
        }

        // Verify token type is 'access'
        if ($decoded->type !== 'access') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid token type'
            ], 401);
        }

        // Get user and set in auth manually
        $user = User::find($decoded->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - User not found'
            ], 401);
        }

        // Set the user for the request
        auth()->setUser($user);

        return $next($request);
    }

    /**
     * Get JWT token from request
     *
     * @param Request $request
     * @return string|null
     */
    private function getTokenFromRequest(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return null;
        }

        // Extract token from "Bearer <token>" format
        if (preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
