@extends('layouts.app')
@section('title', 'Оплата заказа')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border-4 border-[#0D7D4C] text-center p-12">
        
        @if($order->status === 'completed')
            <div class="text-8xl mb-6 animate-bounce">✅</div>
            <h1 class="text-4xl font-black text-[#0D7D4C] mb-4">Оплата прошла успешно!</h1>
            <p class="text-xl text-[#422168] mb-2">Заказ №{{ $order->id }} оплачен</p>
            <p class="text-3xl font-black text-[#422168] mb-8">{{ $order->total_amount }} ₽</p>
        @else
            <div class="text-8xl mb-6"></div>
            <h1 class="text-4xl font-black text-[#CAF204] mb-4">Платеж обрабатывается</h1>
            <p class="text-xl text-[#422168] mb-2">Заказ №{{ $order->id }}</p>
            <p class="text-lg text-gray-600 mb-8">Текущий статус: <strong>{{ $order->status }}</strong></p>
            
            <form action="{{ route('payment.check-status', $order) }}" method="POST" class="mb-6">
                @csrf
                <button type="submit" class="bg-[#CAF204] text-[#422168] font-bold py-3 px-8 rounded-xl btn-animated">
                     Проверить статус платежа
                </button>
            </form>
        @endif
        
        <div class="bg-[#E8FC8C]/30 p-6 rounded-2xl mb-8 text-left">
            <h3 class="font-bold text-[#422168] mb-3"> Ваш заказ:</h3>
            @foreach($order->items as $item)
                <div class="flex justify-between py-2 border-b border-[#E8FC8C]">
                    <span>{{ $item->product_name ?? 'Товар' }}</span>
                    <span class="font-bold">{{ $item->quantity * $item->price_at_moment }} ₽</span>
                </div>
            @endforeach
        </div>

        <div class="flex gap-4">
            <a href="{{ route('profile.edit') }}" 
               class="flex-1 bg-[#0D7D4C] text-white font-bold py-3 rounded-xl btn-animated">
                 В личный кабинет
            </a>
            <a href="{{ route('catalog') }}" 
               class="flex-1 bg-[#CAF204] text-[#422168] font-bold py-3 rounded-xl btn-animated text-center">
                 Продолжить покупки
            </a>
        </div>
    </div>
</div>
@endsection