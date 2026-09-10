@extends('admin.layouts.app')

@section('title', 'إضافة نسخة مؤتمر')

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('assets/admin/css/events.css') }}">
@endpush

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">
        <div class="admin-page-header__content">
            <span class="admin-page-header__badge">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                المؤتمرات والفعاليات
            </span>
            <h1 class="admin-page-header__title">إضافة نسخة مؤتمر جديدة</h1>
            <p class="admin-page-header__description">أضف مؤتمر/فعالية جديدة (زي آبل، COMEX...) وابدأ بتعبئة التوقعات.</p>
        </div>
    </div>

    <div class="events-form-card">
        <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.events._form')
        </form>
    </div>

</div>

@endsection

@push('scripts')
    <script>
        window.__eventUpgradeVerdict = {
            ar: @json(old('upgrade_verdict_text_ar', '')),
            en: @json(old('upgrade_verdict_text_en', ''))
        };
    </script>
    <script src="{{ versioned_asset('assets/admin/js/event-verdict-localization.js') }}" defer></script>
    <script src="{{ versioned_asset('assets/admin/js/event-pricing-auto.js') }}" defer></script>
@endpush
