@extends('layouts.app')
@section('title', $article->title)
@section('content')
    <article class="max-w-3xl mx-auto bg-white rounded-3xl shadow-2xl p-8 border-4 border-[#E8FC8C]">
        <h1 class="text-4xl font-black text-[#422168] mb-2 border-l-8 border-[#CAF204] pl-4">{{ $article->title }}</h1>
        <p class="text-sm text-gray-500 mb-8">Автор: {{ $article->author->name }} · {{ $article->created_at->format('d.m.Y') }}</p>

        <div class="space-y-6">
            @foreach($article->blocks as $block)
                @if($block->type === 'header')
                    <h2 class="text-2xl font-black text-[#422168] border-l-8 border-[#CAF204] pl-4">{{ $block->content }}</h2>
                @elseif($block->type === 'text')
                    <p class="text-gray-700 leading-relaxed text-lg bg-[#E8FC8C]/30 p-4 rounded-xl">{{ $block->content }}</p>
                @elseif($block->type === 'image')
                    <div class="bg-[#E8FC8C] p-4 rounded-2xl">
                        <img src="{{ asset('storage/' . $block->file_path) }}" class="w-full rounded-xl shadow-lg">
                    </div>
                @endif
            @endforeach
        </div>

        <a href="{{ route('articles.index') }}" class="inline-block mt-8 bg-[#422168] text-white px-6 py-3 rounded-xl btn-animated font-bold">
            ← К списку статей
        </a>
    </article>
@endsection