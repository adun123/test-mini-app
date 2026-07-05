@extends('layouts.app', ['title' => 'Edit Employee'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Employee</h1>
        <p class="page-subtitle">Perbarui profil, department, status, dan akses login employee.</p>
    </div>
</div>

<section class="panel">
    <form method="POST" action="{{ route('admin.employees.update', $employee) }}">
        @csrf
        @method('PUT')
        @include('admin.employees.form')
        <div class="actions">
            <button class="button" type="submit">Update</button>
            <a class="button light" href="{{ route('admin.employees.index') }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
