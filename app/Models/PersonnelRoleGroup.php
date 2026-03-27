<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelRoleGroup extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'group_key',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
