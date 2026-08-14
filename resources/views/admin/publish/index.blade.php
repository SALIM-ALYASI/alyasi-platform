@extends('admin.layouts.app')

@section('title', 'النشر الذكي')

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>النشر الذكي</h2>

        <p>
            اكتب نصاً مختصراً وولّد إعلاناً كاملاً بالذكاء الاصطناعي (عنوان، وصف، صورة مربعة،
            صورة Story، وفيديو)، راجعه، ثم اختر المنصات اللي تبي تنشر عليها بعد الاعتماد.
        </p>
    </div>
</section>

<section class="dashboard-panel">

    <div class="dashboard-panel-header">
        <h3>توليد إعلان بالذكاء الاصطناعي</h3>
        <p>
            اكتب نصاً مختصراً فقط — يتولّد تلقائياً: عنوان، وصف، صورة مربعة، صورة Story،
            وفيديو عمودي 15 ثانية، جاهزة للمراجعة قبل أي نشر.
        </p>
    </div>

    <form
        id="generate-form"
        action="{{ route('admin.publish.generate') }}"
        method="POST"
    >
        @csrf

        <div class="form-grid">

            <div class="form-group full">
                <label for="generate-text">
                    نص الإعلان
                    <span class="text-danger">*</span>
                </label>

                <textarea
                    id="generate-text"
                    name="text"
                    class="form-control"
                    rows="3"
                    maxlength="1000"
                    placeholder="مثال: نحن نصمم مواقع"
                    required
                >{{ old('text') }}</textarea>
            </div>

            <div class="form-group">
                <label for="generate-contact">وسيلة التواصل (اختياري)</label>

                <input
                    type="text"
                    id="generate-contact"
                    name="contact"
                    class="form-control"
                    maxlength="100"
                    value="{{ old('contact', 'alyasi.dev') }}"
                >
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="dashboard-button dashboard-button-primary">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>ولّد الإعلان</span>
            </button>
        </div>

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

                            @if ($job->source === 'smart_content')
                                <span style="margin-right:8px; color:var(--admin-muted);">
                                    <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
                                    مولّد بالذكاء الاصطناعي
                                </span>
                            @endif
                        </p>

                        @if ($job->source === 'smart_content')

                            @if (in_array($job->status, ['queued', 'processing'], true))

                                <p style="font-size:13px; color:#f59e0b; margin-top:10px;">
                                    <i class="fa-solid fa-spinner" aria-hidden="true"></i>
                                    جاري التوليد... أعد تحميل الصفحة بعد قليل.
                                </p>

                            @elseif ($job->status === 'failed')

                                <p style="font-size:13px; color:#ef4444; margin-top:10px;">
                                    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                                    فشل التوليد: {{ $job->smart_content_data['error'] ?? 'خطأ غير معروف' }}
                                </p>

                            @elseif ($job->status === 'ready')

                                @php
                                    $copy = $job->smart_content_data['copy'] ?? null;
                                    $assets = $job->smart_content_data['assets'] ?? [];
                                    $alreadyPublished = ! empty($job->platforms);
                                @endphp

                                @if ($copy)
                                    <p style="font-size:13px; margin-top:10px;">{{ $copy['subtitle'] ?? '' }}</p>
                                @endif

                                <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:8px;">
                                    @if (! empty($assets['square']['download_url']))
                                        <a href="{{ $assets['square']['download_url'] }}" target="_blank" rel="noopener" style="font-size:12px;">
                                            <i class="fa-regular fa-image" aria-hidden="true"></i> مربعة
                                        </a>
                                    @endif
                                    @if (! empty($assets['story']['download_url']))
                                        <a href="{{ $assets['story']['download_url'] }}" target="_blank" rel="noopener" style="font-size:12px;">
                                            <i class="fa-regular fa-image" aria-hidden="true"></i> Story
                                        </a>
                                    @endif
                                    @if (! empty($assets['video']['download_url']))
                                        <a href="{{ $assets['video']['download_url'] }}" target="_blank" rel="noopener" style="font-size:12px;">
                                            <i class="fa-solid fa-video" aria-hidden="true"></i> الفيديو
                                        </a>
                                    @endif
                                </div>

                                @unless ($alreadyPublished)

                                    <form action="{{ route('admin.publish.approve', $job->job_id) }}" method="POST" style="margin-top:12px;">
                                        @csrf

                                        <div style="display:flex; flex-wrap:wrap; gap:12px;">
                                            @foreach (['instagram' => 'إنستغرام', 'linkedin' => 'لينكدإن', 'youtube' => 'يوتيوب', 'telegram' => 'تلجرام'] as $value => $label)
                                                <label style="display:flex; align-items:center; gap:6px; font-weight:normal; font-size:13px; cursor:pointer;">
                                                    <input type="checkbox" name="platforms[]" value="{{ $value }}" checked>
                                                    {{ $label }}
                                                </label>
                                            @endforeach
                                        </div>

                                        <button type="submit" class="dashboard-button dashboard-button-primary" style="margin-top:10px; font-size:13px; padding:6px 14px;">
                                            <i class="fa-solid fa-check"></i>
                                            اعتماد ونشر
                                        </button>
                                    </form>

                                @endunless

                            @endif

                        @endif

                        <div style="display:flex; flex-direction:column; gap:10px; margin-top:14px;">

                            @foreach ([
                                'instagram' => ['label' => 'إنستغرام', 'icon' => 'fa-brands fa-instagram'],
                                'telegram' => ['label' => 'تلجرام', 'icon' => 'fa-brands fa-telegram'],
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
