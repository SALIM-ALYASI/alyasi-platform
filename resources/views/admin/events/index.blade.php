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
                إدارة سلاسل المؤتمرات والشركات ثم نسخ كل مؤتمر حسب السنة: Apple، Samsung، Huawei، COMEX وغيرها.
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
                <h2>الشركات وسلاسل المؤتمرات</h2>
                <p>كل بطاقة تجمع جميع النسخ التابعة لنفس المؤتمر، مع أحدث سنة والحالة الحالية.</p>
            </div>

            <span class="admin-results-count">{{ number_format($events->total()) }} سلسلة</span>
        </div>

        @if ($events->isNotEmpty())
            <div class="admin-card-grid">
                @foreach ($events as $event)
                    @php
                        $latestEdition = $event->editions->first();
                        $nextEdition = $event->editions->first(fn ($edition) => $edition->phase === 'upcoming');
                        $phase = $latestEdition?->phase;
                        $phaseLabel = $phase ? ['upcoming' => 'قادم', 'live' => 'مباشر الآن', 'concluded' => 'انتهى'][$phase] : 'بدون نسخة';
                        $phaseStatusClass = $phase ? ['upcoming' => 'admin-status--active', 'live' => 'admin-status--featured', 'concluded' => 'admin-status--inactive'][$phase] : 'admin-status--inactive';
                    @endphp

                    <article class="admin-data-card">
                        <div class="admin-data-card__media">
                            @if ($latestEdition?->image)
                                <img src="{{ asset($latestEdition->image) }}" alt="{{ $event->name }}">
                            @else
                                <i class="fa-solid fa-building" aria-hidden="true"></i>
                            @endif

                            <div class="admin-data-card__badges">
                                <span class="admin-status {{ $phaseStatusClass }}">
                                    <i class="fa-solid fa-circle" aria-hidden="true"></i>
                                    {{ $phaseLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="admin-data-card__body">
                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $event->name }}</h3>
                                    <span>/events/{{ $event->slug ?: '—' }}</span>
                                </div>
                            </div>

                            <div class="admin-data-card__meta">
                                <div>
                                    <span>عدد النسخ</span>
                                    <strong>{{ $event->editions_count }}</strong>
                                </div>

                                <div>
                                    <span>آخر نسخة</span>
                                    <strong>{{ $latestEdition?->year ?: '—' }}</strong>
                                </div>

                                <div>
                                    <span>القادم</span>
                                    <strong>{{ $nextEdition ? $nextEdition->year : '—' }}</strong>
                                </div>
                            </div>

                            @if ($event->editions->isNotEmpty())
                                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:14px;">
                                    @foreach ($event->editions as $edition)
                                        <a href="{{ route('admin.events.edit', $edition) }}"
                                           class="admin-action-button admin-action-button--edit"
                                           style="width:auto;padding-inline:10px;gap:6px;"
                                           title="تعديل نسخة {{ $edition->year }}">
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                            {{ $edition->year }}
                                            @if ($edition->phase === 'upcoming')
                                                · قريبًا
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="admin-data-card__actions">
                            @if ($event->slug)
                                <a href="{{ route('event_editions.show', ['slug' => $event->slug]) }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="admin-action-button"
                                   title="صفحة السلسلة"
                                   aria-label="معاينة صفحة السلسلة">
                                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                </a>
                            @endif

                            @if ($latestEdition)
                                <a href="{{ route('admin.events.edit', $latestEdition) }}"
                                   class="admin-action-button admin-action-button--edit"
                                   title="تعديل أحدث نسخة"
                                   aria-label="تعديل أحدث نسخة">
                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="admin-pagination">
                {{ $events->links() }}
            </div>
        @else
            <div class="admin-empty-state">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                <p>ما فيه سلاسل مؤتمرات بعد.</p>
            </div>
        @endif
    </section>

</div>

@endsection
