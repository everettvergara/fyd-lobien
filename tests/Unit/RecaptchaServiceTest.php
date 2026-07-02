<?php

namespace Tests\Unit;

use App\Services\Recaptcha\RecaptchaService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaServiceTest extends TestCase
{
    public function test_verify_skips_when_disabled(): void
    {
        config([
            'recaptcha.site_key' => null,
            'recaptcha.secret_key' => null,
            'recaptcha.enabled' => false,
        ]);

        Http::fake();

        $service = app(RecaptchaService::class);

        $this->assertTrue($service->verify(null, 'search'));
        Http::assertNothingSent();
    }

    public function test_verify_rejects_empty_token_when_enabled(): void
    {
        config([
            'recaptcha.site_key' => 'test-site-key',
            'recaptcha.secret_key' => 'test-secret-key',
            'recaptcha.score_threshold' => 0.5,
            'recaptcha.enabled' => true,
        ]);

        Http::fake();

        $service = app(RecaptchaService::class);

        $this->assertFalse($service->verify('', 'search', '127.0.0.1'));
        Http::assertNothingSent();
    }

    public function test_verify_accepts_valid_google_response(): void
    {
        config([
            'recaptcha.site_key' => 'test-site-key',
            'recaptcha.secret_key' => 'test-secret-key',
            'recaptcha.score_threshold' => 0.5,
            'recaptcha.enabled' => true,
        ]);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'action' => 'search',
                'score' => 0.9,
            ]),
        ]);

        $service = app(RecaptchaService::class);

        $this->assertTrue($service->verify('valid-token', 'search', '127.0.0.1'));
    }

    public function test_verify_rejects_low_score(): void
    {
        config([
            'recaptcha.site_key' => 'test-site-key',
            'recaptcha.secret_key' => 'test-secret-key',
            'recaptcha.score_threshold' => 0.5,
            'recaptcha.enabled' => true,
        ]);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'action' => 'search',
                'score' => 0.1,
            ]),
        ]);

        $service = app(RecaptchaService::class);

        $this->assertFalse($service->verify('valid-token', 'search', '127.0.0.1'));
    }

    public function test_verify_rejects_mismatched_action(): void
    {
        config([
            'recaptcha.site_key' => 'test-site-key',
            'recaptcha.secret_key' => 'test-secret-key',
            'recaptcha.score_threshold' => 0.5,
            'recaptcha.enabled' => true,
        ]);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'action' => 'login',
                'score' => 0.9,
            ]),
        ]);

        $service = app(RecaptchaService::class);

        $this->assertFalse($service->verify('valid-token', 'search', '127.0.0.1'));
    }
}
