<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPricingTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        $category = Category::create(['name' => 'Овощи']);

        return Product::create(['category_id' => $category->id, 'name' => 'Картофель',
            'description' => 'Тестовый товар', 'is_retail' => true, 'retail_price' => 99.90,
            'is_wholesale' => true, 'wholesale_unit_kg' => 10, 'wholesale_price' => 800]);
    }

    public function test_guest_cannot_use_cart(): void
    {
        $product = $this->product();
        $this->get('/cart')->assertRedirect('/login');
        $this->post('/cart/'.$product->id, ['purchase_type' => 'retail', 'quantity' => 1])->assertRedirect('/login');
    }

    public function test_retail_accepts_half_kilogram_and_rejects_other_fractions(): void
    {
        $product = $this->product();
        $this->actingAs(User::factory()->create());
        $this->postJson('/cart/'.$product->id, ['purchase_type' => 'retail', 'quantity' => 0.5])->assertOk();
        $this->get('/cart')->assertOk()->assertSee('49,95');
        $this->postJson('/cart/'.$product->id, ['purchase_type' => 'retail', 'quantity' => 0.3])
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');
    }

    public function test_wholesale_requires_whole_available_packages_and_checks_merged_limit(): void
    {
        $product = $this->product();
        $this->actingAs(User::factory()->create());
        $this->postJson('/cart/'.$product->id, ['purchase_type' => 'wholesale', 'quantity' => 1.5])->assertUnprocessable();
        $this->postJson('/cart/'.$product->id, ['purchase_type' => 'wholesale', 'quantity' => 9999])->assertOk();
        $this->postJson('/cart/'.$product->id, ['purchase_type' => 'wholesale', 'quantity' => 1])->assertUnprocessable();
        $product->update(['is_wholesale' => false]);
        $this->postJson('/cart/'.$product->id, ['purchase_type' => 'wholesale', 'quantity' => 1])->assertUnprocessable();
    }

    public function test_checkout_uses_current_price_and_saves_weight_and_pack_snapshots(): void
    {
        $product = $this->product();
        $user = User::factory()->create();
        $cart = [
            $product->id.'_retail' => ['product_id' => $product->id, 'purchase_type' => 'retail', 'quantity' => 0.5, 'price' => 1],
            $product->id.'_wholesale' => ['product_id' => $product->id, 'purchase_type' => 'wholesale', 'quantity' => 2, 'price' => 1],
        ];
        $this->actingAs($user)->withSession(['cart' => $cart])->get('/checkout')->assertOk();
        $token = session('checkout_token');
        $payload = ['checkout_token' => $token, 'address_confirmed' => 1, 'contact_phone' => '+79991234567'];
        $this->withSession(['checkout_address' => ['delivery_address' => 'Адрес', 'latitude' => 55.75, 'longitude' => 37.61, 'verified_at' => time()]])->post('/orders', $payload)->assertRedirect();
        $this->post('/orders', $payload)->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total_amount' => 1649.95]);
        $this->assertDatabaseHas('order_items', ['purchase_type' => 'retail', 'quantity' => 0.5, 'line_total' => 49.95]);
        $this->assertDatabaseHas('order_items', ['purchase_type' => 'wholesale', 'quantity' => 2, 'unit_weight_kg' => 10, 'line_total' => 1600]);
    }

    public function test_unavailable_item_blocks_entire_checkout(): void
    {
        $product = $this->product();
        $this->actingAs(User::factory()->create())->withSession(['cart' => [
            ['product_id' => $product->id, 'purchase_type' => 'retail', 'quantity' => 1],
        ]])->get('/checkout')->assertOk();
        $product->update(['is_retail' => false]);
        $this->withSession(['checkout_address' => ['delivery_address' => 'Адрес', 'verified_at' => time()]])
            ->post('/orders', ['checkout_token' => session('checkout_token'), 'address_confirmed' => 1, 'contact_phone' => '+79991234567'])->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_price_change_requires_review_before_order_creation(): void
    {
        $product = $this->product();
        $this->actingAs(User::factory()->create())->withSession(['cart' => [
            ['product_id' => $product->id, 'purchase_type' => 'retail', 'quantity' => 1],
        ]])->get('/checkout')->assertOk();
        $product->update(['retail_price' => 120]);
        $this->withSession(['checkout_address' => ['delivery_address' => 'Адрес', 'verified_at' => time()]])
            ->post('/orders', ['checkout_token' => session('checkout_token'), 'address_confirmed' => 1, 'contact_phone' => '+79991234567'])
            ->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }
}
