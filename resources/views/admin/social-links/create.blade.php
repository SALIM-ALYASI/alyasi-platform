@extends('admin.layouts.app')

@section('title', 'إضافة منصة تواصل')

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
            <h1>إضافة منصة تواصل</h1>
            <p>أضف رابط حساب جديد إلى منصة ALYASI.</p>
        </div>
    </div>

    <div class="social-form-card">

        <form
            action="{{ route('admin.social-links.store') }}"
            method="POST"
        >
            @csrf

            @include('admin.social-links._form')
        </form>

    </div>

</div>

@endsection