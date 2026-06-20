@extends('layouts.app')
@section('title', 'QR-код для оплаты')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border-4 border-[#422168]">
        
        <div class="bg-gradient-to-r from-[#8B3FFD] to-[#422168] p-8 text-white text-center">
            <h1 class="text-4xl font-black mb-2"> Сканируйте QR-код</h1>
            <p class="text-[#CAF204] text-lg">Откройте приложение вашего банка</p>
        </div>

        <div class="p-8 text-center">
            <div class="bg-[#E8FC8C] p-6 rounded-3xl inline-block mb-6 border-4 border-[#CAF204]">
                {!! $qrCode !!}
            </div>

            <div class="space-y-4 mb-8">
                <p class="text-xl text-[#422168]">
                    Сумма к оплате: <strong class="text-3xl text-[#0D7D4C]">{{ $order->total_amount }} ₽</strong>
                </p>
                <p class="text-gray-600">Заказ №{{ $order->id }}</p>
            </div>

            <div class="bg-[#E8FC8C]/30 p-4 rounded-xl text-left space-y-2 mb-6">
                <h3 class="font-bold text-[#422168] mb-2"> Инструкция:</h3>
                <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1">
                    <li>Откройте приложение вашего банка (Сбер-банк, Т-банк, Альфа-банк и др.)</li>
                    <li>Нажмите на иконку QR-сканера или "Оплата по QR"</li>
                    <li>Наведите камеру на QR-код выше</li>
                    <li>Подтвердите оплату в приложении</li>
                </ol>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('payment.success', $order) }}" 
                   class="flex-1 bg-[#0D7D4C] text-white font-bold py-3 rounded-xl btn-animated">
                     Я оплатил
                </a>
                <a href="{{ route('profile.edit') }}" 
                   class="flex-1 bg-gray-200 text-[#422168] font-bold py-3 rounded-xl btn-animated text-center">
                    ← В личный кабинет
                </a>
            </div>
        </div>
    </div>
</div>
@endsection