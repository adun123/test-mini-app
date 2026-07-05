<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceApiController extends Controller
{
    public function checkIn(Request $request, AttendanceController $attendanceController)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'timestamp' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'photo_data' => ['nullable', 'string'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $result = $attendanceController->recordCheckIn(
            Employee::findOrFail($data['employee_id']),
            Carbon::parse($data['timestamp']),
            $data['location'] ?? null,
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $data['photo_data'] ?? null,
            $data['note'] ?? null
        );

        return response()->json($result, $result['ok'] ? 201 : 422);
    }

    public function checkOut(Request $request, AttendanceController $attendanceController)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'timestamp' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $result = $attendanceController->recordCheckOut(
            Employee::findOrFail($data['employee_id']),
            Carbon::parse($data['timestamp']),
            $data['location'] ?? null,
            $data['latitude'] ?? null,
            $data['longitude'] ?? null
        );

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    public function history(Request $request)
    {
        $attendances = Attendance::with('employee.user')
            ->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('attendance_date', $request->date))
            ->latest('attendance_date')
            ->get();

        return response()->json(['data' => $attendances]);
    }
}
