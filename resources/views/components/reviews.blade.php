@props([
    'reviews',
    'action',
])

<link rel="stylesheet" href="{{ versioned_asset('css/shared/reviews.css') }}">

<div class="reviews-block" data-reveal>
    <div class="section-head__eyebrow">{{ __('reviews.badge') }}</div>
    <h2 class="section-head__title reviews-block__title">{{ __('reviews.title', ['count' => $reviews->count()]) }}</h2>

    @if (session('success'))
        <div class="form-alert form-alert--success">{{ session('success') }}</div>
    @endif

    @if ($reviews->isNotEmpty())
        <div class="reviews-block__list">
            @foreach ($reviews as $review)
                <div class="reviews-block__item">
                    <div class="reviews-block__item-head">
                        <strong>{{ $review->name }}</strong>
                        <span class="stars" aria-label="{{ $review->rating }}/5">
                            @for ($i = 1; $i <= 5; $i++)
                                {{ $i <= $review->rating ? '★' : '☆' }}
                            @endfor
                        </span>
                    </div>
                    <p class="reviews-block__item-body">{{ $review->body }}</p>
                    <span class="reviews-block__item-date">{{ $review->created_at->translatedFormat('d.m.Y') }}</span>
                </div>
            @endforeach
        </div>
    @else
        <p class="reviews-block__empty">{{ __('reviews.empty') }}</p>
    @endif

    <form method="POST" action="{{ $action }}" class="reviews-block__form">
        @csrf

        <div class="field">
            <label class="field__label" for="review-rating">{{ __('reviews.rating_label') }}</label>
            <div class="reviews-block__rating-input" role="radiogroup" aria-label="{{ __('reviews.rating_label') }}">
                @for ($i = 5; $i >= 1; $i--)
                    <label class="reviews-block__rating-option">
                        <input type="radio" name="rating" value="{{ $i }}" @checked(old('rating') == $i)>
                        <span>★</span>
                    </label>
                @endfor
            </div>
            @error('rating') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label class="field__label" for="review-name">{{ __('reviews.name_label') }}</label>
            <input type="text" name="name" id="review-name" class="field__control" value="{{ old('name') }}" required maxlength="255">
            @error('name') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label class="field__label" for="review-email">{{ __('reviews.email_label') }}</label>
            <input type="email" name="email" id="review-email" class="field__control" value="{{ old('email') }}" maxlength="255">
            @error('email') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label class="field__label" for="review-body">{{ __('reviews.body_label') }}</label>
            <textarea name="body" id="review-body" class="field__control" required maxlength="2000">{{ old('body') }}</textarea>
            @error('body') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn--primary">{{ __('reviews.submit') }}</button>
        <p class="reviews-block__note">{{ __('reviews.moderation_note') }}</p>
    </form>
</div>
