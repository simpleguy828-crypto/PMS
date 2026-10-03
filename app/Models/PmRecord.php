<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmRecord extends Model
{
    protected $fillable = [
        'office_id',
        'requested_by_name',
        'position',
        'date_started',
        'conducted_by',
        'status',
        // NOTE: 'department' is passed by PreventiveMaintenanceForm::save() but was
        // missing from $fillable before, so it was being silently dropped on save.
        // Keep this line ONLY if your `pm_records` table actually has a `department`
        // column of its own. If it doesn't, remove this line and rely on the
        // office() relationship below instead (e.g. $record->office->name).
        'department',
    ];

    protected $casts = [
        'date_started' => 'date',
    ];

    /**
     * The office this PM record was conducted for.
     * Fixes: RelationNotFoundException — Call to undefined relationship [office].
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * The checklist item results tied to this record.
     * Needed by PmRecordsList::downloadPdf() / downloadDocx().
     */
    public function recordItems()
    {
        return $this->hasMany(PmRecordItem::class);
    }

    /**
     * The user who conducted this PM inspection.
     */
    public function conductedBy()
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }
}