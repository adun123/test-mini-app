@extends('layouts.app', ['title' => 'Work Schedules'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Work Schedules</h1>
        <p class="page-subtitle">Set jam kerja default atau khusus per department untuk menghitung keterlambatan.</p>
    </div>
</div>

<section class="panel">
    <form method="POST" action="{{ route('admin.attendance-settings.store') }}" class="form-grid">
        @csrf
        <div class="field">
            <label for="department">Department</label>
            <select id="department" name="department">
                <option value="">Default / All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->name }}">{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="work_start_time">Start Time</label>
            <input class="input" id="work_start_time" type="time" name="work_start_time" value="08:00" required>
        </div>
        <div class="field">
            <label for="work_end_time">End Time</label>
            <input class="input" id="work_end_time" type="time" name="work_end_time" value="17:00" required>
        </div>
        <div class="field">
            <label for="late_tolerance_minutes">Tolerance</label>
            <input class="input" id="late_tolerance_minutes" type="number" name="late_tolerance_minutes" value="0" min="0" required>
        </div>
        <div class="field" style="align-self: end;">
            <button class="button" type="submit">Add Schedule</button>
        </div>
    </form>
</section>

<section class="panel" style="margin-top: 18px;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Scope</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Tolerance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($settings as $setting)
                    <tr>
                        <form method="POST" action="{{ route('admin.attendance-settings.update', $setting) }}">
                            @csrf
                            @method('PUT')
                            <td>
                                <select name="department">
                                    <option value="" @selected($setting->department === null)>Default / All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}" @selected($setting->department === $department->name)>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input class="input" type="time" name="work_start_time" value="{{ substr($setting->work_start_time, 0, 5) }}" required></td>
                            <td><input class="input" type="time" name="work_end_time" value="{{ substr($setting->work_end_time, 0, 5) }}" required></td>
                            <td><input class="input" type="number" name="late_tolerance_minutes" value="{{ $setting->late_tolerance_minutes }}" min="0" required></td>
                            <td>
                                <div class="actions">
                                    <button class="button secondary" type="submit">Update</button>
                        </form>
                                    <form method="POST" action="{{ route('admin.attendance-settings.destroy', $setting) }}" onsubmit="return confirm('Delete schedule ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada schedule.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 16px;">{{ $settings->links() }}</div>
</section>
@endsection
