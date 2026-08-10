@extends('admin.layouts.app')

@section('title', 'إضافة مقال')

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>إضافة مقال</h2>
        <p>أضف مقالًا جديدًا لقسم "مقالاتي".</p>
    </div>

    <a href="{{ route('admin.articles.index') }}" class="dashboard-button">
        <i class="fa-solid fa-arrow-right"></i>
        <span>رجوع</span>
    </a>
</section>

<section class="dashboard-panel">
    <form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf

        @include('admin.articles._form', ['article' => null])
    </form>
</section>

@endsection
