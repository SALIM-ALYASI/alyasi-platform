{{-- زر واتساب عائم بالصفحة الرئيسية بس (بباقي الصفحات كان يغطي النص). --}}
@if (request()->routeIs('home', 'home.en'))
    <a
        href="{{ \App\Models\SocialLink::whatsappUrl() }}"
        class="wa-float"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="{{ __('layout.whatsapp_chat') }}"
        title="{{ __('layout.whatsapp_chat') }}"
        data-track="whatsapp-float"
    >
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
    </a>
@endif
