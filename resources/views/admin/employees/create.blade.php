@extends('layouts.app', ['title' => 'Add Employee'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add Employee</h1>
        <p class="page-subtitle">Buat akun login dan data employee baru.</p>
    </div>
</div>

<section class="panel">
    <form method="POST" action="{{ route('admin.employees.store') }}">
        @csrf
        @include('admin.employees.form')
        <div class="actions">
            <button class="button" type="submit">Save</button>
            <a class="button light" href="{{ route('admin.employees.index') }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
