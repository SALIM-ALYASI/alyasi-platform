@extends('admin.layouts.app')

@section('title', 'المؤتمرات والفعاليات')

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('assets/admin/css/events.css') }}">
@endpush

@section('content')

<div class="admin-data-page events-index-page">

    <div class="admin-page-header events-index-header">
        <div class="admin-page-header__content">
            <span class="admin-page-header__badge">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                إدارة المؤتمرات
            </span>

            <h1 class="admin-page-header__title">المؤتمرات والفعاليات</h1>

            <p class="admin-page-header__description">
                إدارة سلاسل المؤتمرات والنسخ السنوية، مع متابعة الحالات والمواعيد والتغطيات من مكان واحد.
            </p>
        </div>

        <div class="events-index-header__actions">
            <a href="{{ route('events.index') }}" target="_blank" rel="noopener" class="events-index-secondary-button">
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                عرض المركز
            </a>

            <a href="{{ route('admin.events.create') }}" class="admin-primary-button">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                إضافة نسخة مؤتمر
            </a>
        </div>
    </div>

    <div class="admin-stats-row events-stats-row">
        <div class="admin-stat-card">
            <div class="admin-stat-card__icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></div>
            <div class="admin-stat-card__content">
                <span>سلاسل المؤتمرات</span>
                <strong>{{ number_format($stats['series']) }}</strong>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-card__icon"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></div>
            <div class="admin-stat-card__content">
                <span>إجمالي النسخ</span>
                <strong>{{ number_format($stats['editions']) }}</strong>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-card__icon"><i class="fa-solid fa-clock" aria-hidden="true"></i></div>
            <div class="admin-stat-card__content">
                <span>القادمة</span>
                <strong>{{ number_format($stats['upcoming']) }}</strong>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-card__icon"><i class="fa-solid fa-flag-checkered" aria-hidden="true"></i></div>
            <div class="admin-stat-card__content">
                <span>المنتهية</span>
                <strong>{{ number_format($stats['concluded']) }}</strong>
            </div>
        </div>
    </div>

    <section class="admin-filter-panel events-filter-panel">
        <form method="GET" action="{{ route('admin.events.index') }}" class="admin-filter-form">
            <div class="admin-filter-form__search">
                <label for="events-search">بحث</label>
                <div class="admin-input-with-icon">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input
                        id="events-search"
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Apple، COMEX، Samsung..."
                    >
                </div>
            </div>

            <div class="admin-filter-form__field">
                <label for="events-phase">الحالة</label>
                <select id="events-phase" name="phase">
                    <option value="">كل الحالات</option>
                    <option value="upcoming" @selected($phase === 'upcoming')>قادم</option>
                    <option value="live" @selected($phase === 'live')>مباشر الآن</option>
                    <option value="concluded" @selected($phase === 'concluded')>منتهي</option>
                </select>
            </div>

            <div class="admin-filter-form__actions">
                <button type="submit" class="admin-filter-button">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                    تطبيق
                </button>
                <a href="{{ route('admin.events.index') }}" class="admin-reset-button">إعادة ضبط</a>
            </div>
        </form>
    </section>

    <section class="admin-list-panel events-series-panel">
        <div class="admin-list-panel__header">
            <div>
                <h2>الشركات وسلاسل المؤتمرات</h2>
                <p>كل بطاقة تجمع جميع النسخ التابعة لنفس المؤتمر، مع أحدث سنة والحالة الحالية.</p>
            </div>

            <span class="admin-results-count">{{ number_format($events->total()) }} نتيجة</span>
        </div>

        @if ($events->isNotEmpty())
            <div class="admin-card-grid events-series-grid">
                @foreach ($events as $event)
                    @php
                        $latestEdition = $event->editions->first();
                        $coverEdition = $event->editions->first(fn ($edition) => filled($edition->image)) ?? $latestEdition;
                        $nextEdition = $event->editions->first(fn ($edition) => $edition->phase === 'upcoming');
                        $phaseValue = $latestEdition?->phase;
                        $phaseLabel = $phaseValue ? ['upcoming' => 'قادم', 'live' => 'مباشر الآن', 'concluded' => 'انتهى'][$phaseValue] : 'بدون نسخة';
                        $phaseStatusClass = $phaseValue ? ['upcoming' => 'admin-status--active', 'live' => 'admin-status--featured', 'concluded' => 'admin-status--inactive'][$phaseValue] : 'admin-status--inactive';
                        $description = $latestEdition?->short_description_ar ?: 'سلسلة مؤتمر سنوية ضمن مركز المؤتمرات والفعاليات في ALYASI.';
                    @endphp

                    <article class="admin-data-card event-series-card">
                        <div class="admin-data-card__media event-series-card__media">
                            @if ($coverEdition?->image)
                                <img src="{{ asset($coverEdition->image) }}" alt="{{ $event->name }}">
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

                        <div class="admin-data-card__body event-series-card__body">
                            <div class="admin-data-card__heading event-series-card__heading">
                                <div>
                                    <h3>{{ $event->name }}</h3>
                                    <span>/events/{{ $event->slug ?: '—' }}</span>
                                </div>
                            </div>

                            <p class="admin-data-card__description event-series-card__description">
                                {{ $description }}
                            </p>

                            <div class="admin-data-card__meta event-series-card__meta">
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
                                <div class="event-series-card__editions">
                                    <div class="event-series-card__editions-title">
                                        <span>النسخ</span>
                                        <small>{{ $event->editions_count }} نسخة</small>
                                    </div>

                                    <div class="event-series-card__edition-links">
                                        @foreach ($event->editions as $edition)
                                            <a href="{{ route('admin.events.edit', $edition) }}"
                                               class="event-edition-chip"
                                               title="تعديل نسخة {{ $edition->year }}">
                                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                                <span>{{ $edition->year }}</span>
                                                @if ($edition->phase === 'upcoming')
                                                    <small>قريبًا</small>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="admin-data-card__actions event-series-card__actions">
                            @if ($event->slug)
                                <a href="{{ route('event_editions.show', ['slug' => $event->slug]) }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="event-series-action event-series-action--secondary">
                                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                    عرض السلسلة
                                </a>
                            @endif

                            @if ($latestEdition)
                                <a href="{{ route('admin.events.edit', $latestEdition) }}"
                                   class="event-series-action event-series-action--primary">
                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    تعديل أحدث نسخة
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
                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                </div>
                <h3>لا توجد نتائج</h3>
                <p>جرّب تغيير البحث أو الفلتر، أو أضف نسخة مؤتمر جديدة.</p>
                <a href="{{ route('admin.events.index') }}" class="admin-reset-button">عرض كل المؤتمرات</a>
            </div>
        @endif
    </section>

</div>

@endsection
