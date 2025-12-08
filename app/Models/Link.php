<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Link extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'links';

    protected $fillable = [
        'user',
        'title',
        'description',
        'url',
        'slug',
        'is_disabled',
    ];

    protected $casts = [
        'is_disabled' => 'boolean',
    ];

    /**
     * Get the user that owns the link
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user');
    }

    /**
     * Get the visitors for this link
     */
    public function visitors()
    {
        return $this->hasMany(Visitor::class, 'link_id');
    }
}
