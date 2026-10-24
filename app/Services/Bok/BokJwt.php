<?php

namespace App\Services\Bok;

/**
 * JWT بخوارزمية HS256 مطابق لما يصدره نظام apiBOK القديم (iss, aud, iat, exp, data).
 */
class BokJwt
{
    private const LIFETIME_SECONDS = 3600;

    /**
     * @param  array<string, mixed>  $data
     */
    public function encode(string $issuer, array $data): string
    {
        $now = time();
        $header = $this->base64Url((string) json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = $this->base64Url((string) json_encode([
            'iss' => $issuer,
            'aud' => $issuer,
            'iat' => $now,
            'exp' => $now + self::LIFETIME_SECONDS,
            'data' => $data,
        ]));

        return "{$header}.{$payload}.{$this->sign("{$header}.{$payload}")}";
    }

    /**
     * يرجع محتوى data أو null عند أي توقيع غير صالح أو انتهاء صلاحية.
     *
     * @return array<string, mixed>|null
     */
    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3 || ! hash_equals($this->sign("{$parts[0]}.{$parts[1]}"), $parts[2])) {
            return null;
        }

        $header = json_decode((string) base64_decode(strtr($parts[0], '-_', '+/')), true);
        $payload = json_decode((string) base64_decode(strtr($parts[1], '-_', '+/')), true);

        if (($header['alg'] ?? null) !== 'HS256' || ! is_array($payload) || ($payload['exp'] ?? 0) < time()) {
            return null;
        }

        return is_array($payload['data'] ?? null) ? $payload['data'] : null;
    }

    /**
     * رسالة سبب رفض الرمز كما يرجعها النظام القديم.
     */
    public function failureMessage(string $token): string
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return 'Wrong number of segments';
        }

        if (! hash_equals($this->sign("{$parts[0]}.{$parts[1]}"), $parts[2])) {
            return 'Signature verification failed';
        }

        return 'Expired token';
    }

    private function sign(string $input): string
    {
        return $this->base64Url(hash_hmac('sha256', $input, (string) config('services.bok.jwt_secret'), true));
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
