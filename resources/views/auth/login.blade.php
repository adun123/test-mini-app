@extends('layouts.app', ['title' => 'Login'])

@section('content')
<section class="auth-form">
    <div class="auth-heading">
        <h1>Login</h1>
        <p class="muted">Mini Attendance App</p>
    </div>

    @if($errors->any())
        <div class="message danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="field">
            <label for="email">Email</label>
            <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input class="input" id="password" type="password" name="password" required>
        </div>
        <div class="field" style="display: flex; align-items: center; gap: 8px;">
            <input id="remember" type="checkbox" name="remember" value="1">
            <label for="remember" style="margin: 0; font-weight: 600;">Remember me</label>
        </div>
        <button class="button" type="submit">Login</button>
    </form>

    <div class="auth-link">
        Belum punya akun? <a href="{{ route('register') }}">Register</a>
    </div>
</section>
@endsection
