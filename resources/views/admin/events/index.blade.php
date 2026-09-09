@extends('admin.layouts.app')

@section('title', 'المؤتمرات والفعاليات')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">
            <span class="admin-page-header__badge">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">المؤتمرات والفعاليات</h1>

            <p class="admin-page-header__description">
                إدارة نسخ المؤتمرات (آبل، سامسونج، GITEX...) — المنتجات المتوقعة، الأسعار، والصور.
            </p>
        </div>

        <a href="{{ route('admin.events.create') }}" class="admin-primary-button">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            إضافة نسخة مؤتمر
        </a>

    </div>

    <section class="admin-list-panel">

        <div class="admin-list-panel__header">
            <div>
                <h2>كل النسخ</h2>
                <p>الحالة (قادم/مباشر/انتهى) تُحسب تلقائيًا من وقت البداية والنهاية.</p>
            </div>

            <span class="admin-results-count">{{ number_format($editions->total()) }} نسخة</span>
        </div>

        @if ($editions->isNotEmpty())

            <div class="admin-card-grid">

                @foreach ($editions as $edition)
                    @php
                        $phaseLabel = ['upcoming' => 'قادم', 'live' => 'مباشر الآن', 'concluded' => 'انتهى'][$edition->phase];
                        $phaseStatusClass = ['upcoming' => 'admin-status--active', 'live' => 'admin-status--featured', 'concluded' => 'admin-status--inactive'][$edition->phase];
                    @endphp

                    <article class="admin-data-card">

                        <div class="admin-data-card__media">
                            @if ($edition->image)
                                <img src="{{ asset($edition->image) }}" alt="{{ $edition->title_ar }}">
                            @else
                                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                            @endif

                            <div class="admin-data-card__badges">
                                <span class="admin-status {{ $phaseStatusClass }}">
                                    <i class="fa-solid fa-circle" aria-hidden="true"></i>
                                    {{ $phaseLabel }}
                                </span>
                            </div>

                            <div class="admin-data-card__badges admin-data-card__badges--end">
                                <span class="admin-status {{ $edition->status === 'published' ? 'admin-status--active' : 'admin-status--inactive' }}">
                                    {{ $edition->status === 'published' ? 'منشور' : 'مسودة' }}
                                </span>
                            </div>
                        </div>

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $edition->title_ar }}</h3>
                                    <span>{{ $edition->event->name }}</span>
                                </div>
                            </div>

                            <div class="admin-data-card__meta">
                                <div>
                                    <span>عدد المنتجات المرصودة</span>
                                    <strong>{{ count($edition->announcements ?? []) }}</strong>
                                </div>

                                <div>
                                    <span>صفوف الأسعار</span>
                                    <strong>{{ count($edition->pricing_table ?? []) }}</strong>
                                </div>
                            </div>

                        </div>

                        <div class="admin-data-card__actions">

                            @if ($edition->permalink('ar'))
                                <a href="{{ $edition->permalink('ar')->url() }}" target="_blank" rel="noopener" class="admin-action-button" title="معاينة" aria-label="معاينة الصفحة العامة">
                                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                </a>
                            @endif

                            <a href="{{ route('admin.events.edit', $edition) }}" class="admin-action-button admin-action-button--edit" title="تعديل" aria-label="تعديل النسخة">
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>

                        </div>

                    </article>

                @endforeach

            </div>

            <div class="admin-pagination">
                {{ $editions->links() }}
            </div>

        @else

            <div class="admin-empty-state">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                <p>ما فيه نسخ مؤتمرات بعد.</p>
            </div>

        @endif

    </section>

</div>

@endsection
