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
            'name' => ['required', 'string', 'max:255', 'regex:/^[А-ЯЁ][а-яА-ЯёЁ]*$/u'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[А-ЯЁ][а-яА-ЯёЁ]*$/u'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[А-ЯЁ][а-яА-ЯёЁ]*$/u'],
            'phone' => ['required', 'string', 'regex:/^\+7-\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required', 
                'string', 
                'min:8', 
                'confirmed',
                'regex:/[a-zA-Z]/',      // Минимум 1 буква (русская или английская)
                'regex:/[0-9]/',                 // Минимум 1 цифра
                'regex:/[@$!%*#?&]/',            // Минимум 1 спецсимвол
            ],
        ], [
            'name.regex' => 'Имя должно начинаться с заглавной буквы и содержать только буквы.',
            'last_name.regex' => 'Фамилия должна начинаться с заглавной буквы и содержать только буквы.',
            'middle_name.regex' => 'Отчество должно начинаться с заглавной буквы и содержать только буквы.',
            'phone.regex' => 'Номер телефона должен быть в формате +7-(ХXX)-XXX-XX-XX',
        ]);

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('profile.edit', absolute: false))
            ->with('success', '🎉 Регистрация прошла успешно! Добро пожаловать в Овощную базу!');
    }
}
