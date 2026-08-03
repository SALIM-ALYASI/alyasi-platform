@extends('admin.layouts.app')

@section('title', 'النشر الذكي')

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>النشر الذكي</h2>

        <p>
            ارفع صورة أو فيديو مع نص، وينشر تلقائياً على إنستغرام ولينكدإن ويوتيوب وتيك توك،
            كل منصة بتوقيت وأسلوب يناسب خوارزميتها.
        </p>
    </div>
</section>

<section class="dashboard-panel">

    <form
        action="{{ route('admin.publish.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="form-grid">

            <div class="form-group full">
                <label for="title">العنوان (اختياري)</label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control"
                    maxlength="90"
                    value="{{ old('title') }}"
                    placeholder="لو تركته فاضي، أول سطر من النص يُستخدم كعنوان"
                >
            </div>

            <div class="form-group full">
                <label for="text">
                    النص
                    <span class="text-danger">*</span>
                </label>

                <textarea
                    id="text"
                    name="text"
                    class="form-control"
                    rows="6"
                    maxlength="3000"
                    placeholder="اكتب نص المنشور..."
                    required
                >{{ old('text') }}</textarea>
            </div>

            <div class="form-group">
                <label for="image">الصورة</label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    class="form-control"
                    accept="image/*"
                >

                <p style="margin-top:6px; font-size:12px; color:var(--admin-muted);">
                    تُستخدم مباشرة لإنستغرام ولينكدإن، وتتحول تلقائياً لفيديو ليوتيوب وتيك توك.
                </p>
            </div>

            <div class="form-group">
                <label for="video">الفيديو (اختياري)</label>

                <input
                    type="file"
                    id="video"
                    name="video"
                    class="form-control"
                    accept="video/mp4,video/quicktime"
                >

                <p style="margin-top:6px; font-size:12px; color:var(--admin-muted);">
                    لو رفعت فيديو جاهز، يُستخدم كما هو ليوتيوب وتيك توك بدل التوليد التلقائي.
                </p>
            </div>

        </div>

        <div class="form-actions">
            <button
                type="submit"
                class="dashboard-button dashboard-button-primary"
            >
                <i class="fa-solid fa-paper-plane"></i>
                <span>انشر الآن</span>
            </button>
        </div>

        <p style="margin-top:10px; font-size:12px; color:var(--admin-muted);">
            لازم ترفع صورة أو فيديو على الأقل. النشر يشمل حساباتكم الحقيقية على المنصات الأربع.
        </p>

    </form>

</section>

<section class="dashboard-panel" style="margin-top:24px;">

    <div class="dashboard-panel-header">
        <h3>آخر عمليات النشر</h3>
        <p>تتحدث حالة كل منصة هنا تلقائياً فور اكتمال النشر عليها (أعد تحميل الصفحة لأحدث حالة).</p>
    </div>

    @if ($jobs->isEmpty())

        <p style="padding:20px; color:var(--admin-muted);">
            لا توجد عمليات نشر بعد.
        </p>

    @else

        <div class="admin-card-grid" style="padding:0 20px 20px;">

            @foreach ($jobs as $job)

                <article class="admin-data-card">

                    <div class="admin-data-card__body">

                        <div class="admin-data-card__heading">
                            <div>
                                <h3>{{ $job->title ?: \Illuminate\Support\Str::limit($job->text, 40) }}</h3>
                            </div>
                        </div>

                        <p class="admin-data-card__description" style="font-size:12px;">
                            <i class="fa-regular fa-clock" aria-hidden="true"></i>
                            {{ $job->created_at->diffForHumans() }}
                        </p>

                        <div style="display:flex; flex-direction:column; gap:10px; margin-top:14px;">

                            @foreach ([
                                'instagram' => ['label' => 'إنستغرام', 'icon' => 'fa-brands fa-instagram'],
                                'linkedin' => ['label' => 'لينكدإن', 'icon' => 'fa-brands fa-linkedin'],
                                'youtube' => ['label' => 'يوتيوب', 'icon' => 'fa-brands fa-youtube'],
                                'tiktok' => ['label' => 'تيك توك', 'icon' => 'fa-brands fa-tiktok'],
                            ] as $platform => $meta)

                                @php $info = $job->platforms[$platform] ?? null; @endphp

                                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">

                                    <span style="display:flex; align-items:center; gap:8px;">
                                        <i class="{{ $meta['icon'] }}" aria-hidden="true"></i>
                                        {{ $meta['label'] }}
                                    </span>

                                    <span style="text-align:left;">
                                        @if (! $info)
                                            <span style="color:var(--admin-muted);">—</span>
                                        @elseif ($info['status'] === 'done')
                                            <span style="color:#22c55e;">
                                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                                تم
                                            </span>
                                            @if (! empty($info['result']['url']))
                                                <br>
                                                <a href="{{ $info['result']['url'] }}" target="_blank" rel="noopener" style="font-size:12px;">
                                                    عرض المنشور
                                                </a>
                                            @endif
                                        @elseif ($info['status'] === 'failed')
                                            <span style="color:#ef4444;" title="{{ $info['error'] ?? '' }}">
                                                <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                                                فشل
                                            </span>
                                        @elseif ($info['status'] === 'publishing')
                                            <span style="color:#f59e0b;">
                                                <i class="fa-solid fa-spinner" aria-hidden="true"></i>
                                                جاري النشر
                                            </span>
                                        @else
                                            <span style="color:var(--admin-muted);">
                                                مجدول ({{ $info['delayMinutes'] ?? 0 }}د)
                                            </span>
                                        @endif
                                    </span>

                                </div>

                            @endforeach

                        </div>

                    </div>

                </article>

            @endforeach

        </div>

    @endif

</section>

@endsection
