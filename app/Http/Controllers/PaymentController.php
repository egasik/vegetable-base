<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymentController extends Controller
{
    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 403);
    }

    public function show(Order $order)
    {
        $this->authorizeOrder($order);

        return view('payment.show', compact('order'));
    }

    public function process(Request $request, Order $order, PaymentService $service)
    {
        $this->authorizeOrder($order);
        $data = $request->validate(['payment_method' => 'required|in:bank_card,sbp']);
        try {
            $payment = $service->start($order, $data['payment_method']);
            if (in_array($payment['status'] ?? '', ['succeeded', 'canceled'], true)) {
                return redirect()->route('payment.success', $order);
            }
            $confirmation = $payment['confirmation'] ?? [];
            if (($confirmation['type'] ?? '') === 'qr' && ! empty($confirmation['confirmation_data'])) {
                $qrCode = QrCode::format('svg')->size(280)->margin(2)->generate($confirmation['confirmation_data']);

                return view('payment.qr', compact('order', 'qrCode'));
            }
            $url = $confirmation['confirmation_url'] ?? '';
            if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
                throw new \RuntimeException('Invalid confirmation URL');
            }

            return redirect()->away($url);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
    dd($e->getMessage());
}
    }

    public function success(Order $order, PaymentService $service)
    {
        $this->authorizeOrder($order);
        try {
            $order = $service->sync($order);
        } catch (\Throwable $e) {
            session()->flash('error', 'Статус пока не подтверждён. Повторите проверку позже.');
        }

        return view('payment.success', compact('order'));
    }

    public function fail(Order $order)
    {
        $this->authorizeOrder($order);

        return view('payment.fail', compact('order'));
    }

    public function checkStatus(Order $order, PaymentService $service)
    {
        $this->authorizeOrder($order);

        return redirect()->route('payment.success', $order);
    }

    public function webhook(Request $request, PaymentService $service)
    {
        $data = $request->validate(['object.id' => 'required|string|max:128']);
        $order = Order::where('payment_id', $data['object']['id'])->first();
        if (! $order) {
            return response()->json(['status' => 'ignored']);
        }
        try {
            $service->sync($order);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'retry'], 503);
        }

        return response()->json(['status' => 'ok']);
    }
}
