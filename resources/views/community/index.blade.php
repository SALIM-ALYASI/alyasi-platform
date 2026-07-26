@extends('layouts.app')

@section('title', __('community.title'))
@section('description', __('community.description'))

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/community/css/community.css') }}"
    >
@endpush

@section('content')

    {{-- Hero --}}
    @include('community.sections.hero')

    {{-- Categories --}}
    @include('community.sections.categories')

    {{-- Featured --}}
    @include('community.sections.featured')

    {{-- Community Posts --}}
    @include('community.sections.posts')

    {{-- Call To Action --}}
    @include('community.sections.cta')

@endsection

@push('scripts')
    <script src="{{ asset('assets/community/js/community.js') }}"></script>
@endpush