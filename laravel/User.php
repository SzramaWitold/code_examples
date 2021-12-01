<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\FacebookProvider;

/**
 * @method searchByPhrase(string $phrase)
 * @method excludeMyself()
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'birth',
        'gender'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function findForPassport($username)
    {
        return $this->newQuery()->where('username', $username)->orWhere('email', $username)->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        if (!$this->getAttribute('password')) {
            try {
                /** @var FacebookProvider $socialite */
                $socialite = Socialite::driver('facebook');
                $socialite->stateless()->userFromToken($password);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return Hash::check($password, $this->getAttribute('password'));
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class,'event_user')->withPivot('type');
    }

    public function scopeSearchByPhrase($query, string $phrase)
    {
        return $query->where('username', 'like', '%' . $phrase . '%')->orWhere('email', 'like', '%' . $phrase . '%');
    }

    public function scopeExcludeMyself($query)
    {
        return $query->where('id', '!=', Auth::user()->getAuthIdentifier());
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }
}
