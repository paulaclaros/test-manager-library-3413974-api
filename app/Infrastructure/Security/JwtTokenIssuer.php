<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Ports\Outbound\TokenIssuerInterface;
use App\Domain\Entities\User;

final class JwtTokenIssuer implements TokenIssuerInterface
{
    private readonly string $secret;
    private const TTL_SECONDS = 28800; // 8 horas (CA-01.1)

    public function __construct(?string $secret = null)
    {
        $this->secret = $secret ?? (string) env('JWT_SECRET', 'manager_library_super_secret_jwt_key_3413974_adso');
    }

    public function issue(User $user): array
    {
        $now = time();
        $payload = [
            'iss' => 'manager-library-api',
            'sub' => $user->id(),
            'role' => $user->role()->value(),
            'iat' => $now,
            'exp' => $now + self::TTL_SECONDS,
        ];

        $token = $this->encode($payload);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => self::TTL_SECONDS,
            'role' => $user->role()->value(),
        ];
    }

    public function validate(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headB64, $payloadB64, $sigB64] = $parts;
        $expectedSig = $this->base64UrlEncode(
            hash_hmac('sha256', "{$headB64}.{$payloadB64}", $this->secret, true)
        );

        if (!hash_equals($expectedSig, $sigB64)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($payloadB64), true);
        if (!is_array($payload)) {
            return null;
        }

        if (isset($payload['exp']) && time() > (int) $payload['exp']) {
            return null;
        }

        if (empty($payload['sub']) || empty($payload['role'])) {
            return null;
        }

        return [
            'sub' => (string) $payload['sub'],
            'role' => (string) $payload['role'],
        ];
    }

    private function encode(array $payload): string
    {
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $headB64 = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $payB64 = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $sig = $this->base64UrlEncode(
            hash_hmac('sha256', "{$headB64}.{$payB64}", $this->secret, true)
        );

        return "{$headB64}.{$payB64}.{$sig}";
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padLen = 4 - $remainder;
            $data .= str_repeat('=', $padLen);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
