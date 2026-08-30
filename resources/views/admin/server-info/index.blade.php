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

@if ($html)
    <section class="dashboard-panel" style="padding: 0; overflow: hidden;">
        <iframe
            srcdoc="{{ $html }}"
            title="بيانات سيرفر"
            loading="lazy"
            style="display: block; width: 100%; height: calc(100vh - 220px); min-height: 480px; border: 0;"
        ></iframe>
    </section>
@else
    <div class="admin-empty-state">
        <div class="admin-empty-state__icon">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        </div>
        <h3>تعذّر جلب بيانات السيرفر</h3>
        <p>تعذّر الاتصال بأداة بيانات السيرفر الخارجية. تأكد إنها شغّالة، أو افتحها مباشرة من الرابط أعلاه.</p>
    </div>
@endif

@endsection
