<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmSchedule extends Model
{
    protected $fillable = ['scheduled_date', 'notes'];

    protected $casts = ['scheduled_date' => 'date'];

    public function scheduleOffices()
    {
        return $this->hasMany(PmScheduleOffice::class);
    }
}