<span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider inline-block
    @switch($status)
        @case('pending') bg-gray-200 text-gray-700 @break
        @case('paid') bg-[#A2AB73] text-[#2D3528] @break
        @case('shipping') bg-blue-100 text-blue-700 @break
        @case('delivered') bg-[#CC3A63] text-white @break
        @case('cancelled') bg-red-100 text-red-700 @break
        @default bg-gray-100 text-gray-600
    @endswitch">
    @switch($status)
        @case('pending') Ожидает @break
        @case('paid') Оплачен @break
        @case('shipping') В доставке @break
        @case('delivered') Вручен @break
        @case('cancelled') Отменен @break
    @endswitch
</span>
