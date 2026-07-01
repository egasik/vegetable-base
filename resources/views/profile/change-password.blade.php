@extends('layouts.app')
@section('title', 'Смена пароля')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-4xl font-black text-[#422168]">Смена пароля</h1>
        <a href="{{ route('profile.edit') }}" class="bg-gray-200 text-[#422168] font-bold py-2 px-4 rounded-xl btn-animated">← Назад</a>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-xl border-4 border-[#E8FC8C]">
        <form action="{{ route('profile.password-change.request') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">Текущий пароль</label>
                <input type="password" name="current_password" required
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl focus:border-[#CAF204] focus:outline-none">
                @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">Новый пароль</label>
                <input type="password" name="new_password" required minlength="8"
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl focus:border-[#CAF204] focus:outline-none">
                @error('new_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">Подтверждение пароля</label>
                <input type="password" name="new_password_confirmation" required
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl focus:border-[#CAF204] focus:outline-none">
            </div>
            <button type="submit" class="w-full bg-[#0D7D4C] text-white font-bold py-3 rounded-xl btn-animated">
                Получить код подтверждения на электронную почту 
            </button>
        </form>
    </div>
</div>
@endsection