<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $employees = Employee::with('user')
            ->when($search, function ($query) use ($search) {
                $query->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $employees->getCollection()->transform(function ($employee) {
            $employee->present_total = Attendance::where('employee_id', $employee->id)->where('status', 'present')->count();
            $employee->late_total = Attendance::where('employee_id', $employee->id)->where('status', 'late')->count();
            $workDays = $this->countWeekdays($employee->created_at?->copy()->startOfDay() ?? today(), today());
            $employee->absent_total = max($workDays - $employee->present_total - $employee->late_total, 0);
            $employee->attendance_details = Attendance::where('employee_id', $employee->id)
                ->latest('attendance_date')
                ->get();
            $employee->absent_details = $this->absentDates($employee);

            return $employee;
        });

        return view('admin.employees.index', compact('employees', 'search'));
    }

    public function create()
    {
        return view('admin.employees.create', [
            'departments' => Department::orderBy('name')->get(),
            'positions' => Position::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'min:6'],
            'position' => ['nullable', 'string', 'max:100', 'exists:positions,name'],
            'department' => ['nullable', 'string', 'max:100', 'exists:departments,name'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'employee',
        ]);

        Employee::create($data + [
            'user_id' => $user->id,
            'employee_code' => Employee::generateCode(),
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee berhasil ditambahkan.');
    }

    public function edit(Employee $employee)
    {
        $employee->load('user');

        return view('admin.employees.edit', [
            'employee' => $employee,
            'departments' => Department::orderBy('name')->get(),
            'positions' => Position::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($employee->user_id)],
            'password' => ['nullable', 'min:6'],
            'position' => ['nullable', 'string', 'max:100', 'exists:positions,name'],
            'department' => ['nullable', 'string', 'max:100', 'exists:departments,name'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $employee->user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $employee->user->update(['password' => Hash::make($data['password'])]);
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', 'Employee berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->user()->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee berhasil dihapus.');
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

    private function absentDates(Employee $employee): array
    {
        $attendanceDates = Attendance::where('employee_id', $employee->id)
            ->pluck('attendance_date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->all();
        $absentDates = [];

        foreach (CarbonPeriod::create($employee->created_at?->copy()->startOfDay() ?? today(), today()) as $date) {
            if ($date->isWeekday() && ! in_array($date->format('Y-m-d'), $attendanceDates, true)) {
                $absentDates[] = $date->format('Y-m-d');
            }
        }

        return $absentDates;
    }
}
