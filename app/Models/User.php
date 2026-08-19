<?php

namespace App\Models;

use App\Enums\UserType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property UserType $user_type
 * @property string $first_name
 * @property string $last_name
 * @property string $email_address
 * @property string $user_name
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_name
 */
#[Fillable([
    'user_type',
    'first_name',
    'last_name',
    'email_address',
    'user_name',
    'password',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_type' => UserType::class,
            'password' => 'hashed',
        ];
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function isSuperAdmin(): bool
    {
        return $this->user_type === UserType::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this->user_type === UserType::Admin;
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}
