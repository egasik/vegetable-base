<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
           public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'phone' => ['required', 'string', 'max:20'],
        'password' => ['required', 'confirmed', 'min:8'],
    ]);

    // Создаём пользователя БЕЗ подтверждения email
    $user = User::create([
        'name' => $request->name,
        'last_name' => $request->last_name,
        'email' => $request->email,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
        'email_verified_at' => null, // Явно указываем, что не подтверждён
    ]);

    // Генерируем код подтверждения
    $code = \App\Models\VerificationCode::createForUser($user->id, 'email_confirm');
    
    // Отправляем код на email
    try {
        \Illuminate\Support\Facades\Mail::to($user->email)->send(
            new \App\Mail\VerificationCodeMail($code, $user->name, 'Подтверждение email')
        );
    } catch (\Exception $e) {
        \Log::error('Ошибка отправки кода подтверждения: ' . $e->getMessage());
    }

    // Логиним пользователя
    Auth::login($user);

    // Редирект на страницу подтверждения email
    return redirect()->route('profile.verify-email')
        ->with('success', 'Регистрация успешна! Код подтверждения отправлен на ' . $user->email);
}
}
