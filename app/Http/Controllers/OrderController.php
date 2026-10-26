<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\CartPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request, CartPricing $pricing)
    {
        $data = $request->validate(['checkout_token' => 'required|uuid', 'address_confirmed' => 'accepted',
            'contact_phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9 ()-]{10,30}$/'],
            'delivery_details' => 'nullable|string|max:500']);
        $order = DB::transaction(function () use ($request, $pricing, $data) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $existing = Order::where('user_id', $request->user()->id)->where('checkout_token', $data['checkout_token'])->first();
            if ($existing) {
                return $existing;
            }
            if (! hash_equals((string) session('checkout_token'), $data['checkout_token']) || ! session('cart')) {
                throw ValidationException::withMessages(['cart' => 'Откройте оформление заново из корзины.']);
            }
            $address = session('checkout_address');
            if (! $address || time() - $address['verified_at'] > 1800) {
                throw ValidationException::withMessages(['delivery_address' => 'Повторите проверку адреса доставки.']);
            }
            $quote = $pricing->quote(session('cart'), true);
            $fingerprint = hash('sha256', json_encode(array_map(fn ($line) => [
                $line['product_id'], $line['purchase_type'], $line['quantity'], $line['price'], $line['unit_weight_kg'],
            ], $quote['items'])));
            if (! hash_equals((string) session('checkout_quote'), $fingerprint)) {
                throw ValidationException::withMessages(['cart' => 'Состав, цена или фасовка изменились. Вернитесь в корзину и проверьте итог.']);
            }
            unset($address['verified_at']);
            $order = Order::create([...$address, 'user_id' => $request->user()->id,
                'total_amount' => $quote['total'], 'status' => Order::STATUS_PENDING,
                'checkout_token' => $data['checkout_token'], 'contact_phone' => $data['contact_phone'],
                'delivery_details' => $data['delivery_details'] ?? null]);
            foreach ($quote['items'] as $item) {
                $order->items()->create(['product_id' => $item['product_id'], 'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'], 'price_at_moment' => $item['price'], 'purchase_type' => $item['purchase_type'],
                    'unit' => $item['unit'], 'unit_weight_kg' => $item['unit_weight_kg'], 'line_total' => $item['subtotal']]);
            }

            return $order;
        });
        session()->forget(['cart', 'checkout_quote', 'checkout_token', 'checkout_address']);

        return redirect()->route('payment.show', $order)->with('success', 'Заказ создан. Выберите способ оплаты.');
    }
}
