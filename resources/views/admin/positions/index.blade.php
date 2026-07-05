@extends('layouts.app', ['title' => 'Positions'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Positions</h1>
        <p class="page-subtitle">Kelola master position untuk dropdown employee.</p>
    </div>
</div>

<section class="panel">
    <form method="POST" action="{{ route('admin.positions.store') }}" class="actions">
        @csrf
        <input class="input" style="max-width: 360px;" name="name" placeholder="Position name" required>
        <button class="button" type="submit">Add Position</button>
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
                @forelse($positions as $position)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('admin.positions.update', $position) }}" class="actions">
                                @csrf
                                @method('PUT')
                                <input class="input" name="name" value="{{ $position->name }}" required>
                                <button class="button secondary" type="submit">Update</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.positions.destroy', $position) }}" onsubmit="return confirm('Delete position ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="button danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2">Belum ada position.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 16px;">{{ $positions->links() }}</div>
</section>
@endsection
