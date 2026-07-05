@extends('layouts.app', ['title' => 'Departments'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Departments</h1>
        <p class="page-subtitle">Kelola master department untuk dropdown employee.</p>
    </div>
</div>

<section class="panel">
    <form method="POST" action="{{ route('admin.departments.store') }}" class="actions">
        @csrf
        <input class="input" style="max-width: 360px;" name="name" placeholder="Department name" required>
        <button class="button" type="submit">Add Department</button>
    </form>
</section>

<section class="panel" style="margin-top: 18px;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $department)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('admin.departments.update', $department) }}" class="actions">
                                @csrf
                                @method('PUT')
                                <input class="input" name="name" value="{{ $department->name }}" required>
                                <button class="button secondary" type="submit">Update</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" onsubmit="return confirm('Delete department ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="button danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2">Belum ada department.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 16px;">{{ $departments->links() }}</div>
</section>
@endsection
