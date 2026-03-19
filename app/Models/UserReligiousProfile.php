<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserReligiousProfile extends Model
{
    use HasFactory;

    protected $table = 'user_religious_profiles';
    protected $primaryKey = 'user_id';
    public $incrementing = false; // vì user_id là primary key nhưng không auto increment
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',

        'baptism_date',
        'baptism_place',
        'baptismal_sponsor',

        'first_communion_date',
        'first_communion_place',
        'first_communion_sponsor',

        'confirmation_date',
        'confirmation_place',
        'confirmation_bishop',

        'pledge_date',
        'pledge_place',
        'pledge_sponsor',

        'status_religious',
        'is_attendance',
    ];

    protected $casts = [
        'baptism_date' => 'date',
        'first_communion_date' => 'date',
        'confirmation_date' => 'date',
        'pledge_date' => 'date',
        'is_attendance' => 'boolean',
    ];

    /**
     * Profile thuộc về 1 User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
