<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class JwtService
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $accessTokenExpiry = 21600; // 6 hours in seconds
    private int $refreshTokenExpiry = 2592000; // 30 days in seconds

    public function __construct()
    {
        $this->secretKey = config('app.key');
        
        // Remove 'base64:' prefix if present
        if (strpos($this->secretKey, 'base64:') === 0) {
            $this->secretKey = base64_decode(substr($this->secretKey, 7));
        }
    }

    /**
     * Generate JWT access token
     * 
     * @param User $user
     * @return string
     */
    public function generateAccessToken(User $user): string
    {
        $now = Carbon::now();
        $expiry = $now->copy()->addSeconds($this->accessTokenExpiry);

        $payload = [
            'iat' => $now->timestamp,
            'exp' => $expiry->timestamp,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'user_id_custom' => $user->user_id_custom,
            'type' => 'access',
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Generate JWT refresh token
     * 
     * @param User $user
     * @return string
     */
    public function generateRefreshToken(User $user): string
    {
        $now = Carbon::now();
        $expiry = $now->copy()->addSeconds($this->refreshTokenExpiry);

        $payload = [
            'iat' => $now->timestamp,
            'exp' => $expiry->timestamp,
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'refresh',
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Generate both access and refresh tokens
     * 
     * @param User $user
     * @return array
     */
    public function generateTokens(User $user): array
    {
        $accessToken = $this->generateAccessToken($user);
        $refreshToken = $this->generateRefreshToken($user);

        // Store refresh token in remember_token field
        $user->update(['remember_token' => $refreshToken]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $this->accessTokenExpiry,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Verify and decode JWT token
     * 
     * @param string $token
     * @return object|false
     */
    public function verifyToken(string $token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return $decoded;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Refresh access token using refresh token
     * 
     * @param string $refreshToken
     * @return array|false
     */
    public function refreshAccessToken(string $refreshToken)
    {
        try {
            $decoded = $this->verifyToken($refreshToken);

            if (!$decoded || $decoded->type !== 'refresh') {
                return false;
            }

            $user = User::find($decoded->user_id);

            if (!$user || $user->remember_token !== $refreshToken) {
                return false;
            }

            return $this->generateTokens($user);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Validate token signature and expiry
     * 
     * @param string $token
     * @return bool
     */
    public function isTokenValid(string $token): bool
    {
        return $this->verifyToken($token) !== false;
    }

    /**
     * Get user from token
     * 
     * @param string $token
     * @return User|null
     */
    public function getUserFromToken(string $token): ?User
    {
        $decoded = $this->verifyToken($token);

        if (!$decoded) {
            return null;
        }

        return User::find($decoded->user_id);
    }
}
