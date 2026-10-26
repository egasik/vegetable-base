<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailCodeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmailCodeController extends Controller
{
    public function show(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('checkout.show');
        }
        $code = \Illuminate\Support\Facades\DB::table('verification_codes')
            ->where('user_id', $request->user()->id)->where('email', $request->user()->email)->first();

        return view('auth.verify-email', [
            'attemptsLeft' => $code ? max(0, 5 - $code->attempts) : 5,
            'expiresAt' => $code && ! $code->used_at ? \Carbon\Carbon::parse($code->expires_at)->timestamp : 0,
            'resendAt' => $code ? \Carbon\Carbon::parse($code->sent_at)->addSeconds(60)->timestamp : 0,
        ]);
    }

    public function send(Request $request, EmailCodeService $service)
    {
        try {
            $service->send($request->user());
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', 'Не удалось отправить письмо. Попробуйте позже.');
        }

        return back()->with('success', 'Код отправлен на вашу почту.');
    }

    public function verify(Request $request, EmailCodeService $service)
    {
        $data = $request->validate(['code' => 'required|digits:6']);
        if (! $service->verify($request->user(), $data['code'])) {
            throw ValidationException::withMessages(['code' => 'Код неверен, истёк или исчерпаны 5 попыток. Запросите новый код.']);
        }

        return redirect()->intended(route('checkout.show'));
    }
}
