@extends('layouts.auth')

@section('title', 'Register')
@section('hero_title', 'Set up a fresh account with nasi lemak energy.')
@section('hero_text', 'The register page leans brighter and more open, like a morning service setup: creamy backgrounds, layered cards, and richer spice accents to keep the brand identity distinctly Malaysian.')

@section('form_intro')
    <h2>Create your account</h2>
    <p>Register an account and start your day with breakfast with Restaurant Nusantara </p>
    <div class="auth-meta">
        <span class="auth-pill">Admin: menu items</span>
        <span class="auth-pill">Staff: orders</span>
        <span class="auth-pill">Customer: place orders</span>

    </div>
@endsection

@section('form')
    <form class="auth-form" data-auth-form="register" data-endpoint="{{ url('/api/register') }}" data-success-copy="Registration successful. Your token is ready below.">
        <div class="auth-field-grid">
            <div class="auth-field">
                <label for="name">Full name</label>
                <input id="name" class="auth-input" type="text" name="name" autocomplete="name" placeholder="Aisyah Rahman" required>
                <div class="auth-error-text" data-error-for="name"></div>
            </div>

            <div class="auth-field">
                <label for="register-email">Email address</label>
                <input id="register-email" class="auth-input" type="email" name="email" autocomplete="email" placeholder="aisyah@selera.com" required>
                <div class="auth-error-text" data-error-for="email"></div>
            </div>

            <div class="auth-field">
                <label for="role">Role</label>
                <select id="role" class="auth-input" name="role" required>
                    <option value="customer" selected>Customer - place orders</option>
                    <option value="staff">Staff - manage orders</option>
                    <option value="admin">Admin - manage menu items</option>
                </select>
                <div class="auth-error-text" data-error-for="role"></div>
                <div class="auth-help">Choose the role that matches the permissions you need in the restaurant system.</div>
            </div>

            <div class="auth-field-row">
                <div class="auth-field">
                    <label for="register-password">Password</label>
                    <input id="register-password" class="auth-input" type="password" name="password" autocomplete="new-password" placeholder="Create a password" required>
                    <div class="auth-error-text" data-error-for="password"></div>
                </div>

                <div class="auth-field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat the password" required>
                    <div class="auth-error-text" data-error-for="password_confirmation"></div>
                </div>
            </div>
        </div>

       
        <div class="auth-actions">
            <button class="auth-button" type="submit">Start your account</button>
            <a class="auth-link" href="{{ route('login') }}">Already registered? Log in</a>
        </div>

        <div class="auth-feedback" data-auth-feedback></div>
        <div class="auth-token" data-auth-token></div>
    </form>
@endsection