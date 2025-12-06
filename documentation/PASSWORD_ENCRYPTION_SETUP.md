# Password Encryption Setup Guide

This document explains the salt and pepper password encryption system implemented in ShortSight.

## Overview

The application now uses an enhanced password security system that combines:
- **Salt**: Unique random value for each user
- **Pepper**: Shared secret value for all users
- **Hashing**: Laravel's built-in bcrypt/argon hashing

## Environment Setup

### 1. Add Pepper to Environment Variables

Add the following to your `.env` file:

```env
# Salt and Pepper Encryption Configuration
# IMPORTANT: Generate a strong random string for the pepper (64+ characters recommended)
# You can generate one using: php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
APP_PEPPER=your_super_secure_random_pepper_string_here_change_this_in_production
```

### 2. Generate a Secure Pepper

Run this command to generate a secure pepper:

```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

Copy the output and use it as your `APP_PEPPER` value.

## Database Migration

### For New Installations

The users table is created with all necessary fields in the original migration (`2014_10_12_000000_create_users_table.php`). Simply run:

```bash
php artisan migrate
```

The users table includes:
- Basic authentication fields (name, email, password)
- Salt and pepper encryption fields (password_salt)
- Social login fields (google_id, facebook_id, facebook_token)

### For Existing Installations

If you have an existing database with a users table that was created from separate migrations, you have two options:

**Option 1: Fresh Start (Recommended for development)**
```bash
php artisan migrate:rollback --step=1  # Rollback the users table migration
php artisan migrate                     # Re-run with the complete table structure
```

**Option 2: Keep Existing Data**
If you need to preserve existing user data, you'll need alter migrations to add the missing fields:
- `password_salt` (string, nullable)
- `facebook_id` (string, nullable, unique)
- `facebook_token` (text, nullable)

## How It Works

### Password Hashing Process
1. User enters password
2. System generates unique salt (32 characters)
3. Password is combined: `password + salt + pepper`
4. Combined string is hashed using Laravel's Hash facade
5. Both hash and salt are stored in database

### Password Verification Process
1. User enters password for login
2. System retrieves user's salt from database
3. Password is combined: `entered_password + stored_salt + pepper`
4. Combined string is verified against stored hash

## Security Benefits

- **Salt**: Prevents rainbow table attacks by making each hash unique
- **Pepper**: Adds extra security layer that protects against database breaches
- **Rehashing**: Automatically upgrades hashes when bcrypt parameters change
- **Backward Compatibility**: Works with existing Laravel authentication

## Important Security Notes

⚠️ **CRITICAL**: Never change the `APP_PEPPER` value after going to production. This will invalidate all existing password hashes and lock out all users.

⚠️ **CRITICAL**: Keep the pepper secret. Never commit it to version control or expose it in logs.

⚠️ **RECOMMENDED**: Use environment-specific peppers (different for development/staging/production).

## API Usage

The system integrates seamlessly with existing Laravel authentication:

```php
// Creating a user with encrypted password
$user = User::create([
    'name'      => 'John Doe',
    'email'     => 'john@example.com',
    'password'  => 'secure_password' // Automatically encrypted
]);

// Verifying password
if ($user->verifyPassword('entered_password')) {
    // Password is correct
}

// Checking if password needs rehash (automatic)
$user->rehashPasswordIfNeeded('entered_password');
```

## Testing

You can test the encryption system:

```php
// In tinker or a test
$service = app(\App\Services\PasswordEncryptionService::class);

// Hash a password
$result = $service->hashPassword('test_password');
// Returns: ['hash' => '...', 'salt' => '...']

// Verify password
$isValid = $service->verifyPassword('test_password', $result['hash'], $result['salt']);
// Returns: true
```

## Migration from Old System

The users table is created fresh with all necessary fields. If you have an existing database:

1. **Fresh Installation**: Simply run `php artisan migrate` - the table will be created with all fields
2. **Existing Database**: If you need to migrate from an old system, you may need to:
   - Backup your existing data
   - Drop the old users table (if it exists)
   - Run the migration to create the new complete table structure
   - Import your user data (passwords will need to be reset as the new system uses salt+pepper encryption)

## Troubleshooting

### Pepper Not Set Error
If you see: "APP_PEPPER environment variable must be set"
- Add `APP_PEPPER` to your `.env` file
- Use a strong random string (32+ bytes recommended)

### Authentication Failures
- Verify `APP_PEPPER` is set correctly
- Check that migration ran successfully
- Ensure salts were generated for existing users

### Performance Considerations
- Salt generation uses cryptographically secure random bytes
- Pepper is loaded once per request
- Hashing uses Laravel's optimized bcrypt implementation
