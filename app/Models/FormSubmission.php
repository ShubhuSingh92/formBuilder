<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'payload',
        'ip_address',
        'user_agent',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
