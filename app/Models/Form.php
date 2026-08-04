<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'schema',
        'slug',
        'status',
        'is_public',
        'settings',
    ];

    protected $casts = [
        'schema' => 'array',
        'settings' => 'array',
        'is_public' => 'boolean',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
