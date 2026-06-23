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
    $orders = $user->orders()->with('items')->latest()->paginate(10);

    return view('profile.edit', [
        'user' => $user,
        'orders' => $orders,
    ]);
}

    public function update(Request $request): RedirectResponse
{
    $user = $request->user();
    
    // Валидация данных (только аватар)
    $request->validate([
        'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
    ]);

    // Обработка загрузки аватара
    if ($request->hasFile('avatar')) {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        $user->save();
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