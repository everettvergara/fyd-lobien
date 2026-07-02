<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\URL;

class EmailVerificationUrl
{
    public static function forUser(User $user): string
    {
        $expires = now()->addMinutes((int) config('auth.verification.expire', 60))->timestamp;
        $hash = sha1($user->getEmailForVerification());
        $id = $user->getKey();
        $token = self::makeToken($id, $hash, $expires);

        $path = route('admin.verification.verify', [
            'id' => $id,
            'hash' => $hash,
        ], absolute: false);

        return URL::to($path.'?'.http_build_query([
            'expires' => $expires,
            'token' => $token,
        ]));
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public static function isValid(int $id, string $hash, array $params): bool
    {
        $expires = isset($params['expires']) ? (int) $params['expires'] : 0;
        $token = isset($params['token']) ? (string) $params['token'] : '';

        if ($expires < now()->timestamp || $token === '') {
            return false;
        }

        return hash_equals(self::makeToken($id, $hash, $expires), $token);
    }

    /**
     * Decode query strings that were HTML-escaped (&amp;token= → &token=).
     *
     * @return array<string, mixed>
     */
    public static function queryParamsFromRequestUri(string $requestUri): array
    {
        $query = parse_url($requestUri, PHP_URL_QUERY) ?? '';
        parse_str(html_entity_decode($query), $params);

        return $params;
    }

    private static function makeToken(int|string $id, string $hash, int $expires): string
    {
        return hash_hmac('sha256', "{$id}|{$hash}|{$expires}", (string) config('app.key'));
    }
}
