@extends('layouts.app')
@section('title', 'Личный кабинет')
@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-8">
            
            {{-- Уведомления --}}
            @if (session('success'))
                <div class="bg-[#00F3B5] text-[#422168] p-4 rounded-xl font-bold border-l-8 border-[#0D7D4C] shadow-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 text-red-700 p-4 rounded-xl font-bold border-l-8 border-red-500 shadow-lg">
                    {{ session('error') }}
                </div>
            @endif

            <h1 class="text-4xl font-black text-[#422168] text-center mb-8">Личный кабинет</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                {{-- ЛЕВАЯ КОЛОНКА: Профиль --}}
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-3xl shadow-xl border-4 border-[#E8FC8C] sticky top-24">
                        <h3 class="text-2xl font-black text-[#422168] mb-6 border-b-4 border-[#CAF204] pb-2">Настройки профиля</h3>
                        
                        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            @method('patch')

                            {{-- Аватар --}}
                            <div class="text-center">
                                <div class="relative inline-block">
                                    <div class="w-32 h-32 mx-auto rounded-full bg-[#E8FC8C] flex items-center justify-center text-5xl overflow-hidden border-4 border-[#0D7D4C]">
                                        @if ($user->avatar_path)
                                            <img src="{{ asset('storage/' . $user->avatar_path) }}" class="w-full h-full object-cover">
                                        @else
                                            👤
                                        @endif
                                    </div>
                                </div>
                                <label class="block mt-3">
                                    <span class="sr-only">Выберите аватар</span>
                                    <input type="file" name="avatar" accept="image/*" 
                                           class="block w-full text-sm text-[#422168] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-[#CAF204] file:text-[#422168] hover:file:bg-[#00F3B5] cursor-pointer btn-animated">
                                </label>
                                @error('avatar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Имя (readonly) --}}
                            <div>
                                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">Имя</label>
                                <input type="text" value="{{ $user->name }}" readonly
                                       class="w-full border-2 border-gray-300 bg-gray-100 p-3 rounded-xl text-gray-600 cursor-not-allowed">
                            </div>

                            {{-- Фамилия (readonly) --}}
                            <div>
                                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">Фамилия</label>
                                <input type="text" value="{{ $user->last_name }}" readonly
                                       class="w-full border-2 border-gray-300 bg-gray-100 p-3 rounded-xl text-gray-600 cursor-not-allowed">
                            </div>

                            {{-- Отчество (readonly) --}}
                            <div>
                                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">Отчество</label>
                                <input type="text" value="{{ $user->middle_name }}" readonly
                                       class="w-full border-2 border-gray-300 bg-gray-100 p-3 rounded-xl text-gray-600 cursor-not-allowed">
                            </div>

                            {{-- Email (readonly с кнопкой изменения) --}}
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-bold text-[#0D7D4C]">Email</label>
                                    <a href="{{ route('profile.email-change') }}" class="text-xs text-[#0D7D4C] hover:text-[#422168] font-bold underline">
                                        Изменить
                                    </a>
                                </div>
                                <input type="email" value="{{ $user->email }}" readonly
                                       class="w-full border-2 border-gray-300 bg-gray-100 p-3 rounded-xl text-gray-600 cursor-not-allowed">
                                @if($user->email_verified_at)
                                    <p class="text-xs text-green-600 mt-1">✓ Email подтверждён</p>
                                @else
                                    <p class="text-xs text-orange-600 mt-1">⚠ Email не подтверждён</p>
                                @endif
                            </div>

                            {{-- Пароль (с кнопкой изменения) --}}
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-bold text-[#0D7D4C]">Пароль</label>
                                    <a href="{{ route('profile.password-change') }}" class="text-xs text-[#0D7D4C] hover:text-[#422168] font-bold underline">
                                        Изменить
                                    </a>
                                </div>
                                <input type="password" value="********" readonly
                                       class="w-full border-2 border-gray-300 bg-gray-100 p-3 rounded-xl text-gray-600 cursor-not-allowed">
                            </div>

                            {{-- Телефон (readonly) --}}
                            <div>
                                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">Телефон</label>
                                <input type="tel" id="phone-display" value="{{ $user->phone }}" readonly
                                       class="w-full border-2 border-gray-300 bg-gray-100 p-3 rounded-xl text-gray-600 cursor-not-allowed font-mono">
                                <p class="text-xs text-gray-500 mt-1">Для изменения телефона обратитесь в поддержку</p>
                            </div>

                            <div class="flex items-center gap-4 pt-4">
                                <button type="submit" class="flex-1 bg-[#0D7D4C] text-white font-bold py-3 rounded-xl btn-animated pulse-hover">
                                    Сохранить аватар
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ПРАВАЯ КОЛОНКА: История заказов --}}
                <div class="lg:col-span-2">
                    <div class="bg-white p-6 rounded-3xl shadow-xl border-4 border-[#E8FC8C]">
                        <h3 class="text-2xl font-black text-[#422168] mb-6 border-b-4 border-[#00F3B5] pb-2">История заказов</h3>
                        
                        @if($orders->isEmpty())
                            <div class="text-center py-12 bg-[#E8FC8C]/30 rounded-2xl">
                                <p class="text-4xl mb-3">🛒</p>
                                <p class="text-[#422168] font-bold">У вас пока нет заказов.</p>
                                <a href="{{ route('catalog') }}" class="inline-block mt-4 bg-[#CAF204] text-[#422168] px-6 py-2 rounded-xl font-bold btn-animated">Перейти в каталог</a>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($orders as $order)
                                    <div class="bg-[#E8FC8C]/20 p-5 rounded-2xl border-l-8 
                                        @switch($order->status)
                                            @case('pending') border-gray-400 @break
                                            @case('paid') border-[#CAF204] @break
                                            @case('shipping') border-blue-500 @break
                                            @case('delivered') border-[#0D7D4C] @break
                                            @case('cancelled') border-red-500 @break
                                        @endswitch
                                        card-hover">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <p class="text-sm text-gray-500">Заказ №{{ $order->id }} от {{ $order->created_at->format('d.m.Y H:i') }}</p>
                                                <p class="text-2xl font-black text-[#422168] mt-1">{{ number_format($order->total_amount, 0, '.', ' ') }} ₽</p>
                                            </div>
                                            <div class="text-right">
                                                {{-- Статус заказа --}}
                                                <span class="px-4 py-1 rounded-full text-xs font-black uppercase tracking-wider inline-block
                                                    @switch($order->status)
                                                        @case('pending') bg-gray-200 text-gray-700 @break
                                                        @case('paid') bg-[#CAF204] text-[#422168] @break
                                                        @case('shipping') bg-blue-100 text-blue-700 @break
                                                        @case('delivered') bg-[#0D7D4C] text-white @break
                                                        @case('cancelled') bg-red-100 text-red-700 @break
                                                    @endswitch">
                                                    {{ $order->status_label }}
                                                </span>
                                                @if($order->status === 'pending')
                                                    <a href="{{ route('payment.show', $order) }}" class="block mt-2 bg-[#0D7D4C] text-white px-4 py-2 rounded-lg btn-animated text-xs font-bold">
                                                        Оплатить
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2 mt-4 border-t border-[#422168]/10 pt-3">
                                            @foreach($order->items as $item)
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-[#422168] font-bold">
                                                        {{ $item->product_name ?? 'Товар' }}
                                                    </span>
                                                    <span class="text-gray-600">
                                                        {{ $item->quantity }} шт. × {{ $item->price_at_moment }} ₽
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-6">
                                {{ $orders->links() }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Маска для телефона --}}
    <script src="https://cdn.jsdelivr.net/npm/imask@7.1.3/dist/imask.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const phoneInput = document.getElementById('phone-display');
        if (phoneInput && phoneInput.value) {
            const cleanPhone = phoneInput.value.replace(/\D/g, '');
            if (cleanPhone.length === 11 && cleanPhone.startsWith('7')) {
                const formatted = `+${cleanPhone[0]} (${cleanPhone.substr(1,3)}) ${cleanPhone.substr(4,3)}-${cleanPhone.substr(7,2)}-${cleanPhone.substr(9,2)}`;
                phoneInput.value = formatted;
            }
        }
    });
    </script>
@endsection