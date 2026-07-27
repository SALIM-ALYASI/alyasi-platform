<section class="social-links-section">

    <div class="container">

        <div class="social-section-heading">

            <div class="social-section-heading-content">

                <span class="social-section-label">
                    {{ __('social-links.section_badge') }}
                </span>

                <h2 class="social-section-title">
                    {{ __('social-links.section_title') }}
                </h2>

            </div>

            <p class="social-section-description">
                {{ __('social-links.section_description') }}
            </p>

        </div>

        @if(isset($socialLinks) && $socialLinks->isNotEmpty())

            <div class="social-links-grid">

                @foreach($socialLinks as $socialLink)

                    @php
                        $platform = strtolower($socialLink->platform ?? '');

                        $isWhatsApp = $platform === 'whatsapp';

                        $buttonText = $isWhatsApp
                            ? __('social-links.open_whatsapp')
                            : __('social-links.visit');

                        $accountText = $socialLink->username;

                        if (!$accountText && $isWhatsApp) {
                            $accountText = __('social-links.direct_contact');
                        }

                        if (!$accountText) {
                            $accountText = __('social-links.official_account');
                        }
                    @endphp

                    <article class="social-link-card">

                        <div class="social-link-card-header">

                            <span class="social-link-icon">

                                @if($socialLink->icon)

                                    <i class="{{ $socialLink->icon }}"></i>

                                @else

                                    <i class="fa-solid fa-link"></i>

                                @endif

                            </span>

                            <span class="social-link-official">
                                {{ __('social-links.official') }}
                            </span>

                        </div>

                        <div class="social-link-card-body">

                            <h3 class="social-link-name">
                                {{ $socialLink->name }}
                            </h3>

                            <p class="social-link-username">
                                {{ $accountText }}
                            </p>

                        </div>

                        <a
                            href="{{ $socialLink->url }}"
                            class="social-link-button"
                            @if($socialLink->open_new_tab)
                                target="_blank"
                                rel="noopener noreferrer"
                            @endif
                            aria-label="{{ $buttonText }} - {{ $socialLink->name }}"
                        >

                            <span>
                                {{ $buttonText }}
                            </span>

                            <i class="fa-solid fa-arrow-up-left-from-square"></i>

                        </a>

                    </article>

                @endforeach

            </div>

        @else

            <div class="social-links-empty">

                <span class="social-links-empty-icon">
                    <i class="fa-solid fa-link-slash"></i>
                </span>

                <h3>
                    {{ __('social-links.empty_title') }}
                </h3>

                <p>
                    {{ __('social-links.empty_description') }}
                </p>

            </div>

        @endif

    </div>

</section>