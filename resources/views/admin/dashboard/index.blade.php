@extends('admin.layouts.app')

@section('title', 'لوحة التحكم')

@section('page-title', 'لوحة التحكم')

@section(
    'page-description',
    'نظرة عامة على أداء منصة ALYASI وإدارة محتواها'
)

@section('content')

    {{-- رسالة الترحيب --}}
    <section class="dashboard-welcome">

        <div class="dashboard-welcome-content">

            <span class="dashboard-welcome-label">
                مرحبًا بك
            </span>

            <h2>
                أهلاً {{ auth('admin')->user()?->name ?? 'مدير المنصة' }}
            </h2>

            <p>
                تابع حالة الخدمات وإدارة المحتوى والوصول السريع
                إلى أهم أقسام المنصة من مكان واحد.
            </p>

        </div>

        <div class="dashboard-welcome-actions">

            <a
                href="{{ route('home') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="dashboard-button dashboard-button-secondary"
            >
                <i class="fa-solid fa-arrow-up-right-from-square"></i>

                <span>
                    عرض الموقع
                </span>
            </a>

            <a
                href="{{ route('admin.services.create') }}"
                class="dashboard-button dashboard-button-primary"
            >
                <i class="fa-solid fa-plus"></i>

                <span>
                    إضافة خدمة
                </span>
            </a>

        </div>

    </section>

    {{-- الإحصائيات --}}
    <section class="dashboard-statistics">

        {{-- جميع الخدمات --}}
        <article class="stat-card">

            <div class="stat-card-header">

                <span class="stat-card-icon stat-card-icon-primary">
                    <i class="fa-solid fa-layer-group"></i>
                </span>

                <span class="stat-card-label">
                    جميع الخدمات
                </span>

            </div>

            <div class="stat-card-content">

                <strong>
                    {{ number_format($servicesCount ?? 0) }}
                </strong>

                <span>
                    خدمة مسجلة في المنصة
                </span>

            </div>

        </article>

        {{-- الخدمات النشطة --}}
        <article class="stat-card">

            <div class="stat-card-header">

                <span class="stat-card-icon stat-card-icon-success">
                    <i class="fa-solid fa-circle-check"></i>
                </span>

                <span class="stat-card-label">
                    الخدمات النشطة
                </span>

            </div>

            <div class="stat-card-content">

                <strong>
                    {{ number_format($activeServicesCount ?? 0) }}
                </strong>

                <span>
                    خدمة ظاهرة للزوار
                </span>

            </div>

        </article>

        {{-- الخدمات المعطلة --}}
        <article class="stat-card">

            <div class="stat-card-header">

                <span class="stat-card-icon stat-card-icon-warning">
                    <i class="fa-solid fa-circle-pause"></i>
                </span>

                <span class="stat-card-label">
                    الخدمات المعطلة
                </span>

            </div>

            <div class="stat-card-content">

                <strong>
                    {{ number_format($inactiveServicesCount ?? 0) }}
                </strong>

                <span>
                    خدمة غير ظاهرة للزوار
                </span>

            </div>

        </article>

        {{-- الأخبار --}}
        <article class="stat-card">

            <div class="stat-card-header">

                <span class="stat-card-icon stat-card-icon-info">
                    <i class="fa-regular fa-newspaper"></i>
                </span>

                <span class="stat-card-label">
                    الأخبار
                </span>

            </div>

            <div class="stat-card-content">

                <strong>
                    {{ number_format($newsCount ?? 0) }}
                </strong>

                <span>
                    خبر منشور حاليًا
                </span>

            </div>

        </article>

        {{-- الزيارات --}}
        <article class="stat-card">

            <div class="stat-card-header">

                <span class="stat-card-icon stat-card-icon-primary">
                    <i class="fa-solid fa-eye"></i>
                </span>

                <span class="stat-card-label">
                    الزيارات
                </span>

            </div>

            <div class="stat-card-content">

                <strong>
                    {{ number_format($visitsCount ?? 0) }}
                </strong>

                <span>
                    {{ number_format($visitsTodayCount ?? 0) }} زيارة اليوم
                </span>

            </div>

        </article>

    </section>

    <section class="dashboard-grid">

        {{-- الإجراءات السريعة --}}
        <article class="dashboard-panel dashboard-quick-actions">

            <header class="dashboard-panel-header">

                <div>

                    <h3>
                        إجراءات سريعة
                    </h3>

                    <p>
                        وصول مباشر إلى أكثر المهام استخدامًا.
                    </p>

                </div>

            </header>

            <div class="quick-actions-grid">

                {{-- إضافة خدمة --}}
                <a
                    href="{{ route('admin.services.create') }}"
                    class="quick-action-card"
                >

                    <span class="quick-action-icon">
                        <i class="fa-solid fa-plus"></i>
                    </span>

                    <div>

                        <strong>
                            إضافة خدمة
                        </strong>

                        <span>
                            إنشاء خدمة جديدة
                        </span>

                    </div>

                    <i class="fa-solid fa-arrow-left quick-action-arrow"></i>

                </a>

                {{-- إدارة الخدمات --}}
                <a
                    href="{{ route('admin.services.index') }}"
                    class="quick-action-card"
                >

                    <span class="quick-action-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>

                    <div>

                        <strong>
                            إدارة الخدمات
                        </strong>

                        <span>
                            عرض وتعديل جميع الخدمات
                        </span>

                    </div>

                    <i class="fa-solid fa-arrow-left quick-action-arrow"></i>

                </a>

                {{-- إضافة خبر --}}
                <a
                    href="{{ route('admin.news.create') }}"
                    class="quick-action-card"
                >

                    <span class="quick-action-icon">
                        <i class="fa-regular fa-newspaper"></i>
                    </span>

                    <div>

                        <strong>
                            إضافة خبر
                        </strong>

                        <span>
                            نشر خبر تقني جديد
                        </span>

                    </div>

                    <i class="fa-solid fa-arrow-left quick-action-arrow"></i>

                </a>

                {{-- إعدادات المنصة --}}
                <a
                    href="{{ route('admin.settings.index') }}"
                    class="quick-action-card"
                >

                    <span class="quick-action-icon">
                        <i class="fa-solid fa-gear"></i>
                    </span>

                    <div>

                        <strong>
                            إعدادات المنصة
                        </strong>

                        <span>
                            تعديل بيانات النظام
                        </span>

                    </div>

                    <i class="fa-solid fa-arrow-left quick-action-arrow"></i>

                </a>

            </div>

        </article>

        {{-- حالة المنصة --}}
        <article class="dashboard-panel dashboard-summary">

            <header class="dashboard-panel-header">

                <div>

                    <h3>
                        حالة المنصة
                    </h3>

                    <p>
                        ملخص سريع لحالة الأقسام الأساسية.
                    </p>

                </div>

            </header>

            <div class="platform-status-list">

                {{-- الموقع العام --}}
                <div class="platform-status-item">

                    <div class="platform-status-info">

                        <span class="platform-status-dot is-success"></span>

                        <div>

                            <strong>
                                الموقع العام
                            </strong>

                            <span>
                                يعمل بصورة طبيعية
                            </span>

                        </div>

                    </div>

                    <span class="platform-status-badge is-success">
                        متصل
                    </span>

                </div>

                {{-- قاعدة البيانات --}}
                <div class="platform-status-item">

                    <div class="platform-status-info">

                        <span class="platform-status-dot is-success"></span>

                        <div>

                            <strong>
                                قاعدة البيانات
                            </strong>

                            <span>
                                الاتصال متاح
                            </span>

                        </div>

                    </div>

                    <span class="platform-status-badge is-success">
                        مستقرة
                    </span>

                </div>

                {{-- الخدمات --}}
                <div class="platform-status-item">

                    <div class="platform-status-info">

                        @if(($activeServicesCount ?? 0) > 0)

                            <span class="platform-status-dot is-success"></span>

                        @else

                            <span class="platform-status-dot is-warning"></span>

                        @endif

                        <div>

                            <strong>
                                الخدمات
                            </strong>

                            <span>
                                @if(($activeServicesCount ?? 0) > 0)

                                    يوجد
                                    {{ number_format($activeServicesCount) }}
                                    خدمة نشطة

                                @else

                                    لم تتم إضافة خدمات نشطة بعد

                                @endif
                            </span>

                        </div>

                    </div>

                    @if(($activeServicesCount ?? 0) > 0)

                        <span class="platform-status-badge is-success">
                            جاهزة
                        </span>

                    @else

                        <span class="platform-status-badge is-warning">
                            بانتظار المحتوى
                        </span>

                    @endif

                </div>

                {{-- الأخبار --}}
                <div class="platform-status-item">

                    <div class="platform-status-info">

                        @if (($newsCount ?? 0) > 0)
                            <span class="platform-status-dot is-success"></span>
                        @else
                            <span class="platform-status-dot is-warning"></span>
                        @endif

                        <div>

                            <strong>
                                الأخبار
                            </strong>

                            <span>
                                @if (($newsCount ?? 0) > 0)
                                    يوجد {{ number_format($newsCount) }} خبر منشور
                                @else
                                    لم تتم إضافة أخبار بعد
                                @endif
                            </span>

                        </div>

                    </div>

                    <a
                        href="{{ route('news.index') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="platform-status-badge {{ ($newsCount ?? 0) > 0 ? 'is-success' : 'is-warning' }}"
                    >
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        عرض الأخبار
                    </a>

                </div>

                {{-- المجتمع --}}
                <div class="platform-status-item">

                    <div class="platform-status-info">

                        @if (($communityCount ?? 0) > 0)
                            <span class="platform-status-dot is-success"></span>
                        @else
                            <span class="platform-status-dot is-warning"></span>
                        @endif

                        <div>

                            <strong>
                                المجتمع
                            </strong>

                            <span>
                                @if (($communityCount ?? 0) > 0)
                                    يوجد {{ number_format($communityCount) }} منشور نشط
                                @else
                                    لم تتم إضافة منشورات بعد
                                @endif
                            </span>

                        </div>

                    </div>

                    <a
                        href="{{ route('community.index') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="platform-status-badge {{ ($communityCount ?? 0) > 0 ? 'is-success' : 'is-warning' }}"
                    >
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        عرض المجتمع
                    </a>

                </div>

            </div>

        </article>

    </section>

    {{-- أهم الدول --}}
    <section class="dashboard-panel" style="margin-top: 24px;">

        <header class="dashboard-panel-header">

            <div>

                <h3>
                    أهم الدول زيارة
                </h3>

                <p>
                    توزيع الزيارات حسب بلد الزائر (يُحدَّد تلقائيًا).
                </p>

            </div>

        </header>

        @if ($topCountries->isEmpty())

            <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">
                لا توجد بيانات كافية بعد.
            </p>

        @else

            <div class="platform-status-list">

                @foreach ($topCountries as $country)

                    @php
                        $flag = '';

                        if ($country->country_code) {
                            $flag = mb_convert_encoding(
                                '&#' . (127397 + ord($country->country_code[0])) . ';'
                                . '&#' . (127397 + ord($country->country_code[1])) . ';',
                                'UTF-8',
                                'HTML-ENTITIES'
                            );
                        }
                    @endphp

                    <div class="platform-status-item">

                        <div class="platform-status-info">

                            <span class="platform-status-dot is-success"></span>

                            <div>
                                <strong>
                                    {{ $flag }} {{ $country->country_name }}
                                </strong>
                            </div>

                        </div>

                        <span class="platform-status-badge is-success">
                            {{ number_format($country->visits_count) }} زيارة
                        </span>

                    </div>

                @endforeach

            </div>

        @endif

    </section>

@endsection