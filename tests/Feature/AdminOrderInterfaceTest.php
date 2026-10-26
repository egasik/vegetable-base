<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleted_order_can_be_viewed_but_not_modified(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::create(['user_id' => $admin->id, 'total_amount' => 100, 'status' => 'pending']);
        $order->delete();
        $this->actingAs($admin)->get('/admin/orders/'.$order->id)->assertOk()->assertSee('Заказ удалён');
        $this->patch('/admin/orders/'.$order->id.'/status', ['status' => 'cancelled', 'comment' => 'Test'])->assertNotFound();
    }

    public function test_status_validation_is_visible_and_keeps_order_unchanged(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::create(['user_id' => $admin->id, 'total_amount' => 100, 'status' => 'pending']);
        $this->actingAs($admin)->from('/admin/orders/'.$order->id)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'cancelled'])
            ->assertSessionHasErrors('comment');
        $this->withSession(['errors' => session('errors')])->get('/admin/orders/'.$order->id)
            ->assertOk()->assertSee('Проверьте заполнение формы');
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_invalid_address_attempt_invalidates_previous_confirmation(): void
    {
        $this->actingAs(User::factory()->create())->withSession([
            'checkout_address' => ['delivery_address' => 'Old address', 'verified_at' => time()],
        ])->post('/checkout/address', ['delivery_address' => 'x'])
            ->assertSessionHasErrors('delivery_address')->assertSessionMissing('checkout_address');
    }
}
