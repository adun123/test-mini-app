<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $data = $request->validate([
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'photo_data' => ['nullable', 'string'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = $request->user()->employee;

        if (! $employee) {
            return back()->withErrors(['attendance' => 'Data employee belum tersedia.']);
        }

        $result = $this->recordCheckIn(
            $employee,
            now(),
            $data['location'] ?? null,
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $data['photo_data'] ?? null,
            $data['note'] ?? null
        );

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function checkOut(Request $request)
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return back()->withErrors(['attendance' => 'Data employee belum tersedia.']);
        }

        $result = $this->recordCheckOut($employee, now(), $request->input('location'), $request->input('latitude'), $request->input('longitude'));

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function history(Request $request)
    {
        $query = Attendance::with('employee.user');
        $summaryQuery = Attendance::query();
        $employeeScope = Employee::query();

        if (! $request->user()->isAdmin()) {
            $query->where('employee_id', optional($request->user()->employee)->id);
            $summaryQuery->where('employee_id', optional($request->user()->employee)->id);
            $employeeScope->where('id', optional($request->user()->employee)->id);
        }

        $query->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('attendance_date', $request->date))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('department'), function ($q) use ($request) {
                $q->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('department', $request->department));
            });

        $summaryQuery->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('attendance_date', $request->date))
            ->when($request->filled('department'), function ($q) use ($request) {
                $q->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('department', $request->department));
            });

        $employeeScope->when($request->filled('employee_id'), fn ($q) => $q->where('id', $request->employee_id))
            ->when($request->filled('department'), fn ($q) => $q->where('department', $request->department));

        $attendances = $query->latest('attendance_date')->paginate(15)->withQueryString();
        $employees = Employee::with('user')->orderBy('employee_code')->get();
        $departments = Department::orderBy('name')->get();
        $attendanceSummary = $this->attendanceSummary($summaryQuery, $employeeScope, $request->date);

        return view('employee.history', compact('attendances', 'employees', 'departments', 'attendanceSummary'));
    }

    public function recordCheckIn(Employee $employee, Carbon $timestamp, ?string $locationText, $latitude = null, $longitude = null, ?string $photoData = null, ?string $note = null): array
    {
        $attendanceDate = $timestamp->toDateString();

        if (Attendance::where('employee_id', $employee->id)->whereDate('attendance_date', $attendanceDate)->exists()) {
            return ['ok' => false, 'message' => 'Kamu sudah check-in hari ini.'];
        }

        [$location, $isWithinArea] = $this->detectLocation($latitude, $longitude);
        $setting = $this->resolveAttendanceSetting($employee);
        $workStart = Carbon::parse($attendanceDate.' '.($setting->work_start_time ?? '08:00:00'));
        $tolerance = (int) ($setting->late_tolerance_minutes ?? 0);
        $lateMinutes = max($workStart->copy()->addMinutes($tolerance)->diffInMinutes($timestamp, false), 0);
        $photoPath = $this->storeCheckInPhoto($photoData, $employee->id, $attendanceDate);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'location_id' => $location?->id,
            'attendance_date' => $attendanceDate,
            'check_in_time' => $timestamp,
            'check_in_location' => $locationText ?: $location?->name,
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
            'check_in_photo' => $photoPath,
            'check_in_note' => $note,
            'status' => $lateMinutes > 0 ? 'late' : 'present',
            'late_minutes' => $lateMinutes,
            'is_within_area' => $isWithinArea,
        ]);

        $this->logAttendanceEvent('check_in', $attendance);

        return ['ok' => true, 'message' => 'Check-in berhasil.'];
    }

    public function recordCheckOut(Employee $employee, Carbon $timestamp, ?string $locationText, $latitude = null, $longitude = null): array
    {
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $timestamp->toDateString())
            ->first();

        if (! $attendance) {
            return ['ok' => false, 'message' => 'Kamu belum check-in hari ini.'];
        }

        if ($attendance->check_out_time) {
            return ['ok' => false, 'message' => 'Kamu sudah check-out hari ini.'];
        }

        [$location, $isWithinArea] = $this->detectLocation($latitude, $longitude);

        $attendance->update([
            'location_id' => $attendance->location_id ?: $location?->id,
            'check_out_time' => $timestamp,
            'check_out_location' => $locationText ?: $location?->name,
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'is_within_area' => $attendance->is_within_area && $isWithinArea,
        ]);

        $this->logAttendanceEvent('check_out', $attendance->fresh());

        return ['ok' => true, 'message' => 'Check-out berhasil.'];
    }

    private function logAttendanceEvent(string $event, Attendance $attendance): void
    {
        Storage::append('attendance_events.jsonl', json_encode([
            'event' => $event,
            'attendance_id' => $attendance->id,
            'employee_id' => $attendance->employee_id,
            'attendance_date' => optional($attendance->attendance_date)->format('Y-m-d'),
            'check_in_time' => optional($attendance->check_in_time)->toDateTimeString(),
            'check_out_time' => optional($attendance->check_out_time)->toDateTimeString(),
            'status' => $attendance->status,
            'late_minutes' => $attendance->late_minutes,
            'check_in_photo' => $attendance->check_in_photo,
            'check_in_note' => $attendance->check_in_note,
            'logged_at' => now()->toDateTimeString(),
        ]));
    }

    private function storeCheckInPhoto(?string $photoData, int $employeeId, string $attendanceDate): ?string
    {
        if (! $photoData || ! str_starts_with($photoData, 'data:image')) {
            return null;
        }

        [$meta, $encodedImage] = array_pad(explode(',', $photoData, 2), 2, null);

        if (! $encodedImage) {
            return null;
        }

        $extension = str_contains($meta, 'image/png') ? 'png' : 'jpg';
        $image = base64_decode($encodedImage, true);

        if ($image === false) {
            return null;
        }

        $path = 'attendance-photos/check-in-'.$employeeId.'-'.$attendanceDate.'-'.time().'.'.$extension;
        Storage::disk('public')->put($path, $image);

        return $path;
    }

    private function resolveAttendanceSetting(Employee $employee): ?AttendanceSetting
    {
        return AttendanceSetting::where('department', $employee->department)->first()
            ?: AttendanceSetting::whereNull('department')->first()
            ?: AttendanceSetting::first();
    }

    private function attendanceSummary($attendanceQuery, $employeeQuery, ?string $date): array
    {
        $present = (clone $attendanceQuery)->where('status', 'present')->count();
        $late = (clone $attendanceQuery)->where('status', 'late')->count();
        $employees = $employeeQuery->get(['id', 'created_at']);
        $workDays = 0;
        $targetDate = $date ? Carbon::parse($date) : null;

        foreach ($employees as $employee) {
            if ($targetDate) {
                $workDays += $targetDate->isWeekday() ? 1 : 0;
            } else {
                $workDays += $this->countWeekdays($employee->created_at?->copy()->startOfDay() ?? today(), today());
            }
        }

        $absent = max($workDays - $present - $late, 0);

        return compact('present', 'late', 'absent');
    }

    private function countWeekdays(Carbon $start, Carbon $end): int
    {
        $days = 0;

        foreach (CarbonPeriod::create($start, $end) as $date) {
            if ($date->isWeekday()) {
                $days++;
            }
        }

        return $days;
    }

    private function detectLocation($latitude, $longitude): array
    {
        if (! $latitude || ! $longitude) {
            return [null, true];
        }

        $nearest = null;
        $nearestDistance = null;

        foreach (Location::whereNotNull('latitude')->whereNotNull('longitude')->get() as $location) {
            $distance = $this->distanceInMeters((float) $latitude, (float) $longitude, (float) $location->latitude, (float) $location->longitude);

            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearest = $location;
                $nearestDistance = $distance;
            }
        }

        if (! $nearest) {
            return [null, true];
        }

        return [$nearest, $nearestDistance <= $nearest->radius_meter];
    }

    private function distanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
