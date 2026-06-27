<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymentController extends Controller
{
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

    public function process(Request $request, Order $order)
    {
        $validated = $request->validate([
            'delivery_city' => 'required|string|max:255',
            'delivery_street' => 'required|string|max:255',
            'delivery_house' => 'required|string|max:50',
            'delivery_address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $fullAddress = "{$validated['delivery_city']}, {$validated['delivery_street']}, д. {$validated['delivery_house']}";

        $order->update([
            'delivery_address' => $fullAddress,
            'delivery_city' => $validated['delivery_city'],
            'delivery_region' => 'Иркутская область',
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        // Создаём платёж через HTTP API
        try {
            $response = Http::withBasicAuth(
                config('yookassa.shop_id'),      // ← ИСПРАВЛЕНО
                config('yookassa.secret_key')    // ← ИСПРАВЛЕНО
            )->withoutVerifying()
              ->withHeaders([
                  'Idempotence-Key' => uniqid('', true),
                  'Content-Type' => 'application/json',
              ])
              ->post('https://api.yookassa.ru/v3/payments', [
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
              ]);

            if (!$response->successful()) {
                \Log::error('YooKassa API error: ' . $response->body());
                return back()->with('error', 'Ошибка создания платежа: ' . $response->body());
            }

            $payment = $response->json();

            $order->update([
                'payment_id' => $payment['id'],
            ]);

            return redirect($payment['confirmation']['confirmation_url']);

        } catch (\Exception $e) {
            \Log::error('Ошибка создания платежа: ' . $e->getMessage());
            return back()->with('error', 'Ошибка соединения с платёжной системой');
        }
    }

    public function success(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->payment_id && $order->status !== 'completed') {
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

    public function fail(Order $order)
    {
        return view('payment.fail', compact('order'));
    }

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