<?php

namespace App\Models;

use App\Traits\LogsActivityTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, LogsActivityTrait, Notifiable, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'username',
        'password',
        'phone',
        'avatar',
        'is_active',
        'is_superadmin',
        'is_peserta',
        'deletion_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_superadmin' => 'boolean',
            'is_peserta' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_superadmin === true;
    }

    public function isCompanyAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isPeserta(): bool
    {
        return $this->is_peserta === true;
    }

    public function langgananAktif(): HasOne
    {
        return $this->hasOne(PesertaLangganan::class, 'user_id')
            ->where('status', 'active')
            ->latestOfMany();
    }

    public function langganan(): HasMany
    {
        return $this->hasMany(PesertaLangganan::class, 'user_id');
    }

    public function pesertaPembayaran(): HasMany
    {
        return $this->hasMany(PesertaPembayaran::class, 'user_id');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    public function scopePeserta($query)
    {
        return $query->where('is_peserta', true);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function activeDeviceTokens()
    {
        return $this->deviceTokens()->where('is_active', true);
    }

    /**
     * Check if this is a demo account
     */
    public function isDemoAccount(): bool
    {
        return str_ends_with($this->email, '@demo.gajipro.com')
            || $this->email === 'superadmin@gajipro.com';
    }

    /**
     * Override default email verification to use peserta route
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \Illuminate\Auth\Notifications\VerifyEmail);
        // Note: The VerifyEmail notification by default uses route('verification.verify').
        // Since we are using a custom auth flow for peserta, we can customize the VerifyEmail 
        // notification in AppServiceProvider or simply rely on the default if the route name matches.
        // For Panritta, we override the URL generation in AppServiceProvider or here if needed.
    }

    /**
     * Prevent deletion of demo accounts
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->isDemoAccount()) {
                throw new \Exception('Akun demo tidak dapat dihapus.');
            }
        });
    }
}
