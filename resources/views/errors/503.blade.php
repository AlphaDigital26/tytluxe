@extends('layouts.frontend')

@section('title', 'Website Under Maintenance - TYT Luxe')

@section('content')
<style>
    .maintenance-wrapper {
        min-height: 70vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 80px 20px;
        background-color: var(--bg-light);
    }
    .maintenance-icon {
        font-size: 5rem;
        color: var(--primary);
        margin-bottom: 20px;
        opacity: 0.9;
    }
    .maintenance-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        color: var(--secondary);
        margin-bottom: 20px;
    }
    .maintenance-desc {
        font-family: 'Outfit', sans-serif;
        color: var(--text-muted);
        max-width: 500px;
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 40px;
    }
    .maintenance-btn {
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
        cursor: pointer;
    }
    .maintenance-btn:hover {
        background-color: transparent;
        color: var(--primary);
    }
</style>

<div class="maintenance-wrapper">
    <div class="maintenance-icon">
        <i class="fa-solid fa-tools"></i>
    </div>
    <h1 class="maintenance-title">We are under maintenance</h1>
    <p class="maintenance-desc">
        We're currently performing some scheduled maintenance to improve your experience. 
        We should be back shortly. Thank you for your patience!
    </p>
    <button onclick="window.location.reload()" class="maintenance-btn">Refresh Page</button>
</div>
@endsection
