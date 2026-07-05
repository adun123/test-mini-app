<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'location_id',
        'attendance_date',
        'check_in_time',
        'check_in_location',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_photo',
        'check_in_note',
        'correction_note',
        'corrected_at',
        'check_out_time',
        'check_out_location',
        'check_out_latitude',
        'check_out_longitude',
        'status',
        'late_minutes',
        'is_within_area',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'corrected_at' => 'datetime',
        'is_within_area' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }
}
