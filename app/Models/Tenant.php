<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'plan_id', 'subdomain', 'name', 'email', 'phone', 'document',
        'status', 'compose_project', 'data_path', 'db_password', 'app_key',
        'custom_domain', 'next_billing_date', 'trial_ends_at', 'notes',
    ];

    protected $casts = [
        'next_billing_date' => 'date',
        'trial_ends_at' => 'date',
    ];

    protected $hidden = ['db_password', 'app_key'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function tenantUsers(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function fullUrl(): string
    {
        $domain = $this->custom_domain ?: $this->subdomain . '.' . config('master.base_domain');
        return 'https://' . $domain;
    }

    public function projectName(): string
    {
        return $this->compose_project ?: 'tenant-' . $this->subdomain;
    }
}
