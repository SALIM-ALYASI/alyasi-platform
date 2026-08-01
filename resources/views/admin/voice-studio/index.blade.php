@extends('admin.layouts.app')

@section('title', 'استوديو الصوت')

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>استوديو الصوت</h2>

        <p>
            حوّل أي نص إلى مقطع صوتي مباشرة عبر خدمة SoundInk.
        </p>
    </div>
</section>

@if (! $serviceAvailable)

    <div class="admin-alert admin-alert-danger">
        <i class="fa-solid fa-triangle-exclamation"></i>

        <span>
            تعذر الاتصال بخدمة SoundInk ({{ config('services.soundink.url') }}).
            تأكد من أن الخدمة شغّالة، ثم أعد تحميل الصفحة.
        </span>
    </div>

@endif

<section class="dashboard-panel">

    <form
        action="{{ route('admin.voice-studio.generate') }}"
        method="POST"
    >
        @csrf

        <div class="form-grid">

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
                    maxlength="2000"
                    placeholder="اكتب النص المراد تحويله لصوت..."
                    required
                    @disabled(! $serviceAvailable)
                >{{ old('text') }}</textarea>
            </div>

            <div class="form-group">
                <label for="voice_id">الصوت</label>

                <select
                    id="voice_id"
                    name="voice_id"
                    class="form-control"
                    @disabled(! $serviceAvailable)
                >
                    @forelse ($voices as $voice)
                        <option
                            value="{{ $voice['id'] }}"
                            @selected(old('voice_id', $defaultVoiceId) === $voice['id'])
                        >
                            {{ $voice['label'] ?? $voice['id'] }}
                        </option>
                    @empty
                        <option value="">لا توجد أصوات متاحة</option>
                    @endforelse
                </select>
            </div>

            <div class="form-group">
                <label for="speed">السرعة (0.8 - 1.3)</label>

                <input
                    type="number"
                    id="speed"
                    name="speed"
                    class="form-control"
                    value="{{ old('speed', 1.0) }}"
                    min="0.8"
                    max="1.3"
                    step="0.1"
                    @disabled(! $serviceAvailable)
                >
            </div>

        </div>

        <div class="form-actions">
            <button
                type="submit"
                class="dashboard-button dashboard-button-primary"
                @disabled(! $serviceAvailable)
            >
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>توليد الصوت</span>
            </button>
        </div>

        <p style="margin-top:10px; font-size:12px; color:var(--admin-muted);">
            قد يستغرق التوليد من عدة ثوانٍ إلى دقيقة حسب طول النص.
        </p>

    </form>

</section>

<section class="dashboard-panel" style="margin-top:24px;">

    <div class="dashboard-panel-header">
        <h3>آخر المقاطع المولّدة</h3>
        <p>آخر 10 مقاطع صوتية تم توليدها من هذه الصفحة.</p>
    </div>

    @if ($history->isEmpty())

        <p style="padding:20px; color:var(--admin-muted);">
            لا توجد مقاطع صوتية بعد.
        </p>

    @else

        <div style="display:flex; flex-direction:column; gap:14px; padding:20px;">

            @foreach ($history as $item)

                <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">

                    <audio controls src="{{ $item['url'] }}" style="flex:1; min-width:240px;"></audio>

                    <a
                        href="{{ route('admin.voice-studio.download', $item['name']) }}"
                        class="admin-action-button"
                        title="تنزيل"
                        aria-label="تنزيل المقطع"
                    >
                        <i class="fa-solid fa-download" aria-hidden="true"></i>
                    </a>

                </div>

            @endforeach

        </div>

    @endif

</section>

@endsection
