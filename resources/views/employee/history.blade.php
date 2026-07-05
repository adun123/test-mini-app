@extends('layouts.app', ['title' => 'Attendance History'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Attendance History</h1>
        <p class="page-subtitle">Filter riwayat attendance berdasarkan employee atau tanggal.</p>
    </div>
</div>

<section class="panel">
    <form method="GET" class="form-grid">
        @if(auth()->user()->isAdmin())
            <div class="field">
                <label for="employee_id">Employee</label>
                <select id="employee_id" name="employee_id">
                    <option value="">All Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id)>
                            {{ $employee->employee_code }} - {{ $employee->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="department">Department</label>
                <select id="department" name="department">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->name }}" @selected(request('department') === $department->name)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All Status</option>
                <option value="present" @selected(request('status') === 'present')>Present</option>
                <option value="late" @selected(request('status') === 'late')>Late</option>
            </select>
        </div>
        <div class="field">
            <label for="date">Date</label>
            <input class="input" id="date" type="date" name="date" value="{{ request('date') }}">
        </div>
        <div class="field" style="align-self: end;">
            <button class="button" type="submit">Filter</button>
            <a class="button light" href="{{ route('attendance.history') }}">Reset</a>
        </div>
    </form>
</section>

<section class="panel" style="margin-top: 18px;">
    <h2 style="margin-bottom: 12px;">Attendance Summary</h2>
    <div class="attendance-summary">
        <div class="attendance-item"><span>Present</span><strong>{{ $attendanceSummary['present'] }}</strong></div>
        <div class="attendance-item"><span>Late</span><strong>{{ $attendanceSummary['late'] }}</strong></div>
        <div class="attendance-item"><span>Absent</span><strong>{{ $attendanceSummary['absent'] }}</strong></div>
    </div>
</section>

<section class="panel" style="margin-top: 18px;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Location</th>
                    <th>Photo</th>
                    <th>Note</th>
                    <th>Status</th>
                    <th>Late</th>
                    @if(!auth()->user()->isAdmin())
                        <th>Correction</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td>
                            <strong>{{ $attendance->employee->user->name ?? '-' }}</strong>
                            <div class="muted">{{ $attendance->employee->employee_code ?? '-' }}</div>
                        </td>
                        <td>{{ $attendance->attendance_date->format('Y-m-d') }}</td>
                        <td>{{ optional($attendance->check_in_time)->format('H:i') ?? '-' }}</td>
                        <td>{{ optional($attendance->check_out_time)->format('H:i') ?? '-' }}</td>
                        <td>
                            {{ $attendance->check_in_location ?? '-' }}
                            @if($attendance->check_in_latitude && $attendance->check_in_longitude)
                                <div class="muted">{{ $attendance->check_in_latitude }}, {{ $attendance->check_in_longitude }}</div>
                                <a class="button light" style="margin-top: 6px;" href="https://www.google.com/maps?q={{ $attendance->check_in_latitude }},{{ $attendance->check_in_longitude }}" target="_blank">Open Maps</a>
                            @endif
                        </td>
                        <td>
                            @if($attendance->check_in_photo)
                                <a class="button light" href="{{ asset('storage/'.$attendance->check_in_photo) }}" target="_blank">View</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            {{ $attendance->check_in_note ?: '-' }}
                            @if($attendance->correction_note)
                                <div class="badge warning" style="margin-top: 6px;">{{ $attendance->correction_note }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $attendance->status === 'late' ? 'warning' : 'success' }}">
                                {{ ucfirst($attendance->status) }}
                            </span>
                        </td>
                        <td>{{ $attendance->late_minutes }} min</td>
                        @if(!auth()->user()->isAdmin())
                            <td>
                                <button
                                    class="button light js-open-correction"
                                    type="button"
                                    data-action="{{ route('employee.attendance.correction.store', $attendance) }}"
                                    data-date="{{ $attendance->attendance_date->format('Y-m-d') }}"
                                >
                                    Request
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->isAdmin() ? 9 : 10 }}">Data tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 16px;">{{ $attendances->links() }}</div>
</section>

@if(!auth()->user()->isAdmin())
    <div class="modal-backdrop" id="correctionModal" aria-hidden="true">
        <div class="camera-modal">
            <h2 style="margin-bottom: 4px;">Request Correction</h2>
            <p class="muted" id="correctionDateText" style="margin-bottom: 16px;">Isi data yang perlu dikoreksi.</p>

            <form method="POST" id="correctionForm">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="correctionType">Correction Type</label>
                        <select id="correctionType" name="type" required>
                            <option value="check_in">Check In</option>
                            <option value="check_out">Check Out</option>
                            <option value="location">Location</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="requestedCheckIn">New Check In</label>
                        <input class="input" id="requestedCheckIn" type="time" name="requested_check_in_time">
                    </div>
                    <div class="field">
                        <label for="requestedCheckOut">New Check Out</label>
                        <input class="input" id="requestedCheckOut" type="time" name="requested_check_out_time">
                    </div>
                    <div class="field">
                        <label for="requestedLocation">New Location</label>
                        <input class="input" id="requestedLocation" name="requested_location" placeholder="Nama lokasi">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label for="requestedLatitude">Latitude</label>
                        <input class="input" id="requestedLatitude" name="requested_latitude" placeholder="-6.200000">
                    </div>
                    <div class="field">
                        <label for="requestedLongitude">Longitude</label>
                        <input class="input" id="requestedLongitude" name="requested_longitude" placeholder="106.816666">
                    </div>
                </div>

                <div class="field">
                    <label for="requestedNote">Replacement Note</label>
                    <textarea id="requestedNote" name="requested_note" rows="2" placeholder="Catatan pengganti, opsional"></textarea>
                </div>
                <div class="field">
                    <label for="correctionReason">Reason</label>
                    <textarea id="correctionReason" name="reason" rows="3" placeholder="Alasan koreksi" required></textarea>
                </div>

                <div class="modal-actions">
                    <button class="button" type="submit">Submit Request</button>
                    <button class="button light" type="button" id="closeCorrectionModal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const correctionModal = document.getElementById('correctionModal');
        const correctionForm = document.getElementById('correctionForm');
        const correctionDateText = document.getElementById('correctionDateText');
        const closeCorrectionModal = document.getElementById('closeCorrectionModal');

        document.querySelectorAll('.js-open-correction').forEach(function (button) {
            button.addEventListener('click', function () {
                correctionForm.reset();
                correctionForm.action = button.dataset.action;
                correctionDateText.textContent = 'Attendance date: ' + button.dataset.date;
                correctionModal.classList.add('show');
                correctionModal.setAttribute('aria-hidden', 'false');
            });
        });

        closeCorrectionModal.addEventListener('click', function () {
            correctionModal.classList.remove('show');
            correctionModal.setAttribute('aria-hidden', 'true');
        });
    </script>
@endif
@endsection
