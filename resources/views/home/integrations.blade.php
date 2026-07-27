<section
    class="section"
    id="technology"
>

    @php
        $technologies = [

            [
                'icon' => 'fa-brands fa-laravel',
                'name' => 'Laravel',
            ],

            [
                'icon' => 'fa-brands fa-php',
                'name' => 'PHP 8',
            ],

            [
                'icon' => 'fa-solid fa-database',
                'name' => 'MySQL',
            ],

            [
                'icon' => 'fa-brands fa-js',
                'name' => 'JavaScript',
            ],

            [
                'icon' => 'fa-solid fa-network-wired',
                'name' => 'REST API',
            ],

            [
                'icon' => 'fa-solid fa-memory',
                'name' => 'Redis',
            ],

            [
                'icon' => 'fa-brands fa-github',
                'name' => 'GitHub',
            ],

            [
                'icon' => 'fa-solid fa-bolt',
                'name' => 'Vite',
            ],

            [
                'icon' => 'fa-brands fa-docker',
                'name' => 'Docker',
            ],

            [
                'icon' => 'fa-solid fa-server',
                'name' => 'Nginx',
            ],

            [
                'icon' => 'fa-brands fa-linux',
                'name' => 'Linux',
            ],

            [
                'icon' => 'fa-solid fa-brain',
                'name' => __('home.technology_ai'),
            ],

            [
                'icon' => 'fa-solid fa-image',
                'name' => __('home.technology_ai_images'),
            ],

            [
                'icon' => 'fa-solid fa-video',
                'name' => __('home.technology_ai_videos'),
            ],

            [
                'icon' => 'fa-solid fa-wand-magic-sparkles',
                'name' => __('home.technology_ai_design'),
            ],

            [
                'icon' => 'fa-solid fa-comments',
                'name' => __('home.technology_ai_chatbots'),
            ],

            [
                'icon' => 'fa-solid fa-robot',
                'name' => __('home.technology_automation'),
            ],

        ];
    @endphp

    <div class="section-heading reveal section-center">

        <p class="section-tag">
            {{ __('home.technology_badge') }}
        </p>

        <h2 class="section-title">
            {{ __('home.technology_title') }}

            <strong>
                {{ __('home.technology_title_highlight') }}
            </strong>
        </h2>

        <p class="section-body">
            {{ __('home.technology_description') }}
        </p>

    </div>

    <div class="integrations-grid">

        @foreach ($technologies as $technology)

            <article class="integration-item reveal">

                <div class="integration-icon">

                    <i
                        class="{{ $technology['icon'] }}"
                        aria-hidden="true"
                    ></i>

                </div>

                <div class="integration-name">
                    {{ $technology['name'] }}
                </div>

            </article>

        @endforeach

    </div>

</section>