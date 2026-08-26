<?php

namespace App\Models;

use App\Notifications\AdminResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name_ar',
        'display_name_en',
        'email',
        'password',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    /**
     * Hidden attributes.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casts.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * إرسال إشعار إعادة تعيين كلمة المرور بالرابط الخاص بلوحة تحكم المدير.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new AdminResetPassword($token));
    }

    /**
     * Return the admin avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='
            .urlencode($this->name)
            .'&background=5E2324&color=F5DEB3';
    }

/**
 * اسم الكاتب المناسب للغة الحالية.
 */
public function displayName(?string $locale = null): string
{
    $locale ??= app()->getLocale();

    if ($locale === 'en') {
        return $this->display_name_en
            ?: $this->display_name_ar
            ?: $this->name;
    }

    return $this->display_name_ar
        ?: $this->display_name_en
        ?: $this->name;
}
}
