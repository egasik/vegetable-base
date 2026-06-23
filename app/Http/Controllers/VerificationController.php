<?php

namespace App\Http\Controllers;

use App\Models\VerificationCode;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    /**
     * Метки для писем
     */
    private function getPurposeLabel(string $purpose): string
    {
        return match($purpose) {
            'email_confirm' => 'Подтверждение email',
            'email_change' => 'Смена email',
            'password_change' => 'Смена пароля',
            default => 'Подтверждение операции',
        };
    }

    /**
     * Отправка кода на email
     */
    private function sendCodeByEmail(VerificationCode $code, string $purpose): void
    {
        $user = Auth::user();
        $label = $this->getPurposeLabel($purpose);
        
        // Для смены email — отправляем на НОВЫЙ email
        // Для остальных — на текущий email пользователя
        $recipientEmail = ($purpose === 'email_change' && $code->new_value) 
            ? $code->new_value 
            : $user->email;

        try {
            Mail::to($recipientEmail)->send(new VerificationCodeMail($code, $user->name, $label));
        } catch (\Exception $e) {
            \Log::error("Ошибка отправки кода {$purpose}: " . $e->getMessage());
        }
    }

    /**
     * AJAX-проверка кода в реальном времени
     * ВАЖНО: НЕ помечает код как использованный и НЕ инкрементирует attempts
     */
    public function checkCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'purpose' => 'required|in:email_confirm,email_change,password_change',
        ]);

        $user = Auth::user();
        $activeCode = VerificationCode::getActiveCode($user->id, $request->purpose);

        if (!$activeCode) {
            return response()->json([
                'valid' => false,
                'message' => 'Код не найден или истёк срок действия',
            ]);
        }

        if (!$activeCode->canAttempt()) {
            return response()->json([
                'valid' => false,
                'message' => 'Превышено количество попыток',
            ]);
        }

        $isValid = $activeCode->code === $request->code;

        return response()->json([
            'valid' => $isValid,
            'message' => $isValid ? 'Код верный' : 'Неверный код',
            'attempts_left' => VerificationCode::MAX_ATTEMPTS - $activeCode->attempts,
        ]);
    }

    /**
     * Форма подтверждения email после регистрации
     */
    public function showEmailConfirmForm()
    {
        $user = Auth::user();
        
        if ($user->email_verified_at) {
            return redirect()->route('profile.edit')->with('info', 'Email уже подтверждён');
        }

        $activeCode = VerificationCode::getActiveCode($user->id, 'email_confirm');

        // Если нет активного кода — создаём и отправляем
        if (!$activeCode) {
            $activeCode = VerificationCode::createForUser($user->id, 'email_confirm');
            $this->sendCodeByEmail($activeCode, 'email_confirm');
        }

        // Вычисляем время до следующей отправки
        $secondsUntilResend = (int) max(0, 60 - $activeCode->created_at->diffInSeconds(now()));

        return view('profile.verify-email', compact('activeCode', 'secondsUntilResend'));
    }

    /**
     * Подтверждение email кодом
     */
    public function confirmEmail(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ], [
            'code.size' => 'Код должен содержать 6 символов',
        ]);

        $user = Auth::user();
        $activeCode = VerificationCode::getActiveCode($user->id, 'email_confirm');

        if (!$activeCode) {
            return back()->with('error', 'Код истёк. Сгенерируйте новый.');
        }

        if (!$activeCode->canAttempt()) {
            return back()->with('error', 'Превышено количество попыток. Сгенерируйте новый код.');
        }

        if ($activeCode->code !== $request->code) {
            $activeCode->increment('attempts');
            $left = VerificationCode::MAX_ATTEMPTS - $activeCode->attempts;
            return back()->with('error', "Неверный код. Осталось попыток: {$left}");
        }

        // Подтверждаем email
        $user->email_verified_at = now();
        $user->save();

        $activeCode->update(['confirmed' => true]);

        return redirect()->route('profile.edit')->with('success', 'Email успешно подтверждён!');
    }

    /**
     * Повторная отправка кода подтверждения email
     */
    public function regenerateEmailCode()
    {
        $user = Auth::user();
        
        // Проверяем, что прошло больше 60 секунд с последней отправки
        $lastCode = VerificationCode::where('user_id', $user->id)
            ->where('purpose', 'email_confirm')
            ->where('confirmed', false)
            ->latest()
            ->first();
        
        if ($lastCode && $lastCode->created_at->diffInSeconds(now()) < 60) {
            $secondsLeft = (int) (60 - $lastCode->created_at->diffInSeconds(now()));
            return back()->with('error', "Повторная отправка возможна через {$secondsLeft} секунд");
        }
        
        $newCode = VerificationCode::createForUser($user->id, 'email_confirm');
        $this->sendCodeByEmail($newCode, 'email_confirm');

        return redirect()->route('profile.verify-email')
            ->with('success', 'Новый код отправлен на ваш email');
    }

    /**
     * Форма смены email (первый шаг)
     */
    public function showEmailChangeForm()
    {
        $user = Auth::user();
        return view('profile.change-email', compact('user'));
    }

    /**
     * Запрос смены email (генерация и отправка кода)
     */
    public function requestEmailChange(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'new_email' => 'required|email|max:255|unique:users,email',
            'password' => 'required',
        ], [
            'new_email.unique' => 'Этот email уже используется',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Неверный пароль']);
        }

        if ($request->new_email === $user->email) {
            return back()->withErrors(['new_email' => 'Новый email должен отличаться от текущего']);
        }

        $code = VerificationCode::createForUser($user->id, 'email_change', $request->new_email);
        $this->sendCodeByEmail($code, 'email_change');

        return view('profile.change-email-code', [
            'code' => $code,
            'secondsUntilResend' => 0,
        ]);
    }

    /**
     * Подтверждение смены email
     */
    public function confirmEmailChange(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $activeCode = VerificationCode::getActiveCode($user->id, 'email_change');

        if (!$activeCode) {
            return redirect()->route('profile.email-change')
                ->with('error', 'Код истёк. Создайте новый запрос.');
        }

        if (!$activeCode->canAttempt()) {
            return back()->with('error', 'Превышено количество попыток');
        }

        if ($activeCode->code !== $request->code) {
            $activeCode->increment('attempts');
            $left = VerificationCode::MAX_ATTEMPTS - $activeCode->attempts;
            return back()->with('error', "Неверный код. Осталось попыток: {$left}");
        }

        // Меняем email
        $user->email = $activeCode->new_value;
        $user->email_verified_at = null; // Требуется повторное подтверждение
        $user->save();

        $activeCode->update(['confirmed' => true]);

        return redirect()->route('profile.edit')->with('success', 'Email успешно изменён! Подтвердите новый адрес.');
    }

    /**
     * Повторная отправка кода смены email
     */
    public function regenerateEmailChangeCode()
    {
        $user = Auth::user();
        
        // Проверяем, что прошло больше 60 секунд
        $lastCode = VerificationCode::where('user_id', $user->id)
            ->where('purpose', 'email_change')
            ->where('confirmed', false)
            ->latest()
            ->first();
        
        if ($lastCode && $lastCode->created_at->diffInSeconds(now()) < 60) {
            $secondsLeft = (int) (60 - $lastCode->created_at->diffInSeconds(now()));
            return back()->with('error', "Повторная отправка возможна через {$secondsLeft} секунд");
        }
        
        // Получаем активный код
        $activeCode = VerificationCode::getActiveCode($user->id, 'email_change');
        
        if (!$activeCode) {
            return redirect()->route('profile.email-change')
                ->with('error', 'Нет активного запроса. Создайте новый.');
        }
        
        // Создаём новый код
        $newCode = VerificationCode::createForUser(
            $user->id, 
            'email_change', 
            $activeCode->new_value
        );
        $this->sendCodeByEmail($newCode, 'email_change');

        return view('profile.change-email-code', [
            'code' => $newCode,
            'secondsUntilResend' => 0,
        ]);
    }

    /**
     * Форма смены пароля (первый шаг)
     */
    public function showPasswordChangeForm()
    {
        return view('profile.change-password');
    }

    /**
     * Запрос смены пароля (генерация и отправка кода)
     */
    public function requestPasswordChange(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.confirmed' => 'Пароли не совпадают',
            'new_password.min' => 'Пароль должен быть не менее 8 символов',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Неверный текущий пароль']);
        }

        $code = VerificationCode::createForUser(
            $user->id, 
            'password_change', 
            Hash::make($request->new_password)
        );
        $this->sendCodeByEmail($code, 'password_change');

        return view('profile.change-password-code', [
            'code' => $code,
            'secondsUntilResend' => 0,
        ]);
    }

    /**
     * Подтверждение смены пароля
     */
    public function confirmPasswordChange(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $activeCode = VerificationCode::getActiveCode($user->id, 'password_change');

        if (!$activeCode) {
            return redirect()->route('profile.password-change')
                ->with('error', 'Код истёк. Создайте новый запрос.');
        }

        if (!$activeCode->canAttempt()) {
            return back()->with('error', 'Превышено количество попыток');
        }

        if ($activeCode->code !== $request->code) {
            $activeCode->increment('attempts');
            $left = VerificationCode::MAX_ATTEMPTS - $activeCode->attempts;
            return back()->with('error', "Неверный код. Осталось попыток: {$left}");
        }

        // Меняем пароль
        $user->password = $activeCode->new_value;
        $user->save();

        $activeCode->update(['confirmed' => true]);

        return redirect()->route('profile.edit')->with('success', 'Пароль успешно изменён!');
    }

    /**
     * Повторная отправка кода смены пароля
     */
    public function regeneratePasswordCode()
    {
        $user = Auth::user();
        
        // Проверяем, что прошло больше 60 секунд
        $lastCode = VerificationCode::where('user_id', $user->id)
            ->where('purpose', 'password_change')
            ->where('confirmed', false)
            ->latest()
            ->first();
        
        if ($lastCode && $lastCode->created_at->diffInSeconds(now()) < 60) {
            $secondsLeft = (int) (60 - $lastCode->created_at->diffInSeconds(now()));
            return back()->with('error', "Повторная отправка возможна через {$secondsLeft} секунд");
        }
        
        // Получаем активный код
        $activeCode = VerificationCode::getActiveCode($user->id, 'password_change');
        
        if (!$activeCode) {
            return redirect()->route('profile.password-change')
                ->with('error', 'Нет активного запроса. Создайте новый.');
        }
        
        // Создаём новый код
        $newCode = VerificationCode::createForUser(
            $user->id, 
            'password_change', 
            $activeCode->new_value
        );
        $this->sendCodeByEmail($newCode, 'password_change');

        return view('profile.change-password-code', [
            'code' => $newCode,
            'secondsUntilResend' => 0,
        ]);
    }
}