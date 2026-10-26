@extends('admin.layouts.app')
@section('title', 'Управление заказами')

@section('content')
    <h1 class="text-4xl font-semibold text-[#2D3528] mb-6">Управление заказами</h1>

    {{-- Статистика --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-8">
        <div class="bg-white p-4 rounded-xl border-l-4 border-[#2D3528] shadow">
            <p class="text-xs text-gray-500 uppercase">Всего</p>
            <p class="text-2xl font-semibold text-[#2D3528]">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-gray-400 shadow">
            <p class="text-xs text-gray-500 uppercase">Ожидают</p>
            <p class="text-2xl font-semibold text-gray-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-[#A2AB73] shadow">
            <p class="text-xs text-gray-500 uppercase">Оплачены</p>
            <p class="text-2xl font-semibold text-[#2D3528]">{{ $stats['paid'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-blue-500 shadow">
            <p class="text-xs text-gray-500 uppercase">В доставке</p>
            <p class="text-2xl font-semibold text-blue-600">{{ $stats['shipping'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-[#CC3A63] shadow">
            <p class="text-xs text-gray-500 uppercase">Вручены</p>
            <p class="text-2xl font-semibold text-[#CC3A63]">{{ $stats['delivered'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-red-500 shadow">
            <p class="text-xs text-gray-500 uppercase">Отменены</p>
            <p class="text-2xl font-semibold text-red-600">{{ $stats['cancelled'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-[#F9F0E0] shadow">
            <p class="text-xs text-gray-500 uppercase">Выручка</p>
            <p class="text-2xl font-semibold text-[#CC3A63]">{{ number_format($stats['revenue'], 0, '.', ' ') }} ₽</p>
        </div>
        <div class="bg-white p-4 rounded-xl border-l-4 border-red-700 shadow">
            <p class="text-xs text-gray-500 uppercase">Удалено</p>
            <p class="text-2xl font-semibold text-red-700">{{ $stats['trashed'] }}</p>
        </div>
    </div>

    {{-- Фильтры --}}
    <form action="{{ route('admin.orders.index') }}" method="GET" class="bg-white p-6 rounded-2xl shadow-lg mb-6 border-2 border-[#F9F0E0] grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-bold mb-1 text-[#CC3A63]">Поиск по клиенту</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Имя, фамилия, email..."
                   class="w-full border-2 border-[#F9F0E0] p-2 rounded-lg focus:border-[#A2AB73] focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-bold mb-1 text-[#CC3A63]">№ заказа</label>
            <input type="number" name="order_id" value="{{ request('order_id') }}" placeholder="ID"
                   class="w-full border-2 border-[#F9F0E0] p-2 rounded-lg focus:border-[#A2AB73] focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-bold mb-1 text-[#CC3A63]">Статус</label>
            <select name="status" class="w-full border-2 border-[#F9F0E0] p-2 rounded-lg focus:border-[#A2AB73] focus:outline-none">
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
                       class="w-5 h-5 accent-[#CC3A63]">
                <span class="text-sm font-bold text-[#CC3A63]">Показать удалённые</span>
            </label>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-[#CC3A63] text-white font-bold py-2 rounded-lg btn-animated hover:bg-[#2D3528]">
                Применить
            </button>
            <a href="{{ route('admin.orders.index') }}" class="bg-gray-200 text-[#2D3528] font-bold py-2 px-4 rounded-lg btn-animated">
                Сброс
            </a>
        </div>
    </form>


    @if(session('info'))
        <div class="bg-blue-100 text-blue-700 p-4 rounded-xl font-bold mb-6 border-l-2 border-blue-500">
            {{ session('info') }}
        </div>
    @endif

    {{-- Таблица заказов --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-[#F9F0E0]">
        <table class="w-full">
            <thead class="bg-[#2D3528] text-white">
                <tr>
                    <th class="p-4 text-left">№</th>
                    <th class="p-4 text-left">Клиент</th>
                    <th class="p-4 text-left">Товары</th>
                    <th class="p-4 text-left">Сумма</th>
                    <th class="p-4 text-left">Статус</th>
                    <th class="p-4 text-left">Дата</th>
                    <th class="p-4 text-left">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b border-[#F9F0E0] hover:bg-[#F9F0E0]/20 transition-colors {{ $order->trashed() ? 'opacity-60 bg-red-50' : '' }}">
                        <td class="p-4 font-bold">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-[#CC3A63] hover:underline">
                                #{{ $order->id }}
                            </a>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-[#2D3528]">{{ $order->user->name }} {{ $order->user->last_name }}</div>
                            <div class="text-xs text-gray-500">{{ $order->user->email }}</div>
                            <div class="text-xs text-gray-500">{{ $order->user->phone }}</div>
                        </td>
                        <td class="p-4 text-sm">
                            <div class="font-bold">{{ $order->items->count() }} поз.</div>
                            <div class="text-xs text-gray-500 truncate max-w-xs">
                                {{ $order->items->map(fn($i) => $i->product_name)->join(', ') }}
                            </div>
                        </td>
                        <td class="p-4 font-semibold text-[#CC3A63] text-lg">{{ number_format($order->total_amount, 2, ',', ' ') }} ₽</td>
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
                                        <button type="submit" class="bg-[#CC3A63] text-white px-3 py-1 rounded-lg text-xs font-bold hover:bg-[#2D3528]">
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
                                <div class="flex gap-3 mt-2">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="button button-small">
                                        Подробнее →
                                    </a>
                                    <button type="button" onclick="document.getElementById('delete-modal-{{ $order->id }}').classList.remove('hidden')" 
                                            class="text-xs text-red-500 hover:text-red-700 font-bold">
                                        Удалить
                                    </button>
                                </div>

                                {{-- Модальное окно подтверждения удаления --}}
                                <div id="delete-modal-{{ $order->id }}" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                                    <div class="bg-white p-6 rounded-2xl shadow-sm max-w-md w-full mx-4 border border-red-500">
                                        <h3 class="text-xl font-semibold text-[#2D3528] mb-4">Удаление заказа №{{ $order->id }}</h3>
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
                                                        class="flex-1 bg-gray-200 text-[#2D3528] font-bold py-2 rounded-lg hover:bg-gray-300">
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
                        <td colspan="7" class="p-12 text-center text-gray-500">
                            <p class="text-4xl mb-2">📋</p>
                            <p class="font-bold">Заказы не найдены</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
