@extends('layouts.app')
@section('title', 'Каталог')
@section('content')
<div class="page-heading"><p class="eyebrow">Выбирайте в своём ритме</p><h1>Овощи и фрукты</h1><p>Покупайте по весу или упаковками. Цена и количество всегда перед вами.</p></div>
<form action="{{ route('catalog') }}" method="GET" class="filters"><label>Поиск<input type="search" name="search" placeholder="Например, картофель" value="{{ request('search') }}"></label><label>Категория<select name="category_id"><option value="">Все категории</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>@endforeach</select></label><label>Розница, от ₽/кг<input type="number" min="0" step="0.01" name="min_price" value="{{ request('min_price') }}"></label><label>До ₽/кг<input type="number" min="0" step="0.01" name="max_price" value="{{ request('max_price') }}"></label><button class="button">Найти</button></form>
@if(request()->hasAny(['search','category_id','min_price','max_price']))<p><a class="quiet-link" href="{{ route('catalog') }}">Сбросить фильтры</a></p>@endif
<div class="section-heading"><h2>Каталог</h2><span>{{ $products->total() }} товаров</span></div><div class="product-grid">@forelse($products as $product)@include('partials.product-card')@empty<div class="panel"><h2>Ничего не найдено</h2><p>Попробуйте другое название или сбросьте фильтры.</p></div>@endforelse</div><div class="pagination">{{ $products->links() }}</div>
@endsection
