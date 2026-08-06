@php
    $reviewsCount = $reviews->count();
    $averageRating = $reviewsCount > 0
        ? round($reviews->avg('rating'), 1)
        : null;
@endphp

<section class="reviews-block" id="reviews">

    <div class="reviews-block__heading">

        <div>
            <p class="section-tag">{{ __('reviews.badge') }}</p>
            <h2 class="section-title">
                {{ __('reviews.title', ['count' => $reviewsCount]) }}
            </h2>
        </div>

        @if($averageRating !== null)
            <div class="reviews-block__average">
                <span class="reviews-block__average-value">{{ $averageRating }}</span>
                <div class="reviews-block__stars" aria-hidden="true">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star {{ $i <= round($averageRating) ? 'is-filled' : '' }}"></i>
                    @endfor
                </div>
            </div>
        @endif

    </div>

    @if(session('success'))
        <div class="reviews-block__alert reviews-block__alert--success">
            {{ session('success') }}
        </div>
    @endif

    @if($reviewsCount > 0)
        <ul class="reviews-block__list">
            @foreach($reviews as $review)
                <li class="reviews-block__item">
                    <div class="reviews-block__item-header">
                        <strong>{{ $review->name }}</strong>
                        <time datetime="{{ $review->created_at->toIso8601String() }}">
                            {{ $review->created_at->translatedFormat('d F Y') }}
                        </time>
                    </div>
                    <div class="reviews-block__item-stars" aria-hidden="true">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fa-solid fa-star {{ $i <= $review->rating ? 'is-filled' : '' }}"></i>
                        @endfor
                    </div>
                    <p>{{ $review->body }}</p>
                </li>
            @endforeach
        </ul>
    @else
        <p class="reviews-block__empty">
            {{ __('reviews.empty') }}
        </p>
    @endif

    <form
        method="POST"
        action="{{ $reviewFormAction }}"
        class="reviews-block__form"
    >
        @csrf

        <div class="reviews-block__field">
            <label>{{ __('reviews.rating_label') }}</label>

            <div class="reviews-block__rating-input" dir="ltr">
                @for($i = 5; $i >= 1; $i--)
                    <input
                        type="radio"
                        name="rating"
                        id="rating-{{ $i }}"
                        value="{{ $i }}"
                        {{ old('rating') == $i ? 'checked' : '' }}
                        required
                    >
                    <label for="rating-{{ $i }}" title="{{ $i }}">
                        <i class="fa-solid fa-star"></i>
                    </label>
                @endfor
            </div>

            @error('rating')
                <span class="reviews-block__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="reviews-block__form-row">

            <div class="reviews-block__field">
                <label for="review-name">{{ __('reviews.name_label') }}</label>
                <input
                    type="text"
                    id="review-name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    maxlength="255"
                >
                @error('name')
                    <span class="reviews-block__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="reviews-block__field">
                <label for="review-email">{{ __('reviews.email_label') }}</label>
                <input
                    type="email"
                    id="review-email"
                    name="email"
                    value="{{ old('email') }}"
                    maxlength="255"
                >
                @error('email')
                    <span class="reviews-block__error">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <div class="reviews-block__field">
            <label for="review-body">{{ __('reviews.body_label') }}</label>
            <textarea
                id="review-body"
                name="body"
                rows="4"
                required
                maxlength="2000"
            >{{ old('body') }}</textarea>
            @error('body')
                <span class="reviews-block__error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="reviews-block__submit">
            {{ __('reviews.submit') }}
        </button>

        <p class="reviews-block__moderation-note">
            {{ __('reviews.moderation_note') }}
        </p>

    </form>

</section>
