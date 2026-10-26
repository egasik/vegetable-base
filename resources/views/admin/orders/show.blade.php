@extends('admin.layouts.app')
@section('title', 'Заказ №' . $order->id)

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-4xl font-semibold text-[#2D3528]">Заказ №{{ $order->id }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="bg-gray-200 text-[#2D3528] font-bold py-2 px-4 rounded-xl btn-animated">
            ← К списку заказов
        </a>
    </div>


    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Основная информация --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Статус и управление --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#F9F0E0]">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-semibold text-[#2D3528]">Статус заказа</h2>
                    @include('admin.orders._status_badge', ['status' => $order->status])
                </div>

                @if($order->trashed())
                    <div class="notice">Заказ удалён. Для обработки восстановите его в списке удалённых заказов.</div>
                @endif
                <fieldset @disabled($order->trashed())>
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
    @csrf
    @method('PATCH')
<label id="delivery-photo-field" hidden>Фото вручения *<input type="file" name="delivery_photo" accept="image/jpeg,image/png,image/webp"><span class="admin-help">JPEG, PNG или WebP, до 5 МБ. После ошибки выберите файл заново.</span></label>
    
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-bold mb-1 text-[#CC3A63]">Новый статус</label>
            <select name="status" class="w-full border-2 border-[#F9F0E0] p-3 rounded-lg focus:border-[#A2AB73] focus:outline-none"
                    @if(empty($order->getAllowedNextStatuses())) disabled @endif>
                <option value="" disabled selected>Выберите действие</option>
                @foreach($order->getAllowedNextStatuses() as $allowedStatus)
                    <option value="{{ $allowedStatus }}" @selected(old('status') === $allowedStatus)>
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
            <label class="block text-sm font-bold mb-1 text-[#CC3A63]">
                Комментарий <span id="comment-required" class="text-red-500 hidden">*</span>
            </label>
            <input type="text" name="comment" id="comment-input" value="{{ old('comment') }}" maxlength="500" placeholder="Причина изменения..."
                   class="w-full border-2 border-[#F9F0E0] p-3 rounded-lg focus:border-[#A2AB73] focus:outline-none">
            <p id="comment-hint" class="text-xs text-gray-500 mt-1 hidden">
                Обязательное поле при отмене заказа
            </p>
        </div>
    </div>
    
    <button type="submit" 
            class="bg-[#CC3A63] text-white font-bold py-3 px-6 rounded-lg btn-animated hover:bg-[#2D3528] self-start
                   {{ empty($order->getAllowedNextStatuses()) ? 'opacity-50 cursor-not-allowed' : '' }}"
            {{ empty($order->getAllowedNextStatuses()) ? 'disabled' : '' }}>
        Изменить статус
    </button>
</form>
</fieldset>

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
        const photo = document.querySelector('[name="delivery_photo"]');
        const delivered = statusSelect.value === 'delivered';
        document.getElementById('delivery-photo-field').hidden = !delivered;
        photo.required = delivered;
        photo.disabled = !delivered;
        statusSelect.form.querySelector('button[type="submit"]').disabled = !statusSelect.value || statusSelect.disabled;
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
    statusSelect.form.addEventListener('submit', () => {
        const button = statusSelect.form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Сохраняем…';
    });
});
</script>
            </div>

            {{-- Товары заказа --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#F9F0E0]">
                <h2 class="text-2xl font-semibold text-[#2D3528] mb-4">Товары в заказе</h2>
                <div class="space-y-3">
                    @foreach($order->items as $item)
                        <div class="flex justify-between items-center p-4 bg-[#F9F0E0]/20 rounded-xl border-l-4 border-[#A2AB73]">
                            <div>
                                <p class="font-bold text-[#2D3528] text-lg">{{ $item->product_name }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ (float) $item->quantity }} {{ $item->unit ?? 'ед. (старый заказ)' }} × {{ $item->price_at_moment }} ₽
                                </p>
                            </div>
                            <p class="text-xl font-semibold text-[#CC3A63]">{{ number_format($item->line_total ?? $item->quantity * $item->price_at_moment, 2, ',', ' ') }} ₽</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t-4 border-[#A2AB73] flex justify-between items-center">
                    <span class="text-xl font-bold text-[#2D3528]">Итого:</span>
                    <span class="text-3xl font-semibold text-[#CC3A63]">{{ number_format($order->total_amount, 2, ',', ' ') }} ₽</span>
                </div>
            </div>

            {{-- История статусов --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#F9F0E0]">
                <h2 class="text-2xl font-semibold text-[#2D3528] mb-4">История изменений</h2>
                
                @if($order->statusHistory->isEmpty())
                    <p class="text-gray-500 text-center py-4">История пуста</p>
                @else
                    <div class="space-y-3">
                        @foreach($order->statusHistory as $history)
                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg border-l-4 border-[#2D3528]">
                                <div class="text-xs text-gray-500 min-w-[100px]">
                                    <div>{{ $history->created_at->format('d.m.Y') }}</div>
                                    <div>{{ $history->created_at->format('H:i') }}</div>
                                </div>
                                <div class="flex-1">
                                    @if($history->old_status !== $history->new_status)
                                        <p class="font-bold text-[#2D3528]">
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
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#F9F0E0]">
                <h3 class="text-xl font-semibold text-[#2D3528] mb-4">Клиент</h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <p class="text-gray-500 text-xs">Имя</p>
                        <p class="font-bold text-[#2D3528]">{{ $order->user->name }} {{ $order->user->last_name }}</p>
                    </div>
                    @if($order->user->middle_name)
                        <div>
                            <p class="text-gray-500 text-xs">Отчество</p>
                            <p class="font-bold text-[#2D3528]">{{ $order->user->middle_name }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-gray-500 text-xs">Email</p>
                        <p class="font-bold text-[#2D3528]">{{ $order->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Телефон</p>
                        <p class="font-bold text-[#2D3528] font-mono">{{ $order->user->phone }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#F9F0E0]">
                <h3 class="text-xl font-semibold text-[#2D3528] mb-4">Детали заказа</h3>
                @if($order->delivery_address)
                    <p><strong>Адрес:</strong> {{ $order->delivery_address }}</p>
                    <p>{{ $order->delivery_details }}</p>
                    <p><strong>Телефон доставки:</strong> {{ $order->contact_phone }}</p>
                    <p class="text-sm">Координаты: {{ $order->latitude }}, {{ $order->longitude }}</p>
                @endif
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Создан:</span>
                        <span class="font-bold text-[#2D3528]">{{ $order->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if($order->paid_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Оплачен:</span>
                            <span class="font-bold text-[#CC3A63]">{{ $order->paid_at->format('d.m.Y H:i') }}</span>
                        </div>
                    @endif
                    @if($order->payment_method)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Способ оплаты:</span>
                            <span class="font-bold text-[#2D3528]">
                                {{ $order->payment_method === 'bank_card' ? 'Карта' : 'СБП' }}
                            </span>
                        </div>
                    @endif
                    @if($order->payment_id)
                        <div class="flex justify-between">
                            <span class="text-gray-500">ID платежа:</span>
                            <span class="font-bold text-[#2D3528] font-mono text-xs">{{ $order->payment_id }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@foreach($order->deliveryPhotos as $photo)<p><a href="{{ route('delivery-photos.show', $photo) }}" target="_blank" rel="noopener">Фото вручения №{{ $photo->id }}</a></p>@endforeach
@endsection

