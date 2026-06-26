<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymentController extends Controller
{
    /**
     * Страница выбора способа оплаты
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        if ($order->status === 'completed') {
            return redirect()->route('payment.success', $order);
        }

        return view('payment.show', compact('order'));
    }

    /**
     * Обработка выбора способа оплаты
     */
    public function process(Request $request, Order $order)
{
    $request->validate([
        'delivery_city' => 'required|string|max:255',
        'delivery_street' => 'required|string|max:255',
        'delivery_house' => 'required|string|max:50',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'delivery_address' => 'required|string|max:500',
    ]);

    // Формируем полный адрес
    $fullAddress = implode(', ', array_filter([
        $request->delivery_city,
        $request->delivery_street,
        "д. {$request->delivery_house}",
    ]));

    // Сохраняем адрес доставки
    $order->update([
        'delivery_address' => $fullAddress,
        'delivery_city' => $request->delivery_city,
        'delivery_region' => 'Иркутская область',
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
    ]);

    // Создаём платёж в ЮKassa
    $yooKassaClient = new Client();
    $yooKassaClient->setAuth(
        config('services.yookassa.shop_id'),
        config('services.yookassa.secret_key')
    );

    $payment = $yooKassaClient->createPayment([
        'amount' => [
            'value' => number_format($order->total_amount, 2, '.', ''),
            'currency' => 'RUB',
        ],
        'confirmation' => [
            'type' => 'redirect',
            'return_url' => route('payment.success', $order),
        ],
        'capture' => true,
        'description' => 'Оплата заказа №' . $order->id,
        'metadata' => [
            'order_id' => $order->id,
        ],
    ], uniqid('', true));

    $order->update([
        'payment_id' => $payment->getId(),
    ]);

    return redirect($payment->getConfirmation()->getConfirmationUrl());
}

    /**
     * Страница успешной оплаты
     */
    public function success(Order $order)
{
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    // Проверяем реальный статус платежа в ЮKassa
    if ($order->payment_id && $order->status !== 'completed') {
        try {
            $response = Http::withBasicAuth(
                config('yookassa.shop_id'),
                config('yookassa.secret_key')
            )->withoutVerifying()
              ->get('https://api.yookassa.ru/v3/payments/' . $order->payment_id);

            if ($response->successful()) {
                $payment = $response->json();
                
                // Обновляем статус заказа на основе ответа ЮKassa
                if ($payment['status'] === 'succeeded') {
                    $order->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                } elseif ($payment['status'] === 'canceled') {
                    $order->update(['status' => 'cancelled']);
                } elseif ($payment['status'] === 'pending') {
                    $order->update(['status' => 'pending']);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Ошибка проверки платежа: ' . $e->getMessage());
        }
    }

    return view('payment.success', compact('order'));
}

    /**
     * Страница неуспешной оплаты
     */
    public function fail(Order $order)
    {
        return view('payment.fail', compact('order'));
    }

    /**
     * Webhook от ЮKassa
     */
    public function webhook(Request $request)
    {
        $event = $request->input('event');
        $paymentData = $request->input('object');

        if ($event === 'payment.succeeded') {
            $paymentId = $paymentData['id'];
            $orderId = $paymentData['metadata']['order_id'] ?? null;

            if ($orderId) {
                $order = Order::find($orderId);
                
                if ($order && $order->payment_id === $paymentId) {
                    $order->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
    /**
 * Принудительное обновление статуса заказа (для пользователя)
 */
public function checkStatus(Order $order)
{
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    if (!$order->payment_id) {
        return back()->with('error', 'Нет ID платежа для проверки');
    }

    try {
        $response = Http::withBasicAuth(
            config('yookassa.shop_id'),
            config('yookassa.secret_key')
        )->withoutVerifying()
          ->get('https://api.yookassa.ru/v3/payments/' . $order->payment_id);

        if ($response->successful()) {
            $payment = $response->json();
            
            if ($payment['status'] === 'succeeded') {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                return back()->with('success', '✅ Статус обновлен: заказ оплачен!');
            } elseif ($payment['status'] === 'canceled') {
                $order->update(['status' => 'cancelled']);
                return back()->with('error', '❌ Платеж отменен');
            } else {
                return back()->with('info', '⏳ Платеж еще обрабатывается. Статус: ' . $payment['status']);
            }
        }

        return back()->with('error', 'Не удалось получить статус платежа');

    } catch (\Exception $e) {
        return back()->with('error', 'Ошибка проверки: ' . $e->getMessage());
    }
}
    
}