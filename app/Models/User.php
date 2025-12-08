<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Services\PasswordEncryptionService;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_salt',
        'google_id',
        'facebook_id',
        'facebook_token',
    ]; 

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'password_salt',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /**
     * Set the password attribute with salt and pepper encryption
     *
     * @param string $password
     * @return void
     */
    public function setPasswordAttribute(string $password): void
    {
        $encryptionService = app(PasswordEncryptionService::class);
        $result = $encryptionService->hashPassword($password);

        $this->attributes['password'] = $result['hash'];
        $this->attributes['password_salt'] = $result['salt'];
    }

    /**
     * Verify a password against the stored hash using salt and pepper
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        if (empty($this->password) || empty($this->password_salt)) {
            return false;
        }

        $encryptionService = app(PasswordEncryptionService::class);
        return $encryptionService->verifyPassword($password, $this->password, $this->password_salt);
    }

    /**
     * Check if password needs rehashing and rehash if necessary
     *
     * @param string $password
     * @return void
     */
    public function rehashPasswordIfNeeded(string $password): void
    {
        $encryptionService = app(PasswordEncryptionService::class);
        $rehashResult = $encryptionService->rehashIfNeeded($password, $this->password, $this->password_salt);

        if ($rehashResult) {
            $this->attributes['password'] = $rehashResult['hash'];
            $this->attributes['password_salt'] = $rehashResult['salt'];
            $this->save();
        }
    }

    /**
     * Get the links for the user
     */
    public function links()
    {
        return $this->hasMany(Link::class, 'user');
    }
}
