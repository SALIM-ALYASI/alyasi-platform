@extends('layouts.app')

@section('title', 'أعمالنا | ALYASI')

@section(
    'meta_description',
    'استعرض مجموعة من أعمال ومشاريع ALYASI في تطوير المواقع والتطبيقات والتصميم والحلول الرقمية.'
)

@push('styles')
<link
    rel="stylesheet"
    href="{{ asset('assets/works/css/works.css') }}"
>
@endpush

@section('content')

<main class="works-page">

    {{-- واجهة القسم --}}
    @include('works.sections.hero')

    {{-- البحث والفلاتر --}}
    @include('works.sections.filters')

    {{-- قائمة الأعمال --}}
    @include('works.sections.grid')

    {{-- الدعوة إلى التواصل --}}
    @include('works.sections.cta')

</main>

@endsection

@push('scripts')
<script
    src="{{ asset('assets/works/js/works.js') }}"
    defer
></script>
@endpush