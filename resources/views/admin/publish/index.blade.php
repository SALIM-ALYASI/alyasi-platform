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

            <div class="form-group full">
                <label>نوع المحتوى</label>

                @php $mediaType = old('media_type', 'both'); @endphp

                <div id="generate-media-type" style="display:flex; flex-wrap:wrap; gap:16px; margin-top:6px;">
                    @foreach (['image' => 'صورة فقط', 'video' => 'فيديو فقط', 'both' => 'الاثنين'] as $value => $label)
                        <label style="display:flex; align-items:center; gap:6px; font-weight:normal; cursor:pointer;">
                            <input type="radio" name="media_type" value="{{ $value }}" @checked($mediaType === $value)>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group full">
                <label>المنصات</label>

                @php
                    $genPlatforms = [
                        'instagram' => ['label' => 'إنستغرام', 'image' => true],
                        'facebook' => ['label' => 'فيسبوك', 'image' => true],
                        'linkedin' => ['label' => 'لينكدإن', 'image' => true],
                        'youtube' => ['label' => 'يوتيوب', 'image' => false],
                        'telegram' => ['label' => 'تلجرام', 'image' => true],
                    ];
                    $selectedGenPlatforms = old('platforms', array_keys($genPlatforms));
                @endphp

                <div id="generate-platforms" style="display:flex; flex-wrap:wrap; gap:16px; margin-top:6px;">
                    @foreach ($genPlatforms as $value => $meta)
                        <label
                            data-platform="{{ $value }}"
                            data-supports-image="{{ $meta['image'] ? '1' : '0' }}"
                            style="display:flex; align-items:center; gap:6px; font-weight:normal; cursor:pointer;"
                        >
                            <input type="checkbox" name="platforms[]" value="{{ $value }}" @checked(in_array($value, $selectedGenPlatforms))>
                            {{ $meta['label'] }}
                        </label>
                    @endforeach
                </div>

                <p id="generate-platforms-hint" style="margin-top:6px; font-size:12px; color:var(--admin-muted);"></p>
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

                                    @php
                                        $platformLabels = [
                                            'instagram' => 'إنستغرام', 'facebook' => 'فيسبوك', 'linkedin' => 'لينكدإن',
                                            'youtube' => 'يوتيوب', 'telegram' => 'تلجرام',
                                        ];
                                        // نوع الوسائط محسوم خلاص وقت التوليد — هنا فقط نعرضه ونأكد المنصات.
                                        $requestedPlatforms = $job->smart_content_data['platforms_requested'] ?? [];
                                    @endphp

                                    <form action="{{ route('admin.publish.approve', $job->job_id) }}" method="POST" style="margin-top:12px;">
                                        @csrf

                                        <div style="display:flex; flex-direction:column; gap:8px;">
                                            @foreach ($requestedPlatforms as $value => $format)
                                                <label style="display:flex; align-items:center; gap:6px; font-weight:normal; font-size:13px; cursor:pointer;">
                                                    <input type="checkbox" name="platforms[]" value="{{ $value }}" checked>
                                                    {{ $platformLabels[$value] ?? $value }}
                                                    <span style="color:var(--admin-muted); font-size:12px;">
                                                        ({{ $format === 'image' ? 'صورة' : 'فيديو' }})
                                                    </span>
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
                                'facebook' => ['label' => 'فيسبوك', 'icon' => 'fa-brands fa-facebook'],
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
                                        @elseif (in_array($info['status'], ['done', 'published'], true))
                                            <span style="color:#22c55e;">
                                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                                تم
                                                @if (! empty($info['format']))
                                                    ({{ $info['format'] === 'image' ? 'صورة' : 'فيديو' }})
                                                @endif
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
                                                فشل نهائياً
                                            </span>
                                        @elseif ($info['status'] === 'retrying')
                                            <span style="color:#f59e0b;" title="{{ $info['error'] ?? '' }}">
                                                <i class="fa-solid fa-rotate" aria-hidden="true"></i>
                                                إعادة محاولة
                                                @if (! empty($info['next_retry_at']))
                                                    ({{ \Illuminate\Support\Carbon::parse($info['next_retry_at'])->diffForHumans() }})
                                                @endif
                                            </span>
                                        @elseif ($info['status'] === 'publishing')
                                            <span style="color:#f59e0b;">
                                                <i class="fa-solid fa-spinner" aria-hidden="true"></i>
                                                جاري النشر
                                            </span>
                                        @else
                                            <span style="color:var(--admin-muted);">
                                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                                مجدول
                                                @if (! empty($info['scheduled_at']))
                                                    ({{ \Illuminate\Support\Carbon::parse($info['scheduled_at'])->diffForHumans() }})
                                                @endif
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

@push('scripts')
<script>
(function () {
    var IMAGE_ONLY_LABEL = 'يوتيوب غير متاح مع "صورة فقط" — يقبل فيديو بس.';
    var mediaTypeInputs = document.querySelectorAll('#generate-media-type input[name="media_type"]');
    var platformLabels = document.querySelectorAll('#generate-platforms label[data-platform]');
    var hint = document.getElementById('generate-platforms-hint');

    function applyMediaType() {
        var selected = document.querySelector('#generate-media-type input[name="media_type"]:checked');
        var mediaType = selected ? selected.value : 'both';
        var anyDisabled = false;

        platformLabels.forEach(function (label) {
            var supportsImage = label.dataset.supportsImage === '1';
            var checkbox = label.querySelector('input[type="checkbox"]');
            var mustDisable = mediaType === 'image' && !supportsImage;

            checkbox.disabled = mustDisable;
            label.style.opacity = mustDisable ? '0.45' : '1';
            if (mustDisable) {
                checkbox.checked = false;
                anyDisabled = true;
            }
        });

        hint.textContent = anyDisabled ? IMAGE_ONLY_LABEL : '';
    }

    mediaTypeInputs.forEach(function (input) {
        input.addEventListener('change', applyMediaType);
    });

    applyMediaType();
})();
</script>
@endpush

@endsection
