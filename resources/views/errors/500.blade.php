@extends('layouts.frontend')

@section('title', 'Server Error - TYT Luxe')

@section('content')
<style>
    .not-found-wrapper {
        min-height: 70vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 80px 20px;
        background-color: var(--bg-light);
    }
    .not-found-code {
        font-family: 'Playfair Display', serif;
        font-size: 8rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
        margin-bottom: 10px;
        opacity: 0.9;
    }
    .not-found-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        color: var(--secondary);
        margin-bottom: 20px;
    }
    .not-found-desc {
        font-family: 'Outfit', sans-serif;
        color: var(--text-muted);
        max-width: 500px;
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 40px;
    }
    .not-found-btn {
        display: inline-block;
        background-color: var(--primary);
        color: #fff;
        padding: 14px 36px;
        font-family: 'Outfit', sans-serif;
        font-weight: 500;
        letter-spacing: 1px;
        text-transform: uppercase;
        border-radius: 4px;
        transition: all 0.3s ease;
        border: 1px solid var(--primary);
        text-decoration: none;
    }
    .not-found-btn:hover {
        background-color: transparent;
        color: var(--primary);
    }
</style>

<div class="not-found-wrapper">
    <div class="not-found-code">500</div>
    <h1 class="not-found-title">Server Error</h1>
    <p class="not-found-desc">
        Oops! Something went wrong on our end. We are currently trying to fix the problem. Please try again later.
    </p>
    <a href="{{ route('home') }}" class="not-found-btn">Return to Homepage</a>
</div>
@endsection
