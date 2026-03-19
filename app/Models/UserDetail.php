<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
    *
    *    
    */
    public function getPictureAttribute($value)
    {
        $path = public_path('storage/images/users/' . $value);
        return $value && file_exists($path) ? asset('/storage/images/users/' . $value) : asset('/storage/images/users/default-avatar.png');
    }
}
