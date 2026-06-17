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
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'purchase_type' => 'required|in:retail,wholesale',
            'quantity' => 'required|integer|min:1|max:9999',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = (int) $request->quantity;

        // Определяем цену и описание единицы в зависимости от выбора
        if ($request->purchase_type === 'retail') {
            if (!$product->is_retail) {
                return back()->with('error', 'Данный товар недоступен для розничной покупки.');
            }
            $priceAtMoment = $product->retail_price;
            $unitLabel = 'кг';
        } else {
            if (!$product->is_wholesale) {
                return back()->with('error', 'Данный товар недоступен для оптовой покупки.');
            }
            $priceAtMoment = $product->wholesale_price;
            $unitLabel = 'мешок (' . $product->wholesale_unit_kg . ' кг)';
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
                'quantity' => $quantity, // Количество кг ИЛИ количество мешков
                'price_at_moment' => $priceAtMoment, // Цена за 1 кг ИЛИ цена за 1 мешок
            ]);

            DB::commit();

            return redirect()->route('profile.edit')->with('success', "🎉 Заказ на {$quantity} {$unitLabel} успешно оформлен на сумму {$totalAmount} ₽!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ошибка при оформлении заказа. Попробуйте позже.');
        }
    }
}