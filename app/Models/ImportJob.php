<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'status',
        'file_path',
        'result_schema',
        'message',
        'metadata',
    ];

    protected $casts = [
        'result_schema' => 'array',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
