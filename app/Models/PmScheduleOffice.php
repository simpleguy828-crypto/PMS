<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmScheduleOffice extends Model
{
    protected $fillable = ['pm_schedule_id', 'office_id', 'current_scheduled_date'];

    protected $casts = ['current_scheduled_date' => 'date'];

    public function schedule()
    {
        return $this->belongsTo(PmSchedule::class, 'pm_schedule_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function reschedules()
    {
        return $this->hasMany(PmScheduleReschedule::class);
    }
}