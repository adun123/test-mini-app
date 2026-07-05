<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'employee_id',
        'type',
        'reason',
        'requested_check_in_time',
        'requested_check_out_time',
        'requested_location',
        'requested_latitude',
        'requested_longitude',
        'requested_note',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
        'applied_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'applied_at' => 'datetime',
        'requested_check_in_time' => 'datetime',
        'requested_check_out_time' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
