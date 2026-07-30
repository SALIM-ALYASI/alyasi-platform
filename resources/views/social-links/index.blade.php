@extends('layouts.app')

@section('title', __('social-links.title') . ' | ' . __('home.brand'))

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/social-links/css/social-links.css') }}"
    >
@endpush

@section('content')

    @include('social-links.sections.hero')

    @include('social-links.sections.links')

    @include('social-links.sections.cta')

@endsection

@push('scripts')
    <script src="{{ asset('assets/social-links/js/social-links.js') }}"></script>
@endpush