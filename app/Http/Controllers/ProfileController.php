<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        // Дешифруем данные карты для отображения в форме (если они есть)
        $decrypted_card = $user->card_data ? Crypt::decryptString($user->card_data) : '';
        $orders = $user->orders()->with('items.product')->latest()->paginate(5);

        return view('profile.edit', [
            'user' => $user,
            'decrypted_card' => $decrypted_card,
            'orders' => $orders,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Обработка загрузки аватара
        if ($request->hasFile('avatar')) {
            // Удаляем старый аватар, если он есть
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            // Если файл не загружен, убираем из массива, чтобы не затереть старый путь null-ом
            unset($data['avatar']);
        }

        // Обработка данных карты (шифрование внутренними средствами Laravel)
        if ($request->filled('card_data')) {
            $data['card_data'] = Crypt::encryptString($request->card_data);
        } else {
            unset($data['card_data']);
        }

        $user->fill($data);
        
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

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