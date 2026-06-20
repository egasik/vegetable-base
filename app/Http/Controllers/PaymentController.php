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
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    $request->validate([
        'payment_method' => 'required|in:bank_card,sbp',
    ]);

    $order->update(['payment_method' => $request->payment_method]);

    $user = Auth::user();

    // Базовые данные платежа
    $paymentData = [
        'amount' => [
            'value' => number_format($order->total_amount, 2, '.', ''),
            'currency' => 'RUB',
        ],
        'capture' => true,
        'description' => 'Заказ №' . $order->id . ' в магазине Овощная база',
        'metadata' => [
            'order_id' => $order->id,
        ],
    ];

    // Фискальный чек (обязателен для СБП)
    $receipt = [
        'customer' => [
            'email' => $user->email ?? 'test@example.com',
        ],
        'items' => [],
    ];

    // Добавляем товары из заказа в чек
    foreach ($order->items as $item) {
        $receipt['items'][] = [
            'description' => $item->product->name ?? 'Товар',
            'quantity' => (string) $item->quantity,
            'amount' => [
                'value' => number_format($item->price_at_moment, 2, '.', ''),
                'currency' => 'RUB',
            ],
            'vat_code' => 1, // Без НДС
            'payment_mode' => 'full_payment',
            'payment_subject' => 'commodity',
        ];
    }

    $paymentData['receipt'] = $receipt;

    // Настройка способа оплаты
    if ($request->payment_method === 'bank_card') {
        $paymentData['confirmation'] = [
            'type' => 'redirect',
            'return_url' => route('payment.success', $order),
        ];
        $paymentData['payment_method_data'] = [
            'type' => 'bank_card',
        ];
    } elseif ($request->payment_method === 'sbp') {
        $paymentData['confirmation'] = [
            'type' => 'qr',
        ];
        $paymentData['payment_method_data'] = [
            'type' => 'sbp',
        ];
    }

    try {
        $response = Http::withBasicAuth(
            config('yookassa.shop_id'),
            config('yookassa.secret_key')
        )->withHeaders([
            'Idempotence-Key' => uniqid('order_', true),
            'Content-Type' => 'application/json',
        ])->withoutVerifying()
          ->post('https://api.yookassa.ru/v3/payments', $paymentData);

        // Логируем ответ для диагностики
        \Log::info('ЮKassa ответ:', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if ($response->successful()) {
            $payment = $response->json();
            $order->update(['payment_id' => $payment['id']]);

            // Для СБП — показываем QR-код
            if ($request->payment_method === 'sbp') {
                $qrData = $payment['confirmation']['confirmation_data'] 
                       ?? $payment['confirmation']['confirmation_url'] 
                       ?? null;

                if ($qrData) {
                    $qrCode = QrCode::format('svg')
                        ->size(300)
                        ->margin(1)
                        ->generate($qrData);

                    return view('payment.qr', compact('order', 'qrCode'));
                }

                return back()->with('error', 'ЮKassa не вернула данные для QR-кода');
            }

            // Для карты — редирект
            if (isset($payment['confirmation']['confirmation_url'])) {
                return redirect($payment['confirmation']['confirmation_url']);
            }

            return back()->with('error', 'Не удалось получить ссылку для оплаты');

        } else {
            $error = $response->json()['description'] ?? 'Неизвестная ошибка';
            \Log::error('ЮKassa ошибка:', $response->json());
            return back()->with('error', 'Ошибка ЮKassa: ' . $error);
        }

    } catch (\Exception $e) {
        \Log::error('ЮKassa исключение: ' . $e->getMessage());
        return back()->with('error', 'Ошибка соединения: ' . $e->getMessage());
    }
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
                        'status' => 'completed',
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
                        'status' => 'completed',
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
                    'status' => 'completed',
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