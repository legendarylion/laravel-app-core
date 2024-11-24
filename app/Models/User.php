<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The default profile photo URL if no photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the disk that profile photos should be stored on.
     *
     * @return string
     */
    protected function profilePhotoDisk()
    {
        return 'public';
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_path',
        'provider_name',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'email_verified_at',
    ];


    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function debugTwoFactor($code)
    {
        $google2fa = app(Google2FA::class);

        if (!$this->two_factor_secret) {
            \Log::info('User has no two_factor_secret set.', ['id' => $this->id]);
            return false;
        }

        $isValid = $google2fa->verifyKey(decrypt($this->two_factor_secret), $code);

        \Log::info('Two-factor debug:', [
            'user_id' => $this->id,
            'provided_code' => $code,
            'two_factor_secret' => decrypt($this->two_factor_secret),
            'is_valid' => $isValid,
        ]);

        return $isValid;
    }
}