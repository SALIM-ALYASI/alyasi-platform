{{-- «من شهاداتي»: بطاقات الشهادات وعارضها بشاشة كاملة (الرئيسية و«من نحن»). البيانات بـ lang/*/about.php --}}
    {{-- من شهاداتي --}}
    <section class="container about-section certs-section">
        <div class="section-head" data-reveal>
            <div class="section-head__eyebrow">{{ __('about.certificates_badge') }}</div>
            <h2 class="section-head__title">{{ __('about.certificates_title') }}</h2>
        </div>

        <div class="about-certs">
            @foreach (__('about.certificates') as $cert)
                @php $certSrc = asset('images/about/certificates/'.$cert['image']); @endphp
                <button type="button" class="about-cert" data-reveal data-cert-src="{{ $certSrc }}" data-cert-title="{{ $cert['title'] }}"
                        aria-label="{{ __('about.certificates_view') }}: {{ $cert['title'] }}">
                    <span class="about-cert__media"><img src="{{ $certSrc }}" alt="{{ $cert['title'] }}" loading="lazy"></span>
                    <span class="about-cert__body">
                        <span class="about-cert__type">{{ $cert['type'] }}</span>
                        <strong class="about-cert__title">{{ $cert['title'] }}</strong>
                        <span class="about-cert__issuer">{{ $cert['issuer'] }}</span>
                        <span class="about-cert__date">{{ $cert['date'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </section>

    <div class="about-cert-viewer" id="aboutCertViewer" role="dialog" aria-modal="true" hidden>
        <button type="button" class="about-cert-viewer__close" id="aboutCertClose" aria-label="{{ __('about.certificates_close') }}">✕</button>
        <img id="aboutCertImage" src="" alt="">
    </div>

@push('scripts')
@once
<script>
    // عرض الشهادة بشاشة كاملة عند الضغط عليها.
    (function () {
        var viewer = document.getElementById('aboutCertViewer');
        if (!viewer) return;
        document.body.appendChild(viewer);
        var img = document.getElementById('aboutCertImage');
        var close = function () { viewer.hidden = true; img.src = ''; document.body.style.overflow = ''; };
        document.querySelectorAll('[data-cert-src]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                img.src = btn.dataset.certSrc;
                img.alt = btn.dataset.certTitle;
                viewer.hidden = false;
                document.body.style.overflow = 'hidden';
            });
        });
        document.getElementById('aboutCertClose').addEventListener('click', close);
        viewer.addEventListener('click', function (e) { if (e.target === viewer) close(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !viewer.hidden) close(); });
    })();
</script>
@endonce
@endpush
