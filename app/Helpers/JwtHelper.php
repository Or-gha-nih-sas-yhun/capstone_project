<?php

namespace App\Helpers;

use RuntimeException;

/**
 * Lightweight HS256 JWT helper — no external packages needed.
 * Uses the JWT_SECRET env variable as the signing key.
 */
class JwtHelper
{
    private static function secret(): string
    {
        $secret = config('app.jwt_secret', env('JWT_SECRET'));
        if (empty($secret)) {
            throw new RuntimeException('JWT_SECRET is not configured. Add it to your .env file.');
        }
        return $secret;
    }

    /**
     * Base64URL encode (RFC 4648 §5).
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64URL decode.
     */
    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Generate a signed JWT token.
     *
     * @param  int    $userId
     * @param  string $role       e.g. 'resident', 'staff', 'admin'
     * @param  int    $expiresIn  Seconds until expiry (default 7 days)
     * @return string
     */
    public static function generate(int $userId, string $role, int $expiresIn = 604800): string
    {
        $header = self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));

        $payload = self::base64UrlEncode(json_encode([
            'sub' => $userId,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + $expiresIn,
        ]));

        $signature = self::base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", self::secret(), true)
        );

        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * Verify and decode a JWT token.
     *
     * @param  string $token
     * @return array  Decoded payload
     * @throws RuntimeException on invalid/expired token
     */
    public static function verify(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid JWT structure.');
        }

        [$header, $payload, $signature] = $parts;

        // Verify signature
        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", self::secret(), true)
        );

        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('JWT signature mismatch.');
        }

        $claims = json_decode(self::base64UrlDecode($payload), true);

        if (!$claims || !isset($claims['exp'])) {
            throw new RuntimeException('JWT payload is malformed.');
        }

        if (time() > $claims['exp']) {
            throw new RuntimeException('JWT token has expired.');
        }

        return $claims;
    }
}
