{{-- Единая форма смены статуса с фотофиксацией --}}
<form action="{{ route('admin.orders.update-status', $order) }}" 
      method="POST" 
      enctype="multipart/form-data"
      class="space-y-3"
      id="status-form-{{ $order->id }}">
    @csrf
    @method('PATCH')
    
    {{-- Выбор нового статуса --}}
    <div>
        <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Новый статус</label>
        <select name="status" 
                id="status-select-{{ $order->id }}"
                onchange="handleStatusChange({{ $order->id }}, this.value)"
                class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none"
                @if(empty($order->getAllowedNextStatuses())) disabled @endif>
            <option value="{{ $order->status }}" selected>
                {{ $order->status_label }} (текущий)
            </option>
            @foreach($order->getAllowedNextStatuses() as $allowedStatus)
                <option value="{{ $allowedStatus }}">
                    @if($allowedStatus === $order->previous_status && $order->canRevert())
                        ← Откатить на «{{ match($allowedStatus) {
                            'pending' => 'Ожидает оплаты',
                            'paid' => 'Оплачен',
                            'shipping' => 'В доставке',
                            'delivered' => 'Вручен',
                            'cancelled' => 'Отменен',
                        } }}» ({{ $order->getRevertMinutesLeft() }} мин)
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

    {{-- Поле для фото (показывается только для статуса "delivered") --}}
    <div id="photo-field-{{ $order->id }}" class="hidden">
        <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">
            📷 Фото подтверждения доставки <span class="text-red-500">*</span>
        </label>
        <input type="file" 
               name="photo" 
               accept="image/*"
               id="photo-input-{{ $order->id }}"
               class="w-full text-sm border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none">
        <p class="text-xs text-gray-500 mt-1">
            Фото необходимо для подтверждения факта доставки
        </p>
    </div>

    {{-- Комментарий --}}
    <div>
        <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">
            Комментарий <span id="comment-required-{{ $order->id }}" class="text-red-500 hidden">*</span>
        </label>
        <textarea name="comment" 
                  id="comment-input-{{ $order->id }}"
                  placeholder="Причина изменения (необязательно)"
                  class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none"
                  rows="2"></textarea>
        <p id="comment-hint-{{ $order->id }}" class="text-xs text-gray-500 mt-1 hidden">
            Обязательное поле при отмене заказа
        </p>
    </div>

    <button type="submit" 
            id="submit-btn-{{ $order->id }}"
            class="bg-[#0D7D4C] text-white font-bold py-3 px-6 rounded-lg btn-animated hover:bg-[#422168]
                   {{ empty($order->getAllowedNextStatuses()) ? 'opacity-50 cursor-not-allowed' : '' }}"
            {{ empty($order->getAllowedNextStatuses()) ? 'disabled' : '' }}>
        Изменить статус
    </button>
</form>

<script>
function handleStatusChange(orderId, newStatus) {
    // Управление полем фото
    const photoField = document.getElementById('photo-field-' + orderId);
    const photoInput = document.getElementById('photo-input-' + orderId);
    
    if (newStatus === 'delivered') {
        photoField.classList.remove('hidden');
        photoInput.required = true;
    } else {
        photoField.classList.add('hidden');
        photoInput.required = false;
        photoInput.value = '';
    }
    
    // Управление обязательностью комментария
    const commentInput = document.getElementById('comment-input-' + orderId);
    const commentRequired = document.getElementById('comment-required-' + orderId);
    const commentHint = document.getElementById('comment-hint-' + orderId);
    
    if (newStatus === 'cancelled') {
        commentInput.required = true;
        commentRequired.classList.remove('hidden');
        commentHint.classList.remove('hidden');
    } else {
        commentInput.required = false;
        commentRequired.classList.add('hidden');
        commentHint.classList.add('hidden');
    }
}

// Блокировка поля фото если статус уже "delivered"
function lockPhotoFieldIfDelivered(orderId, currentStatus) {
    if (currentStatus === 'delivered') {
        const photoField = document.getElementById('photo-field-' + orderId);
        const photoInput = document.getElementById('photo-input-' + orderId);
        const submitBtn = document.getElementById('submit-btn-' + orderId);
        
        if (photoField && photoInput && submitBtn) {
            photoField.classList.remove('hidden');
            photoInput.required = false;
            photoInput.disabled = true;
            photoInput.classList.add('opacity-50', 'cursor-not-allowed');
            
            // Блокируем кнопку отправки
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            submitBtn.textContent = 'Статус уже подтверждён';
        }
    }
}

// Инициализация при загрузке
document.addEventListener('DOMContentLoaded', function() {
    handleStatusChange({{ $order->id }}, '{{ $order->status }}');
    lockPhotoFieldIfDelivered({{ $order->id }}, '{{ $order->status }}');
});
</script>