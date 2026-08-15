@extends('admin.layouts.app')

@section('title', 'تعديل تصنيف مقالات')

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>تعديل: {{ $category->name_ar }}</h2>
        <p>تعديل بيانات تصنيف المقالات.</p>
    </div>

    <a href="{{ route('admin.article-categories.index') }}" class="dashboard-button">
        <i class="fa-solid fa-arrow-right"></i>
        <span>رجوع</span>
    </a>
</section>

<section class="dashboard-panel">
    <form action="{{ route('admin.article-categories.update', $category) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        @include('admin.article-categories._form')
    </form>
</section>

@endsection
