<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
    use LogsModelActivity;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $table = 'user_details';

    protected $fillable = [
        'user_id',
        'picture',
        'bio',
        'phone',
        'address',
        'gender',
    ];

    /**
     * Relationship with User
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPictureAttribute($value)
    {
        $path = public_path('storage/images/users/'.$value);

        return $value && file_exists($path) ? asset('/storage/images/users/'.$value) : asset('/storage/images/users/default-avatar.png');
    }
}
