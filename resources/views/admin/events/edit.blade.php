@extends('admin.layouts.app')

@section('title', 'تعديل نسخة مؤتمر')

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
            <h1 class="admin-page-header__title">تعديل: {{ $edition->title_ar }}</h1>
            <p class="admin-page-header__description">
                الحالة الحالية:
                <strong>{{ ['upcoming' => 'قادم', 'live' => 'مباشر الآن', 'concluded' => 'انتهى'][$edition->phase] }}</strong>
                @if ($edition->permalink('ar'))
                    — <a href="{{ $edition->permalink('ar')->url() }}" target="_blank" rel="noopener">معاينة الصفحة العامة ↗</a>
                @endif
            </p>
        </div>
    </div>

    <div class="events-form-card">
        <form action="{{ route('admin.events.update', $edition) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.events._form')
        </form>
    </div>

</div>

@endsection
