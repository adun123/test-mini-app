<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function index()
    {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('employee.dashboard');
    }

    public function admin()
    {
        $today = Carbon::today()->toDateString();
        $totalEmployees = Employee::where('status', 'active')->count();
        $presentToday = Attendance::whereDate('attendance_date', $today)->count();
        $lateToday = Attendance::whereDate('attendance_date', $today)->where('status', 'late')->count();
        $notCheckedIn = max($totalEmployees - $presentToday, 0);
        $recentAttendances = Attendance::with('employee.user')->latest()->limit(10)->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'presentToday',
            'lateToday',
            'notCheckedIn',
            'recentAttendances'
        ));
    }

    public function employee()
    {
        $employee = auth()->user()->employee;
        $todayAttendance = $employee
            ? Attendance::where('employee_id', $employee->id)->whereDate('attendance_date', today())->first()
            : null;
        $attendances = $employee
            ? Attendance::where('employee_id', $employee->id)->latest('attendance_date')->limit(7)->get()
            : collect();
        $attendanceSummary = $employee
            ? $this->employeeAttendanceSummary($employee)
            : ['present' => 0, 'late' => 0, 'absent' => 0];

        return view('employee.dashboard', compact('employee', 'todayAttendance', 'attendances', 'attendanceSummary'));
    }

    private function employeeAttendanceSummary(Employee $employee): array
    {
        $present = Attendance::where('employee_id', $employee->id)->where('status', 'present')->count();
        $late = Attendance::where('employee_id', $employee->id)->where('status', 'late')->count();
        $workDays = $this->countWeekdays($employee->created_at?->copy()->startOfDay() ?? today(), today());
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
}
