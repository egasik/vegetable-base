<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-3xl text-[#422168]">👤 Личный кабинет</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Уведомления --}}
            @if (session('success'))
                <div class="bg-[#00F3B5] text-[#422168] p-4 rounded-xl font-bold border-l-8 border-[#0D7D4C] shadow-lg">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 text-red-700 p-4 rounded-xl font-bold border-l-8 border-red-500 shadow-lg">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- ЛЕВАЯ КОЛОНКА: Профиль --}}
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-3xl shadow-xl border-4 border-[#E8FC8C]">
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

                            {{-- Имя --}}
                            <div>
                                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">Имя</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl focus:border-[#CAF204] focus:outline-none transition-colors">
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl focus:border-[#CAF204] focus:outline-none transition-colors">
                                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Данные карты --}}
                            <div>
                                <label class="block text-sm font-bold text-[#0D7D4C] mb-1">💳 Данные карты (шифруются)</label>
                                <input type="text" name="card_data" value="{{ old('card_data', $decrypted_card) }}" placeholder="0000 0000 0000 0000"
                                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl focus:border-[#CAF204] focus:outline-none transition-colors font-mono">
                                <p class="text-xs text-gray-500 mt-1">Данные защищены шифрованием Laravel.</p>
                                @error('card_data') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex items-center gap-4 pt-4">
                                <button type="submit" class="flex-1 bg-[#0D7D4C] text-white font-bold py-3 rounded-xl btn-animated pulse-hover">
                                    💾 Сохранить изменения
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ПРАВАЯ КОЛОНКА: История заказов --}}
                <div class="lg:col-span-2">
                    <div class="bg-white p-6 rounded-3xl shadow-xl border-4 border-[#E8FC8C]">
                        <h3 class="text-2xl font-black text-[#422168] mb-6 border-b-4 border-[#00F3B5] pb-2">📦 История заказов</h3>
                        
                        @if($orders->isEmpty())
                            <div class="text-center py-12 bg-[#E8FC8C]/30 rounded-2xl">
                                <p class="text-4xl mb-3">🛒</p>
                                <p class="text-[#422168] font-bold">У вас пока нет заказов.</p>
                                <a href="{{ route('catalog') }}" class="inline-block mt-4 bg-[#CAF204] text-[#422168] px-6 py-2 rounded-xl font-bold btn-animated">Перейти в каталог</a>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($orders as $order)
                                    <div class="bg-[#E8FC8C]/20 p-5 rounded-2xl border-l-8 {{ $order->status === 'completed' ? 'border-[#0D7D4C]' : 'border-[#CAF204]' }} card-hover">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <p class="text-sm text-gray-500">Заказ №{{ $order->id }} от {{ $order->created_at->format('d.m.Y H:i') }}</p>
                                                <p class="text-2xl font-black text-[#422168] mt-1">{{ $order->total_amount }} ₽</p>
                                            </div>
                                            <span class="px-4 py-1 rounded-full text-xs font-black uppercase tracking-wider 
                                                {{ $order->status === 'completed' ? 'bg-[#00F3B5] text-[#422168]' : 'bg-[#CAF204] text-[#422168]' }}">
                                                {{ $order->status === 'completed' ? 'Выполнен' : 'В обработке' }}
                                            </span>
                                        </div>
                                        
                                        <div class="space-y-2 mt-4 border-t border-[#422168]/10 pt-3">
                                            @foreach($order->items as $item)
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-[#422168] font-bold">{{ $item->product->name }}</span>
                                                    <span class="text-gray-600">{{ $item->quantity }} шт. × {{ $item->price_at_moment }} ₽</span>
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
</x-app-layout>