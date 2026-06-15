@extends('admin.layouts.app')
@section('title', 'Редактировать статью')
@section('content')
    <h1 class="text-4xl font-black text-[#422168] mb-6">✏️ Редактирование: {{ $article->title }}</h1>
    <form id="article-form" action="{{ route('admin.articles.update', $article) }}" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); openPreviewModal();">
        @csrf @method('PUT')
        @include('admin.articles._form')
    </form>
@endsection