<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\BillingGatewayFactory;
use Illuminate\Console\Command;

class BillingGenerateCommand extends Command
{
    protected $signature = 'billing:generate';
    protected $description = 'Gera faturas para tenants com vencimento hoje';

    public function handle(): int
    {
        $billing = BillingGatewayFactory::make();

        $tenants = Tenant::where('status', 'active')
            ->whereDate('next_billing_date', '<=', today())
            ->with('plan')
            ->get();

        $this->info("Gateway ativo: " . BillingGatewayFactory::activeGateway());
        $this->info("Gerando faturas para {$tenants->count()} tenant(s)...");

        foreach ($tenants as $tenant) {
            try {
                $invoice = $billing->generateMonthlyInvoice($tenant);
                $this->line("  ✓ {$tenant->subdomain} - R$ {$invoice->amountFormatted()}");
            } catch (\Exception $e) {
                $this->error("  ✗ {$tenant->subdomain}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
