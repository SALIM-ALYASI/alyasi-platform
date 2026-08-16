@extends('admin.layouts.app')

@section('title', 'تنظيف روابط الخدمات')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-link-slash" aria-hidden="true"></i>
                صيانة الروابط
            </span>

            <h1 class="admin-page-header__title">
                تنظيف روابط الخدمات
            </h1>

            <p class="admin-page-header__description">
                يعرض روابط الخدمات القديمة المخزّنة بنص عربي خام (تظهر مرمّزة %D8%… عند المشاركة) ويصلحها لصيغة إنجليزية نظيفة.
                هذي الصفحة <strong>معاينة فقط</strong> — ما فيها أي تعديل تلقائي؛ التنفيذ يصير فقط بعد ضغطك على زر التنفيذ أدناه.
            </p>

        </div>

    </div>

    <div class="dashboard-panel">

        <div class="dashboard-panel-header">
            <div>
                <h3>نتيجة المعاينة (Dry Run)</h3>
                <p>
                    {{ $fixableCount }} رابط جاهز للإصلاح التلقائي،
                    و{{ $manualCount }} يحتاج تعديل يدوي (خدمة بلا عنوان إنجليزي).
                </p>
            </div>

            <a href="{{ route('admin.service-slug-cleanup.index') }}" class="admin-action-button">
                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                تحديث المعاينة
            </a>
        </div>

        @if ($rows->isEmpty())

            <p style="padding: 20px 0; opacity: .7;">
                لا يوجد أي رابط خدمة يحتاج تصحيح حاليًا. كل الروابط نظيفة. 🎉
            </p>

        @else

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>الخدمة</th>
                            <th>اللغة</th>
                            <th>الرابط الحالي (القديم)</th>
                            <th>الرابط المقترح (الجديد)</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>
                                    {{ $row['service_title_ar'] }}
                                    @if ($row['service_id'])
                                        <br>
                                        <a href="{{ route('admin.services.edit', $row['service_id']) }}" style="font-size: .78rem; opacity: .7;">
                                            فتح بلوحة التحكم
                                        </a>
                                    @endif
                                </td>
                                <td>{{ strtoupper($row['locale']) }}</td>
                                <td dir="ltr" style="text-align: right; word-break: break-all;">{{ $row['old_slug'] }}</td>
                                <td dir="ltr" style="text-align: right;">
                                    {{ $row['new_slug'] ?? '—' }}
                                </td>
                                <td>
                                    @if ($row['fixable'])
                                        <span class="admin-status admin-status--active">جاهز للإصلاح</span>
                                    @else
                                        <span class="admin-status admin-status--inactive">يحتاج عنوان إنجليزي</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($fixableCount > 0)

                <form
                    action="{{ route('admin.service-slug-cleanup.execute') }}"
                    method="POST"
                    style="margin-top: 24px;"
                    onsubmit="return confirm('سيتم تعديل {{ $fixableCount }} رابط فعليًا بقاعدة البيانات (مع حفظ تحويل 301 تلقائي للرابط القديم). هل أنت متأكد؟');"
                >
                    @csrf

                    <button type="submit" class="admin-primary-button">
                        <i class="fa-solid fa-wrench" aria-hidden="true"></i>
                        تنفيذ الإصلاح الآن ({{ $fixableCount }} رابط)
                    </button>
                </form>

            @endif

        @endif

    </div>

</div>

@endsection
