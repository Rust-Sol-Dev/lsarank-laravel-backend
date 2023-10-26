<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'active',
        'paid',
        'logo_file_name',
        'logo_file_path',
        'intercom_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        if ($this->hasRole('Admin')) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is customer
     *
     * @return bool
     */
    public function isCustomer()
    {
        if ($this->hasRole('Customer')) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is active
     *
     * @return bool
     */
    public function isActive()
    {
        if ($this->active == 1) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is active
     *
     * @return bool
     */
    public function isPaid()
    {
        if ($this->paid == 1) {
            return true;
        }

        return false;
    }

    /**
     * Get users keyword searches
     *
     * @return mixed
     */
    public function getUsersKeywords()
    {
        if (!$this->relationLoaded('keywords')) {
           $this->load('keywords');
        }

        return $this->keywords;
    }

    /**
     * Return count of users keyword searches
     *
     * @return int
     */
    public function getKeywordCountAttribute()
    {
        $kewords = $this->getUsersKeywords();

        return count($kewords);
    }

    /**
     * Update users timezone
     *
     * @param $timeZone
     * @return bool
     */
    public function updateTimezone($timeZone)
    {
        $this->tz = $timeZone;
        $this->save();
        return true;
    }

    //////// Relations //////

    /**
     * User has many keywords
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function keywords()
    {
        return $this->hasMany(Keyword::class, 'user_id', 'id');
    }

    /**
     * User has one preference
     *
     * @param $keywordId
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function preference($keywordId)
    {
        return $this->hasOne(UserEntityPreference::class, 'user_id', 'id')->where('keyword_id', $keywordId);
    }

    /**
     * User has many Business Entities through Lsa Keywords
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function businessEntities()
    {
        return $this->hasManyThrough(
            BusinessEntity::class,
            Keyword::class,
            'user_id', // Foreign key on the lsa_keywords...
            'keyword_id', // Foreign key on the lsa_business_entities...
            'id', // Local key on the lsa_keywords table...
            'id' // Local key on the lsa_business_entities table...
        );
    }
}
