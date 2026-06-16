<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use App\Models\UserCard;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        
        // Получаем карту пользователя (если есть)
        $card = $user->card;
        
        // Дешифруем данные карты для отображения
        if ($card) {
            $card->card_number_decrypted = $card->card_number ? Crypt::decryptString($card->card_number) : '';
            $card->cvc_code_decrypted = $card->cvc_code ? Crypt::decryptString($card->cvc_code) : '';
            $card->pin_code_decrypted = $card->pin_code ? Crypt::decryptString($card->pin_code) : '';
        }
        
        $orders = $user->orders()->with('items.product')->latest()->paginate(5);

        return view('profile.edit', [
            'user' => $user,
            'card' => $card,
            'orders' => $orders,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Валидация данных
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'card_number' => ['nullable', 'string', 'max:19'],
            'cvc_code' => ['nullable', 'string', 'max:3'],
            'pin_code' => ['nullable', 'string', 'max:4'],
        ]);

        // Обновление имени и email
        $user->name = $request->name;
        
        if ($user->email !== $request->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
        }

        // Обработка загрузки аватара
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        // Обработка данных карты
        if ($request->filled('card_number') || $request->filled('cvc_code') || $request->filled('pin_code')) {
            $card = UserCard::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'card_number' => $request->card_number ? Crypt::encryptString($request->card_number) : null,
                    'cvc_code' => $request->cvc_code ? Crypt::encryptString($request->cvc_code) : null,
                    'pin_code' => $request->pin_code ? Crypt::encryptString($request->pin_code) : null,
                    'is_default' => true,
                ]
            );
        }

        return Redirect::route('profile.edit')->with('success', 'Профиль успешно обновлен!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}