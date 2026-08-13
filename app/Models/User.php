<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Observers\UserObserver;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Ptj;
use Illuminate\Support\Facades\Storage;
use Filament\Auth\Notifications\ResetPassword;

#[Fillable(['name', 'email', 'password', 'ptj_id', 'nokp', 'phone_number', 'status', 'role', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
// #[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }


    public function ptj()
    {
        return $this->belongsTo(Ptj::class, 'ptj_id');
    }

    public function isSuperAdmin()
    {
        return $this->role === 1;
    }

    public function isAdmin()
    {
        return $this->role === 2;
    }

    public function isUser()
    {
        return $this->role === 3;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar) {
            return Storage::disk('public')->url($this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    protected function name(): Attribute
{
    return Attribute::make(
        set: fn ($value) => mb_strtoupper(trim($value), 'UTF-8'),
    );
}
}

