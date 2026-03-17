@extends('layouts.auth')

@section('title', 'Login')
@section('hero_title', 'Sign in with the warmth of a kopitiam morning.')
@section('hero_text', 'This login screen borrows from Malaysian breakfast staples: coconut cream softness, pandan depth, and sambal heat for the primary action. It is designed to feel more like a curated restaurant brand than a default auth page.')

@section('form_intro')
    <h2>Welcome back</h2>
    <p>Sign in as an admin, staff member, or customer to enter the right side of the restaurant flow from a branded Malaysian-themed screen.</p>
    <div class="auth-meta">
        <span class="auth-pill">Admin: menu items</span>
        <span class="auth-pill">Staff: orders</span>
        <span class="auth-pill">Customer: place orders</span>
    </div>
@endsection

@section('form')
    <form class="auth-form" data-auth-form="login" data-endpoint="{{ url('/api/login') }}" data-success-copy="Login successful. Your token is ready below.">
        <div class="auth-field-grid">
            <div class="auth-field">
                <label for="email">Email address</label>
                <input id="email" class="auth-input" type="email" name="email" autocomplete="email" placeholder="chef@selera.com" required>
                <div class="auth-error-text" data-error-for="email"></div>
            </div>

            <div class="auth-field">
                <label for="password">Password</label>
                <input id="password" class="auth-input" type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
                <div class="auth-error-text" data-error-for="password"></div>
                <div class="auth-help">Use the same credentials accepted by your existing API login endpoint.</div>
            </div>
        </div>

        <div class="auth-actions">
            <button class="auth-button" type="submit">Enter the kitchen</button>
            <a class="auth-link" href="{{ route('register') }}">Need an account? Create one</a>
        </div>

        <p class="auth-note">Admin accounts manage menu items, staff accounts manage order status, and customer accounts place orders.</p>

        <div class="auth-feedback" data-auth-feedback></div>
        <div class="auth-token" data-auth-token></div>
    </form>
@endsection