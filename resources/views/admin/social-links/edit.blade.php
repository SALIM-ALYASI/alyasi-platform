@extends('admin.layouts.app')

@section('title', 'تعديل منصة التواصل')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/admin/css/social-links.css') }}"
    >
@endpush

@section('content')

<div class="social-links-page">

    <div class="page-header">
        <div>
            <h1>تعديل منصة التواصل</h1>
            <p>تعديل بيانات حساب {{ $socialLink->name }}.</p>
        </div>
    </div>

    <div class="social-form-card">

        <form
            action="{{ route('admin.social-links.update', $socialLink) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

            @include('admin.social-links._form')
        </form>

    </div>

</div>

@endsection