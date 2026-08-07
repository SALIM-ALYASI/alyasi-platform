@extends('admin.layouts.app')

@section('title', 'رسائل التواصل')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">
                رسائل التواصل
            </h1>

            <p class="admin-page-header__description">
                الرسائل المرسلة عبر نموذج التواصل بصفحة "تواصل معنا".
            </p>

        </div>

    </div>

    <section class="admin-stats-row">

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-regular fa-envelope-open" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>رسائل جديدة</span>
                <strong>{{ number_format($newCount) }}</strong>
            </div>
        </article>

    </section>

    <section class="admin-filter-panel">

        <form
            action="{{ route('admin.messages.index') }}"
            method="GET"
            class="admin-filter-form"
        >

            <div class="admin-filter-form__field">

                <label for="message-status">الحالة</label>

                <select id="message-status" name="status">
                    <option value="" @selected($status === '')>كل الحالات</option>
                    <option value="new" @selected($status === 'new')>جديدة</option>
                    <option value="read" @selected($status === 'read')>مقروءة</option>
                    <option value="replied" @selected($status === 'replied')>تم الرد عليها</option>
                </select>

            </div>

            <div class="admin-filter-form__actions">

                <button type="submit" class="admin-filter-button">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                    تطبيق
                </button>

                @if ($status !== '')
                    <a href="{{ route('admin.messages.index') }}" class="admin-reset-button">
                        <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                        إعادة تعيين
                    </a>
                @endif

            </div>

        </form>

    </section>

    <section class="admin-list-panel">

        <div class="admin-list-panel__header">

            <div>
                <h2>قائمة الرسائل</h2>
                <p>اطّلع على الرسائل وتابع الرد عليها.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($messages->total()) }}
                رسالة
            </span>

        </div>

        @if ($messages->isNotEmpty())

            @php
                $statusLabels = [
                    'new' => 'جديدة',
                    'read' => 'مقروءة',
                    'replied' => 'تم الرد عليها',
                ];

                $statusClasses = [
                    'new' => 'admin-status--featured',
                    'read' => 'admin-status--inactive',
                    'replied' => 'admin-status--active',
                ];
            @endphp

            <div class="admin-card-grid">

                @foreach ($messages as $message)

                    <article class="admin-data-card">

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $message->name }}</h3>

                                    <p style="margin: 4px 0 0; font-size: 0.8rem; opacity: 0.6;" dir="ltr">
                                        {{ $message->email }}
                                    </p>
                                </div>

                                <span class="admin-status {{ $statusClasses[$message->status] ?? 'admin-status--inactive' }}">
                                    {{ $statusLabels[$message->status] ?? $message->status }}
                                </span>
                            </div>

                            @if($message->subject)
                                <p style="margin: 6px 0; color: #fff; font-weight: 600; font-size: 0.9rem;">
                                    {{ $message->subject }}
                                </p>
                            @endif

                            <p class="admin-data-card__description">
                                {{ \Illuminate\Support\Str::limit($message->message, 140) }}
                            </p>

                            <p style="margin: 8px 0 0; font-size: 0.78rem; opacity: 0.5;">
                                {{ $message->created_at->translatedFormat('d F Y - h:i A') }}
                            </p>

                        </div>

                        <div class="admin-data-card__actions">

                            <a
                                href="{{ route('admin.messages.show', $message) }}"
                                class="admin-action-button"
                                title="عرض الرسالة"
                                aria-label="عرض الرسالة"
                            >
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </a>

                            <form
                                action="{{ route('admin.messages.destroy', $message) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة؟');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حذف"
                                    aria-label="حذف الرسالة"
                                >
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($messages->hasPages())
                <div class="admin-pagination">
                    {{ $messages->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                </div>

                <h3>لا توجد رسائل</h3>

                <p>لم يُرسل أي زائر رسالة عبر نموذج التواصل حتى الآن.</p>

            </div>

        @endif

    </section>

</div>

@endsection
