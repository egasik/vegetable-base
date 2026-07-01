@extends('admin.layouts.app')
@section('title', 'Новая статья')
@section('content')
    <h1 class="text-4xl font-black text-[#422168] mb-6"> Новая статья для справочника</h1>
    <form id="article-form" action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); openPreviewModal();">
        @csrf
        @include('admin.articles._form')
    </form>
@endsection