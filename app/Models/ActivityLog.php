<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = ['tenant_id', 'action', 'description', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function log(string $action, string $description, ?int $tenantId = null, ?array $metadata = null): static
    {
        return static::create([
            'tenant_id' => $tenantId,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
