<?php

namespace Spiggle\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAuditLog extends Model
{
    protected $fillable = [
        'form_id',
        'user_id',
        'action',
        'payload',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function getTable(): string
    {
        return config('form-builder.tables.audit_logs', 'form_builder_audit_logs');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class), 'user_id');
    }
}
