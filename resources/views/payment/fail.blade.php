@extends('layouts.app')
@section('title', 'Ошибка оплаты')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border-4 border-red-500 text-center p-12">
        <div class="text-8xl mb-6">❌</div>
        <h1 class="text-4xl font-black text-red-600 mb-4">Оплата не прошла</h1>
        <p class="text-xl text-[#422168] mb-8">К сожалению, оплата заказа №{{ $order->id }} не была выполнена.</p>
        
        <div class="bg-red-50 p-6 rounded-2xl mb-8 text-left">
            <h3 class="font-bold text-red-700 mb-2">Возможные причины:</h3>
            <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                <li>Недостаточно средств на карте</li>
                <li>Неверно введены данные карты</li>
                <li>Технические проблемы на стороне банка</li>
                <li>Превышен лимит операций</li>
            </ul>
        </div>

        <div class="flex gap-4">
            <a href="{{ route('payment.show', $order) }}" 
               class="flex-1 bg-[#0D7D4C] text-white font-bold py-3 rounded-xl btn-animated">
                🔄 Попробовать снова
            </a>
            <a href="{{ route('profile.edit') }}" 
               class="flex-1 bg-gray-200 text-[#422168] font-bold py-3 rounded-xl btn-animated text-center">
                ← В личный кабинет
            </a>
        </div>
    </div>
</div>
@endsection