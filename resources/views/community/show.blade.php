@extends('layouts.app')

@section('title', $communityPost->title)

@section(
    'description',
    $communityPost->short_description
        ?: \Illuminate\Support\Str::limit(strip_tags($communityPost->content), 160)
)

@push('styles')
    <link
        rel="stylesheet"
        href="{{ versioned_asset('assets/community/css/community-show.css') }}"
    >
@endpush

@section('content')

<main class="community-detail">

    {{-- Hero --}}
    <section class="community-detail__hero">
        <div class="container">

            <nav class="community-detail__breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('home') }}">
                    {{ __('home.home') }}
                </a>

                <i class="fa-solid fa-chevron-left"></i>

                <a href="{{ route('community.index') }}">
                    {{ __('community.title') }}
                </a>

                @if($communityPost->category)
                    <i class="fa-solid fa-chevron-left"></i>

                    <a
                        href="{{ route('community.index', [
                            'category' => $communityPost->category->slug,
                        ]) }}"
                    >
                        {{ $communityPost->category->name }}
                    </a>
                @endif
            </nav>

            <div class="community-detail__hero-grid">

                <div class="community-detail__intro">

                    <div class="community-detail__labels">

                        @if($communityPost->category)
                            <a
                                href="{{ route('community.index', [
                                    'category' => $communityPost->category->slug,
                                ]) }}"
                                class="community-detail__label"
                            >
                                {{ $communityPost->category->name }}
                            </a>
                        @endif

                        <span class="community-detail__label">
                            {{ __('community.types.' . $communityPost->type) }}
                        </span>

                        @if($communityPost->is_featured)
                            <span class="community-detail__label community-detail__label--featured">
                                <i class="fa-solid fa-star"></i>
                                {{ __('community.featured') }}
                            </span>
                        @endif

                    </div>

                    <h1 class="community-detail__title">
                        {{ $communityPost->title }}
                    </h1>

                    @if($communityPost->short_description)
                        <p class="community-detail__summary">
                            {{ $communityPost->short_description }}
                        </p>
                    @endif

                    <div class="community-detail__meta">

                        @if($communityPost->published_at)
                            <span>
                                <i class="fa-regular fa-calendar"></i>

                                <time datetime="{{ $communityPost->published_at->toIso8601String() }}">
                                    {{ $communityPost->published_at->translatedFormat('d F Y') }}
                                </time>
                            </span>
                        @endif

                        @if($communityPost->location)
                            <span>
                                <i class="fa-solid fa-location-dot"></i>
                                {{ $communityPost->location }}
                            </span>
                        @endif

                    </div>

                </div>

                <div class="community-detail__cover">
                    <img
                        src="{{ $communityPost->image
                            ? asset('storage/' . $communityPost->image)
                            : asset('images/logo/logo-dark.png') }}"
                        class="{{ $communityPost->image ? '' : 'is-placeholder' }}"
                        alt="{{ $communityPost->title }}"
                        fetchpriority="high"
                    >
                </div>

            </div>

        </div>
    </section>

    {{-- Main Content --}}
    <section class="community-detail__main">
        <div class="container">

            <div class="community-detail__layout">

                <article class="community-detail__article">

                    @if(
                        $communityPost->type === 'event'
                        && (
                            $communityPost->event_start_at
                            || $communityPost->event_end_at
                            || $communityPost->location
                            || $communityPost->registration_url
                        )
                    )
                        <section class="community-event-box">

                            <div class="community-event-box__header">
                                <span class="community-event-box__icon">
                                    <i class="fa-regular fa-calendar-check"></i>
                                </span>

                                <div>
                                    <span class="community-event-box__eyebrow">
                                        {{ __('community.event_details') }}
                                    </span>

                                    <h2>
                                        {{ __('community.event_information') }}
                                    </h2>
                                </div>
                            </div>

                            <div class="community-event-box__grid">

                                @if($communityPost->event_start_at)
                                    <div class="community-event-box__item">
                                        <i class="fa-regular fa-clock"></i>

                                        <div>
                                            <span>{{ __('community.event_start') }}</span>

                                            <strong>
                                                {{ $communityPost->event_start_at
                                                    ->translatedFormat('d F Y - h:i A') }}
                                            </strong>
                                        </div>
                                    </div>
                                @endif

                                @if($communityPost->event_end_at)
                                    <div class="community-event-box__item">
                                        <i class="fa-regular fa-hourglass-half"></i>

                                        <div>
                                            <span>{{ __('community.event_end') }}</span>

                                            <strong>
                                                {{ $communityPost->event_end_at
                                                    ->translatedFormat('d F Y - h:i A') }}
                                            </strong>
                                        </div>
                                    </div>
                                @endif

                                @if($communityPost->location)
                                    <div class="community-event-box__item">
                                        <i class="fa-solid fa-location-dot"></i>

                                        <div>
                                            <span>{{ __('community.location') }}</span>
                                            <strong>{{ $communityPost->location }}</strong>
                                        </div>
                                    </div>
                                @endif

                            </div>

                            {{-- مخفي مؤقتاً: بعض روابط التسجيل المُستخرجة تلقائياً (Gemini) طلعت غلط
                                 (مثال: LEAP 2026 حطت رابط شركة غير مرتبطة). نعيد إظهاره بعد ما نلقى
                                 حل للتحقق من صحة الرابط قبل النشر. --}}
                            @if(false && $communityPost->registration_url)
                                <a
                                    href="{{ $communityPost->registration_url }}"
                                    class="community-event-box__button"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    {{ __('community.register_now') }}
                                </a>
                            @endif

                        </section>
                    @endif

                    <div class="community-detail__content">
                        {!! $communityPost->content !!}
                    </div>

                    <footer class="community-detail__footer">

                        <div class="community-detail__share">
                            <span>{{ __('community.share') }}</span>

                            <div class="community-detail__share-links">

                                <a
                                    href="https://wa.me/?text={{ urlencode(
                                        $communityPost->title . ' ' . request()->url()
                                    ) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="{{ __('community.share_whatsapp') }}"
                                >
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>

                                <a
                                    href="https://twitter.com/intent/tweet?text={{ urlencode(
                                        $communityPost->title
                                    ) }}&url={{ urlencode(request()->url()) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="{{ __('community.share_x') }}"
                                >
                                    <i class="fa-brands fa-x-twitter"></i>
                                </a>

                                <button
                                    type="button"
                                    data-copy-url="{{ request()->url() }}"
                                    aria-label="{{ __('community.copy_link') }}"
                                >
                                    <i class="fa-solid fa-link"></i>
                                </button>

                            </div>
                        </div>

                        <a
                            href="{{ route('community.index') }}"
                            class="community-detail__back"
                        >
                            <i class="fa-solid fa-arrow-right-long"></i>
                            {{ __('community.back_to_community') }}
                        </a>

                    </footer>

                </article>

                <aside class="community-detail__aside">

                    <div class="community-detail__aside-card">

                        <span class="community-detail__aside-label">
                            {{ __('community.title') }}
                        </span>

                        <h2>
                            {{ __('community.related_posts') }}
                        </h2>

                        <p>
                            {{ __('community.latest_posts_description') }}
                        </p>

                        <a href="{{ route('community.index') }}">
                            {{ __('community.show_all') }}
                            <i class="fa-solid fa-arrow-left-long"></i>
                        </a>

                    </div>

                </aside>

            </div>

        </div>
    </section>

    {{-- Comments --}}
    <section class="community-comments" id="comments">
        <div class="container">

            <div class="section-heading reveal">
                <p class="section-tag">{{ __('community.comments_badge') }}</p>
                <h2 class="section-title">
                    {{ __('community.comments_title', ['count' => $communityPost->approvedComments->count()]) }}
                </h2>
            </div>

            @if(session('success'))
                <div class="community-comments__alert community-comments__alert--success">
                    {{ session('success') }}
                </div>
            @endif

            @if($communityPost->approvedComments->isNotEmpty())
                <ul class="community-comments__list">
                    @foreach($communityPost->approvedComments as $comment)
                        <li class="community-comments__item">
                            <div class="community-comments__item-header">
                                <strong>{{ $comment->name }}</strong>
                                <time datetime="{{ $comment->created_at->toIso8601String() }}">
                                    {{ $comment->created_at->translatedFormat('d F Y') }}
                                </time>
                            </div>
                            <p>{{ $comment->body }}</p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="community-comments__empty">
                    {{ __('community.comments_empty') }}
                </p>
            @endif

            <form
                method="POST"
                action="{{ route('community.comments.store', $communityPost) }}"
                class="community-comments__form"
            >
                @csrf

                <div class="community-comments__form-row">

                    <div class="community-comments__field">
                        <label for="comment-name">{{ __('community.comment_name') }}</label>
                        <input
                            type="text"
                            id="comment-name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            maxlength="255"
                        >
                        @error('name')
                            <span class="community-comments__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="community-comments__field">
                        <label for="comment-email">{{ __('community.comment_email') }}</label>
                        <input
                            type="email"
                            id="comment-email"
                            name="email"
                            value="{{ old('email') }}"
                            maxlength="255"
                        >
                        @error('email')
                            <span class="community-comments__error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="community-comments__field">
                    <label for="comment-body">{{ __('community.comment_body') }}</label>
                    <textarea
                        id="comment-body"
                        name="body"
                        rows="4"
                        required
                        maxlength="2000"
                    >{{ old('body') }}</textarea>
                    @error('body')
                        <span class="community-comments__error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="community-comments__field community-comments__field--checkbox">
                    <label>
                        <input
                            type="checkbox"
                            name="followed_confirmation"
                            value="1"
                            required
                        >
                        {{ __('community.comment_follow_confirmation') }}
                        <a
                            href="{{ route('social-links.index') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ __('community.comment_follow_link') }}
                        </a>
                    </label>
                    @error('followed_confirmation')
                        <span class="community-comments__error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="community-comments__submit">
                    {{ __('community.comment_submit') }}
                </button>

                <p class="community-comments__moderation-note">
                    {{ __('community.comment_moderation_note') }}
                </p>

            </form>

        </div>
    </section>

    {{-- Related Posts --}}
    @if($relatedPosts->isNotEmpty())
        <section class="community-related">
            <div class="container">

                <div class="community-related__heading">
                    <span>{{ __('community.related') }}</span>
                    <h2>{{ __('community.related_posts') }}</h2>
                </div>

                <div class="community-related__grid">

                    @foreach($relatedPosts as $relatedPost)

                        <article class="community-related-card">

                            <a
                                href="{{ route('community.show', $relatedPost) }}"
                                class="community-related-card__image"
                            >
                                <img
                                    src="{{ $relatedPost->image
                                        ? asset('storage/' . $relatedPost->image)
                                        : asset('images/logo/logo-dark.png') }}"
                                    class="{{ $relatedPost->image ? '' : 'is-placeholder' }}"
                                    alt="{{ $relatedPost->title }}"
                                    loading="lazy"
                                >

                                <span class="community-related-card__type">
                                    {{ __('community.types.' . $relatedPost->type) }}
                                </span>
                            </a>

                            <div class="community-related-card__content">

                                @if($relatedPost->category)
                                    <a
                                        href="{{ route('community.index', [
                                            'category' => $relatedPost->category->slug,
                                        ]) }}"
                                        class="community-related-card__category"
                                    >
                                        {{ $relatedPost->category->name }}
                                    </a>
                                @endif

                                <h3>
                                    <a href="{{ route('community.show', $relatedPost) }}">
                                        {{ $relatedPost->title }}
                                    </a>
                                </h3>

                                <p>
                                    {{ $relatedPost->short_description }}
                                </p>

                                <a
                                    href="{{ route('community.show', $relatedPost) }}"
                                    class="community-related-card__link"
                                >
                                    {{ __('community.read_more') }}
                                    <i class="fa-solid fa-arrow-left-long"></i>
                                </a>

                            </div>

                        </article>

                    @endforeach

                </div>

            </div>
        </section>
    @endif

</main>

@endsection

@push('scripts')
    <script src="{{ versioned_asset('assets/community/js/community-show.js') }}"></script>
@endpush