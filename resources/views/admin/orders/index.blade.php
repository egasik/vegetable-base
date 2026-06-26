@extends('admin.layouts.app')
@section('title', 'Управление заказами')

@section('content')
    <h1 class="text-4xl font-black text-[#422168] mb-6">Управление заказами</h1>

    {{-- Статистика --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-8">
        <div class="bg-white p-4 rounded-xl border-l-4 border-[#422168] shadow">
            <p class="text-xs text-gray-500 uppercase">Всего</p>
            <p class="text-2xl font-black text-[#422168]">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-gray-400 shadow">
            <p class="text-xs text-gray-500 uppercase">Ожидают</p>
            <p class="text-2xl font-black text-gray-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-[#CAF204] shadow">
            <p class="text-xs text-gray-500 uppercase">Оплачены</p>
            <p class="text-2xl font-black text-[#422168]">{{ $stats['paid'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-blue-500 shadow">
            <p class="text-xs text-gray-500 uppercase">В доставке</p>
            <p class="text-2xl font-black text-blue-600">{{ $stats['shipping'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-[#0D7D4C] shadow">
            <p class="text-xs text-gray-500 uppercase">Вручены</p>
            <p class="text-2xl font-black text-[#0D7D4C]">{{ $stats['delivered'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-red-500 shadow">
            <p class="text-xs text-gray-500 uppercase">Отменены</p>
            <p class="text-2xl font-black text-red-600">{{ $stats['cancelled'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-[#00F3B5] shadow">
            <p class="text-xs text-gray-500 uppercase">Выручка</p>
            <p class="text-2xl font-black text-[#0D7D4C]">{{ number_format($stats['revenue'], 0, '.', ' ') }} ₽</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-red-700 shadow">
            <p class="text-xs text-gray-500 uppercase">Удалено</p>
            <p class="text-2xl font-black text-red-700">{{ $stats['trashed'] }}</p>
        </div>
    </div>

    {{-- Фильтры --}}
    <form action="{{ route('admin.orders.index') }}" method="GET" class="bg-white p-6 rounded-2xl shadow-lg mb-6 border-2 border-[#E8FC8C] grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Поиск по клиенту</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Имя, фамилия, email..."
                   class="w-full border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">№ заказа</label>
            <input type="number" name="order_id" value="{{ request('order_id') }}" placeholder="ID"
                   class="w-full border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Статус</label>
            <select name="status" class="w-full border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none">
                <option value="">Все</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Ожидает оплаты</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Оплачен</option>
                <option value="shipping" {{ request('status') === 'shipping' ? 'selected' : '' }}>В доставке</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Вручен</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Отменен</option>
            </select>
        </div>
        <div class="flex items-end">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="trashed" value="1" 
                       {{ $showTrashed ? 'checked' : '' }}
                       class="w-5 h-5 accent-[#0D7D4C]">
                <span class="text-sm font-bold text-[#0D7D4C]">Показать удалённые</span>
            </label>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-[#0D7D4C] text-white font-bold py-2 rounded-lg btn-animated hover:bg-[#422168]">
                Применить
            </button>
            <a href="{{ route('admin.orders.index') }}" class="bg-gray-200 text-[#422168] font-bold py-2 px-4 rounded-lg btn-animated">
                Сброс
            </a>
        </div>
    </form>

    @if(session('info'))
        <div class="bg-blue-100 text-blue-700 p-4 rounded-xl font-bold mb-6 border-l-8 border-blue-500">
            {{ session('info') }}
        </div>
    @endif

    {{-- Таблица заказов --}}
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border-4 border-[#E8FC8C]">
        <table class="w-full">
            <thead class="bg-[#422168] text-white">
                <tr>
                    <th class="p-4 text-left">№</th>
                    <th class="p-4 text-left">Клиент</th>
                    <th class="p-4 text-left">Товары</th>
                    <th class="p-4 text-left">Сумма</th>
                    <th class="p-4 text-left">Адрес доставки</th>
                    <th class="p-4 text-left">Статус</th>
                    <th class="p-4 text-left">Дата</th>
                    <th class="p-4 text-left">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b border-[#E8FC8C] hover:bg-[#E8FC8C]/20 transition-colors {{ $order->trashed() ? 'opacity-60 bg-red-50' : '' }}">
                        <td class="p-4 font-bold">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-[#0D7D4C] hover:underline">
                                #{{ $order->id }}
                            </a>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-[#422168]">{{ $order->user->name }} {{ $order->user->last_name }}</div>
                            <div class="text-xs text-gray-500">{{ $order->user->email }}</div>
                            <div class="text-xs text-gray-500">{{ $order->user->phone }}</div>
                        </td>
                        <td class="p-4 text-sm">
                            <div class="font-bold">{{ $order->items->count() }} поз.</div>
                            <div class="text-xs text-gray-500 truncate max-w-xs">
                                {{ $order->items->map(fn($i) => $i->product_name)->join(', ') }}
                            </div>
                        </td>
                        <td class="p-4 font-black text-[#0D7D4C] text-lg">{{ number_format($order->total_amount, 0, '.', ' ') }} ₽</td>
                        
                        {{-- Адрес доставки --}}
                        <td class="p-4 text-sm">
                            @if($order->delivery_address)
                                <div class="font-bold text-[#422168] text-xs" title="{{ $order->delivery_address }}">
                                    {{ Str::limit($order->delivery_address, 40) }}
                                </div>
                                @if($order->latitude && $order->longitude)
                                    <a href="https://yandex.ru/maps/?pt={{ $order->longitude }},{{ $order->latitude }}&z=16" 
                                       target="_blank" 
                                       class="text-xs text-[#0D7D4C] hover:underline">
                                        📍 На карте
                                    </a>
                                @endif
                            @else
                                <span class="text-xs text-gray-400 italic">Не указан</span>
                            @endif
                        </td>
                        
                        <td class="p-4">
                            @include('admin.orders._status_badge', ['status' => $order->status])
                        </td>
                        <td class="p-4 text-sm text-gray-600">
                            <div>{{ $order->created_at->format('d.m.Y') }}</div>
                            <div class="text-xs">{{ $order->created_at->format('H:i') }}</div>
                        </td>
                        <td class="p-4">
                            @if($order->trashed())
                                {{-- БЛОК ДЛЯ УДАЛЁННЫХ ЗАКАЗОВ --}}
                                <div class="text-xs text-red-600 font-bold mb-2">
                                    Удалён: {{ $order->deleted_at->format('d.m.Y H:i') }}
                                </div>
                                @if($order->delete_reason)
                                    <div class="text-xs text-gray-600 mb-2 italic">
                                        Причина: «{{ $order->delete_reason }}»
                                    </div>
                                @endif
                                
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.orders.restore', $order->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-[#0D7D4C] text-white px-3 py-1 rounded-lg text-xs font-bold hover:bg-[#422168]">
                                            Восстановить
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.orders.force-delete', $order->id) }}" method="POST" 
                                          onsubmit="return confirm('Удалить заказ безвозвратно? Это действие нельзя отменить!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-bold hover:bg-red-800">
                                            Удалить навсегда
                                        </button>
                                    </form>
                                </div>
                            @else
                                {{-- БЛОК ДЛЯ АКТИВНЫХ ЗАКАЗОВ --}}
                                <form action="{{ route('admin.orders.update-status', $order) }}" 
                                      method="POST" 
                                      enctype="multipart/form-data"
                                      class="space-y-2"
                                      id="status-form-{{ $order->id }}">
                                    @csrf
                                    @method('PATCH')
                                    
                                    {{-- Выбор статуса --}}
                                    <div>
                                        <select name="status" 
                                                id="status-select-{{ $order->id }}"
                                                onchange="togglePhotoField({{ $order->id }}, this.value)"
                                                class="w-full border-2 border-[#E8FC8C] rounded-lg px-2 py-1 text-sm focus:outline-none focus:border-[#CAF204]"
                                                @if(empty($order->getAllowedNextStatuses())) disabled @endif>
                                            <option value="{{ $order->status }}" selected>{{ $order->status_label }}</option>
                                            @foreach($order->getAllowedNextStatuses() as $allowedStatus)
                                                <option value="{{ $allowedStatus }}">
                                                    @if($allowedStatus === $order->previous_status && $order->canRevert())
                                                        ← Откатить ({{ $order->getRevertMinutesLeft() }} мин)
                                                    @else
                                                        → {{ match($allowedStatus) {
                                                            'pending' => 'Ожидает',
                                                            'paid' => 'Оплачен',
                                                            'shipping' => 'В доставке',
                                                            'delivered' => 'Вручен',
                                                            'cancelled' => 'Отменен',
                                                        } }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Поле для фото (показывается только для статуса "delivered") --}}
                                    <div id="photo-field-{{ $order->id }}" class="hidden">
                                        <label class="block text-xs font-bold text-[#0D7D4C] mb-1">
                                            📷 Фото подтверждения доставки <span class="text-red-500">*</span>
                                        </label>
                                        <input type="file" 
                                               name="photo" 
                                               accept="image/*"
                                               id="photo-input-{{ $order->id }}"
                                               class="w-full text-xs border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none">
                                        <p class="text-xs text-gray-500 mt-1">
                                            Фото необходимо для подтверждения факта доставки
                                        </p>
                                    </div>

                                    {{-- Комментарий --}}
                                    <div>
                                        <textarea name="comment" 
                                                  placeholder="Комментарий (необязательно)"
                                                  class="w-full text-xs border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none"
                                                  rows="2"></textarea>
                                    </div>

                                    <button type="submit" 
                                            class="w-full bg-[#0D7D4C] text-white px-3 py-1 rounded-lg text-sm font-bold hover:bg-[#422168] transition-colors
                                                   {{ empty($order->getAllowedNextStatuses()) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            {{ empty($order->getAllowedNextStatuses()) ? 'disabled' : '' }}>
                                        Применить
                                    </button>
                                </form>
                                
                                @if($order->canRevert())
                                    <div class="mt-2 text-xs text-blue-600 font-bold">
                                        ⏱ Можно откатить: {{ $order->getRevertMinutesLeft() }} мин
                                    </div>
                                @endif

                                {{-- Фото доставки (если есть) --}}
                                @if($order->deliveryPhotos && $order->deliveryPhotos->count() > 0)
                                    <div class="mt-3 pt-3 border-t border-[#E8FC8C]">
                                        <p class="text-xs font-bold text-[#0D7D4C] mb-2">📸 Фото доставки:</p>
                                        <div class="flex gap-2 flex-wrap">
                                            @foreach($order->deliveryPhotos as $photo)
                                                <a href="{{ asset('storage/' . $photo->file_path) }}" 
                                                   target="_blank" 
                                                   class="block group relative"
                                                   title="{{ $photo->comment ?? 'Без комментария' }} — {{ $photo->created_at->format('d.m.Y H:i') }}">
                                                    <img src="{{ asset('storage/' . $photo->file_path) }}" 
                                                         class="w-16 h-16 object-cover rounded-lg border-2 border-[#E8FC8C] group-hover:border-[#CAF204] transition-colors">
                                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 rounded-lg transition-colors flex items-center justify-center">
                                                        <span class="text-white opacity-0 group-hover:opacity-100 text-xl">🔍</span>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="flex gap-3 mt-2">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-xs text-[#422168] hover:text-[#0D7D4C] font-bold">
                                        Подробнее →
                                    </a>
                                    <button type="button" onclick="document.getElementById('delete-modal-{{ $order->id }}').classList.remove('hidden')" 
                                            class="text-xs text-red-500 hover:text-red-700 font-bold">
                                        Удалить
                                    </button>
                                </div>

                                {{-- Модальное окно подтверждения удаления --}}
                                <div id="delete-modal-{{ $order->id }}" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
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
                                                        onclick="document.getElementById('delete-modal-{{ $order->id }}').classList.add('hidden')"
                                                        class="flex-1 bg-gray-200 text-[#422168] font-bold py-2 rounded-lg hover:bg-gray-300">
                                                    Отмена
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-gray-500">
                            <p class="text-4xl mb-2">📋</p>
                            <p class="font-bold">Заказы не найдены</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>

    {{-- JavaScript для управления полем фото --}}
    <script>
    function togglePhotoField(orderId, status) {
        const photoField = document.getElementById('photo-field-' + orderId);
        const photoInput = document.getElementById('photo-input-' + orderId);
        
        if (status === 'delivered') {
            photoField.classList.remove('hidden');
            photoInput.required = true;
        } else {
            photoField.classList.add('hidden');
            photoInput.required = false;
            photoInput.value = ''; // Очищаем выбор файла
        }
    }

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($orders as $order)
            @if(!$order->trashed())
                togglePhotoField({{ $order->id }}, '{{ $order->status }}');
            @endif
        @endforeach
    });
    </script>
@endsection