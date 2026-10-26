@extends('layouts.app')
@section('title', $product->name)
@section('content')
<p style="margin-bottom:28px"><a href="{{ route('catalog') }}">← Каталог</a></p><section class="product-detail"><img class="product-image" src="{{ $product->image_path ? asset('storage/'.$product->image_path) : asset('images/product-placeholder.svg') }}" alt="{{ $product->name }}" width="480" height="400"><div><p class="eyebrow">{{ $product->category?->name }}</p><h1>{{ $product->name }}</h1><p class="product-description">{{ $product->description }}</p>@include('partials.purchase-options')</div></section>
@endsection
