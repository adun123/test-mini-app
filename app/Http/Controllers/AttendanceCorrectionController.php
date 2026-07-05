<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSetting;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AttendanceCorrectionController extends Controller
{
    public function store(Request $request, Attendance $attendance)
    {
        $employee = $request->user()->employee;

        abort_if(! $employee || $attendance->employee_id !== $employee->id, 403);

        $data = $request->validate([
            'type' => ['required', Rule::in(['check_in', 'check_out', 'location', 'other'])],
            'reason' => ['required', 'string', 'max:1000'],
            'requested_check_in_time' => ['nullable', 'date_format:H:i'],
            'requested_check_out_time' => ['nullable', 'date_format:H:i'],
            'requested_location' => ['nullable', 'string', 'max:255'],
            'requested_latitude' => ['nullable', 'numeric'],
            'requested_longitude' => ['nullable', 'numeric'],
            'requested_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $existingPending = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->with('error', 'Kamu masih punya correction request yang pending untuk attendance ini.');
        }

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'type' => $data['type'],
            'reason' => $data['reason'],
            'requested_check_in_time' => $this->combineDateAndTime($attendance, $data['requested_check_in_time'] ?? null),
            'requested_check_out_time' => $this->combineDateAndTime($attendance, $data['requested_check_out_time'] ?? null),
            'requested_location' => $data['requested_location'] ?? null,
            'requested_latitude' => $data['requested_latitude'] ?? null,
            'requested_longitude' => $data['requested_longitude'] ?? null,
            'requested_note' => $data['requested_note'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Correction request berhasil dikirim ke HR.');
    }

    public function index()
    {
        $corrections = AttendanceCorrection::with(['attendance', 'employee.user', 'reviewer'])
            ->latest()
            ->paginate(15);

        return view('admin.corrections.index', compact('corrections'));
    }

    public function update(Request $request, AttendanceCorrection $correction)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['status'] === 'approved') {
            $this->applyCorrection($correction, $data['admin_note'] ?? null);
        }

        $correction->update([
            'status' => $data['status'],
            'admin_note' => $data['admin_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'applied_at' => $data['status'] === 'approved' ? now() : null,
        ]);

        return back()->with('success', 'Correction request berhasil diperbarui.');
    }

    private function combineDateAndTime(Attendance $attendance, ?string $time): ?Carbon
    {
        if (! $time) {
            return null;
        }

        return Carbon::parse($attendance->attendance_date->format('Y-m-d').' '.$time);
    }

    private function applyCorrection(AttendanceCorrection $correction, ?string $adminNote): void
    {
        $attendance = $correction->attendance;
        $updates = [];

        if ($correction->requested_check_in_time) {
            $updates['check_in_time'] = $correction->requested_check_in_time;
        }

        if ($correction->requested_check_out_time) {
            $updates['check_out_time'] = $correction->requested_check_out_time;
        }

        if ($correction->requested_location) {
            if (in_array($correction->type, ['location', 'check_in', 'other'], true)) {
                $updates['check_in_location'] = $correction->requested_location;
            }

            if (in_array($correction->type, ['check_out'], true)) {
                $updates['check_out_location'] = $correction->requested_location;
            }
        }

        if ($correction->requested_latitude && $correction->requested_longitude) {
            if (in_array($correction->type, ['location', 'check_in', 'other'], true)) {
                $updates['check_in_latitude'] = $correction->requested_latitude;
                $updates['check_in_longitude'] = $correction->requested_longitude;
            }

            if (in_array($correction->type, ['check_out'], true)) {
                $updates['check_out_latitude'] = $correction->requested_latitude;
                $updates['check_out_longitude'] = $correction->requested_longitude;
            }
        }

        if ($correction->requested_note) {
            $updates['check_in_note'] = $correction->requested_note;
        }

        $updates['correction_note'] = trim('Attendance telah diupdate oleh HR. '.$adminNote);
        $updates['corrected_at'] = now();

        if (isset($updates['check_in_time'])) {
            $setting = AttendanceSetting::where('department', $attendance->employee?->department)->first()
                ?: AttendanceSetting::whereNull('department')->first()
                ?: AttendanceSetting::first();
            $workStart = Carbon::parse($attendance->attendance_date->format('Y-m-d').' '.($setting->work_start_time ?? '08:00:00'));
            $tolerance = (int) ($setting->late_tolerance_minutes ?? 0);
            $lateMinutes = max($workStart->copy()->addMinutes($tolerance)->diffInMinutes(Carbon::parse($updates['check_in_time']), false), 0);
            $updates['late_minutes'] = $lateMinutes;
            $updates['status'] = $lateMinutes > 0 ? 'late' : 'present';
        }

        $attendance->update($updates);
    }
}
