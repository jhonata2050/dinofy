<?php

namespace App\Console\Commands;

use App\Services\BillingGatewayFactory;
use Illuminate\Console\Command;

class BillingSuspendOverdueCommand extends Command
{
    protected $signature = 'billing:suspend-overdue';
    protected $description = 'Suspende tenants com faturas vencidas alem do grace period';

    public function handle(): int
    {
        $billing = BillingGatewayFactory::make();
        $suspended = $billing->suspendOverdue();
        $this->info("{$suspended} tenant(s) suspenso(s) por inadimplencia.");
        return self::SUCCESS;
    }
}
