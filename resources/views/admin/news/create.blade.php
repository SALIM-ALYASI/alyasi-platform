@extends('admin.layouts.app')

@section('title', 'إضافة خبر')

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>إضافة خبر جديد</h2>

        <p>
            أدخل بيانات الخبر بالعربية والإنجليزية.
        </p>
    </div>

    <a href="{{ route('admin.news.index') }}" class="dashboard-button">
        <i class="fa-solid fa-arrow-right"></i>
        <span>رجوع</span>
    </a>
</section>

<section class="dashboard-panel">
    <form
        action="{{ route('admin.news.store') }}"
        method="POST"
    >
        @csrf

        @include('admin.news._form', ['article' => null])
    </form>
</section>

@endsection
