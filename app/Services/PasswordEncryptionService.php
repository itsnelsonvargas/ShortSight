<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordEncryptionService
{
    /**
     * The pepper value used for additional password security.
     * This should be set in your .env file as APP_PEPPER
     */
    private string $pepper;

    /**
     * The length of the salt to generate
     */
    private int $saltLength = 32;

    public function __construct()
    {
        $this->pepper = env('APP_PEPPER', 'default_pepper_change_this_in_production');

        // Ensure pepper is set and not the default
        if ($this->pepper === 'default_pepper_change_this_in_production') {
            throw new \Exception('APP_PEPPER environment variable must be set for security. Please set a strong random string in your .env file.');
        }
    }

    /**
     * Hash a password using salt and pepper method
     *
     * @param string $password
     * @param string|null $salt If null, a new salt will be generated
     * @return array Returns ['hash' => string, 'salt' => string]
     */
    public function hashPassword(string $password, ?string $salt = null): array
    {
        // Generate salt if not provided
        if ($salt === null) {
            $salt = $this->generateSalt();
        }

        // Apply pepper: password + salt + pepper
        $pepperedPassword = $password . $salt . $this->pepper;

        // Hash using Laravel's Hash facade (bcrypt by default)
        $hash = Hash::make($pepperedPassword);

        return [
            'hash' => $hash,
            'salt' => $salt
        ];
    }

    /**
     * Verify a password against a hash using salt and pepper
     *
     * @param string $password
     * @param string $hash
     * @param string $salt
     * @return bool
     */
    public function verifyPassword(string $password, string $hash, string $salt): bool
    {
        // Apply the same peppering: password + salt + pepper
        $pepperedPassword = $password . $salt . $this->pepper;

        // Check using Laravel's Hash facade
        return Hash::check($pepperedPassword, $hash);
    }

    /**
     * Generate a cryptographically secure salt
     *
     * @return string
     */
    public function generateSalt(): string
    {
        return Str::random($this->saltLength);
    }

    /**
     * Rehash a password if needed (for upgrading hashing parameters)
     *
     * @param string $password
     * @param string $currentHash
     * @param string $salt
     * @return array|null Returns new hash data if rehash needed, null otherwise
     */
    public function rehashIfNeeded(string $password, string $currentHash, string $salt): ?array
    {
        if (Hash::needsRehash($currentHash)) {
            return $this->hashPassword($password, $salt);
        }

        return null;
    }

    /**
     * Get the pepper value (for debugging/testing purposes)
     *
     * @return string
     */
    public function getPepper(): string
    {
        return $this->pepper;
    }

    /**
     * Set salt length (for testing purposes)
     *
     * @param int $length
     * @return void
     */
    public function setSaltLength(int $length): void
    {
        $this->saltLength = $length;
    }
}
