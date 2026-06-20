<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
{
    $query = Order::with(['user', 'items']);

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
        $order->load(['user', 'items.product', 'statusHistory.changedBy']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
{
    $newStatus = $request->input('status');
    
    // Валидация
    $rules = [
        'status' => 'required|in:pending,paid,shipping,delivered,cancelled',
        'comment' => 'nullable|string|max:500',
    ];

    // Если отмена — комментарий обязателен
    if ($newStatus === Order::STATUS_CANCELLED) {
        $rules['comment'] = 'required|string|max:500';
    }

    $request->validate($rules, [
        'comment.required' => 'При отмене заказа необходимо указать причину отмены',
    ]);

    $oldStatus = $order->status;

    // Если статус не изменился
    if ($oldStatus === $newStatus) {
        return back()->with('info', 'Статус не изменился');
    }

    // Определяем, является ли это откатом
    $isRevert = $order->canRevert() && $newStatus === $order->previous_status;

    // Проверка возможности перехода
    if (!$order->canTransitionTo($newStatus)) {
        return back()->with('error', 
            "Невозможно изменить статус с «{$order->status_label}» на «{$this->statusLabel($newStatus)}». " .
            "Разрешенные переходы: " . $this->getAllowedTransitionsLabel($order)
        );
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
    if ($isRevert) {
        // Это откат — previous_status НЕ меняем, чтобы можно было откатить ещё раз
        $order->update([
            'status' => $newStatus,
            'status_changed_at' => now(),
            // previous_status остаётся прежним
        ]);
    } else {
 
$order->update([
    'status' => $newStatus,
    'status_changed_at' => now(),
    'previous_status' => $oldStatus,
]);
    }

    // Если заказ оплачен — устанавливаем дату оплаты
    if ($newStatus === Order::STATUS_PAID && !$order->paid_at) {
        $order->update(['paid_at' => now()]);
    }

    $message = "Статус заказа №{$order->id} изменен: «{$this->statusLabel($oldStatus)}» → «{$this->statusLabel($newStatus)}»";
    
    if ($order->canRevert()) {
        $message .= ". У вас есть 10 минут для отката изменения.";
    }

    return back()->with('success', $message);
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
    /**
 * Мягкое удаление заказа
 */
public function destroy(Request $request, Order $order)
{
    $request->validate([
        'delete_reason' => 'required|string|max:500',
    ]);

    // Нельзя удалять уже удалённые заказы
    if ($order->trashed()) {
        return back()->with('error', 'Заказ уже удалён');
    }

    $order->update([
        'deleted_by' => Auth::id(),
        'delete_reason' => $request->delete_reason,
    ]);

    $order->delete(); // Мягкое удаление

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
}