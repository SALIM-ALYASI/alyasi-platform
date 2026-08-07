@extends('admin.layouts.app')

@section('title', 'رسالة من ' . $message->name)

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">
                {{ $message->subject ?: 'رسالة من ' . $message->name }}
            </h1>

        </div>

        <a href="{{ route('admin.messages.index') }}" class="admin-primary-button">
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            العودة للرسائل
        </a>

    </div>

    <section class="admin-list-panel">

        <div class="admin-list-panel__header">
            <div>
                <h2>{{ $message->name }}</h2>
                <p dir="ltr" style="margin-top: 4px;">
                    <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                    @if($message->phone)
                        · <a href="tel:{{ $message->phone }}">{{ $message->phone }}</a>
                    @endif
                </p>
            </div>

            <span style="font-size: 0.78rem; opacity: 0.5;">
                {{ $message->created_at->translatedFormat('d F Y - h:i A') }}
            </span>
        </div>

        <div style="padding: 24px; white-space: pre-line; line-height: 1.8; color: rgba(255,255,255,.85);">
            {{ $message->message }}
        </div>

        <div class="admin-data-card__actions" style="padding: 0 24px 24px;">

            <a
                href="mailto:{{ $message->email }}?subject={{ urlencode('رد: ' . ($message->subject ?: 'رسالتك لـ ALYASI')) }}"
                class="admin-primary-button"
            >
                <i class="fa-regular fa-paper-plane" aria-hidden="true"></i>
                الرد عبر البريد
            </a>

            @if($message->status !== 'replied')
                <form action="{{ route('admin.messages.mark-replied', $message) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="admin-action-button">
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                        تحديد كمردود عليها
                    </button>
                </form>
            @endif

            <form
                action="{{ route('admin.messages.destroy', $message) }}"
                method="POST"
                onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة؟');"
            >
                @csrf
                @method('DELETE')

                <button type="submit" class="admin-action-button admin-action-button--delete">
                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                    حذف
                </button>
            </form>

        </div>

    </section>

</div>

@endsection
