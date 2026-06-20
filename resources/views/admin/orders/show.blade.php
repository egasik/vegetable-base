@extends('admin.layouts.app')
@section('title', 'Заказ №' . $order->id)

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-4xl font-black text-[#422168]">Заказ №{{ $order->id }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="bg-gray-200 text-[#422168] font-bold py-2 px-4 rounded-xl btn-animated">
            ← К списку заказов
        </a>
    </div>


    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Основная информация --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Статус и управление --}}
            <div class="bg-white p-6 rounded-2xl shadow-xl border-4 border-[#E8FC8C]">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-black text-[#422168]">Статус заказа</h2>
                    @include('admin.orders._status_badge', ['status' => $order->status])
                </div>

                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="flex flex-col gap-3">
    @csrf
    @method('PATCH')
    
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Новый статус</label>
            <select name="status" class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none"
                    @if(empty($order->getAllowedNextStatuses())) disabled @endif>
                <option value="{{ $order->status }}" selected>{{ $order->status_label }} (текущий)</option>
                @foreach($order->getAllowedNextStatuses() as $allowedStatus)
                    <option value="{{ $allowedStatus }}">
                        @if($allowedStatus === $order->previous_status && $order->canRevert())
                            ← Откатить к «{{ match($allowedStatus) {
                                'pending' => 'Ожидает оплаты',
                                'paid' => 'Оплачен',
                                'shipping' => 'В доставке',
                                'delivered' => 'Вручен',
                                'cancelled' => 'Отменен',
                            } }}»
                        @else
                            → {{ match($allowedStatus) {
                                'pending' => 'Ожидает оплаты',
                                'paid' => 'Оплачен',
                                'shipping' => 'В доставке',
                                'delivered' => 'Вручен',
                                'cancelled' => 'Отменен',
                            } }}
                        @endif
                    </option>
                @endforeach
            </select>
            @if($order->canRevert())
                <p class="text-xs text-blue-600 mt-1 font-bold">
                    ⏱ Доступен откат в течение {{ $order->getRevertMinutesLeft() }} мин
                </p>
            @endif
        </div>
        
        <div>
            <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">
                Комментарий <span id="comment-required" class="text-red-500 hidden">*</span>
            </label>
            <input type="text" name="comment" id="comment-input" placeholder="Причина изменения..."
                   class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none">
            <p id="comment-hint" class="text-xs text-gray-500 mt-1 hidden">
                Обязательное поле при отмене заказа
            </p>
        </div>
    </div>
    
    <button type="submit" 
            class="bg-[#0D7D4C] text-white font-bold py-3 px-6 rounded-lg btn-animated hover:bg-[#422168] self-start
                   {{ empty($order->getAllowedNextStatuses()) ? 'opacity-50 cursor-not-allowed' : '' }}"
            {{ empty($order->getAllowedNextStatuses()) ? 'disabled' : '' }}>
        Изменить статус
    </button>
</form>

@if(empty($order->getAllowedNextStatuses()))
    <div class="mt-4 p-3 bg-gray-100 rounded-lg border-l-4 border-gray-400">
        <p class="text-sm text-gray-600">
            <strong>Финальный статус.</strong> Дальнейшие изменения статуса невозможны.
        </p>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.querySelector('select[name="status"]');
    const commentInput = document.getElementById('comment-input');
    const commentRequired = document.getElementById('comment-required');
    const commentHint = document.getElementById('comment-hint');
    
    function updateCommentRequirement() {
        if (statusSelect.value === 'cancelled') {
            commentInput.required = true;
            commentRequired.classList.remove('hidden');
            commentHint.classList.remove('hidden');
        } else {
            commentInput.required = false;
            commentRequired.classList.add('hidden');
            commentHint.classList.add('hidden');
        }
    }
    
    statusSelect.addEventListener('change', updateCommentRequirement);
    updateCommentRequirement();
});
</script>
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

            {{-- История статусов --}}
            <div class="bg-white p-6 rounded-2xl shadow-xl border-4 border-[#E8FC8C]">
                <h2 class="text-2xl font-black text-[#422168] mb-4">История изменений</h2>
                
                @if($order->statusHistory->isEmpty())
                    <p class="text-gray-500 text-center py-4">История пуста</p>
                @else
                    <div class="space-y-3">
                        @foreach($order->statusHistory as $history)
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

        {{-- Информация о клиенте и заказе --}}
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
                    @if($order->payment_method)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Способ оплаты:</span>
                            <span class="font-bold text-[#422168]">
                                {{ $order->payment_method === 'bank_card' ? 'Карта' : 'СБП' }}
                            </span>
                        </div>
                    @endif
                    @if($order->payment_id)
                        <div class="flex justify-between">
                            <span class="text-gray-500">ID платежа:</span>
                            <span class="font-bold text-[#422168] font-mono text-xs">{{ $order->payment_id }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection