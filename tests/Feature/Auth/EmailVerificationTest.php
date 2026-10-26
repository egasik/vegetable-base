<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\VerificationCode;
use App\Services\EmailCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_code_is_single_use_and_verifies_email(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get('/verify-email')->assertOk();
        $this->post('/email/verification-notification')->assertSessionHasNoErrors();
        $code = Notification::sent($user, VerificationCode::class)->first()->code;
        $this->post('/verify-email', ['code' => $code])->assertRedirect('/checkout');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertFalse(app(EmailCodeService::class)->verify($user, $code));
    }

    public function test_resend_cooldown_and_expired_code(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->post('/email/verification-notification');
        $code = Notification::sent($user, VerificationCode::class)->first()->code;
        $this->post('/email/verification-notification')->assertSessionHasErrors('code');
        $this->travel(11)->minutes();
        $this->post('/verify-email', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_five_wrong_attempts_invalidate_code(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        app(EmailCodeService::class)->send($user);
        $code = Notification::sent($user, VerificationCode::class)->first()->code;
        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse(app(EmailCodeService::class)->verify($user, '000000'));
        }
        $this->assertFalse(app(EmailCodeService::class)->verify($user, $code));
    }

    public function test_unverified_customer_can_use_cart_but_not_checkout(): void
    {
        $this->actingAs(User::factory()->unverified()->create())->get('/cart')->assertOk();
        $this->get('/checkout')->assertRedirect('/verify-email');
    }
}
