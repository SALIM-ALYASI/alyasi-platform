@extends('admin.layouts.app')

@section('title', 'تعديل مقال')

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>تعديل: {{ $article->title }}</h2>
        <p>تعديل بيانات المقال.</p>
    </div>

    <a href="{{ route('admin.articles.index') }}" class="dashboard-button">
        <i class="fa-solid fa-arrow-right"></i>
        <span>رجوع</span>
    </a>
</section>

<section class="dashboard-panel">
    <form action="{{ route('admin.articles.update', $article) }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        @include('admin.articles._form')
    </form>
</section>

@endsection
