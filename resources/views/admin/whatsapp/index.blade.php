@extends('admin.layouts.app')

@section('title', 'واتساب API')

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>واتساب API</h2>

        <p>
            حالة اتصال بوت واتساب وإرسال رسائل تجريبية.
        </p>
    </div>
</section>

@if (! $configured)
    <div class="admin-empty-state">
        <div class="admin-empty-state__icon">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        </div>
        <h3>الربط غير مُعد</h3>
        <p>
            أضف <code>WHATSAPP_NOTIFY_BASE_URL</code> و<code>WHATSAPP_NOTIFY_API_KEY</code> و<code>WHATSAPP_NOTIFY_NUMBER</code> في ملف <code>.env</code>.
        </p>
    </div>
@else
    <section class="dashboard-panel">
        @if ($status && ($status['connected'] ?? false))
            <div class="admin-alert admin-alert-success" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-check"></i>
                <span>متصل — الرقم: {{ $status['number'] ?? '—' }}</span>
            </div>
        @else
            <div class="admin-alert admin-alert-danger" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>غير متصل — امسح رمز QR أدناه من واتساب (الأجهزة المرتبطة ← ربط جهاز) لإعادة الربط.</span>
            </div>

            @if ($qrImage)
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <img src="{{ $qrImage }}" alt="QR ربط واتساب" style="max-width: 320px; width: 100%; border-radius: .5rem;">
                </div>
            @else
                <p>تعذّر جلب رمز QR حاليًا. حاول تحديث الصفحة بعد قليل.</p>
            @endif
        @endif

        <hr style="margin: 1.5rem 0;">

        <h3 style="margin-bottom: 1rem;">إرسال رسالة تجريبية</h3>

        <form method="POST" action="{{ route('admin.whatsapp.send-test') }}">
            @csrf

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="number">الرقم (مع كود الدولة، بدون + أو صفر)</label>
                <input
                    type="text"
                    id="number"
                    name="number"
                    class="form-control"
                    value="{{ old('number', config('services.whatsapp_notify.number')) }}"
                    required
                >
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="message">نص الرسالة</label>
                <textarea
                    id="message"
                    name="message"
                    class="form-control"
                    rows="3"
                    required
                >{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane"></i>
                إرسال
            </button>
        </form>
    </section>
@endif

@endsection
