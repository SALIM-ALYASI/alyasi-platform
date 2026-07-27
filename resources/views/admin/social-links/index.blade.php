@extends('admin.layouts.app')

@section('title', 'روابط التواصل الاجتماعي')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/admin/css/social-links.css') }}"
    >
@endpush

@section('content')

<div class="social-links-page">

    <div class="page-header">

        <div>
            <h1>روابط التواصل الاجتماعي</h1>
            <p>إدارة روابط وحسابات منصة ALYASI.</p>
        </div>

        <a
            href="{{ route('admin.social-links.create') }}"
            class="btn btn-primary"
        >
            <i class="fa-solid fa-plus"></i>
            إضافة منصة
        </a>

    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($socialLinks->isNotEmpty())

        <div class="social-admin-grid">

            @foreach($socialLinks as $socialLink)

                <article
                    class="social-admin-card"
                    style="--platform-color: {{ $socialLink->color ?: '#5E2324' }}"
                >

                    <div class="social-card-header">

                        <div class="platform-info">

                            <span class="platform-icon">
                                <i class="{{ $socialLink->icon }}"></i>
                            </span>

                            <div>
                                <h2 class="platform-name">
                                    {{ $socialLink->name }}
                                </h2>

                                <span class="platform-key">
                                    {{ $socialLink->platform }}
                                </span>
                            </div>

                        </div>

                        @if($socialLink->is_active)
                            <span class="status-badge status-active">
                                <i class="fa-solid fa-circle-check"></i>
                                نشط
                            </span>
                        @else
                            <span class="status-badge status-inactive">
                                <i class="fa-solid fa-eye-slash"></i>
                                مخفي
                            </span>
                        @endif

                    </div>

                    <div class="social-card-body">

                        <div class="social-detail">

                            <span class="detail-label">
                                <i class="fa-solid fa-user"></i>
                                اسم المستخدم
                            </span>

                            <span class="detail-value">
                                {{ $socialLink->username ?: 'غير محدد' }}
                            </span>

                        </div>

                        <div class="social-detail">

                            <span class="detail-label">
                                <i class="fa-solid fa-arrow-down-1-9"></i>
                                ترتيب الظهور
                            </span>

                            <span class="detail-value">
                                {{ $socialLink->sort_order }}
                            </span>

                        </div>

                        <div class="social-detail social-detail-full">

                            <span class="detail-label">
                                <i class="fa-solid fa-link"></i>
                                رابط الحساب
                            </span>

                            <a
                                href="{{ $socialLink->url }}"
                                class="account-url"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ $socialLink->url }}
                            </a>

                        </div>

                    </div>

                    <div class="social-card-actions">

                        <a
                            href="{{ $socialLink->url }}"
                            class="btn btn-visit"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            زيارة الحساب
                        </a>

                        <a
                            href="{{ route('admin.social-links.edit', $socialLink) }}"
                            class="btn btn-edit"
                        >
                            <i class="fa-solid fa-pen-to-square"></i>
                            تعديل
                        </a>

                        <form
                            action="{{ route('admin.social-links.destroy', $socialLink) }}"
                            method="POST"
                            class="delete-form"
                            onsubmit="return confirm('هل أنت متأكد من حذف رابط {{ addslashes($socialLink->name) }}؟');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-delete"
                            >
                                <i class="fa-solid fa-trash"></i>
                                حذف
                            </button>

                        </form>

                    </div>

                </article>

            @endforeach

        </div>

        @if($socialLinks->hasPages())
            <div class="pagination-wrapper">
                {{ $socialLinks->links() }}
            </div>
        @endif

    @else

        <div class="content-card empty-state">

            <i class="fa-solid fa-share-nodes"></i>

            <h2>لا توجد روابط تواصل اجتماعي</h2>

            <p>
                أضف أول منصة تواصل اجتماعي ليتم عرضها هنا.
            </p>

            <a
                href="{{ route('admin.social-links.create') }}"
                class="btn btn-primary"
            >
                <i class="fa-solid fa-plus"></i>
                إضافة منصة
            </a>

        </div>

    @endif

</div>

@endsection