@extends('admin.layouts.app')

@section('title', 'الزوار المحظورون')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
                الحماية والإزعاج
            </span>

            <h1 class="admin-page-header__title">
                الزوار المحظورون
            </h1>

            <p class="admin-page-header__description">
                الزوار الممنوعون من إضافة تعليقات جديدة على صفحات المجتمع، بناءً على معرّف الجهاز أو عنوان الـ IP.
            </p>

        </div>

    </div>

    <section class="admin-list-panel">

        <div class="admin-list-panel__header">

            <div>
                <h2>قائمة الزوار المحظورين</h2>
                <p>يمكنك رفع الحظر عن أي زائر في أي وقت.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($blockedVisitors->total()) }}
                زائر
            </span>

        </div>

        @if ($blockedVisitors->isNotEmpty())

            <div class="admin-card-grid">

                @foreach ($blockedVisitors as $blockedVisitor)

                    <article class="admin-data-card">

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $blockedVisitor->ip_address ?: '—' }}</h3>

                                    <p style="margin: 4px 0 0; font-size: 0.78rem; opacity: 0.6;">
                                        معرّف الجهاز:
                                        {{ $blockedVisitor->device_token
                                            ? \Illuminate\Support\Str::limit($blockedVisitor->device_token, 18)
                                            : '—' }}
                                    </p>
                                </div>
                            </div>

                            @if ($blockedVisitor->reason)
                                <p class="admin-data-card__description">
                                    {{ $blockedVisitor->reason }}
                                </p>
                            @endif

                            <p style="margin: 8px 0 0; font-size: 0.78rem; opacity: 0.5;">
                                محظور منذ {{ $blockedVisitor->created_at->translatedFormat('d F Y') }}
                            </p>

                        </div>

                        <div class="admin-data-card__actions">

                            <form
                                action="{{ route('admin.blocked-visitors.destroy', $blockedVisitor) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من رفع الحظر عن هذا الزائر؟');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button"
                                    title="رفع الحظر"
                                    aria-label="رفع الحظر"
                                >
                                    <i class="fa-solid fa-unlock" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($blockedVisitors->hasPages())
                <div class="admin-pagination">
                    {{ $blockedVisitors->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
                </div>

                <h3>لا يوجد زوار محظورون</h3>

                <p>يمكنك حظر زائر من صفحة تعليقات المجتمع.</p>

                <a href="{{ route('admin.community-comments.index') }}" class="admin-primary-button">
                    <i class="fa-solid fa-comments" aria-hidden="true"></i>
                    الانتقال إلى تعليقات المجتمع
                </a>

            </div>

        @endif

    </section>

</div>

@endsection
