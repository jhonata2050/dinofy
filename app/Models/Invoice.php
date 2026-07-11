<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'tenant_id', 'plan_id', 'amount_cents', 'period_start', 'period_end',
        'due_date', 'status', 'gateway_charge_id', 'pix_copy_paste',
        'pix_qr_code', 'idempotency_key', 'paid_at',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function amountFormatted(): string
    {
        return number_format($this->amount_cents / 100, 2, ',', '.');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }
}
