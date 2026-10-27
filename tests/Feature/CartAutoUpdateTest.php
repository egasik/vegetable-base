<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CartAutoUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        return Product::create(['name' => 'Картофель', 'description' => 'Тестовый товар', 'category_id' => Category::create(['name' => 'Овощи'])->id,
            'is_retail' => true, 'retail_price' => 123, 'is_wholesale' => true,
            'wholesale_price' => 1230, 'wholesale_unit_kg' => 10]);
    }

    public function test_update_returns_authoritative_totals_for_both_purchase_types(): void
    {
        $product = $this->product();
        $this->actingAs(User::factory()->create());
        $this->postJson('/cart/'.$product->id, ['purchase_type' => 'retail', 'quantity' => 1])->assertOk();
        $this->postJson('/cart/'.$product->id, ['purchase_type' => 'wholesale', 'quantity' => 1])->assertOk();
        $this->patchJson('/cart/'.$product->id.'_retail', ['quantity' => 0.5])->assertOk()
            ->assertJsonPath('total', 1291.5)->assertJsonPath('weight', 10.5)->assertJsonPath('count', 2)
            ->assertJsonPath('items.0.subtotal', 61.5);
        $this->patchJson('/cart/'.$product->id.'_wholesale', ['quantity' => 2])->assertOk()
            ->assertJsonPath('total', 2521.5)->assertJsonPath('weight', 20.5);
        $this->patchJson('/cart/'.$product->id.'_wholesale', ['quantity' => 1.5])->assertUnprocessable();
        $this->assertEquals(2, session('cart')[$product->id.'_wholesale']['quantity']);
    }

    public function test_oversized_update_does_not_change_saved_cart(): void
    {
        $product = $this->product();
        $this->actingAs(User::factory()->create())->postJson('/cart/'.$product->id, ['purchase_type' => 'retail', 'quantity' => 1]);
        $product->update(['retail_price' => 999999.99]);
        $this->patchJson('/cart/'.$product->id.'_retail', ['quantity' => 9999])->assertUnprocessable();
        $this->assertEquals(1, session('cart')[$product->id.'_retail']['quantity']);
    }

    public function test_existing_image_is_used_and_missing_file_has_a_placeholder(): void
    {
        Storage::fake('public');
        $product = $this->product();
        $product->image_path = 'products/photo.jpg';
        $this->assertSame(asset('images/product-placeholder.svg'), $product->image_url);
        Storage::disk('public')->put('products/photo.jpg', 'test-image-file');
        $this->assertSame(asset('storage/products/photo.jpg'), $product->image_url);
    }
}
