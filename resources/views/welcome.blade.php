@extends('layouts.guest')

@section('title', 'Kezdőlap')

@section('content')
    <!-- Hero Section -->
    <x-hero-section :guild-count="$guild_count" :user-count="$user_count" />

    <!-- Features Section -->
    <x-features-section />

    <!-- CTA Section -->
    <x-cta-section />
@endsection
