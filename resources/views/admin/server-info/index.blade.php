@extends('admin.layouts.app')

@section('title', 'بيانات سيرفر')

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>بيانات سيرفر</h2>

        <p>
            عرض مباشر لأداة بيانات السيرفر الخارجية داخل لوحة التحكم.
        </p>
    </div>

    <div class="page-header-actions">
        <a
            href="{{ $url }}"
            target="_blank"
            rel="noopener noreferrer"
            class="btn btn-outline"
        >
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            فتح في تبويب جديد
        </a>
    </div>
</section>

<section class="dashboard-panel" style="padding: 0; overflow: hidden;">
    <iframe
        src="{{ $url }}"
        title="بيانات سيرفر"
        loading="lazy"
        style="display: block; width: 100%; height: calc(100vh - 220px); min-height: 480px; border: 0;"
    ></iframe>
</section>

@endsection
