@extends('layouts.app')

@section('title', __('home.brand') . ' | ' . __('home.tagline'))

@section('description', __('home.hero_description'))

@section('content')

    @include('home.hero')

    @include('home.services')

    @include('home.pricing')

    @include('home.testimonials')

    @include('home.integrations')

    @include('home.faq')

    @include('home.cta')

@endsection