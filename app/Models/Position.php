<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $fillable = ['name', 'module_access'];

    protected function casts(): array
    {
        return ['module_access' => 'array'];
    }

    public function hasModuleAccess(string $module): bool
    {
        return in_array($module, $this->module_access ?? [], true);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'position', 'name');
    }
}
