<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $table = 'visitors';

    protected $fillable = [
        'slug',
        'ip_address',
        'user_agent',
        'browser',
        'device',
        'platform',
        'referer',
        'country',
        'city',
        'region',
        'postal_code',
        'latitude',
        'longitude',
        'has_vpn',
        'vpn',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'has_vpn' => 'boolean',
    ];

    /**
     * Get the link that this visitor accessed
     */
    public function link()
    {
        return $this->belongsTo(Link::class, 'slug', 'slug');
    }
}
