<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'description', 'quantity', 'unit_price_cents', 'total_cents',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_cents' => 'integer',
        'total_cents' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function totalFormatted(): string
    {
        return number_format($this->total_cents / 100, 2, ',', '.');
    }

    public function unitPriceFormatted(): string
    {
        return number_format($this->unit_price_cents / 100, 2, ',', '.');
    }
}
