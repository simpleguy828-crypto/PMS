<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmRecordItem extends Model
{
    protected $fillable = ['pm_record_id', 'pm_checklist_item_id', 'status', 'date_completed', 'remarks'];

    protected $casts = [
        'date_completed' => 'date',
    ];

    public function checklistItem()
    {
        return $this->belongsTo(PmChecklistItem::class, 'pm_checklist_item_id');
    }

    public function pmRecord()
    {
        return $this->belongsTo(PmRecord::class);
    }
}