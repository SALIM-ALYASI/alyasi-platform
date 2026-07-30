@extends('admin.layouts.app')

@section('title', 'عرض التقنية')

@push('styles')
<link
    rel="stylesheet"
    href="{{ asset('assets/admin/css/technology-show.css') }}"
>
@endpush

@section('content')

@php
    $worksCount = isset($technology->works_count)
        ? (int) $technology->works_count
        : (
            $technology->relationLoaded('works')
                ? $technology->works->count()
                : 0
        );
@endphp

<div class="technology-show-page">

    <section class="technology-show-hero">

        <div class="technology-show-hero__background"></div>

        <div class="technology-show-hero__content">

            <div class="technology-show-hero__main">

                <div class="technology-show-hero__visual">

                    @if ($technology->icon)

                        <i
                            class="{{ $technology->icon }}"
                            aria-hidden="true"
                        ></i>

                    @else

                        <i
                            class="fa-solid fa-microchip"
                            aria-hidden="true"
                        ></i>

                    @endif

                </div>

                <div class="technology-show-hero__text">

                    <div class="technology-show-hero__badges">

                        <span class="technology-show-badge">
                            <i
                                class="fa-solid fa-microchip"
                                aria-hidden="true"
                            ></i>

                            تقنية
                        </span>

                        <span
                            class="technology-show-status
                                {{ $technology->is_active
                                    ? 'technology-show-status--active'
                                    : 'technology-show-status--inactive' }}"
                        >
                            <i
                                class="{{ $technology->is_active
                                    ? 'fa-solid fa-circle-check'
                                    : 'fa-solid fa-circle-xmark' }}"
                                aria-hidden="true"
                            ></i>

                            {{ $technology->is_active
                                ? 'مفعلة'
                                : 'غير مفعلة' }}
                        </span>

                    </div>

                    <h1>
                        {{ $technology->name }}
                    </h1>

                </div>

            </div>

            <div class="technology-show-hero__actions">

                <a
                    href="{{ route(
                        'admin.technologies.edit',
                        $technology
                    ) }}"
                    class="technology-show-button technology-show-button--primary"
                >
                    <i
                        class="fa-solid fa-pen-to-square"
                        aria-hidden="true"
                    ></i>

                    تعديل التقنية
                </a>

                <a
                    href="{{ route('admin.technologies.index') }}"
                    class="technology-show-button technology-show-button--secondary"
                >
                    <i
                        class="fa-solid fa-arrow-right"
                        aria-hidden="true"
                    ></i>

                    العودة للقائمة
                </a>

            </div>

        </div>

    </section>

    <section class="technology-show-statistics">

        <article class="technology-show-statistic">

            <div class="technology-show-statistic__icon">
                <i
                    class="fa-solid fa-layer-group"
                    aria-hidden="true"
                ></i>
            </div>

            <div>
                <span>
                    الأعمال المرتبطة
                </span>

                <strong>
                    {{ number_format($worksCount) }}
                </strong>
            </div>

        </article>

        <article class="technology-show-statistic">

            <div class="technology-show-statistic__icon">
                <i
                    class="fa-solid fa-arrow-down-1-9"
                    aria-hidden="true"
                ></i>
            </div>

            <div>
                <span>
                    ترتيب الظهور
                </span>

                <strong>
                    {{ number_format($technology->sort_order ?? 0) }}
                </strong>
            </div>

        </article>

        <article class="technology-show-statistic">

            <div class="technology-show-statistic__icon">
                <i
                    class="fa-regular fa-calendar-plus"
                    aria-hidden="true"
                ></i>
            </div>

            <div>
                <span>
                    تاريخ الإضافة
                </span>

                <strong>
                    {{ optional($technology->created_at)->format('Y/m/d') }}
                </strong>
            </div>

        </article>

        <article class="technology-show-statistic">

            <div class="technology-show-statistic__icon">
                <i
                    class="fa-regular fa-clock"
                    aria-hidden="true"
                ></i>
            </div>

            <div>
                <span>
                    آخر تحديث
                </span>

                <strong>
                    {{ optional($technology->updated_at)->diffForHumans() }}
                </strong>
            </div>

        </article>

    </section>

    <div class="technology-show-grid">

        <main class="technology-show-main">

            @if (
                $technology->relationLoaded('works')
                && $technology->works->isNotEmpty()
            )

                <section class="technology-show-card">

                    <header class="technology-show-card__header">

                        <div>

                            <span class="technology-show-card__badge">
                                <i
                                    class="fa-solid fa-briefcase"
                                    aria-hidden="true"
                                ></i>

                                الأعمال
                            </span>

                            <h2>
                                الأعمال المرتبطة بالتقنية
                            </h2>

                        </div>

                        <span class="technology-show-card__count">
                            {{ $technology->works->count() }}
                        </span>

                    </header>

                    <div class="technology-show-card__body">

                        <div class="technology-show-works">

                            @foreach ($technology->works as $work)

                                <article class="technology-show-work">

                                    <div class="technology-show-work__image">

                                        @if ($work->cover_image)

                                            <img
                                                src="{{ asset(
                                                    'storage/' . $work->cover_image
                                                ) }}"
                                                alt="{{ $work->title }}"
                                            >

                                        @else

                                            <i
                                                class="fa-solid fa-briefcase"
                                                aria-hidden="true"
                                            ></i>

                                        @endif

                                    </div>

                                    <div class="technology-show-work__content">

                                        <h3>
                                            {{ $work->title }}
                                        </h3>

                                        <div class="technology-show-work__meta">

                                            <span>
                                                <i
                                                    class="{{ $work->is_active
                                                        ? 'fa-solid fa-circle-check'
                                                        : 'fa-solid fa-circle-xmark' }}"
                                                    aria-hidden="true"
                                                ></i>

                                                {{ $work->is_active
                                                    ? 'مفعل'
                                                    : 'غير مفعل' }}
                                            </span>

                                            @if ($work->is_featured)

                                                <span>
                                                    <i
                                                        class="fa-solid fa-star"
                                                        aria-hidden="true"
                                                    ></i>

                                                    مميز
                                                </span>

                                            @endif

                                        </div>

                                    </div>

                                    <a
                                        href="{{ route(
                                            'admin.works.show',
                                            $work
                                        ) }}"
                                        class="technology-show-work__link"
                                        aria-label="عرض العمل"
                                    >
                                        <i
                                            class="fa-solid fa-arrow-left"
                                            aria-hidden="true"
                                        ></i>
                                    </a>

                                </article>

                            @endforeach

                        </div>

                    </div>

                </section>

            @else

                <section class="technology-show-card">

                    <div class="technology-show-card__body">

                        <div class="technology-show-empty">
                            لا توجد أعمال مرتبطة بهذه التقنية حتى الآن.
                        </div>

                    </div>

                </section>

            @endif

        </main>

        <aside class="technology-show-sidebar">

            <section class="technology-show-card">

                <header class="technology-show-card__header">

                    <div>

                        <span class="technology-show-card__badge">
                            <i
                                class="fa-solid fa-circle-info"
                                aria-hidden="true"
                            ></i>

                            التفاصيل
                        </span>

                        <h2>
                            معلومات التقنية
                        </h2>

                    </div>

                </header>

                <div class="technology-show-card__body">

                    <dl class="technology-show-details">

                        <div>

                            <dt>
                                الرابط المختصر
                            </dt>

                            <dd dir="ltr">
                                {{ $technology->slug }}
                            </dd>

                        </div>

                        <div>

                            <dt>
                                كلاس الأيقونة
                            </dt>

                            <dd dir="ltr">
                                {{ $technology->icon ?: 'غير مضاف' }}
                            </dd>

                        </div>

                        <div>

                            <dt>
                                ترتيب الظهور
                            </dt>

                            <dd>
                                {{ number_format(
                                    $technology->sort_order ?? 0
                                ) }}
                            </dd>

                        </div>

                        <div>

                            <dt>
                                حالة النشر
                            </dt>

                            <dd>
                                <span
                                    class="technology-show-inline-status
                                        {{ $technology->is_active
                                            ? 'technology-show-inline-status--active'
                                            : 'technology-show-inline-status--inactive' }}"
                                >
                                    {{ $technology->is_active
                                        ? 'مفعلة'
                                        : 'غير مفعلة' }}
                                </span>
                            </dd>

                        </div>

                        <div>

                            <dt>
                                تاريخ الإنشاء
                            </dt>

                            <dd>
                                {{ optional(
                                    $technology->created_at
                                )->format('Y/m/d - H:i') }}
                            </dd>

                        </div>

                        <div>

                            <dt>
                                آخر تحديث
                            </dt>

                            <dd>
                                {{ optional(
                                    $technology->updated_at
                                )->format('Y/m/d - H:i') }}
                            </dd>

                        </div>

                    </dl>

                </div>

            </section>

            <section class="technology-show-card technology-show-danger-card">

                <header class="technology-show-card__header">

                    <div>

                        <span class="technology-show-card__badge">
                            <i
                                class="fa-solid fa-triangle-exclamation"
                                aria-hidden="true"
                            ></i>

                            إجراءات حساسة
                        </span>

                        <h2>
                            حذف التقنية
                        </h2>

                    </div>

                </header>

                <div class="technology-show-card__body">

                    <p class="technology-show-danger-text">
                        حذف التقنية إجراء نهائي، وقد يؤثر في الأعمال المرتبطة بها.
                    </p>

                    <form
                        action="{{ route(
                            'admin.technologies.destroy',
                            $technology
                        ) }}"
                        method="POST"
                        onsubmit="return confirm(
                            'هل أنت متأكد من حذف هذه التقنية؟'
                        );"
                    >

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="technology-show-delete-button"
                        >
                            <i
                                class="fa-solid fa-trash"
                                aria-hidden="true"
                            ></i>

                            حذف التقنية
                        </button>

                    </form>

                </div>

            </section>

        </aside>

    </div>

</div>

@endsection
