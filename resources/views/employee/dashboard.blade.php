@extends('layouts.app', ['title' => 'Employee Dashboard'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Employee Dashboard</h1>
        <p class="page-subtitle">Check-in, check-out, dan pantau status attendance hari ini.</p>
    </div>
</div>

<section class="panel attendance-card">
    <div>
        <h2 style="margin-bottom: 4px;">Attendance Today</h2>
        <p class="muted" style="margin-bottom: 0;">Gunakan tombol di bawah untuk absen hari ini.</p>
    </div>
    @if(!$employee)
        <p>Data employee belum tersedia.</p>
    @else
        <div class="attendance-summary">
            <div class="attendance-item"><span>Check In</span><strong>{{ optional($todayAttendance?->check_in_time)->format('H:i') ?? '-' }}</strong></div>
            <div class="attendance-item"><span>Check Out</span><strong>{{ optional($todayAttendance?->check_out_time)->format('H:i') ?? '-' }}</strong></div>
            <div class="attendance-item"><span>Status</span><strong>{{ $todayAttendance ? ucfirst($todayAttendance->status) : '-' }}</strong></div>
            <div class="attendance-item"><span>Late</span><strong>{{ $todayAttendance->late_minutes ?? 0 }} min</strong></div>
        </div>

        <div class="attendance-actions">
            <form method="POST" action="{{ route('employee.attendance.check-in') }}" class="attendance-form">
                @csrf
                <input type="hidden" name="location" value="GPS Location">
                <input type="hidden" name="latitude">
                <input type="hidden" name="longitude">
                <input type="hidden" name="photo_data">
                <input type="hidden" name="note">
                <button class="button attendance-button js-open-check-in" type="button">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 5v14"></path>
                        <path d="M5 12h14"></path>
                    </svg>
                    Check In
                </button>
            </form>
            <form method="POST" action="{{ route('employee.attendance.check-out') }}" class="attendance-form">
                @csrf
                <input type="hidden" name="location" value="GPS Location">
                <input type="hidden" name="latitude">
                <input type="hidden" name="longitude">
                <button class="button secondary attendance-button" type="submit">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 12h14"></path>
                    </svg>
                    Check Out
                </button>
            </form>
        </div>

        <div class="attendance-location">
            <span>GPS location will be detected automatically when allowed by browser.</span>
        </div>
    @endif
</section>

<section class="panel" style="margin-top: 18px;">
    <h2 style="margin-bottom: 12px;">Attendance Summary</h2>
    <div class="attendance-summary">
        <div class="attendance-item"><span>Total Present</span><strong>{{ $attendanceSummary['present'] }}</strong></div>
        <div class="attendance-item"><span>Total Late</span><strong>{{ $attendanceSummary['late'] }}</strong></div>
        <div class="attendance-item"><span>Total Absent</span><strong>{{ $attendanceSummary['absent'] }}</strong></div>
    </div>
</section>

<div class="modal-backdrop" id="checkInModal" aria-hidden="true">
    <div class="camera-modal">
        <h2 style="margin-bottom: 4px;">Check In</h2>
        <p class="muted" style="margin-bottom: 0;">Ambil foto selfie dan izinkan GPS untuk menyimpan lokasi absen.</p>

        <div class="camera-box">
            <video id="cameraVideo" autoplay playsinline></video>
            <img id="cameraPreview" class="camera-preview" alt="Check-in photo preview">
        </div>

        <canvas id="cameraCanvas" style="display: none;"></canvas>

        <div class="field">
            <label for="checkInNote">Catatan</label>
            <textarea id="checkInNote" rows="3" placeholder="Opsional, contoh: meeting luar kantor"></textarea>
        </div>

        <div class="attendance-location" id="gpsStatus">GPS belum dibaca.</div>

        <div class="modal-actions">
            <button class="button secondary" type="button" id="capturePhotoButton">Ambil Foto</button>
            <button class="button" type="button" id="submitCheckInButton" disabled>Submit Check In</button>
            <button class="button light" type="button" id="retakePhotoButton" style="display: none;">Ulangi Foto</button>
            <button class="button light" type="button" id="closeCheckInModal">Cancel</button>
        </div>
    </div>
</div>

<section class="panel" style="margin-top: 18px;">
    <h2>Last 7 Attendance</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Late</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
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
                    <tr><td colspan="5">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<script>
    const checkInForm = document.querySelector('.js-open-check-in')?.closest('form');
    const checkInModal = document.getElementById('checkInModal');
    const cameraVideo = document.getElementById('cameraVideo');
    const cameraPreview = document.getElementById('cameraPreview');
    const cameraCanvas = document.getElementById('cameraCanvas');
    const capturePhotoButton = document.getElementById('capturePhotoButton');
    const retakePhotoButton = document.getElementById('retakePhotoButton');
    const submitCheckInButton = document.getElementById('submitCheckInButton');
    const closeCheckInModal = document.getElementById('closeCheckInModal');
    const checkInNote = document.getElementById('checkInNote');
    const gpsStatus = document.getElementById('gpsStatus');
    let cameraStream = null;
    let capturedPhoto = '';
    let gpsReady = false;

    function fillGps(form, callback) {
        if (!navigator.geolocation) {
            callback(false);
            return;
        }

        navigator.geolocation.getCurrentPosition(function (position) {
            form.querySelector('[name="latitude"]').value = position.coords.latitude;
            form.querySelector('[name="longitude"]').value = position.coords.longitude;
            callback(true, position);
        }, function () {
            callback(false);
        }, { enableHighAccuracy: true, timeout: 8000 });
    }

    function updateSubmitState() {
        submitCheckInButton.disabled = !(capturedPhoto && gpsReady);
    }

    function stopCamera() {
        if (!cameraStream) {
            return;
        }

        cameraStream.getTracks().forEach(function (track) {
            track.stop();
        });
        cameraStream = null;
    }

    async function openCameraModal() {
        capturedPhoto = '';
        gpsReady = false;
        updateSubmitState();
        cameraPreview.style.display = 'none';
        cameraVideo.style.display = 'block';
        retakePhotoButton.style.display = 'none';
        capturePhotoButton.style.display = 'inline-flex';
        checkInNote.value = '';
        gpsStatus.textContent = 'Membaca GPS...';
        checkInModal.classList.add('show');
        checkInModal.setAttribute('aria-hidden', 'false');

        fillGps(checkInForm, function (ok, position) {
            gpsReady = ok;
            gpsStatus.textContent = ok
                ? 'GPS terbaca: ' + position.coords.latitude.toFixed(6) + ', ' + position.coords.longitude.toFixed(6)
                : 'GPS gagal dibaca. Coba izinkan akses lokasi di browser.';
            updateSubmitState();
        });

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
            cameraVideo.srcObject = cameraStream;
        } catch (error) {
            gpsStatus.textContent = 'Kamera gagal dibuka. Pastikan browser mendapat izin kamera.';
        }
    }

    function closeModal() {
        stopCamera();
        checkInModal.classList.remove('show');
        checkInModal.setAttribute('aria-hidden', 'true');
    }

    document.querySelector('.js-open-check-in')?.addEventListener('click', openCameraModal);

    capturePhotoButton?.addEventListener('click', function () {
        if (!cameraVideo.videoWidth) {
            return;
        }

        cameraCanvas.width = cameraVideo.videoWidth;
        cameraCanvas.height = cameraVideo.videoHeight;
        cameraCanvas.getContext('2d').drawImage(cameraVideo, 0, 0);
        capturedPhoto = cameraCanvas.toDataURL('image/jpeg', 0.82);
        cameraPreview.src = capturedPhoto;
        cameraPreview.style.display = 'block';
        cameraVideo.style.display = 'none';
        capturePhotoButton.style.display = 'none';
        retakePhotoButton.style.display = 'inline-flex';
        stopCamera();
        updateSubmitState();
    });

    retakePhotoButton?.addEventListener('click', openCameraModal);
    closeCheckInModal?.addEventListener('click', closeModal);

    submitCheckInButton?.addEventListener('click', function () {
        checkInForm.querySelector('[name="photo_data"]').value = capturedPhoto;
        checkInForm.querySelector('[name="note"]').value = checkInNote.value;
        checkInForm.dataset.ready = '1';
        checkInForm.submit();
    });

    document.querySelectorAll('.attendance-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form === checkInForm && form.dataset.ready !== '1') {
                event.preventDefault();
                openCameraModal();
                return;
            }

            if (!navigator.geolocation || form.dataset.ready === '1') {
                return;
            }

            event.preventDefault();
            navigator.geolocation.getCurrentPosition(function (position) {
                form.querySelector('[name="latitude"]').value = position.coords.latitude;
                form.querySelector('[name="longitude"]').value = position.coords.longitude;
                form.dataset.ready = '1';
                form.submit();
            }, function () {
                form.dataset.ready = '1';
                form.submit();
            }, { enableHighAccuracy: true, timeout: 5000 });
        });
    });
</script>
@endsection
