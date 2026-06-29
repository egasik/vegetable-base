<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\DeliveryPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items', 'deliveryPhotos']);

        // Фильтр: показывать удалённые
        $showTrashed = $request->boolean('trashed');
        if ($showTrashed) {
            $query->onlyTrashed();
        }

        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Поиск по клиенту
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Поиск по ID заказа
        if ($request->filled('order_id')) {
            $query->where('id', $request->order_id);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        // Статистика (только активные заказы)
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'paid' => Order::where('status', Order::STATUS_PAID)->count(),
            'shipping' => Order::where('status', Order::STATUS_SHIPPING)->count(),
            'delivered' => Order::where('status', Order::STATUS_DELIVERED)->count(),
            'cancelled' => Order::where('status', Order::STATUS_CANCELLED)->count(),
            'trashed' => Order::onlyTrashed()->count(),
            'revenue' => Order::where('status', '!=', Order::STATUS_CANCELLED)->sum('total_amount'),
        ];

        return view('admin.orders.index', compact('orders', 'stats', 'showTrashed'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'statusHistory.changedBy', 'deliveryPhotos.user']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
{
    $request->validate([
        'status' => 'required|in:pending,paid,shipping,delivered,cancelled',
        'photo' => $request->status === 'delivered' 
            ? 'required|image|mimes:jpeg,png,jpg,gif,webp'
            : 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
        'comment' => 'nullable|string|max:500',
    ]);

    $oldStatus = $order->status;
    $newStatus = $request->status;

    if ($oldStatus === $newStatus) {
        return back()->with('info', 'Статус не изменился');
    }

    // Проверка возможности перехода через модель
    if (!$order->canTransitionTo($newStatus)) {
        return back()->with('error', 
            "Невозможно изменить статус с «{$order->status_label}» на «{$this->statusLabel($newStatus)}». Недопустимый переход."
        );
    }

    // Если статус "доставлено" — фото обязательно
    if ($newStatus === Order::STATUS_DELIVERED && !$request->hasFile('photo')) {
        return back()->with('error', 'При подтверждении доставки необходимо прикрепить фото');
    }

    // Загрузка фото если есть
    if ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('delivery-photos', 'public');
        
        DeliveryPhoto::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'file_path' => $path,
            'comment' => $request->comment,
        ]);
    }

    // Сохраняем историю
    OrderStatusHistory::create([
        'order_id' => $order->id,
        'changed_by' => Auth::id(),
        'old_status' => $oldStatus,
        'new_status' => $newStatus,
        'comment' => $request->comment,
    ]);

    // Обновляем заказ
    $order->update([
        'status' => $newStatus,
        'status_changed_at' => now(),
        'previous_status' => $oldStatus,
    ]);

    // Если доставлено — устанавливаем дату доставки
    if ($newStatus === Order::STATUS_DELIVERED) {
        $order->update(['delivered_at' => now()]);
    }

    return back()->with('success', 
        "Статус заказа №{$order->id} изменён: «{$this->statusLabel($oldStatus)}» → «{$this->statusLabel($newStatus)}»"
    );
}

    public function addComment(Request $request, Order $order)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'changed_by' => Auth::id(),
            'old_status' => $order->status,
            'new_status' => $order->status,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Комментарий добавлен');
    }

    /**
     * Мягкое удаление заказа
     */
    public function destroy(Request $request, Order $order)
    {
        $request->validate([
            'delete_reason' => 'required|string|max:500',
        ]);

        if ($order->trashed()) {
            return back()->with('error', 'Заказ уже удалён');
        }

        $order->update([
            'deleted_by' => Auth::id(),
            'delete_reason' => $request->delete_reason,
        ]);

        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', "Заказ №{$order->id} удалён");
    }

    /**
     * Восстановление удалённого заказа
     */
    public function restore($id)
    {
        $order = Order::withTrashed()->findOrFail($id);

        if (!$order->trashed()) {
            return back()->with('info', 'Заказ не удалён');
        }

        $order->restore();
        $order->update([
            'deleted_by' => null,
            'delete_reason' => null,
        ]);

        return back()->with('success', "Заказ №{$order->id} восстановлен");
    }

    /**
     * Полное (безвозвратное) удаление заказа
     */
    public function forceDelete($id)
    {
        $order = Order::withTrashed()->findOrFail($id);

        if (!$order->trashed()) {
            return back()->with('error', 'Сначала нужно мягко удалить заказ');
        }

        $order->items()->delete();
        $order->statusHistory()->delete();
        $order->forceDelete();

        return redirect()->route('admin.orders.index')
            ->with('success', "Заказ №{$id} удалён безвозвратно");
    }

    /**
     * Метка статуса (ЕДИНСТВЕННАЯ ВЕРСИЯ)
     */
    private function statusLabel(string $status): string
    {
        return match($status) {
            Order::STATUS_PENDING => 'Ожидает оплаты',
            Order::STATUS_PAID => 'Оплачен',
            Order::STATUS_SHIPPING => 'В доставке',
            Order::STATUS_DELIVERED => 'Вручен',
            Order::STATUS_CANCELLED => 'Отменен',
            default => $status,
        };
    }
}