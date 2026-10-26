<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    private function client()
    {
        if (! config('yookassa.shop_id') || ! config('yookassa.secret_key')) {
            throw ValidationException::withMessages(['payment' => 'Оплата пока не настроена. Обратитесь к администратору.']);
        }

                return Http::withBasicAuth(
            config('yookassa.shop_id'),
            config('yookassa.secret_key')
        )
            ->withoutVerifying()
            ->acceptJson()
            ->timeout(15);
    }

    public function start(Order $order, string $method): array
    {
        DB::transaction(function () use ($order, $method) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== Order::STATUS_PENDING || $locked->paid_at) {
                throw ValidationException::withMessages(['payment' => 'Этот заказ уже оплачен или закрыт.']);
            }
            if (! $locked->payment_key && ! $locked->payment_id) {
                $locked->update(['payment_key' => (string) Str::uuid(), 'payment_key_created_at' => now(), 'payment_method' => $method]);
            }
        });

        return DB::transaction(function () use ($order) {
            $locked = Order::with(['items', 'user'])->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->paid_at || $locked->status !== Order::STATUS_PENDING) {
                throw ValidationException::withMessages(['payment' => 'Заказ больше не ожидает оплаты.']);
            }
            if ($locked->payment_id) {
                return $this->fetch($locked->payment_id);
            }
            if (! $locked->payment_key_created_at || $locked->payment_key_created_at->lt(now()->subHours(23))) {
                throw ValidationException::withMessages(['payment' => 'Срок попытки оплаты истёк. Обратитесь к администратору для проверки платежа.']);
            }
            $payload = ['amount' => ['value' => number_format($locked->total_amount, 2, '.', ''), 'currency' => 'RUB'],
                'capture' => true, 'description' => 'Заказ №'.$locked->id,
                'metadata' => ['order_id' => (string) $locked->id],
                'payment_method_data' => ['type' => $locked->payment_method],
                'confirmation' => $locked->payment_method === 'sbp' ? ['type' => 'qr'] : [
                    'type' => 'redirect', 'return_url' => route('payment.success', $locked)],
                'receipt' => ['customer' => ['email' => $locked->user->email], 'items' => $locked->items->map(fn ($item) => [
                    'description' => mb_substr(($item->product_name ?? 'Товар').' ('.($item->unit ?? 'ед.').')', 0, 128),
                    'quantity' => (string) $item->quantity,
                    'amount' => ['value' => number_format($item->price_at_moment, 2, '.', ''), 'currency' => 'RUB'],
                    'vat_code' => (int) config('yookassa.vat_code', 1), 'payment_mode' => 'full_payment', 'payment_subject' => 'commodity',
                ])->all()]];
            $payment = $this->client()->withHeaders(['Idempotence-Key' => $locked->payment_key])
                ->post('https://api.yookassa.ru/v3/payments', $payload)->throw()->json();
            if (empty($payment['id'])) {
                throw new \RuntimeException('Missing payment ID');
            }
            $locked->update(['payment_id' => $payment['id']]);

            return $payment;
        });
    }

    public function fetch(string $id): array
    {
        return $this->client()->get('https://api.yookassa.ru/v3/payments/'.rawurlencode($id))->throw()->json();
    }

    public function sync(Order $order): Order
    {
        if (! $order->payment_id) {
            return $order;
        }
        $payment = $this->fetch($order->payment_id);

        return DB::transaction(function () use ($order, $payment) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (($payment['id'] ?? '') !== $locked->payment_id
                || (string) data_get($payment, 'metadata.order_id') !== (string) $locked->id
                || data_get($payment, 'amount.currency') !== 'RUB'
                || number_format((float) data_get($payment, 'amount.value', 0), 2, '.', '') !== number_format($locked->total_amount, 2, '.', '')
                || (bool) ($payment['test'] ?? false) !== (bool) config('yookassa.test_mode')) {
                throw new \RuntimeException('Payment does not match order');
            }
            if (($payment['status'] ?? '') === 'succeeded' && ($payment['paid'] ?? false) && ! $locked->paid_at) {
                $old = $locked->status;
                // Keep delivery/cancellation decisions, but always record the confirmed payment fact.
                $new = $old === Order::STATUS_PENDING ? Order::STATUS_PAID : $old;
                $locked->update(['paid_at' => now(), 'status' => $new, 'status_changed_at' => now(), 'previous_status' => null]);
                $locked->statusHistory()->create(['old_status' => $old, 'new_status' => $new, 'changed_by' => null,
                    'comment' => 'Оплата подтверждена API ЮKassa.']);
            } elseif (($payment['status'] ?? '') === 'canceled' && $locked->status === Order::STATUS_PENDING && ! $locked->paid_at) {
                $locked->update(['status' => Order::STATUS_CANCELLED, 'status_changed_at' => now(), 'previous_status' => null]);
                $locked->statusHistory()->create(['old_status' => Order::STATUS_PENDING, 'new_status' => Order::STATUS_CANCELLED,
                    'changed_by' => null, 'comment' => 'Платёж отменён в ЮKassa.']);
            }

            return $locked;
        });
    }
}
