<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    protected $fillable = [
        'tenant_id', 'domain', 'verification_token', 'verified_at', 'ssl_provisioned',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'ssl_provisioned' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
