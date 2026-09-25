<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmChecklistItem extends Model
{
    protected $fillable = ['section', 'task_name', 'sort_order', 'is_active', 'finding_label'];
}