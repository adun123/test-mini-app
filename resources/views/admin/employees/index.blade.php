@extends('layouts.app', ['title' => 'Employees'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Employees</h1>
        <p class="page-subtitle">Kelola data employee, department, status, dan akun login.</p>
    </div>
    <a class="button" href="{{ route('admin.employees.create') }}">Add Employee</a>
</div>

<section class="panel">
    <form method="GET" class="actions">
        <input class="input" style="max-width: 420px;" name="search" value="{{ $search }}" placeholder="Search name, email, code, department">
        <button class="button" type="submit">Search</button>
        <a class="button light" href="{{ route('admin.employees.index') }}">Reset</a>
    </form>
</section>

<section class="panel" style="margin-top: 18px;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Attendance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td><strong>{{ $employee->employee_code }}</strong></td>
                        <td>{{ $employee->user->name }}</td>
                        <td class="muted">{{ $employee->user->email }}</td>
                        <td>{{ $employee->department ?? '-' }}</td>
                        <td>{{ $employee->position ?? '-' }}</td>
                        <td>
                            <div class="actions" style="gap: 6px;">
                                <button
                                    class="badge success js-open-attendance-detail"
                                    type="button"
                                    data-employee-id="{{ $employee->id }}"
                                    data-employee-name="{{ $employee->user->name }}"
                                    data-filter="present"
                                    style="border: 0; cursor: pointer;"
                                >
                                    P {{ $employee->present_total }}
                                </button>
                                <button
                                    class="badge warning js-open-attendance-detail"
                                    type="button"
                                    data-employee-id="{{ $employee->id }}"
                                    data-employee-name="{{ $employee->user->name }}"
                                    data-filter="late"
                                    style="border: 0; cursor: pointer;"
                                >
                                    L {{ $employee->late_total }}
                                </button>
                                <button
                                    class="badge danger js-open-attendance-detail"
                                    type="button"
                                    data-employee-id="{{ $employee->id }}"
                                    data-employee-name="{{ $employee->user->name }}"
                                    data-filter="absent"
                                    style="border: 0; cursor: pointer;"
                                >
                                    A {{ $employee->absent_total }}
                                </button>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $employee->status === 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="actions" style="gap: 6px;">
                                <a class="button secondary small" href="{{ route('admin.employees.edit', $employee) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" onsubmit="return confirm('Delete employee ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button danger small" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">Belum ada employee.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 16px;">{{ $employees->links() }}</div>
</section>

<div class="modal-backdrop" id="attendanceDetailModal" aria-hidden="true">
    <div class="camera-modal" style="width: min(920px, 100%);">
        <div class="actions" style="justify-content: space-between; margin-bottom: 14px;">
            <div>
                <h2 id="attendanceDetailTitle" style="margin-bottom: 4px;">Attendance Detail</h2>
                <p class="muted" style="margin-bottom: 0;">Detail tanggal, lokasi, foto, dan catatan attendance.</p>
            </div>
            <button class="button light small" id="closeAttendanceDetailModal" type="button">Close</button>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                        <th>Late</th>
                        <th>Location</th>
                        <th>Photo</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody id="attendanceDetailRows">
                    <tr><td colspan="8">No attendance data.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const employeeAttendanceDetails = {
        @foreach($employees as $employee)
            "{{ $employee->id }}": [
                @foreach($employee->attendance_details as $attendance)
                    {
                        date: "{{ $attendance->attendance_date->format('Y-m-d') }}",
                        checkIn: "{{ optional($attendance->check_in_time)->format('H:i') ?? '-' }}",
                        checkOut: "{{ optional($attendance->check_out_time)->format('H:i') ?? '-' }}",
                        status: "{{ ucfirst($attendance->status) }}",
                        statusKey: "{{ $attendance->status }}",
                        statusClass: "{{ $attendance->status === 'late' ? 'warning' : 'success' }}",
                        late: "{{ $attendance->late_minutes }} min",
                        location: "{{ $attendance->check_in_location ?? '-' }}",
                        latitude: "{{ $attendance->check_in_latitude }}",
                        longitude: "{{ $attendance->check_in_longitude }}",
                        photoUrl: "{{ $attendance->check_in_photo ? asset('storage/'.$attendance->check_in_photo) : '' }}",
                        note: @json($attendance->check_in_note ?: '-')
                    },
                @endforeach
            ],
        @endforeach
    };

    const employeeAbsentDetails = {
        @foreach($employees as $employee)
            "{{ $employee->id }}": [
                @foreach($employee->absent_details as $absentDate)
                    {
                        date: "{{ $absentDate }}",
                        checkIn: "-",
                        checkOut: "-",
                        status: "Absent",
                        statusKey: "absent",
                        statusClass: "danger",
                        late: "-",
                        location: "-",
                        latitude: "",
                        longitude: "",
                        photoUrl: "",
                        note: "No attendance record"
                    },
                @endforeach
            ],
        @endforeach
    };

    const detailModal = document.getElementById('attendanceDetailModal');
    const detailTitle = document.getElementById('attendanceDetailTitle');
    const detailRows = document.getElementById('attendanceDetailRows');
    const closeDetailModal = document.getElementById('closeAttendanceDetailModal');

    document.querySelectorAll('.js-open-attendance-detail').forEach(function (button) {
        button.addEventListener('click', function () {
            const filter = button.dataset.filter;
            const allRows = filter === 'absent'
                ? (employeeAbsentDetails[button.dataset.employeeId] || [])
                : (employeeAttendanceDetails[button.dataset.employeeId] || []);
            const rows = filter === 'absent'
                ? allRows
                : allRows.filter(function (row) { return row.statusKey === filter; });
            const filterTitle = filter.charAt(0).toUpperCase() + filter.slice(1);
            detailTitle.textContent = filterTitle + ' Detail - ' + button.dataset.employeeName;

            if (!rows.length) {
                detailRows.innerHTML = '<tr><td colspan="8">No attendance data.</td></tr>';
            } else {
                detailRows.innerHTML = rows.map(function (row) {
                    const mapsLink = row.latitude && row.longitude
                        ? '<a class="button light small" href="https://www.google.com/maps?q=' + row.latitude + ',' + row.longitude + '" target="_blank">Maps</a>'
                        : '';
                    const photoLink = row.photoUrl
                        ? '<a class="button light small" href="' + row.photoUrl + '" target="_blank">Photo</a>'
                        : '-';

                    return '<tr>' +
                        '<td>' + row.date + '</td>' +
                        '<td>' + row.checkIn + '</td>' +
                        '<td>' + row.checkOut + '</td>' +
                        '<td><span class="badge ' + row.statusClass + '">' + row.status + '</span></td>' +
                        '<td>' + row.late + '</td>' +
                        '<td>' + row.location + '<div style="margin-top: 6px;">' + mapsLink + '</div></td>' +
                        '<td>' + photoLink + '</td>' +
                        '<td>' + row.note + '</td>' +
                    '</tr>';
                }).join('');
            }

            detailModal.classList.add('show');
            detailModal.setAttribute('aria-hidden', 'false');
        });
    });

    closeDetailModal.addEventListener('click', function () {
        detailModal.classList.remove('show');
        detailModal.setAttribute('aria-hidden', 'true');
    });
</script>
@endsection
