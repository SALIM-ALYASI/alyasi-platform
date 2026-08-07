@extends('admin.layouts.app')

@section('title', 'تصحيحات النطق')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-spell-check" aria-hidden="true"></i>
                استوديو الصوت
            </span>

            <h1 class="admin-page-header__title">
                تصحيحات النطق
            </h1>

            <p class="admin-page-header__description">
                اقتراحات تصحيح نطق اكتشفها فحص تعليقات يوتيوب اليومي، بانتظار
                اعتمادك. القبول يضيف الكلمة مباشرة لقاموس نطق SoundInk الرسمي
                (يُطبَّق تلقائيًا على كل صوت جديد).
            </p>

        </div>

    </div>

    @if ($error)

        <div class="admin-empty-state">

            <div class="admin-empty-state__icon">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            </div>

            <h3>تعذّر جلب القائمة</h3>

            <p>{{ $error }}</p>

        </div>

    @elseif (count($items) > 0)

        <section class="admin-list-panel">

            <div class="admin-list-panel__header">

                <div>
                    <h2>اقتراحات بانتظار المراجعة</h2>
                    <p>راجع كل اقتراح مع سياق التعليق قبل القبول أو الرفض.</p>
                </div>

                <span class="admin-results-count">
                    {{ number_format(count($items)) }}
                    اقتراح
                </span>

            </div>

            <div class="admin-card-grid">

                @foreach ($items as $item)

                    <article class="admin-data-card">

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $item['originalWord'] ?? '—' }} ← {{ $item['suggestedPronunciation'] ?? '—' }}</h3>

                                    @if (! empty($item['videoTitle']))
                                        <p style="margin: 4px 0 0; font-size: 0.8rem; opacity: 0.6;">
                                            الفيديو: {{ $item['videoTitle'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if (! empty($item['commentText']))
                                <p class="admin-data-card__description">
                                    "{{ $item['commentText'] }}"
                                </p>
                            @endif

                        </div>

                        <div class="admin-data-card__actions">

                            <form
                                action="{{ route('admin.pronunciation-queue.accept', $item['id']) }}"
                                method="POST"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="admin-action-button"
                                    title="قبول وإضافة لقاموس النطق"
                                    aria-label="قبول"
                                >
                                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                                </button>
                            </form>

                            <form
                                action="{{ route('admin.pronunciation-queue.reject', $item['id']) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من رفض هذا الاقتراح؟');"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="رفض"
                                    aria-label="رفض"
                                >
                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

        </section>

    @else

        <div class="admin-empty-state">

            <div class="admin-empty-state__icon">
                <i class="fa-solid fa-spell-check" aria-hidden="true"></i>
            </div>

            <h3>لا توجد اقتراحات حالياً</h3>

            <p>فحص تعليقات يوتيوب يشتغل يومياً — أي اقتراح جديد بيظهر هنا.</p>

        </div>

    @endif

</div>

@endsection
