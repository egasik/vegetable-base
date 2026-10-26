@extends('layouts.app')
@section('title', 'Овощи и фрукты оптом и в розницу')
@section('content')
<section class="hero"><div><p class="eyebrow">Для дома и для бизнеса</p><h1>Больше свежего<br>на вашем столе.</h1><p>Овощи и фрукты в нужном количестве: от половины килограмма до целых упаковок.</p><div class="hero-links"><a class="button" href="{{ route('catalog') }}">Выбрать продукты →</a><a href="{{ route('articles.index') }}">Как выбирать и хранить</a></div></div><img class="hero-art" src="{{ asset('images/harvest.svg') }}" alt="Иллюстрация корзины с овощами" width="500" height="440"></section>
<div class="section-heading"><h2>В нашем каталоге</h2><a href="{{ route('catalog') }}">Все товары →</a></div><div class="product-grid">@forelse($products as $product)@include('partials.product-card')@empty<p>Ассортимент скоро появится.</p>@endforelse</div>
@endsection
