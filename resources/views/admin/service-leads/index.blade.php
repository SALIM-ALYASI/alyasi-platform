@extends('admin.layouts.app')

@section('title', 'طلبات العملاء')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">
        <div class="admin-page-header__content">
            <span class="admin-page-header__badge">
                <i class="fa-solid fa-bullseye" aria-hidden="true"></i>
                من منصات التواصل
            </span>
            <h1 class="admin-page-header__title">طلبات العملاء</h1>
            <p class="admin-page-header__description">
                منشورات علنية في عُمان والخليج يطلب فيها أصحابها مصمم جرافيك أو مونتير أو موقعًا (توصلك فورًا)، ومشاريع مستقل وخمسات من أي بلد بميزانية 100$ فأكثر (قائمة يومية). يلقطها Grok مرتين باليوم.
                ما يتواصل النظام مع أحد: تشوف الطلب وتقرر. تنحذف تلقائيًا بعد 60 يومًا.
            </p>
        </div>
    </div>

    <section class="admin-stats-row">
        @foreach (\App\Models\ServiceLead::STATUSES as $key => $label)
            <a class="admin-stat-card" href="{{ route('admin.service-leads.index', array_filter(['status' => $key, 'scope' => $scope])) }}" @if ($status === $key) style="outline: 2px solid var(--admin-accent, #5E76A8);" @endif>
                <div class="admin-stat-card__content">
                    <span>{{ $label }}</span>
                    <strong>{{ number_format($counts[$key] ?? 0) }}</strong>
                </div>
            </a>
        @endforeach
    </section>

    <section class="admin-filter-panel">
        <form action="{{ route('admin.service-leads.index') }}" method="GET" class="admin-filter-form">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="admin-filter-form__field">
                <label for="lead-scope">المصدر</label>
                <select id="lead-scope" name="scope">
                    <option value="">الكل</option>
                    @foreach (\App\Models\ServiceLead::SCOPES as $key => $label)
                        <option value="{{ $key }}" @selected($scope === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-form__field">
                <label for="lead-category">النوع</label>
                <select id="lead-category" name="category">
                    <option value="">كل الأنواع</option>
                    @foreach (\App\Models\ServiceLead::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-form__actions">
                <button type="submit" class="admin-filter-button">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                    تطبيق
                </button>
            </div>
        </form>
    </section>

    <section class="admin-list-panel">
        <div class="admin-list-panel__header">
            <div>
                <h2>{{ \App\Models\ServiceLead::STATUSES[$status] }}</h2>
                <p>الأعلى أولوية أول: عُمان، ثم وجود ميزانية أو موعد، ثم الأحدث.</p>
            </div>
            <span class="admin-results-count">{{ number_format($leads->total()) }} طلب</span>
        </div>

        @if ($leads->isNotEmpty())
            <div class="admin-card-grid">
                @foreach ($leads as $lead)
                    <article class="admin-data-card">
                        <div class="admin-data-card__body">
                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $lead->countryLabel() }}@if ($lead->city) – {{ $lead->city }}@endif · {{ $lead->categoryLabel() }}</h3>
                                    <p style="margin: 4px 0 0; font-size: 0.8rem; opacity: 0.6;">
                                        {{ $lead->platformLabel() }}@if ($lead->author) · <span dir="ltr">{{ $lead->author }}</span>@endif
                                        · {{ ($lead->posted_at ?? $lead->created_at)->timezone('Asia/Muscat')->translatedFormat('d F - h:i A') }}
                                    </p>
                                </div>
                                <span class="admin-status {{ $lead->status === 'new' ? 'admin-status--featured' : 'admin-status--inactive' }}">
                                    {{ $lead->status === 'rejected' ? (\App\Models\ServiceLead::REJECT_REASONS[$lead->reject_reason] ?? $lead->reject_reason) : ($lead->scope === 'marketplace' ? '$'.number_format((int) $lead->budget_usd) : 'أولوية '.$lead->score) }}
                                </span>
                            </div>

                            @if ($lead->summary)
                                <p style="margin: 6px 0; color: #fff; font-weight: 600; font-size: 0.9rem;">{{ $lead->summary }}</p>
                            @endif

                            <p class="admin-data-card__description" dir="auto">{{ \Illuminate\Support\Str::limit($lead->text, 400) }}</p>

                            @if ($lead->budget || $lead->deadline)
                                <p style="margin: 8px 0 0; font-size: 0.82rem;">
                                    @if ($lead->budget) الميزانية: {{ $lead->budget }} @endif
                                    @if ($lead->budget && $lead->deadline) · @endif
                                    @if ($lead->deadline) الموعد: {{ $lead->deadline }} @endif
                                </p>
                            @endif
                        </div>

                        <div class="admin-data-card__actions">
                            <a href="{{ $lead->url }}" target="_blank" rel="noopener noreferrer" class="admin-action-button" title="فتح المنشور" aria-label="فتح المنشور">
                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            </a>
                            @foreach (['contacted' => ['fa-solid fa-check', 'تواصلت'], 'dismissed' => ['fa-solid fa-xmark', 'غير مناسب'], 'new' => ['fa-solid fa-rotate-left', 'رجّعه جديد']] as $to => [$icon, $label])
                                @continue($lead->status === $to || ($lead->status === 'new' && $to === 'new'))
                                <form action="{{ route('admin.service-leads.status', $lead) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $to }}">
                                    <button type="submit" class="admin-action-button" title="{{ $label }}" aria-label="{{ $label }}">
                                        <i class="{{ $icon }}" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($leads->hasPages())
                <div class="admin-pagination">{{ $leads->links() }}</div>
            @endif
        @else
            <div class="admin-empty-state">
                <div class="admin-empty-state__icon"><i class="fa-solid fa-bullseye" aria-hidden="true"></i></div>
                <h3>لا توجد طلبات هنا</h3>
                <p>أول ما يلقط Grok طلبات جديدة بتظهر هنا وتوصلك على واتساب.</p>
            </div>
        @endif
    </section>

</div>

@endsection
