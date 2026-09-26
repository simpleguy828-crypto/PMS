<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PmRecord;

class Office extends Model
{
    protected $fillable = ['name', 'department', 'status', 'computer_count'];

    public function pmRecords()
    {
        return $this->hasMany(PmRecord::class, 'office_id');
    }

    public function scheduleOffices()
    {
        return $this->hasMany(PmScheduleOffice::class);
    }
}
