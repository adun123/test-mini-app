@extends('layouts.app', ['title' => 'Register'])

@section('content')
<section class="auth-form wide">
    <div class="auth-heading">
        <h1>Register</h1>
        <p class="muted">Buat akun Mini Attendance App</p>
    </div>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="form-grid">
            <div class="field">
                <label for="name">Name</label>
                <input class="input" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="employee" @selected(old('role', 'employee') === 'employee')>Employee</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin / HR</option>
                </select>
            </div>
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="password">Password</label>
                <input class="input" id="password" type="password" name="password" required>
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm Password</label>
                <input class="input" id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
        </div>
        <button class="button" type="submit">Register</button>
    </form>

    <div class="auth-link">
        Sudah punya akun? <a href="{{ route('login') }}">Login</a>
    </div>
</section>
@endsection
