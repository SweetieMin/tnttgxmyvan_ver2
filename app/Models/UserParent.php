<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserParent extends Model
{
    use LogsModelActivity,SoftDeletes;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $table = 'user_parents';

    protected $fillable = [
        'user_id',
        'christian_name_father',
        'name_father',
        'phone_father',
        'christian_name_mother',
        'name_mother',
        'phone_mother',
        'christian_name_god_parent',
        'name_god_parent',
        'phone_god_parent',

    ];

    /**
     * 🔹 Quan hệ 1-1 với model User
     * Một user sẽ có một bản ghi user_parents.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
