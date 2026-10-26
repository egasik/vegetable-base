<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            'revenue' => Order::whereNotNull('paid_at')->where('status', '!=', Order::STATUS_CANCELLED)->sum('total_amount'),
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
        $data = $request->validate([
            'status' => 'required|in:pending,paid,shipping,delivered,cancelled',
            'comment' => 'required_if:status,cancelled|nullable|string|max:500',
            'delivery_photo' => 'required_if:status,delivered|nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        $path = $request->hasFile('delivery_photo') ? $request->file('delivery_photo')->store('delivery-photos', 'local') : null;
        if ($request->hasFile('delivery_photo') && ! $path) {
            throw ValidationException::withMessages(['delivery_photo' => 'Не удалось сохранить фотографию. Статус не изменён.']);
        }
        try {
            DB::transaction(function () use ($order, $data, $path) {
                $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                if (! $locked->canTransitionTo($data['status']) || ($data['status'] === 'paid' && ! $locked->paid_at)) {
                    throw ValidationException::withMessages(['status' => 'Этот переход недоступен. Оплату подтверждает только ЮKassa.']);
                }
                $old = $locked->status;
                $revert = $locked->canRevert() && $data['status'] === $locked->previous_status;
                $locked->statusHistory()->create(['changed_by' => auth()->id(), 'old_status' => $old,
                    'new_status' => $data['status'], 'comment' => $data['comment'] ?? null]);
                if ($path) {
                    $locked->deliveryPhotos()->create(['uploaded_by' => auth()->id(), 'file_path' => $path, 'comment' => $data['comment'] ?? null]);
                }
                $locked->update(['status' => $data['status'], 'status_changed_at' => now(), 'previous_status' => $revert ? null : $old]);
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }

        return back()->with('success', 'Статус обновлён. Допустимый откат доступен в течение 30 минут.');
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
        return match ($status) {
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

        if (! $order->trashed()) {
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

        if (! $order->trashed()) {
            return back()->with('error', 'Сначала нужно мягко удалить заказ');
        }

        $order->items()->delete();
        $order->statusHistory()->delete();
        $order->forceDelete();

        return redirect()->route('admin.orders.index')
            ->with('success', "Заказ №{$id} удалён безвозвратно");
    }
}
