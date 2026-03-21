<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Illuminate\Support\Facades\Auth;
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

    /**
     * @param  array<int, string>  $permissions
     */
    public function syncPermissionsWithActivityLog(array $permissions): void
    {
        $originalPermissions = $this->permissions()
            ->pluck('name')
            ->sort()
            ->values()
            ->all();

        $normalizedPermissions = collect($permissions)
            ->sort()
            ->values()
            ->all();

        $this->syncPermissions($permissions);

        $attachedPermissions = array_values(array_diff($normalizedPermissions, $originalPermissions));
        $detachedPermissions = array_values(array_diff($originalPermissions, $normalizedPermissions));

        if ($attachedPermissions === [] && $detachedPermissions === []) {
            return;
        }

        activity($this->getTable())
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->event('updated')
            ->withProperties([
                'attributes' => [
                    'attached_permissions' => $attachedPermissions,
                    'detached_permissions' => $detachedPermissions,
                ],
            ])
            ->log(class_basename($this).' updated');
    }
}
