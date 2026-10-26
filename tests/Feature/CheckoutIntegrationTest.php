<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function order(): Order
    {
        return Order::create(['user_id' => User::factory()->create()->id, 'total_amount' => 150,
            'status' => 'pending', 'payment_id' => 'test-payment']);
    }

    public function test_webhook_rechecks_provider_and_is_idempotent(): void
    {
        config(['yookassa.shop_id' => 'test', 'yookassa.secret_key' => 'test', 'yookassa.test_mode' => true]);
        $order = $this->order();
        Http::fake(['api.yookassa.ru/*' => Http::response(['id' => 'test-payment', 'status' => 'succeeded', 'paid' => true,
            'test' => true, 'metadata' => ['order_id' => $order->id], 'amount' => ['value' => '150.00', 'currency' => 'RUB']])]);
        for ($i = 0; $i < 2; $i++) {
            $this->postJson('/payment/webhook', ['object' => ['id' => 'test-payment']])->assertOk();
        }
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertDatabaseCount('order_status_history', 1);
    }

    public function test_forged_webhook_cannot_mark_unpaid_order_paid(): void
    {
        config(['yookassa.shop_id' => 'test', 'yookassa.secret_key' => 'test', 'yookassa.test_mode' => false]);
        $order = $this->order();
        Http::fake(['api.yookassa.ru/*' => Http::response(['id' => 'test-payment', 'status' => 'pending', 'paid' => false,
            'test' => false, 'metadata' => ['order_id' => $order->id], 'amount' => ['value' => '150.00', 'currency' => 'RUB']])]);
        $this->postJson('/payment/webhook', ['event' => 'payment.succeeded', 'object' => ['id' => 'test-payment', 'paid' => true]])->assertOk();
        $this->assertNull($order->fresh()->paid_at);
    }

    public function test_payment_amount_mismatch_is_rejected(): void
    {
        config(['yookassa.shop_id' => 'test', 'yookassa.secret_key' => 'test', 'yookassa.test_mode' => false]);
        $order = $this->order();
        Http::fake(['api.yookassa.ru/*' => Http::response(['id' => 'test-payment', 'status' => 'succeeded', 'paid' => true,
            'test' => false, 'metadata' => ['order_id' => $order->id], 'amount' => ['value' => '1.00', 'currency' => 'RUB']])]);
        $this->postJson('/payment/webhook', ['object' => ['id' => 'test-payment']])->assertStatus(503);
        $this->assertNull($order->fresh()->paid_at);
    }

    public function test_geocoder_saves_confirmed_address_on_server(): void
    {
        config(['services.yandex.geocoder_key' => 'test']);
        Http::fake(['geocode-maps.yandex.ru/*' => Http::response(['response' => ['GeoObjectCollection' => ['featureMember' => [[
            'GeoObject' => ['metaDataProperty' => ['GeocoderMetaData' => ['precision' => 'exact', 'text' => 'Иркутск, Ленина, 1', 'Address' => ['Components' => [['kind' => 'province', 'name' => 'Иркутская область']]]]], 'Point' => ['pos' => '37.61 55.75']],
        ]]]]])]);
        $this->actingAs(User::factory()->create())->post('/checkout/address', ['delivery_address' => 'Москва Тверская 1'])
            ->assertSessionHas('checkout_address.latitude', '55.75')->assertSessionHas('checkout_address.longitude', '37.61');
    }

    public function test_delivered_requires_private_photo_and_owner_access(): void
    {
        Storage::fake('local');
        $order = $this->order();
        $order->update(['status' => 'shipping', 'paid_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch('/admin/orders/'.$order->id.'/status', ['status' => 'delivered'])->assertSessionHasErrors('delivery_photo');
        $this->patch('/admin/orders/'.$order->id.'/status', ['status' => 'delivered', 'delivery_photo' => UploadedFile::fake()->image('delivery.jpg')])->assertSessionHasNoErrors();
        $photo = $order->deliveryPhotos()->firstOrFail();
        $this->assertSame('delivered', $order->fresh()->status);
        $this->actingAs(User::find($order->user_id))->get('/delivery-photos/'.$photo->id)->assertOk();
        $this->actingAs(User::factory()->create())->get('/delivery-photos/'.$photo->id)->assertForbidden();
    }

    public function test_admin_cannot_mark_order_paid_manually(): void
    {
        $order = $this->order();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'paid'])->assertSessionHasErrors('status');
        $this->assertNull($order->fresh()->paid_at);
    }

    public function test_outside_region_invalidates_previous_address_confirmation(): void
    {
        config(['services.yandex.geocoder_key' => 'test']);
        Http::fake(['geocode-maps.yandex.ru/*' => Http::response(['response' => ['GeoObjectCollection' => ['featureMember' => [[
            'GeoObject' => ['metaDataProperty' => ['GeocoderMetaData' => ['precision' => 'exact', 'text' => 'Москва',
                'Address' => ['Components' => [['kind' => 'province', 'name' => 'Москва']]]]], 'Point' => ['pos' => '37.61 55.75']],
        ]]]]])]);
        $this->actingAs(User::factory()->create())->withSession(['checkout_address' => ['delivery_address' => 'Старый адрес']])
            ->post('/checkout/address', ['delivery_address' => 'Москва, Тверская, 1'])
            ->assertSessionHasErrors('delivery_address')->assertSessionMissing('checkout_address');
    }

    public function test_payment_retry_after_connection_failure_reuses_same_key(): void
    {
        config(['yookassa.shop_id' => 'test', 'yookassa.secret_key' => 'test']);
        $order = $this->order();
        $order->update(['payment_id' => null]);
        Http::fake(fn () => throw new ConnectionException('Test timeout'));
        try {
            app(PaymentService::class)->start($order, 'bank_card');
        } catch (ConnectionException $e) {
        }
        $key = $order->fresh()->payment_key;
        $this->assertNotEmpty($key);
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['api.yookassa.ru/*' => Http::response(['id' => 'retry-payment'])]);
        app(PaymentService::class)->start($order, 'bank_card');
        Http::assertSent(fn ($request) => $request->hasHeader('Idempotence-Key', $key));
        $this->assertSame('retry-payment', $order->fresh()->payment_id);
    }

    public function test_non_image_cannot_complete_delivery(): void
    {
        Storage::fake('local');
        $order = $this->order();
        $order->update(['status' => 'shipping', 'paid_at' => now()]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->patch('/admin/orders/'.$order->id.'/status', [
            'status' => 'delivered', 'delivery_photo' => UploadedFile::fake()->create('payload.php', 1, 'text/plain'),
        ])->assertSessionHasErrors('delivery_photo');
        $this->assertSame('shipping', $order->fresh()->status);
        $this->assertDatabaseCount('delivery_photos', 0);
    }
}
