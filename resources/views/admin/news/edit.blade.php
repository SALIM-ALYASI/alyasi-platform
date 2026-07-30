@extends('admin.layouts.app')

@section('title', 'تعديل الخبر')

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>تعديل الخبر</h2>

        <p>
            عدّل بيانات
            <strong>{{ $article->title_ar }}</strong>
        </p>
    </div>

    <a href="{{ route('admin.news.index') }}" class="dashboard-button">
        <i class="fa-solid fa-arrow-right"></i>
        <span>رجوع</span>
    </a>
</section>

<section class="dashboard-panel">
    <form
        action="{{ route('admin.news.update', $article) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        @include('admin.news._form')
    </form>
</section>

@endsection
