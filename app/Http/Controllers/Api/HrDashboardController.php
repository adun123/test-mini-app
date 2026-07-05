<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Carbon;

class HrDashboardController extends Controller
{
    public function summary()
    {
        $today = Carbon::today()->toDateString();
        $totalEmployees = Employee::where('status', 'active')->count();
        $presentEmployeeIds = Attendance::whereDate('attendance_date', $today)->pluck('employee_id');
        $lateEmployees = Attendance::with('employee.user')
            ->whereDate('attendance_date', $today)
            ->where('status', 'late')
            ->get();
        $notCheckedIn = Employee::with('user')
            ->where('status', 'active')
            ->whereNotIn('id', $presentEmployeeIds)
            ->get();

        return response()->json([
            'date' => $today,
            'total_employees' => $totalEmployees,
            'present_today' => $presentEmployeeIds->count(),
            'late_today' => $lateEmployees->count(),
            'not_checked_in_today' => $notCheckedIn->count(),
            'late_employees' => $lateEmployees,
            'not_checked_in_employees' => $notCheckedIn,
        ]);
    }
}
