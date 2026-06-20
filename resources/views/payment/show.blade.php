@extends('layouts.app')
@section('title', 'Оплата заказа №' . $order->id)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border-4 border-[#E8FC8C]">
        
        {{-- Заголовок --}}
        <div class="bg-gradient-to-r from-[#0D7D4C] to-[#422168] p-8 text-white">
            <h1 class="text-4xl font-black mb-2"> Оплата заказа</h1>
            <p class="text-[#CAF204] text-lg">Заказ №{{ $order->id }} от {{ $order->created_at->format('d.m.Y H:i') }}</p>
        </div>

        <div class="grid md:grid-cols-2 gap-0">
            
            {{-- Левая колонка: Детали заказа --}}
            <div class="p-8 bg-[#E8FC8C]/20 border-r-4 border-[#E8FC8C]">
                <h2 class="text-2xl font-black text-[#422168] mb-6"> Детали заказа</h2>
                
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="bg-white p-4 rounded-xl border-2 border-[#E8FC8C]">
                            <div class="flex justify-between items-start">
                                <div>
                                
                                    <p class="font-bold text-[#422168] text-lg">
                                        {{ $item->product_name ?? 'Товар' }}
                                    </p>
                                    <p class="text-sm text-gray-600">{{ $item->quantity }} × {{ $item->price_at_moment }} ₽</p>
                                </div>
                                <p class="text-xl font-black text-[#0D7D4C]">{{ $item->quantity * $item->price_at_moment }} ₽</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 pt-6 border-t-4 border-[#CAF204]">
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold text-[#422168]">Итого к оплате:</span>
                        <span class="text-4xl font-black text-[#0D7D4C]">{{ $order->total_amount }} ₽</span>
                    </div>
                </div>
            </div>

            {{-- Правая колонка: Выбор способа оплаты --}}
            <div class="p-8">
                <h2 class="text-2xl font-black text-[#422168] mb-6"> Способ оплаты</h2>
                
                <form action="{{ route('payment.process', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    {{-- Банковская карта --}}
                    <label class="block cursor-pointer group">
                        <input type="radio" name="payment_method" value="bank_card" required 
                               class="peer sr-only">
                        <div class="p-5 rounded-2xl border-4 border-[#E8FC8C] bg-white transition-all
                                    peer-checked:border-[#0D7D4C] peer-checked:bg-[#0D7D4C]/5
                                    group-hover:border-[#CAF204] group-hover:shadow-lg">
                            <div class="flex items-center gap-4">
                                
                                <div class="flex-1">
                                    <p class="font-black text-lg text-[#422168]">Банковская карта</p>
                                    <p class="text-sm text-gray-600">Visa, MasterCard, МИР</p>
                                </div>
                                <div class="w-6 h-6 rounded-full border-4 border-gray-300 peer-checked:border-[#0D7D4C] peer-checked:bg-[#0D7D4C]"></div>
                            </div>
                        </div>
                    </label>

                    {{-- СБП QR-код --}}
                    <label class="block cursor-pointer group">
                        <input type="radio" name="payment_method" value="sbp" 
                               class="peer sr-only">
                        <div class="p-5 rounded-2xl border-4 border-[#E8FC8C] bg-white transition-all
                                    peer-checked:border-[#422168] peer-checked:bg-[#422168]/5
                                    group-hover:border-[#CAF204] group-hover:shadow-lg">
                            <div class="flex items-center gap-4">
                                
                                <div class="flex-1">
                                    <p class="font-black text-lg text-[#422168]">СБП по QR-коду</p>
                                    <p class="text-sm text-gray-600">Оплата через приложение банка</p>
                                </div>
                                <div class="w-6 h-6 rounded-full border-4 border-gray-300 peer-checked:border-[#422168] peer-checked:bg-[#422168]"></div>
                            </div>
                        </div>
                    </label>

                    <button type="submit" class="w-full bg-[#0D7D4C] text-white font-black py-4 rounded-xl btn-animated pulse-hover hover:bg-[#422168] text-lg mt-6">
                        Перейти к оплате →
                    </button>
                </form>
                <form action="{{ route('payment.process', $order) }}" method="POST" class="space-y-4">
    @csrf
    
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-4">
            <p class="font-bold">⚠️ Ошибки:</p>
            <ul class="list-disc list-inside text-sm mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-4">
            <p class="font-bold">⚠️ {{ session('error') }}</p>
        </div>
    @endif
    
                <div class="mt-6 p-4 bg-[#CAF204]/20 rounded-xl border-l-4 border-[#CAF204]">
                    <p class="text-xs text-[#422168]">
                         <strong>Безопасная оплата</strong> через платежную систему Robokassa. 
                        Данные вашей карты защищены шифрованием SSL.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection