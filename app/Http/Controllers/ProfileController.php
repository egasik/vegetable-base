<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user(),
            'orders' => $request->user()->orders()->with(['items.product', 'deliveryPhotos'])->latest()->paginate(5)]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate(['name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048']);
        $user->name = $data['name'];
        if ($user->email !== $data['email']) {
            $user->email = $data['email'];
            $user->email_verified_at = null;
        }
        if ($request->hasFile('avatar')) {
            $old = $user->avatar_path;
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
            $user->save();
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        } else {
            $user->save();
        }

        return redirect()->route('profile.edit')->with('success', 'Профиль обновлён.');
    }
}
