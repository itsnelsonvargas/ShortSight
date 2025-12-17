<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\RedisCacheService;
use App\Services\PasswordEncryptionService;

class Link extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'links';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'url',
        'slug',
        'is_disabled',
        'expires_at',
        'auto_delete_expired',
        'is_password_protected',
        'password_hash',
        'password_salt',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_disabled' => 'boolean',
        'is_password_protected' => 'boolean',
        'auto_delete_expired' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the link.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the visitors/clicks for this link.
     */
    public function visitors()
    {
        return $this->hasMany(Visitor::class, 'slug', 'slug');
    }

    /**
     * Get the click count for this link.
     */
    public function getClickCountAttribute()
    {
        return $this->visitors()->count();
    }

    /**
     * Set the password for link protection
     *
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $encryptionService = app(PasswordEncryptionService::class);
        $result = $encryptionService->hashPassword($password);

        $this->password_hash = $result['hash'];
        $this->password_salt = $result['salt'];
        $this->is_password_protected = true;
    }

    /**
     * Verify a password against the stored hash
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        if (!$this->is_password_protected || empty($this->password_hash) || empty($this->password_salt)) {
            return false;
        }

        $encryptionService = app(PasswordEncryptionService::class);
        return $encryptionService->verifyPassword($password, $this->password_hash, $this->password_salt);
    }

    /**
     * Remove password protection from the link
     *
     * @return void
     */
    public function removePassword(): void
    {
        $this->is_password_protected = false;
        $this->password_hash = null;
        $this->password_salt = null;
    }

    /**
     * Check if the link has expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the link should be auto-deleted when expired
     *
     * @return bool
     */
    public function shouldAutoDelete(): bool
    {
        return $this->auto_delete_expired && $this->isExpired();
    }

    /**
     * Scope to get only active (non-expired) links
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to get only expired links
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Boot the model and add event listeners for cache invalidation
     */
    protected static function boot()
    {
        parent::boot();

        // Invalidate cache when link is updated
        static::updated(function ($link) {
            $cacheService = app(RedisCacheService::class);
            $cacheService->invalidateSlugCache($link->slug);

            // If URL changed, also invalidate URL safety cache
            if ($link->wasChanged('url')) {
                $cacheService->invalidateUrlSafetyCache($link->getOriginal('url'));
            }
        });

        // Invalidate cache when link is deleted
        static::deleted(function ($link) {
            $cacheService = app(RedisCacheService::class);
            $cacheService->invalidateSlugCache($link->slug);
            $cacheService->invalidateUrlSafetyCache($link->url);
        });

        // Cache link metadata when created
        static::created(function ($link) {
            $cacheService = app(RedisCacheService::class);
            $cacheService->cacheSlugLookup($link->slug, $link);
            $cacheService->cacheLinkMetadata($link);
        });
    }
}
