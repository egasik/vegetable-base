<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
        public function store(Request $request)
{
    // Если заказ из корзины
    if ($request->filled('from_cart')) {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return back()->with('error', 'Корзина пуста');
        }

        $totalAmount = 0;
        $orderItems = [];

        foreach ($cart as $cartKey => $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $price = $item['price'];
            $quantity = $item['quantity'];
            $totalAmount += $price * $quantity;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price_at_moment' => $price,
            ];
        }

        if (empty($orderItems)) {
            return back()->with('error', 'Нет товаров для заказа');
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            foreach ($orderItems as $orderItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $orderItem['product_id'],
                    'quantity' => $orderItem['quantity'],
                    'price_at_moment' => $orderItem['price_at_moment'],
                ]);
            }

            DB::commit();
            session()->forget('cart');

            return redirect()->route('payment.show', $order)
                ->with('success', 'Заказ создан! Выберите способ оплаты.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ошибка при оформлении заказа');
        }
    }

    // Обычный заказ одного товара (старая логика)
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'purchase_type' => 'required|in:retail,wholesale',
        'quantity' => 'required|integer|min:1|max:9999',
    ]);

    $product = Product::findOrFail($request->product_id);
    $quantity = (int) $request->quantity;

    if ($request->purchase_type === 'retail') {
        if (!$product->is_retail) {
            return back()->with('error', 'Товар недоступен для розничной покупки.');
        }
        $priceAtMoment = $product->retail_price;
    } else {
        if (!$product->is_wholesale) {
            return back()->with('error', 'Товар недоступен для оптовой покупки.');
        }
        $priceAtMoment = $product->wholesale_price;
    }

    $totalAmount = $priceAtMoment * $quantity;

    DB::beginTransaction();
    try {
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price_at_moment' => $priceAtMoment,
        ]);

        DB::commit();

        return redirect()->route('payment.show', $order)
            ->with('success', 'Заказ создан! Выберите способ оплаты.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Ошибка при оформлении заказа');
    }
}
}