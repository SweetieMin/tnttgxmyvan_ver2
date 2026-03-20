<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use LogsModelActivity;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
    ];
}
