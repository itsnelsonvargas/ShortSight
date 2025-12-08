<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'user',
        'title',
        'description',
        'url',
        'slug',
        'is_disabled',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_disabled' => 'boolean',
    ];

    /**
     * Get the user that owns the link.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user');
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
}
