<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,delivering,delivered,cancelled',
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Если статус стал "delivered" — проверяем связанные товары
        if ($request->status === Order::STATUS_DELIVERED) {
            $this->cleanupDeliveredProducts($order);
        }

        return back()->with('success', 
            "Статус заказа №{$order->id} изменён: " . 
            Order::getStatuses()[$oldStatus] . ' → ' . 
            Order::getStatuses()[$request->status]
        );
    }

    /**
     * После вручения заказа — проверяем, можно ли окончательно удалить товары
     */
    private function cleanupDeliveredProducts(Order $order)
    {
        $productIds = $order->items()->pluck('product_id')->unique();

        foreach ($productIds as $productId) {
            $product = Product::withTrashed()->find($productId);
            
            if ($product && $product->trashed() && !$product->hasActiveOrders()) {
                // Товар был soft-deleted и больше не используется — удаляем полностью
                $product->orderItems()->delete();
                if ($product->image_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image_path);
                }
                $product->forceDelete();
            }
        }
    }
}