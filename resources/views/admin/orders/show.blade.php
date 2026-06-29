@extends('admin.layouts.app')
@section('title', 'Заказ №' . $order->id)

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-4xl font-black text-[#422168]">Заказ №{{ $order->id }}</h1>
        
    </div>

    @if(session('success'))
        <div class="bg-[#00F3B5] text-[#422168] p-4 rounded-xl font-bold mb-6 border-l-8 border-[#0D7D4C]">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-4 rounded-xl font-bold mb-6 border-l-8 border-red-500">
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-100 text-blue-700 p-4 rounded-xl font-bold mb-6 border-l-8 border-blue-500">
            {{ session('info') }}
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Основная информация --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Статус и управление --}}
<div class="bg-white p-6 rounded-2xl shadow-xl border-4 border-[#E8FC8C]">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-black text-[#422168]">Статус заказа</h2>
        @include('admin.orders._status_badge', ['status' => $order->status])
    </div>

    @if($order->previous_status)
        <div class="mb-4 p-3 bg-gray-50 rounded-lg border-l-4 border-gray-400">
            <p class="text-xs text-gray-600">
                Предыдущий статус: 
                <strong>{{ match($order->previous_status) {
                    'pending' => 'Ожидает оплаты',
                    'paid' => 'Оплачен',
                    'shipping' => 'В доставке',
                    'delivered' => 'Вручен',
                    'cancelled' => 'Отменен',
                } }}</strong>
                @if($order->status_changed_at)
                    <span class="text-gray-400">(изменён {{ $order->status_changed_at->format('d.m.Y H:i') }})</span>
                @endif
            </p>
        </div>
    @endif

    {{-- ЕДИНАЯ форма смены статуса (с фотофиксацией) --}}
    @include('admin.orders._status_form', ['order' => $order])

    @if(empty($order->getAllowedNextStatuses()))
        <div class="mt-4 p-3 bg-gray-100 rounded-lg border-l-4 border-gray-400">
            <p class="text-sm text-gray-600">
                <strong>Финальный статус.</strong> Дальнейшие изменения статуса невозможны.
            </p>
        </div>
    @endif
</div>

            {{-- Адрес доставки --}}
            <div class="bg-white p-6 rounded-2xl shadow-xl border-4 border-[#E8FC8C]">
                <h2 class="text-2xl font-black text-[#422168] mb-4"> Адрес доставки</h2>
                @if($order->delivery_address)
                    <div class="p-4 bg-[#E8FC8C]/20 rounded-xl border-l-4 border-[#CAF204]">
                        <p class="font-bold text-[#422168] text-lg">{{ $order->delivery_address }}</p>
                        @if($order->delivery_city)
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $order->delivery_city }}, {{ $order->delivery_region ?? 'Иркутская область' }}
                            </p>
                        @endif
                        @if($order->latitude && $order->longitude)
                            <a href="https://yandex.ru/maps/?pt={{ $order->longitude }},{{ $order->latitude }}&z=16" 
                               target="_blank" 
                               class="inline-block mt-3 text-sm text-[#0D7D4C] hover:text-[#422168] font-bold underline">
                                 Открыть на Яндекс.Картах
                            </a>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500 italic">Адрес не указан</p>
                @endif
            </div>

            {{-- Товары заказа --}}
            <div class="bg-white p-6 rounded-2xl shadow-xl border-4 border-[#E8FC8C]">
                <h2 class="text-2xl font-black text-[#422168] mb-4">Товары в заказе</h2>
                <div class="space-y-3">
                    @foreach($order->items as $item)
                        <div class="flex justify-between items-center p-4 bg-[#E8FC8C]/20 rounded-xl border-l-4 border-[#CAF204]">
                            <div>
                                <p class="font-bold text-[#422168] text-lg">{{ $item->product_name }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ $item->quantity }} шт. × {{ $item->price_at_moment }} ₽
                                </p>
                            </div>
                            <p class="text-xl font-black text-[#0D7D4C]">{{ $item->quantity * $item->price_at_moment }} ₽</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t-4 border-[#CAF204] flex justify-between items-center">
                    <span class="text-xl font-bold text-[#422168]">Итого:</span>
                    <span class="text-3xl font-black text-[#0D7D4C]">{{ number_format($order->total_amount, 0, '.', ' ') }} ₽</span>
                </div>
            </div>

            {{-- Фото подтверждения доставки --}}
            @if($order->deliveryPhotos && $order->deliveryPhotos->count() > 0)
                <div class="bg-white p-6 rounded-2xl shadow-xl border-4 border-[#E8FC8C]">
                    <h2 class="text-2xl font-black text-[#422168] mb-4">
                         Фото подтверждения доставки ({{ $order->deliveryPhotos->count() }})
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($order->deliveryPhotos as $photo)
                            <a href="{{ asset('storage/' . $photo->file_path) }}" 
                               target="_blank" 
                               class="block group relative">
                                <img src="{{ asset('storage/' . $photo->file_path) }}" 
                                     class="w-full h-48 object-cover rounded-xl border-4 border-[#E8FC8C] group-hover:border-[#CAF204] transition-colors">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 rounded-xl transition-colors flex items-center justify-center">
                                    <span class="text-white opacity-0 group-hover:opacity-100 text-4xl">🔍</span>
                                </div>
                                <div class="mt-2 text-xs text-gray-600">
                                    <div>{{ $photo->created_at->format('d.m.Y H:i') }}</div>
                                    @if($photo->comment)
                                        <div class="italic text-gray-500 mt-1">«{{ $photo->comment }}»</div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- История статусов --}}
            <div class="bg-white p-6 rounded-2xl shadow-xl border-4 border-[#E8FC8C]">
                <h2 class="text-2xl font-black text-[#422168] mb-4"> История изменений</h2>
                
                @if($order->statusHistory->isEmpty())
                    <p class="text-gray-500 text-center py-4">История пуста</p>
                @else
                    <div class="space-y-3">
                        @foreach($order->statusHistory->reverse() as $history)
                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg border-l-4 border-[#422168]">
                                <div class="text-xs text-gray-500 min-w-[100px]">
                                    <div>{{ $history->created_at->format('d.m.Y') }}</div>
                                    <div>{{ $history->created_at->format('H:i') }}</div>
                                </div>
                                <div class="flex-1">
                                    @if($history->old_status !== $history->new_status)
                                        <p class="font-bold text-[#422168]">
                                            @include('admin.orders._status_badge', ['status' => $history->old_status])
                                            <span class="mx-2">→</span>
                                            @include('admin.orders._status_badge', ['status' => $history->new_status])
                                        </p>
                                    @endif
                                    @if($history->comment)
                                        <p class="text-sm text-gray-600 mt-1 italic">«{{ $history->comment }}»</p>
                                    @endif
                                    @if($history->changedBy)
                                        <p class="text-xs text-gray-500 mt-1">
                                            Изменил: {{ $history->changedBy->name }} {{ $history->changedBy->last_name }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Правая колонка: Информация о клиенте и заказе --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-xl border-4 border-[#E8FC8C]">
                <h3 class="text-xl font-black text-[#422168] mb-4">Клиент</h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <p class="text-gray-500 text-xs">Имя</p>
                        <p class="font-bold text-[#422168]">{{ $order->user->name }} {{ $order->user->last_name }}</p>
                    </div>
                    @if($order->user->middle_name)
                        <div>
                            <p class="text-gray-500 text-xs">Отчество</p>
                            <p class="font-bold text-[#422168]">{{ $order->user->middle_name }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-gray-500 text-xs">Email</p>
                        <p class="font-bold text-[#422168]">{{ $order->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Телефон</p>
                        <p class="font-bold text-[#422168] font-mono">{{ $order->user->phone }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl border-4 border-[#E8FC8C]">
                <h3 class="text-xl font-black text-[#422168] mb-4">Детали заказа</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Создан:</span>
                        <span class="font-bold text-[#422168]">{{ $order->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if($order->paid_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Оплачен:</span>
                            <span class="font-bold text-[#0D7D4C]">{{ $order->paid_at->format('d.m.Y H:i') }}</span>
                        </div>
                    @endif
                    @if($order->delivered_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Доставлен:</span>
                            <span class="font-bold text-[#0D7D4C]">{{ $order->delivered_at->format('d.m.Y H:i') }}</span>
                        </div>
                    @endif
                    @if($order->payment_id)
                        <div class="flex justify-between">
                            <span class="text-gray-500">ID платежа:</span>
                            <span class="font-bold text-[#422168] font-mono text-xs break-all">{{ $order->payment_id }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Быстрые действия --}}
            <div class="bg-white p-6 rounded-2xl shadow-xl border-4 border-[#E8FC8C]">
                <h3 class="text-xl font-black text-[#422168] mb-4">Действия</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.orders.index') }}" 
                       class="block w-full bg-gray-200 text-[#422168] font-bold py-2 px-4 rounded-lg text-center btn-animated">
                        ← К списку заказов
                    </a>
                    <button type="button" 
                            onclick="document.getElementById('delete-modal').classList.remove('hidden')" 
                            class="block w-full bg-red-500 text-white font-bold py-2 px-4 rounded-lg text-center btn-animated hover:bg-red-600">
                        🗑 Удалить заказ
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Модальное окно удаления --}}
    <div id="delete-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-2xl shadow-2xl max-w-md w-full mx-4 border-4 border-red-500">
            <h3 class="text-xl font-black text-[#422168] mb-4">Удаление заказа №{{ $order->id }}</h3>
            <p class="text-sm text-gray-600 mb-4">
                Заказ будет перемещён в корзину удалённых. Его можно будет восстановить в любой момент.
            </p>
            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-1 text-red-600">
                        Причина удаления <span class="text-red-500">*</span>
                    </label>
                    <textarea name="delete_reason" required rows="3" 
                              placeholder="Укажите причину удаления заказа..."
                              class="w-full border-2 border-red-300 p-2 rounded-lg focus:border-red-500 focus:outline-none"></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-red-600 text-white font-bold py-2 rounded-lg hover:bg-red-800">
                        Удалить
                    </button>
                    <button type="button" 
                            onclick="document.getElementById('delete-modal').classList.add('hidden')"
                            class="flex-1 bg-gray-200 text-[#422168] font-bold py-2 rounded-lg hover:bg-gray-300">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection