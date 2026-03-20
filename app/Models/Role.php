<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
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
