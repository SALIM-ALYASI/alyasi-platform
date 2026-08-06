@extends('admin.layouts.app')

@section('title', 'تعليقات المجتمع')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-comments" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">
                تعليقات المجتمع
            </h1>

            <p class="admin-page-header__description">
                راجع تعليقات الزوار على منشورات المجتمع قبل نشرها للعامة.
            </p>

        </div>

    </div>

    <section class="admin-stats-row">

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>بانتظار المراجعة</span>
                <strong>{{ number_format($pendingCount) }}</strong>
            </div>
        </article>

    </section>

    <section class="admin-filter-panel">

        <form
            action="{{ route('admin.community-comments.index') }}"
            method="GET"
            class="admin-filter-form"
        >

            <div class="admin-filter-form__field">

                <label for="comment-status">الحالة</label>

                <select id="comment-status" name="status">
                    <option value="" @selected($status === '')>كل الحالات</option>
                    <option value="pending" @selected($status === 'pending')>بانتظار المراجعة</option>
                    <option value="approved" @selected($status === 'approved')>معتمد</option>
                    <option value="rejected" @selected($status === 'rejected')>مرفوض</option>
                </select>

            </div>

            <div class="admin-filter-form__actions">

                <button type="submit" class="admin-filter-button">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                    تطبيق
                </button>

                @if ($status !== '')
                    <a href="{{ route('admin.community-comments.index') }}" class="admin-reset-button">
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
                <h2>قائمة التعليقات</h2>
                <p>اعتماد أو رفض تعليقات زوار المجتمع.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($comments->total()) }}
                تعليق
            </span>

        </div>

        @if ($comments->isNotEmpty())

            @php
                $statusLabels = [
                    'pending' => 'بانتظار المراجعة',
                    'approved' => 'معتمد',
                    'rejected' => 'مرفوض',
                ];

                $statusClasses = [
                    'pending' => 'admin-status--featured',
                    'approved' => 'admin-status--active',
                    'rejected' => 'admin-status--inactive',
                ];
            @endphp

            <div class="admin-card-grid">

                @foreach ($comments as $comment)

                    <article class="admin-data-card">

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $comment->name }}</h3>

                                    @if ($comment->communityPost)
                                        <p style="margin: 4px 0 0; font-size: 0.8rem; opacity: 0.6;">
                                            على: {{ $comment->communityPost->title }}
                                        </p>
                                    @endif
                                </div>

                                <span class="admin-status {{ $statusClasses[$comment->status] ?? 'admin-status--inactive' }}">
                                    {{ $statusLabels[$comment->status] ?? $comment->status }}
                                </span>
                            </div>

                            <p class="admin-data-card__description">
                                {{ $comment->body }}
                            </p>

                            <p style="margin: 8px 0 0; font-size: 0.78rem; opacity: 0.5;">
                                {{ $comment->created_at->translatedFormat('d F Y - h:i A') }}
                                @if ($comment->email)
                                    · {{ $comment->email }}
                                @endif
                            </p>

                        </div>

                        <div class="admin-data-card__actions">

                            @if ($comment->status !== 'approved')
                                <form
                                    action="{{ route('admin.community-comments.approve', $comment) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="admin-action-button"
                                        title="اعتماد التعليق"
                                        aria-label="اعتماد التعليق"
                                    >
                                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @endif

                            @if ($comment->status !== 'rejected')
                                <form
                                    action="{{ route('admin.community-comments.reject', $comment) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="admin-action-button"
                                        title="رفض التعليق"
                                        aria-label="رفض التعليق"
                                    >
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @endif

                            <form
                                action="{{ route('admin.community-comments.block', $comment) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حظر هذا الزائر؟ سيتم رفض جميع تعليقاته الحالية.');"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حظر الزائر"
                                    aria-label="حظر الزائر"
                                >
                                    <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
                                </button>
                            </form>

                            <form
                                action="{{ route('admin.community-comments.destroy', $comment) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذا التعليق؟');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حذف"
                                    aria-label="حذف التعليق"
                                >
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($comments->hasPages())
                <div class="admin-pagination">
                    {{ $comments->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-comments" aria-hidden="true"></i>
                </div>

                <h3>لا توجد تعليقات</h3>

                <p>لم يُضف أي زائر تعليقًا حتى الآن.</p>

            </div>

        @endif

    </section>

</div>

@endsection
