<?php

namespace App\Console\Commands;

use App\Services\BillingGatewayFactory;
use Illuminate\Console\Command;

class BillingReconcileCommand extends Command
{
    protected $signature = 'billing:reconcile';
    protected $description = 'Reconcilia pagamentos pendentes com o gateway ativo';

    public function handle(): int
    {
        $billing = BillingGatewayFactory::make();
        $this->info("Gateway ativo: " . BillingGatewayFactory::activeGateway());

        $confirmed = $billing->reconcile();
        $this->info("Reconciliacao: {$confirmed} pagamento(s) confirmado(s).");
        return self::SUCCESS;
    }
}
