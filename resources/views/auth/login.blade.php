@extends('layouts.auth')

@section('title', 'Login')
@section('hero_title', 'Sign in with the warmth of a kopitiam morning.')
@section('hero_text', 'Savor authentic Nusantara flavors from hearty soto ayam to spicy ayam penyet, paired with refreshing drinks. Taste tradition today')

@section('form_intro')
    <h2>Welcome back</h2>
    <p>Sign in to order and pickup your Nusantara meals with Restaurant Nusantara.</p>
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