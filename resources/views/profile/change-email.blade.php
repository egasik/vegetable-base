@extends('layouts.app')
@section('title', 'Смена email')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-4xl font-black text-[#422168]">Смена email</h1>
        <a href="{{ route('profile.edit') }}" class="bg-gray-200 text-[#422168] font-bold py-2 px-4 rounded-xl btn-animated">← Назад</a>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-xl border-4 border-[#E8FC8C]">
        <h2 class="text-2xl font-black text-[#422168] mb-4">Запросить смену email</h2>
        <p class="text-sm text-gray-600 mb-6">Текущий email: <strong>{{ Auth::user()->email }}</strong></p>

        <form action="{{ route('profile.email-change.request') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">Новый email</label>
                <input type="email" name="new_email" value="{{ old('new_email') }}" required
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl focus:border-[#CAF204] focus:outline-none">
                @error('new_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">Текущий пароль</label>
                <input type="password" name="password" required
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl focus:border-[#CAF204] focus:outline-none">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full bg-[#0D7D4C] text-white font-bold py-3 rounded-xl btn-animated">
                Получить код подтверждения
            </button>
        </form>
    </div>
</div>
@endsection