<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceSettingController extends Controller
{
    public function index()
    {
        $settings = AttendanceSetting::orderByRaw('department IS NOT NULL')
            ->orderBy('department')
            ->paginate(10);
        $departments = Department::orderBy('name')->get();

        return view('admin.attendance-settings.index', compact('settings', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department' => ['nullable', 'string', 'max:100', 'exists:departments,name'],
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => ['required', 'date_format:H:i'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:240'],
        ]);

        $exists = AttendanceSetting::where('department', $data['department'])->exists();

        if ($exists) {
            return back()->with('error', 'Schedule untuk department tersebut sudah ada.');
        }

        AttendanceSetting::create($data);

        return back()->with('success', 'Work schedule berhasil ditambahkan.');
    }

    public function update(Request $request, AttendanceSetting $attendanceSetting)
    {
        $data = $request->validate([
            'department' => ['nullable', 'string', 'max:100', 'exists:departments,name'],
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => ['required', 'date_format:H:i'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:240'],
        ]);

        $exists = AttendanceSetting::where('department', $data['department'])
            ->where('id', '!=', $attendanceSetting->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Schedule untuk department tersebut sudah ada.');
        }

        $attendanceSetting->update($data);

        return back()->with('success', 'Work schedule berhasil diperbarui.');
    }

    public function destroy(AttendanceSetting $attendanceSetting)
    {
        if ($attendanceSetting->department === null && AttendanceSetting::whereNull('department')->count() <= 1) {
            return back()->with('error', 'Default schedule tidak boleh dihapus.');
        }

        $attendanceSetting->delete();

        return back()->with('success', 'Work schedule berhasil dihapus.');
    }
}
