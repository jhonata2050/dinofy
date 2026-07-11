<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'cpu_limit', 'memory_limit', 'storage_limit_gb',
        'max_db_connections', 'price_cents', 'is_active',
    ];

    protected $casts = [
        'cpu_limit' => 'decimal:2',
        'storage_limit_gb' => 'integer',
        'max_db_connections' => 'integer',
        'price_cents' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function priceFormatted(): string
    {
        return number_format($this->price_cents / 100, 2, ',', '.');
    }

    public function cpuReservation(): string
    {
        return (string) round($this->cpu_limit / 2, 2);
    }

    public function memoryReservation(): string
    {
        $mb = (int) filter_var($this->memory_limit, FILTER_SANITIZE_NUMBER_INT);
        return max(64, (int) ($mb / 2)) . 'M';
    }
}
