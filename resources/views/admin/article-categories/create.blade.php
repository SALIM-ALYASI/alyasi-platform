@extends('admin.layouts.app')

@section('title', 'إضافة تصنيف مقالات')

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>إضافة تصنيف مقالات</h2>
        <p>أضف تصنيفًا جديدًا لتنظيم مقالات قسم "مقالاتي".</p>
    </div>

    <a href="{{ route('admin.article-categories.index') }}" class="dashboard-button">
        <i class="fa-solid fa-arrow-right"></i>
        <span>رجوع</span>
    </a>
</section>

<section class="dashboard-panel">
    <form action="{{ route('admin.article-categories.store') }}" method="POST" novalidate>
        @csrf

        @include('admin.article-categories._form', ['category' => null])
    </form>
</section>

@endsection
