<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmScheduleReschedule extends Model
{
    protected $fillable = ['old_date', 'new_date', 'rescheduled_at'];

    protected $casts = [
        'old_date' => 'date',
        'new_date' => 'date',
        'rescheduled_at' => 'datetime',
    ];

    public function scheduleOffice()
    {
        return $this->belongsTo(PmScheduleOffice::class);
    }
}