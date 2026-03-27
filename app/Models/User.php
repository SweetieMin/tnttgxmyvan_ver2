<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\LogsModelActivity;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsModelActivity, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'christian_name',
        'last_name',
        'name',
        'birthday',
        'username',
        'email',
        'password',
        'status_login',
        'token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'full_name',
        'christian_full_name',
        'short_name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'birthday' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function getTokenQrCode()
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(250, 1, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(0, 0, 0))),
                new SvgImageBackEnd
            )
        ))->writeString(url('/profile/'.$this->token));

        return $svg;
    }

    /**
     * Get the full name of the user.
     * Example: "Nguyễn Văn A"
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name} {$this->name}");
    }

    /**
     * Get the Christian full name of the user.
     * Example: "Phêrô Nguyễn Văn A"
     */
    public function getChristianFullNameAttribute(): string
    {
        return trim("{$this->christian_name} {$this->last_name} {$this->name}");
    }

    /**
     * Get the short name of the user.
     * Example: "Văn A"
     */
    public function getShortNameAttribute(): string
    {
        return trim($this->name);
    }

    /**
     * Relationship with User Detail
     *
     * @return HasOne
     */
    public function details()
    {
        return $this->hasOne(UserDetail::class);
    }

    /**
     * Relationship with User Parent
     *
     * @return HasOne
     */
    public function parents()
    {
        return $this->hasOne(UserParent::class);
    }

    /**
     * Relationship with User Religious Profile
     *
     * @return HasOne
     */
    public function religious_profile()
    {
        return $this->hasOne(UserReligiousProfile::class);
    }

    /**
     * Relationship with User Setting
     *
     * @return HasOne
     */
    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }
}
