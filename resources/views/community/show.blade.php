@extends('layouts.app')

@section('title', $communityPost->title.' — ALYASI')
@section('meta_description', \Illuminate\Support\Str::limit($communityPost->short_description, 160))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/community-show.css') }}">
@endpush

@section('content')

@php
    $isEvent = (bool) $communityPost->event_status;
    $paragraphs = collect(preg_split('/\r?\n+/', trim((string) $communityPost->content)))->filter();
@endphp

    <section class="container community-detail__hero-wrap">
        <div class="community-detail__hero-media">
            <img src="{{ media_url($communityPost->image) }}" alt="{{ $communityPost->title }}">
            @if ($isEvent)
                <span class="badge badge--status-{{ $communityPost->event_status }} community-detail__hero-badge">{{ __('events.status.'.$communityPost->event_status) }}</span>
            @endif
        </div>
    </section>

    <section class="container community-detail__body">
        @unless ($isEvent)
            @if ($communityPost->category)
                <span class="badge">{{ $communityPost->category->name }}</span>
            @endif
        @endunless

        <h1 class="community-detail__title">{{ $communityPost->title }}</h1>

        @if ($isEvent)
            <div class="grid-2 community-detail__info-grid">
                <div class="community-detail__info-card">
                    <div class="community-detail__info-label">{{ __('community.event_information') }}</div>
                    <div class="community-detail__info-value">{{ $communityPost->event_start_at->translatedFormat('d.m.Y — H:i') }}</div>
                </div>
                <div class="community-detail__info-card">
                    <div class="community-detail__info-label">{{ __('community.location') }}</div>
                    <div class="community-detail__info-value">{{ $communityPost->location ?: '—' }}</div>
                </div>
            </div>
        @endif

        @if ($paragraphs->isNotEmpty())
            @foreach ($paragraphs as $paragraph)
                <p class="community-detail__paragraph">{{ $paragraph }}</p>
            @endforeach
        @else
            <p class="community-detail__paragraph">{{ $communityPost->short_description }}</p>
        @endif

        @if ($isEvent)
            <div class="community-detail__map">{{ __('community.location') }}</div>

            @if ($communityPost->registration_url)
                <a href="{{ $communityPost->registration_url }}" target="_blank" rel="noopener" class="btn btn--primary community-detail__register">{{ __('community.register_now') }}</a>
            @endif
        @endif
    </section>

    @if ($relatedPosts->isNotEmpty())
        <section class="community-detail__related">
            <div class="container">
                <div class="section-head__eyebrow">{{ __('community.related') }}</div>
                <h2 class="section-head__title community-detail__related-title">{{ __('community.related_posts') }}</h2>

                <div class="grid-3">
                    @foreach ($relatedPosts as $related)
                        <a href="{{ route('community.show', $related) }}" class="card card--hover community-detail__related-card">
                            <div class="community-detail__related-media">
                                <img src="{{ media_url($related->image) }}" alt="{{ $related->title }}" loading="lazy">
                            </div>
                            <div class="community-detail__related-body">
                                <div class="community-detail__related-meta">{{ optional($related->event_start_at ?? $related->published_at)->translatedFormat('d.m.Y') }}</div>
                                <div class="community-detail__related-title-text">{{ $related->title }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section id="comments" class="container community-detail__comments">
        <div class="section-head__eyebrow">{{ __('community.comments_badge') }}</div>
        <h2 class="section-head__title community-detail__comments-title">{{ __('community.comments_title', ['count' => $communityPost->approvedComments->count()]) }}</h2>

        @if (session('success'))
            <div class="form-alert form-alert--success">{{ session('success') }}</div>
        @endif

        @if ($communityPost->approvedComments->isNotEmpty())
            <div class="community-detail__comment-list">
                @foreach ($communityPost->approvedComments as $comment)
                    <div class="community-detail__comment">
                        <strong>{{ $comment->name }}</strong>
                        <p>{{ $comment->body }}</p>
                        <span class="community-detail__comment-date">{{ $comment->created_at->translatedFormat('d.m.Y') }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="community-detail__comments-empty">{{ __('community.comments_empty') }}</p>
        @endif

        <form method="POST" action="{{ route('community.comments.store', $communityPost) }}" class="community-detail__comment-form">
            @csrf

            <div class="field">
                <label class="field__label" for="comment-name">{{ __('community.comment_name') }}</label>
                <input type="text" name="name" id="comment-name" class="field__control" value="{{ old('name') }}" required maxlength="255">
                @error('name') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label class="field__label" for="comment-email">{{ __('community.comment_email') }}</label>
                <input type="email" name="email" id="comment-email" class="field__control" value="{{ old('email') }}" maxlength="255">
                @error('email') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label class="field__label" for="comment-body">{{ __('community.comment_body') }}</label>
                <textarea name="body" id="comment-body" class="field__control" required maxlength="2000">{{ old('body') }}</textarea>
                @error('body') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <label class="field__checkbox">
                <input type="checkbox" name="followed_confirmation" value="1" required>
                {{ __('community.comment_follow_confirmation') }}
                <a href="{{ route('social-links.index') }}" target="_blank">{{ __('community.comment_follow_link') }}</a>
            </label>
            @error('followed_confirmation') <span class="field__error">{{ $message }}</span> @enderror

            <button type="submit" class="btn btn--primary">{{ __('community.comment_submit') }}</button>
            <p class="reviews-block__note">{{ __('community.comment_moderation_note') }}</p>
        </form>
    </section>

@endsection
