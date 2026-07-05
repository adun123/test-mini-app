@extends('layouts.app', ['title' => 'HR Dashboard'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">HR Dashboard</h1>
        <p class="page-subtitle">Pantau attendance hari ini, keterlambatan, dan aktivitas terbaru employee.</p>
    </div>
    <a class="button" href="{{ route('admin.employees.create') }}">Add Employee</a>
</div>

<div class="grid">
    <div class="stat"><span>Total Employees</span><strong>{{ $totalEmployees }}</strong></div>
    <div class="stat"><span>Present Today</span><strong>{{ $presentToday }}</strong></div>
    <div class="stat"><span>Late Today</span><strong>{{ $lateToday }}</strong></div>
    <div class="stat"><span>Not Checked In</span><strong>{{ $notCheckedIn }}</strong></div>
</div>

<section class="panel" style="margin-top: 18px;">
    <div class="actions" style="justify-content: space-between;">
        <div>
            <h2 style="margin-bottom: 4px;">Recent Attendance</h2>
            <p class="muted" style="margin-bottom: 0;">Data check-in dan check-out terbaru.</p>
        </div>
        <a class="button light" href="{{ route('attendance.history') }}">View History</a>
    </div>
    <div class="table-wrap" style="margin-top: 16px;">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Late</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAttendances as $attendance)
                    <tr>
                        <td>
                            <strong>{{ $attendance->employee->user->name ?? '-' }}</strong>
                            <div class="muted">{{ $attendance->employee->employee_code ?? '-' }}</div>
                        </td>
                        <td>{{ $attendance->attendance_date->format('Y-m-d') }}</td>
                        <td>{{ optional($attendance->check_in_time)->format('H:i') ?? '-' }}</td>
                        <td>{{ optional($attendance->check_out_time)->format('H:i') ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $attendance->status === 'late' ? 'warning' : 'success' }}">
                                {{ ucfirst($attendance->status) }}
                            </span>
                        </td>
                        <td>{{ $attendance->late_minutes }} min</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada data absensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
