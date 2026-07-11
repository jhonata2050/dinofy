<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;

interface BillingGatewayInterface
{
    public function generateMonthlyInvoice(Tenant $tenant): Invoice;

    public function createPixCharge(Invoice $invoice, Tenant $tenant): void;

    public function handleWebhook(array $payload): void;

    public function reconcile(): int;

    public function suspendOverdue(): int;
}
