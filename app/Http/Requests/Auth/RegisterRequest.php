<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];
        foreach (['name', 'last_name', 'middle_name', 'email', 'phone'] as $field) {
            if (is_string($this->input($field))) {
                $normalized[$field] = trim($this->input($field));
            }
        }
        if (isset($normalized['email'])) {
            $normalized['email'] = mb_strtolower($normalized['email']);
        }
        $phone = $normalized['phone'] ?? null;
        if ($phone !== null && preg_match('/^[+0-9()\s-]{1,40}$/D', $phone)) {
            $digits = preg_replace('/\D/', '', $phone);
            if (strlen($digits) === 10) {
                $digits = '7'.$digits;
            }
            if (strlen($digits) === 11 && in_array($digits[0], ['7', '8'], true)) {
                $normalized['phone'] = '+7-('.substr($digits, 1, 3).')-'.substr($digits, 4, 3).'-'.substr($digits, 7, 2).'-'.substr($digits, 9, 2);
            }
        }
        $this->merge($normalized);
    }

    public function rules(): array
    {
        $name = ['bail', 'required', 'string', 'min:2', 'max:60', 'regex:/^[А-ЯЁ][А-Яа-яЁё]+(?:[ -][А-ЯЁ][А-Яа-яЁё]+)*$/u'];

        return [
            'name' => $name,
            'last_name' => $name,
            'middle_name' => ['bail', 'nullable', 'string', 'min:2', 'max:60', 'regex:/^[А-ЯЁ][А-Яа-яЁё]+(?:[ -][А-ЯЁ][А-Яа-яЁё]+)*$/u'],
            'phone' => ['bail', 'required', 'string', 'regex:/^\+7-\([0-9]{3}\)-[0-9]{3}-[0-9]{2}-[0-9]{2}$/D'],
            'email' => ['bail', 'required', 'string', 'email:rfc', 'max:254', function ($attribute, $value, $fail) {
                if (User::whereRaw('LOWER(email) = ?', [$value])->exists()) {
                    $fail('Аккаунт с такой почтой уже существует. Войдите или восстановите пароль.');
                }
            }],
            'password' => ['bail', 'required', 'string', 'min:8', 'max:72', 'regex:/^[\x21-\x7E]+$/D',
                'regex:/[a-zA-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/', 'confirmed'],
            'password_confirmation' => ['bail', 'required', 'string', 'max:72'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'Имя', 'last_name' => 'Фамилия', 'middle_name' => 'Отчество',
            'phone' => 'Телефон', 'email' => 'Почта', 'password' => 'Пароль', 'password_confirmation' => 'Повтор пароля'];
    }

    public function messages(): array
    {
        return [
            'required' => 'Заполните поле «:attribute».',
            'string' => 'Проверьте значение поля «:attribute».',
            'min' => 'Поле «:attribute» должно содержать не менее :min символов.',
            'max' => 'Поле «:attribute» должно содержать не более :max символов.',
            'name.regex' => 'Введите имя кириллицей с заглавной буквы. В составном имени допустим пробел или дефис.',
            'last_name.regex' => 'Введите фамилию кириллицей с заглавной буквы. В составной фамилии допустим пробел или дефис.',
            'middle_name.regex' => 'Введите отчество кириллицей с заглавной буквы или оставьте поле пустым.',
            'phone.regex' => 'Введите полный российский номер: +7 (999) 123-45-67.',
            'email.email' => 'Введите почту в формате name@example.ru.',
            'password.regex' => 'Используйте латинские буквы, цифру и хотя бы один символ @$!%*#?&. Пробелы и кириллица не допускаются.',
            'password.confirmed' => 'Пароли не совпадают. Проверьте повтор пароля.',
        ];
    }
}
